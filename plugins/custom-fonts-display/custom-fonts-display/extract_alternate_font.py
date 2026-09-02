# -*- coding: utf-8 -*-
"""
Builds standalone "alternate form" font files from a font whose alternate
letterforms are only reachable via a GSUB trigger sequence (e.g. Proto
Canaanite: type a base letter + Dagesh/Rafe, or the Phoenician-numeral
equivalents, per Yoram Gnat's own documentation — see chat).

Approach: keep the ENTIRE original font byte-for-byte — every glyph
outline, every table — and only replace the cmap table, so that typing an
ordinary letter (Hebrew or Phoenician) resolves DIRECTLY to the alternate
glyph instead of the regular one. No glyph outlines are touched, so
there's no glyph-renumbering or glyf/loca surgery to get wrong — this is
a "true subset" font's cheaper, lower-risk cousin, chosen deliberately
because Proto Canaanite is only 18KB: true subsetting would save a few
hundred bytes at best, not worth the much larger implementation and
correctness risk (see chat for the full comparison).

Two output files, since a single cmap can only point each codepoint at
ONE glyph:
  {name}-alt2.ttf — every Hebrew letter with a dagesh alternate
    (all 22, per the real font) is remapped to that alternate.
  {name}-alt3.ttf — the 9 Hebrew letters with a rafe alternate are
    remapped to THAT; the other 13 keep their regular glyph, so the font
    still reads as a complete, sensible alphabet.

Hebrew-only deliberately: the original font also lets you reach these
same alternates via the Phoenician-numeral triggers (see chat), but
these two output fonts only remap the Hebrew-block codepoints, since
that's the character set the rest of this project's display already
uses throughout (the abgad line, sample lines, filters, custom-text
field) — adding Phoenician-block access here with no corresponding
display convention anywhere else would be an inconsistency with no
real benefit. Codepoints outside the remapped set (including the
Phoenician letters, still present from the original font's own cmap)
are left completely untouched, still pointing at their regular glyphs.

PURE PYTHON STANDARD LIBRARY (via font_io.py) — no fontTools, no pip.
Every piece this depends on — load_tables()/build_sfnt() round-tripping
the real Proto Canaanite file unchanged, and build_cmap_table() resolving
exactly the intended glyph for both BMP and astral-plane codepoints — was
independently verified against fontTools before this was ever pointed at
a real extraction. See chat, not reproduced here for the same reason as
every other test-against-fontTools note in this project.

Usage:
    python3 extract_alternate_font.py --font /path/to/ProtoCanaanite.ttf \
        --out-dir /path/to/output
"""
import argparse
import struct
from pathlib import Path

import font_io

DAGESH = 0x05BC
RAFE = 0x05BF
PHOENICIAN_TEN = 0x10917
PHOENICIAN_TWENTY = 0x10918


def _reverse_map(glyph_map: dict) -> dict:
    reverse = {}
    for cp, gid in glyph_map.items():
        reverse.setdefault(gid, []).append(cp)
    return reverse


def find_trigger_mapping(path: Path, trigger_codepoints: tuple) -> dict:
    """Returns {base_codepoint: alt_glyph_id} for every GSUB ligature
    substitution whose trigger (every glyph in the sequence after the
    first, reverse-mapped back to codepoints) exactly matches
    `trigger_codepoints` — e.g. (DAGESH,) or (RAFE,)."""
    glyph_map = font_io.font_glyph_map(path)
    reverse_map = _reverse_map(glyph_map)

    features = font_io.font_gsub_features(path)
    result = {}
    for tag, info in features.items():
        for seq, lig_glyph in info["ligatures"].items():
            if len(seq) < 2:
                continue
            cps = []
            ok = True
            for gid in seq:
                c = reverse_map.get(gid)
                if not c:
                    ok = False
                    break
                cps.append(c[0])
            if not ok:
                continue
            base_cp, trigger_cps = cps[0], tuple(cps[1:])
            if trigger_cps == trigger_codepoints:
                result[base_cp] = lig_glyph
    return result


def build_name_table(family: str) -> bytes:
    """Minimal name table — just enough (Family/Subfamily/Unique ID/Full
    name/PostScript name, for both Mac and Windows platforms) for the
    font to be correctly and distinctly recognized by name. Rebuilt from
    scratch rather than patching the original font's name table, since a
    different-length family string would break that table's internal
    offset math if patched in place."""
    postscript_name = family.replace(" ", "")
    records_text = [
        (1, 0, 0, family), (1, 0, 0, "Regular"), (1, 0, 0, f"1.0;{postscript_name}"),
        (1, 0, 0, family), (1, 0, 0, postscript_name),
        (3, 1, 0x409, family), (3, 1, 0x409, "Regular"), (3, 1, 0x409, f"1.0;{postscript_name}"),
        (3, 1, 0x409, family), (3, 1, 0x409, postscript_name),
    ]
    name_ids = [1, 2, 3, 4, 6, 1, 2, 3, 4, 6]

    string_data = b""
    entries = []
    for (platform_id, encoding_id, language_id, text), name_id in zip(records_text, name_ids):
        encoded = text.encode("mac_roman") if platform_id == 1 else text.encode("utf-16-be")
        offset = len(string_data)
        string_data += encoded
        entries.append((platform_id, encoding_id, language_id, name_id, len(encoded), offset))

    count = len(entries)
    string_offset = 6 + 12 * count
    header = struct.pack(">HHH", 0, count, string_offset)
    record_bytes = b"".join(
        struct.pack(">HHHHHH", p, e, l, n, length, off) for p, e, l, n, length, off in entries
    )
    return header + record_bytes + string_data


def build_variant(path: Path, remap: dict, new_family: str) -> bytes:
    """Copies every table from the original font unchanged except cmap
    (rebuilt with `remap` overriding the original mapping) and name
    (rebuilt with `new_family`, so this reads as a genuinely distinct
    font rather than colliding with the original)."""
    tables = font_io.load_tables(path)
    original_map = font_io.font_glyph_map(path)

    new_map = dict(original_map)
    new_map.update(remap)

    tables[b"cmap"] = font_io.build_cmap_table(new_map)
    tables[b"name"] = build_name_table(new_family)
    return font_io.build_sfnt(tables)


def extract(path: Path, out_dir: Path, base_family: str):
    hebrew_form2 = find_trigger_mapping(path, (DAGESH,))
    hebrew_form3 = find_trigger_mapping(path, (RAFE,))

    print(f"form-2 (dagesh): {len(hebrew_form2)} Hebrew letter(s)")
    print(f"form-3 (rafe):   {len(hebrew_form3)} Hebrew letter(s)")

    out_dir.mkdir(parents=True, exist_ok=True)

    alt2_bytes = build_variant(path, hebrew_form2, f"{base_family} Alt2")
    alt2_path = out_dir / f"{base_family.replace(' ', '')}-alt2.ttf"
    alt2_path.write_bytes(alt2_bytes)
    print(f"wrote {alt2_path} ({len(alt2_bytes)} bytes)")

    alt3_bytes = build_variant(path, hebrew_form3, f"{base_family} Alt3")
    alt3_path = out_dir / f"{base_family.replace(' ', '')}-alt3.ttf"
    alt3_path.write_bytes(alt3_bytes)
    print(f"wrote {alt3_path} ({len(alt3_bytes)} bytes)")

    return alt2_path, alt3_path


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--font", type=Path, required=True)
    ap.add_argument("--out-dir", type=Path, required=True)
    ap.add_argument("--family", default="ProtoCanaanite", help="base family name for the output fonts")
    args = ap.parse_args()
    extract(args.font, args.out_dir, args.family)


if __name__ == "__main__":
    main()