# -*- coding: utf-8 -*-
"""
Computes, per font, which characters in the abgad + sample-script strings
that font's own cmap table doesn't actually contain, and writes the
result into each font's `missingChars` field in fonts.json — the sole
source of truth for both the "Missing Characters" detail line AND the
per-character gray-out on the abgad/sample lines (see
cfd_missing_codepoint_set() in custom-fonts-display.php 0.9.0+). An
earlier version also stored a `coverage` field of positional true/false
arrays for the gray-out specifically; that turned out to be fully
redundant with missingChars (PHP just checks each character's own
codepoint against the missing set instead), so this version retires it
and deletes it from any font that still has it from an earlier run.

PURE PYTHON STANDARD LIBRARY ONLY — no fontTools, no pip install, nothing
beyond `python3` itself (font_io.py, imported below, is the same). See
font_io.py's docstring for why (WOFF1/zlib vs. WOFF2/Brotli).

SAMPLE_STRINGS_VERSION: bump this — and CFD_SAMPLE_STRINGS_VERSION in
custom-fonts-display.php — every time ABGAD or SAMPLE_LINES changes below.
Coverage arrays are positional: inserting a character in the middle of a
string shifts every index after it, silently mis-coloring old data that
was computed against the previous string. The version number is a tripwire
for exactly that: the plugin checks it before trusting any coverage data,
and falls back to plain (unflagged) rendering on a mismatch rather than
mis-flagging characters — so forgetting to re-run this script after an
edit fails safe instead of failing silently wrong.

Also computes, per font, straight from the font file itself:
- `scriptTags`: ["hebrew-latin"] if the font has the full basic Latin
  alphabet, else ["hebrew-only"] — powers the plugin's Script filter.
- `version`/`versionDate`: read from the font's own name-table nameID 5
  ("Version string"), the same field Windows Explorer's font Properties
  dialog surfaces. Fills in ONLY when the existing field is empty, unless
  you pass --force-version — so re-running this never silently overwrites
  hand-curated data you trust more than what's embedded in a given font
  file. For a brand-new font you're onboarding with version left blank in
  fonts.json, this fills it in automatically.

Usage:
    python3 generate_coverage.py \
        --fonts-json data/fonts.json \
        --fonts-dir  /path/to/local/mirror/of/wp-content/uploads/fonts

`--fonts-dir` should mirror the {family}/{family}.woff layout already
used on your server — copy /uploads/fonts/ down locally, point
--fonts-dir at it, done. See --inspect below for troubleshooting a
specific font that doesn't process cleanly.
"""
import argparse
import json
import re
import struct
import sys
import unicodedata
from pathlib import Path

import font_io

# Bump alongside CFD_SAMPLE_STRINGS_VERSION in custom-fonts-display.php
# whenever ABGAD or SAMPLE_LINES changes — see module docstring.
SAMPLE_STRINGS_VERSION = 10

# Must match CFD_ABGAD and cfd_sample_lines() in custom-fonts-display.php
# exactly — coverage arrays are positional, so any drift here silently
# mis-colors glyphs (see SAMPLE_STRINGS_VERSION above).
ABGAD = ("\u05d0 \ufb21 \u05d1\u05f3 \u05d2 \u05d3 \ufb22 \u05d4 \ufb23 \u05d5 \u05d6 \u05d7 \u05d8 "
         "\u05d9 \u05ef \u05db \u05db\u05c7 \ufb24 \u05da \u05dc \ufb25 \u05de\u05be\u05dd \ufb26 "
         "\u05e0 \u05c6 \u05df \u05e1 \ufb20 \u05e2 \u05e4 \u05e3 \u05e6 \u05e5 \u05e7\u05f4 "
         "\u05e8 \ufb27 \u05e9 \u05ea \ufb28\u05c3")

SAMPLE_LINES = {
    'Numerals & Mathematical Operators': '№ ∅ ☉ 0 ¼ ½ ¾ 1 2 3 4 5 6 7 8 9 Ⅰ Ⅱ Ⅲ Ⅳ Ⅴ Ⅵ Ⅶ Ⅷ Ⅸ Ⅹ Ⅼ Ⅽ Ⅾ Ⅿ ℸ ℷ ℶ ℵ ∞ ≈ ﬩ × ° ≠ ₪',
    'Punctuation': '« » “ ⹂ „ ” ‽ ; : ⸿ § ¶ ☜ ☞ ☝ ☟ ◦ · ▪ ⹝ ⸗ — – ‒ ⁂ ◌ ❦ ❧',
    'Miscellaneous Symbols': '✍︎ 👂︎ 💬︎ 🧏︎ 🗣︎ 🤦︎ 🧎︎ 🧘︎ ☠︎',
    'File Symbols': '🗀︎ 🕮︎ 📖︎ 📜︎ 📃︎ 🗍︎ 📚︎ 🗎︎ 🗐︎',
    'Religious Signifiers': '🖖︎ 🪬︎ ✡︎ 🔯︎ 🟌︎ ꙳︎ 🕍︎ 🕎︎ 🧿︎ ⌘︎ ☥︎ ⚕︎ ☤︎ ⚚︎ ☬︎ ☸︎ ֍︎ ֎︎ ☩︎ ۞︎ ﷻ︎ ☯︎ 🔥︎ ♛︎ ⚡︎',
    'Meteorology, Astronomy, and Angelology': '☁︎ ⛈︎ ☔︎ 🌈︎ 🌀︎ ☄︎ 🪐︎ 🌌︎ 🎡︎ 🌐︎ 🗺︎ ☀︎ 🌑︎ 🌒︎ 🌓︎ 🌔︎ 🌕︎ 🌖︎ 🌗︎ 🌘︎ ☽︎ ☾︎ ☿︎ 🌟︎ ♁︎ ⚔︎ ♃︎ ♄︎ ♅︎ ♆︎ ♇︎ ♈︎ ♉︎ ♊︎ ♋︎ ♌︎ ♍︎ ♎︎ ♏︎ ♐︎ ♑︎ ♒︎ ♓︎ ⛎︎',
    'Gender Symbols': '⚲︎ ♀︎ ♂︎ ⚥︎ ⚧︎',
    'Latin (simple)': 'Aa Bb Cc Dd Ee Ff Gg Hh Ii Jj Kk Ll Mm Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz',
    'Latin (extended)': 'Ăă Ââ Ḇḇ Éé Èè Ɛɛ Əə Ȝȝ Ŋŋ Ḥḥ ʰ Ḳḳ Ƙƙ Íí Ōō Šš Śś Ṭṭ þ Ūū Ẓẓ',
    'Yiddish': 'אַ אָ װ ױ יִ (יִ) ײ ײַ (ײַ) בֿ כֿ פֿ',
    'Ladino': 'ﭏ בﬞ גﬞ דﬞ זﬞ טﬞ פﬞ ףﬞ קﬞ שﬞ',
}



_VERSION_NUM_RE = re.compile(r"[Vv]ersion\s+([0-9]+\.[0-9]+(?:\.[0-9]+)?)")
_LEADING_NUM_RE = re.compile(r"^\s*([0-9]+\.[0-9]+(?:\.[0-9]+)?)")
_ISO_DATE_RE = re.compile(r"(\d{4}-\d{2}-\d{2})")
_YEAR_RE = re.compile(r"(19|20)\d{2}")


def parse_version_string(raw: str):
    """'Version 2.001;PS 002.001;hotconv 1.0.70;...' -> ('2.001', None)
    '1.10;2019-03-02' -> ('1.10', '2019-03-02')
    Best-effort: some fonts embed no date at all, in which case the
    second element is None — that's expected, not an error."""
    version = None
    m = _VERSION_NUM_RE.search(raw)
    if m:
        version = m.group(1)
    else:
        m2 = _LEADING_NUM_RE.match(raw)
        if m2:
            version = m2.group(1)

    date = None
    dm = _ISO_DATE_RE.search(raw)
    if dm:
        date = dm.group(1)
    else:
        ym = _YEAR_RE.search(raw)
        if ym:
            date = ym.group(0)

    return version, date


def _codepoints_of(text: str) -> set:
    return {ord(c) for c in text if c != " "}


def script_tags_for(codepoints: set) -> list:
    """Every font in this catalogue supports Hebrew by definition, so
    that's not worth its own tag. Every axis here is derived the same
    way — full coverage of a specific named sample line, not a
    separately-maintained constant — so a line's content is the single
    source of truth for both what's displayed and what's required for
    its tag. "Latin (simple)" happens to be exactly the 52 basic A-Za-z
    letters, same as the old hardcoded BASIC_LATIN_LETTERS check this
    replaces. Multiple tags can apply at once — a font can be both
    "hebrew-latin" and "yiddish", for instance."""
    tags = ["hebrew-latin"] if _codepoints_of(SAMPLE_LINES["Latin (simple)"]) <= codepoints else ["hebrew-only"]
    if _codepoints_of(SAMPLE_LINES["Latin (extended)"]) <= codepoints:
        tags.append("latin-extended")
    if _codepoints_of(SAMPLE_LINES["Yiddish"]) <= codepoints:
        tags.append("yiddish")
    if _codepoints_of(SAMPLE_LINES["Ladino"]) <= codepoints:
        tags.append("ladino")
    return tags


SCRIPT_TAG_TITLES = {
    "hebrew-latin": "Latin (simple)",
    "hebrew-only": "Hebrew only",
    "latin-extended": "Latin (extended)",
    "yiddish": "Yiddish",
    "ladino": "Ladino",
}


def missing_chars_for(codepoints: set, text: str, seen: set) -> list:
    """Character/codepoint/name for each char in `text` NOT in `codepoints`
    — this is what powers the "Missing Characters" line the plugin
    renders, AND (as of schemaVersion 0.9.0) the only data source for
    per-character gray-out too — see cfd_missing_codepoint_set() in
    custom-fonts-display.php. `seen` is shared across all calls for one
    font (abgad + every sample line) so a codepoint appearing in more
    than one string is only reported once, not once per string.
    unicodedata.name() is stdlib — no external character-name database
    needs fetching."""
    out = []
    for ch in text:
        cp = ord(ch)
        # variation selectors (VS15 U+FE0E used for text/line-art
        # presentation, per the site's own convention of following an
        # emoji entity with &#xFE0E;) are invisible selectors, not glyphs
        # — a font lacking one in its cmap has no visible-rendering
        # consequence, so don't clutter the missing-characters list with it
        if cp in codepoints or ch == " " or cp in seen or 0xFE00 <= cp <= 0xFE0F:
            continue
        seen.add(cp)
        try:
            name = unicodedata.name(ch)
        except ValueError:
            name = None  # unassigned/unnamed codepoint — rare here
        out.append({"char": ch, "codepoint": f"U+{cp:04X}", "name": name})
    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--fonts-json", type=Path)
    ap.add_argument("--fonts-dir", type=Path)
    ap.add_argument("--only", help="comma-separated slugs to (re)compute; default: all")
    ap.add_argument("--inspect", type=Path, metavar="FONT_FILE",
                     help="diagnostic mode: dump this font's cmap subtables "
                          "(platform/encoding/format) and exit — no fonts.json needed")
    ap.add_argument("--force-version", action="store_true",
                     help="overwrite existing version/versionDate fields from the font's own "
                          "name table (nameID 5), not just fill in when empty. Off by default "
                          "so re-running this never clobbers hand-curated data you trust more "
                          "than what's embedded in the font file.")
    args = ap.parse_args()

    if args.inspect:
        inspect_font(args.inspect)
        return

    if not args.fonts_json or not args.fonts_dir:
        ap.error("--fonts-json and --fonts-dir are required unless using --inspect")

    data = json.loads(args.fonts_json.read_text(encoding="utf-8"))
    only = set(args.only.split(",")) if args.only else None

    updated, skipped, warnings, version_filled = 0, [], [], []
    for font in data["fonts"]:
        if only and font["slug"] not in only:
            continue

        font_path, warning = font_io.find_font_file(args.fonts_dir, font["family"])
        if warning:
            warnings.append((font["slug"], warning))
        if font_path is None:
            skipped.append((font["slug"], "no .woff/.ttf/.otf found"))
            continue

        try:
            codepoints = font_io.font_codepoints(font_path)
        except Exception as e:
            skipped.append((font["slug"], f"{font_path.name}: {e}"))
            continue

        if not codepoints:
            skipped.append((font["slug"], f"{font_path.name}: no usable cmap subtable found "
                                           f"(run --inspect {font_path} to see what it does have)"))
            continue

        # `missingChars` is now a dict keyed by sample-line label (plus
        # "abgad" for the main line) rather than one flat combined list —
        # lets the plugin show missing characters per-line, on click,
        # instead of one giant list before "Test your own text" (0.13.0+).
        # Each line gets its own dedup set now (a codepoint missing from
        # two different lines is reported under both, since each line's
        # report needs to be independently complete).
        font["missingChars"] = {"abgad": missing_chars_for(codepoints, ABGAD, set())}
        for label, text in SAMPLE_LINES.items():
            font["missingChars"][label] = missing_chars_for(codepoints, text, set())
        font.pop("coverage", None)  # retire the old positional-boolean field, if present from an earlier run
        font["scriptTags"] = script_tags_for(codepoints)

        # Version, from the font's own name table (nameID 5) — fills in
        # only when the existing field is empty, unless --force-version.
        # See parse_version_string()'s docstring for the format handled.
        try:
            names = font_io.font_name_table(font_path)
        except Exception:
            names = {}
        raw_version = names.get(font_io.NAME_ID_VERSION)
        if raw_version:
            extracted_version, extracted_date = parse_version_string(raw_version)
            if extracted_version and (args.force_version or not font.get("version")):
                if font.get("version") != extracted_version:
                    version_filled.append((font["slug"], font.get("version") or "(empty)", extracted_version))
                font["version"] = extracted_version
            if extracted_date and (args.force_version or not font.get("versionDate")):
                font["versionDate"] = extracted_date

        updated += 1

    data.setdefault("_meta", {})["sampleStringsVersion"] = SAMPLE_STRINGS_VERSION
    data["_meta"]["scriptTagTitles"] = SCRIPT_TAG_TITLES
    args.fonts_json.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"coverage computed for {updated} font(s); sampleStringsVersion set to {SAMPLE_STRINGS_VERSION}")
    if version_filled:
        print(f"\n{len(version_filled)} version field(s) {'overwritten' if args.force_version else 'filled in'} from the font file itself:")
        for slug, old, new in version_filled:
            print(f"  {slug}: {old!r} -> {new!r}")
    if warnings:
        print(f"\n{len(warnings)} processed via a case-insensitive fallback — fix fonts.json:", file=sys.stderr)
        for slug, msg in warnings:
            print(f"  {slug}: {msg}", file=sys.stderr)
    if skipped:
        print(f"\nskipped {len(skipped)}:", file=sys.stderr)
        for slug, reason in skipped:
            print(f"  {slug}: {reason}", file=sys.stderr)


def inspect_font(path: Path):
    if not path.exists():
        print(f"'{path}' does not exist according to Python — this is worth trusting over a "
              f"visual read of the path, since lookalike characters or stray whitespace in a "
              f"filename are easy to miss by eye but exact-match here.")
        parent = path.parent
        if parent.is_dir():
            print(f"\nActual contents of {parent}:")
            for entry in sorted(parent.iterdir()):
                print(f"  {entry.name!r}")
        else:
            print(f"\n{parent} doesn't exist either. Contents of {parent.parent}:")
            if parent.parent.is_dir():
                for entry in sorted(parent.parent.iterdir()):
                    print(f"  {entry.name!r}")
        return

    data = path.read_bytes()
    print(f"{path.name}: {len(data)} bytes, header: {data[:16].hex()}  (first 4 bytes as text: {data[:4]!r})")
    if data[:4] not in (b"wOFF", b"wOF2", b"\x00\x01\x00\x00", b"OTTO", b"true", b"typ1"):
        print("^ that signature doesn't match WOFF1, WOFF2, or raw TTF/OTF — this file likely "
              "isn't a font this script (or any font loader) can read as-is. Common culprits: "
              "an .eot file saved with a .woff extension, a corrupted/truncated download, or an "
              "HTML error page saved in place of the real font.")
        return

    try:
        tables = font_io.load_tables(path)
    except Exception as e:
        print(f"signature looked valid but parsing still failed: {e}")
        return
    print(f"{len(tables)} table(s): {sorted(t.decode('latin-1') for t in tables.keys())}")
    if b"cmap" not in tables:
        print("no 'cmap' table at all — this font can't drive coverage detection")
        return
    cmap_data = tables[b"cmap"]
    version, num_tables = struct.unpack(">HH", cmap_data[:4])
    print(f"cmap: version={version} numTables={num_tables}")
    pos = 4
    for i in range(num_tables):
        platform_id, encoding_id, offset = struct.unpack(">HHI", cmap_data[pos:pos + 8])
        pos += 8
        fmt = struct.unpack(">H", cmap_data[offset:offset + 2])[0]
        supported = "supported" if fmt in font_io.FORMAT_PARSERS else "NOT SUPPORTED by this script"
        print(f"  subtable {i}: platform={platform_id} encoding={encoding_id} "
              f"format={fmt} offset={offset}  [{supported}]")


if __name__ == "__main__":
    main()