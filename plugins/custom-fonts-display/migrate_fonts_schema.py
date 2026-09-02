# -*- coding: utf-8 -*-
"""
One-time schema migration for fonts.json (schemaVersion 0.4.x -> 0.7.0):

1. `flags` -> `positioningErrors`: entries that already say "positioning: X"
   become plain "X" in a renamed array — this is the one category of
   note that genuinely can't be derived programmatically (see chat).
   Entries that say "missing: X" are DROPPED entirely: as of
   generate_coverage.py's `missingChars` field, that information is now
   computed from real per-character cmap coverage instead of hand-typed
   text, so it would just be stale duplication to keep both.

2. `style` -> `styleTags`: the free-text `style` field (e.g. "ktav-yad /
   Sans-Serif", "sans-sofit / inscription / ancient") gets split on "/"
   into a normalized tag list for the style-filter checkboxes. `style`
   itself is left untouched — styleTags is a new, separate field, so
   nothing currently displaying `style` breaks.

3. Scans `notes` for "missing:" text. If a note is PURELY a "missing: X,
   Y, Z" list with nothing else, it's cleared automatically (now fully
   redundant with `missingChars`). If it's mixed with other content
   (a parenthetical aside, an extra sentence), it's left untouched and
   reported instead — auto-editing prose risks losing real information.

Run this BEFORE generate_coverage.py's next run (SAMPLE_STRINGS_VERSION
was bumped for the Numerals change, so a full coverage regeneration is
needed anyway — this migration doesn't touch `coverage`, so order
between the two doesn't actually matter, but doing schema cleanup first
means you're looking at final field names when you review coverage output).

Usage:
    python3 migrate_fonts_schema.py --fonts-json data/fonts.json
"""
import argparse
import json
import re
from pathlib import Path

# Canonical style tags. Left side: substring as it appears (lowercased) in
# the current `style` field, after splitting on "/". Right side: the tag
# it maps to. "ancient" and "antiquity" are the same underlying idea used
# inconsistently across entries — merged into one tag here.
STYLE_TAG_MAP = {
    "serif": "serif",
    "sans-serif": "sans-serif",
    "cursive": "cursive",
    "display": "display",
    "monospaced": "monospaced",
    "scribal": "scribal",
    "ktav-yad": "ktav-yad",
    "mashkit": "mashkit",
    "symbols & dingbats": "symbols-dingbats",
    "ashkenaz": "ashkenaz",
    "sepharadi": "sepharadi",
    "ancient": "ancient",
    "antiquity": "ancient",
    "inscription": "inscription",
    "sans-sofit": "sans-sofit",
}

STYLE_TAG_TITLES = {
    "serif": "Serif", "sans-serif": "Sans-Serif", "cursive": "Cursive",
    "display": "Display", "monospaced": "Monospaced", "scribal": "Scribal",
    "ktav-yad": "Ktav-Yad", "mashkit": "Mashkit", "symbols-dingbats": "Symbols & Dingbats",
    "ashkenaz": "Ashkenaz", "sepharadi": "Sepharadi", "ancient": "Ancient/Antiquity",
    "inscription": "Inscription", "sans-sofit": "Sans-Sofit",
}


def derive_style_tags(style_text: str) -> list:
    tags = []
    for part in style_text.split("/"):
        key = part.strip().lower()
        tag = STYLE_TAG_MAP.get(key)
        if tag is None:
            # unrecognized fragment — surfaced in the report so you can add
            # it to STYLE_TAG_MAP rather than silently dropping it
            tag = None
        if tag and tag not in tags:
            tags.append(tag)
        elif tag is None and key:
            tags.append(f"UNRECOGNIZED:{key}")
    return tags


def split_flags(flags: list):
    positioning, dropped = [], []
    for f in flags:
        m = re.match(r"^\s*positioning:\s*(.+)$", f, re.IGNORECASE)
        if m:
            positioning.append(m.group(1).strip())
            continue
        m = re.match(r"^\s*missing:\s*(.+)$", f, re.IGNORECASE)
        if m:
            dropped.append(f)
            continue
        # anything else (rare/free-form) — keep as a positioning error
        # rather than silently discard; flag it for review
        positioning.append(f + "  [unrecognized flag format, kept as-is — please review]")
    return positioning, dropped


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--fonts-json", required=True, type=Path)
    args = ap.parse_args()

    data = json.loads(args.fonts_json.read_text(encoding="utf-8"))

    unrecognized_style_fragments = set()
    notes_with_missing = []
    dropped_flag_report = []
    cleared_notes_report = []

    for font in data["fonts"]:
        # --- flags -> positioningErrors (idempotent: only runs the actual
        #     conversion if `flags` is still present; a font already
        #     migrated in a prior run keeps its existing positioningErrors
        #     untouched, rather than being wiped to empty on a re-run) ---
        if "flags" in font:
            old_flags = font.pop("flags")
            positioning, dropped = split_flags(old_flags)
            font["positioningErrors"] = positioning
            if dropped:
                dropped_flag_report.append((font["slug"], dropped))
        else:
            font.setdefault("positioningErrors", [])

        # --- style -> styleTags (always safe to recompute — pure function
        #     of the current `style` text, so re-running after a manual
        #     `style` edit correctly re-derives styleTags to match) ---
        tags = derive_style_tags(font.get("style", ""))
        clean_tags = [t for t in tags if not t.startswith("UNRECOGNIZED:")]
        for t in tags:
            if t.startswith("UNRECOGNIZED:"):
                unrecognized_style_fragments.add((font["slug"], t.split(":", 1)[1]))
        font["styleTags"] = clean_tags

        # --- notes: auto-clear if it's PURELY a "missing: ..." list
        #     (fully redundant now that missingChars covers this from
        #     real cmap data), otherwise report for manual review since
        #     it may carry other information worth keeping ---
        notes = font.get("notes", "")
        if notes and re.search(r"\bmissing:", notes, re.IGNORECASE):
            is_pure_missing_list = bool(
                re.fullmatch(r"missing:\s*[A-Za-zÀ-ÿḤḥĠġ' /,.-]+", notes, re.IGNORECASE)
                and "(" not in notes and ";" not in notes
            )
            if is_pure_missing_list:
                font["notes"] = ""
                cleared_notes_report.append((font["slug"], notes))
            else:
                notes_with_missing.append((font["slug"], notes))

    data.setdefault("_meta", {})["schemaVersion"] = "0.7.0"
    data["_meta"]["styleTagTitles"] = STYLE_TAG_TITLES

    args.fonts_json.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"migrated {len(data['fonts'])} fonts to schemaVersion 0.7.0")

    if dropped_flag_report:
        print(f"\n{sum(len(d) for _, d in dropped_flag_report)} 'missing:' flag(s) dropped "
              f"(now covered by missingChars once you re-run generate_coverage.py):")
        for slug, flags in dropped_flag_report:
            print(f"  {slug}: {flags}")

    if cleared_notes_report:
        print(f"\n{len(cleared_notes_report)} notes field(s) auto-cleared (were purely a "
              f"'missing: …' list, now redundant with missingChars):")
        for slug, old_notes in cleared_notes_report:
            print(f"  {slug}: was {old_notes!r}")

    if notes_with_missing:
        print(f"\n{len(notes_with_missing)} font(s) still have 'missing:' text inside `notes` "
              f"mixed with other content — left untouched, please review by hand:")
        for slug, notes in notes_with_missing:
            print(f"  {slug}: {notes!r}")

    if unrecognized_style_fragments:
        print(f"\n{len(unrecognized_style_fragments)} style fragment(s) not in STYLE_TAG_MAP — "
              f"add them there and re-run, or they won't get a filter checkbox:")
        for slug, frag in sorted(unrecognized_style_fragments):
            print(f"  {slug}: {frag!r}")


if __name__ == "__main__":
    main()