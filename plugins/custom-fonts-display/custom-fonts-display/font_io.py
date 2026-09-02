# -*- coding: utf-8 -*-
"""
Shared, pure-stdlib font-reading code: opens a .woff/.ttf/.otf file and
returns the set of Unicode codepoints its cmap table actually covers.

Used by generate_coverage.py (per-character missing-glyph detection) and
estimate_unicode_version.py (Unicode-version inference from real cmap
coverage). Split out here so both scripts share one tested implementation
instead of drifting apart as separate copies.

No dependencies beyond the Python standard library — see generate_coverage.py's
module docstring for why (no pip access on shared hosting). Reads WOFF 1.0
or raw TTF/OTF; NOT WOFF2 (needs Brotli, a C extension unavailable without
pip). Verified against fontTools' own cmap parsing on a synthetic test font
before this was ever shipped — see the chat history for that test, not
reproduced here since it needs fontTools as an oracle, which is exactly the
dependency this module exists to avoid requiring for actual use.
"""
import struct
import zlib
from pathlib import Path


# ------------------------------------------------------------------
# Container parsing: WOFF 1.0 and raw sfnt (TTF/OTF) — both give back
# a {tag: bytes} dict of decompressed table data. WOFF2 is deliberately
# NOT handled.
# ------------------------------------------------------------------

def _read_woff1(data: bytes) -> dict:
    (signature, flavor, length, num_tables, reserved, total_sfnt_size,
     major, minor, meta_off, meta_len, meta_orig_len, priv_off, priv_len) = \
        struct.unpack(">4sIIHHIHHIIIII", data[:44])
    tables = {}
    pos = 44
    for _ in range(num_tables):
        tag, offset, comp_len, orig_len, checksum = struct.unpack(">4sIIII", data[pos:pos + 20])
        pos += 20
        raw = data[offset:offset + comp_len]
        tables[tag] = zlib.decompress(raw) if comp_len < orig_len else raw
    return tables


def _read_sfnt(data: bytes) -> dict:
    sfnt_version, num_tables, search_range, entry_selector, range_shift = \
        struct.unpack(">4sHHHH", data[:12])
    tables = {}
    pos = 12
    for _ in range(num_tables):
        tag, checksum, offset, length = struct.unpack(">4sIII", data[pos:pos + 16])
        pos += 16
        tables[tag] = data[offset:offset + length]
    return tables


def load_tables(path: Path) -> dict:
    data = path.read_bytes()
    if data[:4] == b"wOFF":
        return _read_woff1(data)
    if data[:4] == b"wOF2":
        raise ValueError("WOFF2 needs Brotli (not available without pip) — use the .woff instead")
    return _read_sfnt(data)  # raw TTF (0x00010000) or OTF ('OTTO')


# ------------------------------------------------------------------
# cmap parsing — formats 0, 4, 6, 12 cover essentially every font
# you'll encounter here. (2, 8, 10, 13, 14 are for CJK/variation-
# selector edge cases this catalogue doesn't need.)
# ------------------------------------------------------------------

SUBTABLE_PREFERENCE = [
    (3, 10), (0, 6), (0, 4),   # full-Unicode (BMP + supplementary) first
    (3, 1), (0, 3), (0, 2), (0, 1), (0, 0),  # BMP-only Unicode
    (1, 0),                    # Mac Roman, last resort
]


def _parse_format0(data: bytes, off: int) -> dict:
    glyph_ids = data[off + 6:off + 6 + 256]
    return {c: glyph_ids[c] for c in range(256) if glyph_ids[c] != 0}


def _parse_format4(data: bytes, off: int) -> dict:
    fmt, length, language, seg_x2 = struct.unpack(">HHHH", data[off:off + 8])
    seg_count = seg_x2 // 2
    pos = off + 8 + 6  # skip searchRange/entrySelector/rangeShift
    end_codes = struct.unpack(f">{seg_count}H", data[pos:pos + seg_x2]); pos += seg_x2 + 2  # + reservedPad
    start_codes = struct.unpack(f">{seg_count}H", data[pos:pos + seg_x2]); pos += seg_x2
    id_deltas = struct.unpack(f">{seg_count}h", data[pos:pos + seg_x2]); pos += seg_x2
    id_range_offsets_pos = pos
    id_range_offsets = struct.unpack(f">{seg_count}H", data[pos:pos + seg_x2])

    covered = {}
    for i in range(seg_count):
        start, end = start_codes[i], end_codes[i]
        if start == 0xFFFF and end == 0xFFFF:
            continue
        delta = id_deltas[i]
        range_offset = id_range_offsets[i]
        for c in range(start, end + 1):
            if range_offset == 0:
                glyph_id = (c + delta) & 0xFFFF
            else:
                addr = id_range_offsets_pos + i * 2 + range_offset + 2 * (c - start)
                if addr + 2 > len(data):
                    continue
                glyph_id = struct.unpack(">H", data[addr:addr + 2])[0]
                if glyph_id != 0:
                    glyph_id = (glyph_id + delta) & 0xFFFF
            if glyph_id != 0:
                covered[c] = glyph_id
    return covered


def _parse_format6(data: bytes, off: int) -> dict:
    fmt, length, language, first, count = struct.unpack(">HHHHH", data[off:off + 10])
    glyph_ids = struct.unpack(f">{count}H", data[off + 10:off + 10 + count * 2])
    return {first + i: g for i, g in enumerate(glyph_ids) if g != 0}


def _parse_format12(data: bytes, off: int) -> dict:
    fmt, reserved, length, language, n_groups = struct.unpack(">HHIII", data[off:off + 16])
    covered = {}
    pos = off + 16
    for _ in range(n_groups):
        start, end, start_glyph = struct.unpack(">III", data[pos:pos + 12])
        pos += 12
        for c in range(start, end + 1):
            covered[c] = start_glyph + (c - start)
    return covered


FORMAT_PARSERS = {0: _parse_format0, 4: _parse_format4, 6: _parse_format6, 12: _parse_format12}


def cmap_glyph_map(cmap_data: bytes) -> dict:
    """{codepoint: glyphID} for the font's best cmap subtable — same
    subtable-selection logic as cmap_codepoints(), which now just derives
    its set from this dict's keys."""
    version, num_tables = struct.unpack(">HH", cmap_data[:4])
    records = []
    pos = 4
    for _ in range(num_tables):
        platform_id, encoding_id, offset = struct.unpack(">HHI", cmap_data[pos:pos + 8])
        pos += 8
        records.append((platform_id, encoding_id, offset))

    chosen = None
    for pref in SUBTABLE_PREFERENCE:
        chosen = next((r for r in records if (r[0], r[1]) == pref), None)
        if chosen:
            break
    if chosen is None and records:
        chosen = records[0]
    if chosen is None:
        return {}

    _, _, offset = chosen
    fmt = struct.unpack(">H", cmap_data[offset:offset + 2])[0]
    parser = FORMAT_PARSERS.get(fmt)
    if parser is None:
        return {}  # unsupported subtable format — rare for these fonts
    return parser(cmap_data, offset)


def cmap_codepoints(cmap_data: bytes) -> set:
    return set(cmap_glyph_map(cmap_data).keys())


def font_codepoints(path: Path) -> set:
    """The full set of Unicode codepoints this font's cmap covers — not
    just characters in some sample string. Used directly by
    estimate_unicode_version.py; generate_coverage.py narrows this down
    to just the characters in ABGAD/SAMPLE_LINES via coverage_for()."""
    tables = load_tables(path)
    if b"cmap" not in tables:
        return set()
    return cmap_codepoints(tables[b"cmap"])


def font_glyph_map(path: Path) -> dict:
    """{codepoint: glyphID} for this font's best cmap subtable. Needed
    (unlike the plain codepoint set) to reverse-map a GSUB alternate's
    glyph ID back to the character it belongs to — see gsub_alternates()
    in inspect_alternates.py."""
    tables = load_tables(path)
    if b"cmap" not in tables:
        return {}
    return cmap_glyph_map(tables[b"cmap"])


# ------------------------------------------------------------------
# GSUB (Glyph Substitution) — alternate-glyph discovery.
#
# Only lookup types 1 (Single Substitution) and 3 (Alternate
# Substitution) are parsed — those are the two mechanisms fonts use for
# "here is another form of this same glyph" (stylistic sets ssXX,
# stylistic alternates salt, access-all-alternates aalt, character
# variants cvXX). Other GSUB lookup types (ligatures, contextual/chaining
# substitution, reverse chaining) do different jobs — automatic
# script-shaping behavior, not a user-selectable alternate — and aren't
# what "does this font have alternate letterforms" is asking about, so
# they're deliberately not parsed here.
#
# Verified against fontTools' own GSUB interpretation on a synthetic
# test font with real ss01 (single) and aalt (multiple-choice) features
# before this was ever used for real — see chat, not reproduced here
# since it needs fontTools as a test oracle, which is exactly the
# dependency this module exists to avoid requiring for actual use.
# ------------------------------------------------------------------

def _parse_coverage(data: bytes, off: int) -> list:
    """Returns glyph IDs in coverage-index order — index i here lines up
    with index i in whatever list (substitute glyphs, alternate sets)
    the coverage table is paired with."""
    fmt = struct.unpack(">H", data[off:off + 2])[0]
    if fmt == 1:
        count = struct.unpack(">H", data[off + 2:off + 4])[0]
        return list(struct.unpack(f">{count}H", data[off + 4:off + 4 + count * 2]))
    if fmt == 2:
        range_count = struct.unpack(">H", data[off + 2:off + 4])[0]
        glyphs = []
        pos = off + 4
        for _ in range(range_count):
            start, end, start_cov = struct.unpack(">HHH", data[pos:pos + 6])
            pos += 6
            glyphs.extend(range(start, end + 1))
        return glyphs
    return []


def _parse_single_subst(data: bytes, off: int) -> dict:
    """GSUB LookupType 1: {glyphID: altGlyphID}."""
    fmt = struct.unpack(">H", data[off:off + 2])[0]
    cov_off = struct.unpack(">H", data[off + 2:off + 4])[0]
    coverage = _parse_coverage(data, off + cov_off)
    result = {}
    if fmt == 1:
        delta = struct.unpack(">h", data[off + 4:off + 6])[0]
        for g in coverage:
            result[g] = (g + delta) & 0xFFFF
    elif fmt == 2:
        count = struct.unpack(">H", data[off + 4:off + 6])[0]
        subs = struct.unpack(f">{count}H", data[off + 6:off + 6 + count * 2])
        for g, s in zip(coverage, subs):
            result[g] = s
    return result


def _parse_alternate_subst(data: bytes, off: int) -> dict:
    """GSUB LookupType 3: {glyphID: [altGlyphID, ...]} — the "choice of
    several alternates" mechanism (what aalt-style UI pickers use)."""
    cov_off = struct.unpack(">H", data[off + 2:off + 4])[0]
    coverage = _parse_coverage(data, off + cov_off)
    count = struct.unpack(">H", data[off + 4:off + 6])[0]
    set_offsets = struct.unpack(f">{count}H", data[off + 6:off + 6 + count * 2])
    result = {}
    for g, set_off in zip(coverage, set_offsets):
        alt_off = off + set_off
        glyph_count = struct.unpack(">H", data[alt_off:alt_off + 2])[0]
        alts = struct.unpack(f">{glyph_count}H", data[alt_off + 2:alt_off + 2 + glyph_count * 2])
        result[g] = list(alts)
    return result


def _parse_ligature_subst(data: bytes, off: int) -> dict:
    """GSUB LookupType 4: {(firstGlyphID, componentGlyphID, ...): ligatureGlyphID}.
    This is the mechanism behind ordinary text ligatures (f+i -> fi), but
    fonts also use it for "type this real, independently-existing
    sequence of characters to get an alternate form of the first one" —
    e.g. a base letter followed by a specific combining mark, where the
    mark is effectively consumed as a trigger rather than displayed on
    its own. Each key is the FULL input glyph sequence (first glyph plus
    however many components follow it) that triggers the substitution."""
    cov_off = struct.unpack(">H", data[off + 2:off + 4])[0]
    coverage = _parse_coverage(data, off + cov_off)  # first glyph of each sequence
    set_count = struct.unpack(">H", data[off + 4:off + 6])[0]
    set_offsets = struct.unpack(f">{set_count}H", data[off + 6:off + 6 + set_count * 2])
    result = {}
    for first_glyph, set_off in zip(coverage, set_offsets):
        ligset_off = off + set_off
        lig_count = struct.unpack(">H", data[ligset_off:ligset_off + 2])[0]
        lig_offsets = struct.unpack(f">{lig_count}H", data[ligset_off + 2:ligset_off + 2 + lig_count * 2])
        for lig_rel in lig_offsets:
            lig_off = ligset_off + lig_rel
            lig_glyph, comp_count = struct.unpack(">HH", data[lig_off:lig_off + 4])
            components = ()
            if comp_count > 1:
                components = struct.unpack(
                    f">{comp_count - 1}H", data[lig_off + 4:lig_off + 4 + (comp_count - 1) * 2]
                )
            result[(first_glyph,) + components] = lig_glyph
    return result


def parse_gsub(gsub_data: bytes) -> dict:
    """Returns, per feature tag:
        {
          "uiNameID": int|None,
          "alternates": {glyphID: [altGlyphID, ...]},           # types 1/3
          "ligatures": {(glyphID, ...): ligatureGlyphID, ...},  # type 4
        }
    `uiNameID`, when present (stylistic-set ssXX / character-variant cvXX
    features only), is a nameID into the font's `name` table — resolve
    it with parse_name_table() for a human-readable feature name like
    "Archaic forms" instead of just the four-letter tag.

    A feature tag can appear as more than one FeatureRecord in a real
    font (once per script/language it applies to) — all of them are
    merged into one entry here rather than the later one silently
    overwriting the earlier, which an earlier version of this function
    got wrong until tested against a real font that actually does this
    (Yoram Gnat's Proto Canaanite — its `ccmp` feature has two records).
    """
    major, minor = struct.unpack(">HH", gsub_data[:4])
    script_off, feature_off, lookup_off = struct.unpack(">HHH", gsub_data[4:10])

    # -- LookupList: parse every lookup once, keyed by index --
    lookup_count = struct.unpack(">H", gsub_data[lookup_off:lookup_off + 2])[0]
    lookup_offsets = struct.unpack(
        f">{lookup_count}H", gsub_data[lookup_off + 2:lookup_off + 2 + lookup_count * 2]
    )
    lookups = {}  # index -> {"alternates": {...}, "ligatures": {...}}
    for i, rel_off in enumerate(lookup_offsets):
        lut_off = lookup_off + rel_off
        lookup_type, lookup_flag, subtable_count = struct.unpack(">HHH", gsub_data[lut_off:lut_off + 6])
        sub_offsets = struct.unpack(
            f">{subtable_count}H", gsub_data[lut_off + 6:lut_off + 6 + subtable_count * 2]
        )
        alternates, ligatures = {}, {}
        for sub_rel in sub_offsets:
            sub_off = lut_off + sub_rel
            actual_type, actual_off = lookup_type, sub_off
            if lookup_type == 7:  # Extension Substitution — transparently unwrap
                _, ext_type, ext_off = struct.unpack(">HHI", gsub_data[sub_off:sub_off + 8])
                actual_type, actual_off = ext_type, sub_off + ext_off
            if actual_type == 1:
                for g, alt in _parse_single_subst(gsub_data, actual_off).items():
                    alternates.setdefault(g, []).append(alt)
            elif actual_type == 3:
                for g, alts in _parse_alternate_subst(gsub_data, actual_off).items():
                    alternates.setdefault(g, []).extend(alts)
            elif actual_type == 4:
                ligatures.update(_parse_ligature_subst(gsub_data, actual_off))
        lookups[i] = {"alternates": alternates, "ligatures": ligatures}

    # -- FeatureList: merge every FeatureRecord's lookups into its tag's entry --
    feature_count = struct.unpack(">H", gsub_data[feature_off:feature_off + 2])[0]
    pos = feature_off + 2
    features = {}
    for _ in range(feature_count):
        tag = gsub_data[pos:pos + 4].decode("latin-1")
        feat_rel = struct.unpack(">H", gsub_data[pos + 4:pos + 6])[0]
        pos += 6
        feat_off = feature_off + feat_rel
        params_off, lookup_idx_count = struct.unpack(">HH", gsub_data[feat_off:feat_off + 4])
        indices = struct.unpack(
            f">{lookup_idx_count}H", gsub_data[feat_off + 4:feat_off + 4 + lookup_idx_count * 2]
        )
        ui_name_id = None
        if params_off != 0 and tag[:2] in ("ss", "cv"):
            # FeatureParamsStylisticSet / FeatureParamsCharacterVariant
            # both start with version(2) + uiNameID(2) at this offset
            try:
                _, ui_name_id = struct.unpack(">HH", gsub_data[feat_off + params_off:feat_off + params_off + 4])
            except struct.error:
                pass

        entry = features.setdefault(tag, {"uiNameID": None, "alternates": {}, "ligatures": {}})
        if ui_name_id is not None:
            entry["uiNameID"] = ui_name_id  # any record's name is as good as another
        for idx in indices:
            lk = lookups.get(idx, {"alternates": {}, "ligatures": {}})
            for g, alts in lk["alternates"].items():
                bucket = entry["alternates"].setdefault(g, [])
                for a in alts:
                    if a not in bucket:
                        bucket.append(a)
            entry["ligatures"].update(lk["ligatures"])

    # drop any feature tag that ended up empty (e.g. a required-but-
    # unrelated feature that only ever referenced other lookup types)
    return {tag: e for tag, e in features.items() if e["alternates"] or e["ligatures"]}


def font_gsub_features(path: Path) -> dict:
    tables = load_tables(path)
    if b"GSUB" not in tables:
        return {}
    return parse_gsub(tables[b"GSUB"])


# ------------------------------------------------------------------
# sfnt WRITING — the mirror image of load_tables(), and a genuinely
# different category of work: everything above this point only reads
# existing, already-valid binary font data. This writes new binary font
# data from scratch, which means getting checksums and offsets exactly
# right ourselves, with nothing to fall back on if we don't. Round-trip
# tested (read a real font -> rebuild it unchanged -> confirm the
# rebuilt bytes are still a valid, correctly-parseable font, verified
# against fontTools independently) before this was ever used to build
# anything real — see chat, not reproduced here for the same reason as
# every other test-against-fontTools note in this file.
# ------------------------------------------------------------------

def _table_checksum(data: bytes) -> int:
    """OpenType table checksum: sum of the table's bytes, interpreted as
    big-endian uint32 words, zero-padded to a 4-byte boundary for this
    calculation only (the padding here isn't necessarily how the table
    ends up stored — build_sfnt() pads separately when laying out the
    actual file, though in practice both paddings are identical zero
    bytes to the same boundary)."""
    padded = data + b"\x00" * (-len(data) % 4)
    checksum = 0
    for i in range(0, len(padded), 4):
        checksum = (checksum + struct.unpack(">I", padded[i:i + 4])[0]) & 0xFFFFFFFF
    return checksum


def build_sfnt(tables: dict, sfnt_version: bytes = b"\x00\x01\x00\x00") -> bytes:
    """Reassembles {tag_bytes: data_bytes} into a valid sfnt (TTF) binary.
    `tables` keys must be 4-byte bytes objects (e.g. b"cmap"), matching
    what load_tables() itself returns, so a round-trip is just
    build_sfnt(load_tables(path)).

    Handles table-directory construction (sorted by tag, per spec),
    4-byte padding between tables, per-table checksums, and the head
    table's checkSumAdjustment — which per spec must be computed with
    itself temporarily treated as 0 (both for its own directory-entry
    checksum AND for the whole-file checksum used to derive the real
    adjustment value), then patched into the final bytes afterward. This
    two-phase requirement is the single easiest thing to get subtly
    wrong when reassembling a font by hand — get it wrong and some
    renderers reject the file outright while others silently accept it,
    which is a much worse failure mode than a clean rejection.
    """
    tables = dict(tables)  # don't mutate the caller's dict
    if b"head" in tables:
        head_bytes = bytearray(tables[b"head"])
        head_bytes[8:12] = b"\x00\x00\x00\x00"  # checksumAdjustment, zeroed for now
        tables[b"head"] = bytes(head_bytes)

    tags = sorted(tables.keys())
    num_tables = len(tags)
    entry_selector = num_tables.bit_length() - 1 if num_tables > 0 else 0
    search_range = (1 << entry_selector) * 16
    range_shift = num_tables * 16 - search_range
    header = struct.pack(">4sHHHH", sfnt_version, num_tables, search_range, entry_selector, range_shift)

    offset = 12 + 16 * num_tables  # header + directory
    padded_tables = {}
    entries = []
    for tag in tags:
        data = tables[tag]
        padded = data + b"\x00" * (-len(data) % 4)
        padded_tables[tag] = padded
        entries.append((tag, _table_checksum(data), offset, len(data)))
        offset += len(padded)

    directory = b"".join(struct.pack(">4sIII", tag, checksum, off, length) for tag, checksum, off, length in entries)
    body = b"".join(padded_tables[tag] for tag in tags)
    font_bytes = header + directory + body

    if b"head" in tables:
        whole_font_checksum = _table_checksum(font_bytes)  # still has adjustment=0 in it here
        adjustment = (0xB1B0AFBA - whole_font_checksum) & 0xFFFFFFFF
        head_off = next(off for tag, _, off, _ in entries if tag == b"head")
        font_bytes = font_bytes[:head_off + 8] + struct.pack(">I", adjustment) + font_bytes[head_off + 12:]

    return font_bytes


def build_cmap_format4(mapping: dict) -> bytes:
    """One segment per codepoint (rather than merging contiguous runs
    into fewer, larger segments) — less space-efficient, but completely
    spec-valid and removes any chance of a range-merging bug for what's
    always a small character set in this project's actual use. BMP
    (<= 0xFFFF) codepoints only — astral ones need build_cmap_format12()."""
    bmp = {cp: gid for cp, gid in mapping.items() if cp < 0xFFFF}
    codepoints = sorted(bmp.keys())
    seg_count = len(codepoints) + 1  # +1 for the required terminal 0xFFFF segment

    end_codes = codepoints + [0xFFFF]
    start_codes = codepoints + [0xFFFF]
    deltas = []
    for cp in codepoints:
        d = (bmp[cp] - cp) % 65536
        if d > 32767:
            d -= 65536
        deltas.append(d)
    deltas.append(1)  # terminal segment's delta — value is irrelevant since it never matches a real char
    id_range_offsets = [0] * seg_count

    seg_x2 = seg_count * 2
    p2 = 1 << (seg_count.bit_length() - 1) if seg_count > 0 else 1
    search_range = 2 * p2
    entry_selector = p2.bit_length() - 1 if p2 > 0 else 0
    range_shift = seg_x2 - search_range

    body = struct.pack(">HHHH", seg_x2, search_range, entry_selector, range_shift)
    body += struct.pack(f">{seg_count}H", *end_codes)
    body += struct.pack(">H", 0)  # reservedPad
    body += struct.pack(f">{seg_count}H", *start_codes)
    body += struct.pack(f">{seg_count}h", *deltas)
    body += struct.pack(f">{seg_count}H", *id_range_offsets)

    length = 14 + len(body)
    return struct.pack(">HHH", 4, length, 0) + body  # format, length, language


def build_cmap_format12(mapping: dict) -> bytes:
    """One group per codepoint, same simplicity trade-off as format 4
    above. Covers the full range (BMP and astral both), unlike format 4."""
    codepoints = sorted(mapping.keys())
    body = b"".join(struct.pack(">III", cp, cp, mapping[cp]) for cp in codepoints)
    length = 16 + len(body)
    return struct.pack(">HHIII", 12, 0, length, 0, len(codepoints)) + body  # format,reserved,length,language,nGroups


def build_cmap_table(mapping: dict) -> bytes:
    """Full cmap table, two subtables — (3,1) format 4 for BMP, (3,10)
    format 12 for the full range — matching this module's own
    SUBTABLE_PREFERENCE, so a font built this way round-trips cleanly
    back through cmap_glyph_map() too."""
    format4 = build_cmap_format4(mapping)
    format12 = build_cmap_format12(mapping)

    header_size = 4 + 8 * 2  # version+numTables(4) + 2 EncodingRecords(8 each)
    format4_offset = header_size
    format12_offset = header_size + len(format4)

    header = struct.pack(">HH", 0, 2)
    records = struct.pack(">HHI", 3, 1, format4_offset) + struct.pack(">HHI", 3, 10, format12_offset)
    return header + records + format4 + format12


# ------------------------------------------------------------------

def find_font_file(fonts_dir: Path, family: str):
    """Returns (path, warning) — warning is None on an exact case match,
    or a message if a differently-cased match had to be used instead."""
    candidates = [fonts_dir / family / f"{family}{ext}" for ext in (".woff", ".ttf", ".otf")]
    for p in candidates:
        if p.exists():
            return p, None

    # exact match failed — try a case-insensitive scan of the family's
    # own subdirectory name, since a mismatch here also breaks the live
    # plugin's @font-face URL (it builds the same path from `family`).
    if not fonts_dir.is_dir():
        return None, None
    for child in fonts_dir.iterdir():
        if child.is_dir() and child.name.lower() == family.lower() and child.name != family:
            for ext in (".woff", ".ttf", ".otf"):
                for f in child.iterdir():
                    if f.is_file() and f.name.lower() == f"{family}{ext}".lower():
                        warning = (
                            f"fonts.json has family=\"{family}\" but the actual folder/file "
                            f"on disk is \"{child.name}/{f.name}\" — update fonts.json to match "
                            f"exactly, or the live plugin's @font-face URL for this font will 404 "
                            f"even though this script found it"
                        )
                        return f, warning
    return None, None


# ------------------------------------------------------------------
# name table — nameID 5 is the standard OpenType "Version string" field
# (what Windows Explorer's font Properties > Details > File description
# is generally reading, or a close cousin of it). nameID 1/2/4/6 are
# family/subfamily/full-name/PostScript-name; 8/9 are Manufacturer and
# Designer — i.e. what we've been hand-curating as `foundry` and
# `typographer` — and 13/14 are License description/URL. All of that is
# available here if useful later; generate_coverage.py currently only
# uses nameID 5 (version), non-destructively — see its own docstring.
# ------------------------------------------------------------------

NAME_ID_VERSION = 5
NAME_ID_FAMILY = 1
NAME_ID_SUBFAMILY = 2
NAME_ID_FULL_NAME = 4
NAME_ID_POSTSCRIPT_NAME = 6
NAME_ID_MANUFACTURER = 8
NAME_ID_DESIGNER = 9
NAME_ID_LICENSE_DESCRIPTION = 13
NAME_ID_LICENSE_URL = 14


def parse_name_table(data: bytes) -> dict:
    """Returns {nameID: str}, preferring the Windows/US-English (platform
    3, language 0x409) record for each nameID when present, falling back
    to whatever's available otherwise. Format-1 name tables (with
    lang-tag records) aren't specially handled — the extra lang-tag
    records are simply ignored, which is fine since we only care about
    the standard numbered fields, not custom language variants."""
    fmt, count, string_offset = struct.unpack(">HHH", data[:6])
    records = []
    pos = 6
    for _ in range(count):
        platform_id, encoding_id, language_id, name_id, length, offset = \
            struct.unpack(">HHHHHH", data[pos:pos + 12])
        pos += 12
        records.append((platform_id, encoding_id, language_id, name_id, length, offset))

    def decode(platform_id, raw):
        try:
            if platform_id == 1:  # Macintosh — typically Mac Roman, single-byte
                return raw.decode("mac_roman")
            return raw.decode("utf-16-be")  # Windows (3) or Unicode (0) platforms
        except UnicodeDecodeError:
            return None

    preferred, fallback = {}, {}
    for platform_id, encoding_id, language_id, name_id, length, offset in records:
        raw = data[string_offset + offset: string_offset + offset + length]
        text = decode(platform_id, raw)
        if text is None:
            continue
        if platform_id == 3 and language_id == 0x409:  # Windows, US English
            preferred[name_id] = text
        elif name_id not in fallback:
            fallback[name_id] = text

    result = dict(fallback)
    result.update(preferred)
    return result


def font_name_table(path: Path) -> dict:
    tables = load_tables(path)
    if b"name" not in tables:
        return {}
    return parse_name_table(tables[b"name"])