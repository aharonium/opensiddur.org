# -*- coding: utf-8 -*-
"""
Strips computed/derived fields from fonts.json that make manual editing
harder without adding anything you'd actually hand-edit — each is fully
restorable by re-running the script that generates it.

  --missing-chars   removes `missingChars` (from generate_coverage.py).
                     Restore: python3 generate_coverage.py --fonts-json ... --fonts-dir ...
  --style-tags      removes `styleTags` (as of custom-fonts-display.php
                     0.13.0, this is computed on the fly in PHP from
                     `style` and no longer needs to live in the JSON at
                     all — see cfd_derive_style_tags(). Not "restorable"
                     in the sense of needing a script — it just isn't
                     read from this field anymore, so there's nothing to
                     restore. Safe to remove permanently.)

Neither flag touches anything you'd actually hand-edit: name, slug,
style, foundry, typographer, version, license, notes, positioningErrors,
categories are all left completely alone.

Usage:
    python3 strip_derived_fields.py --fonts-json data/fonts.json --missing-chars --style-tags
"""
import argparse
import json
from pathlib import Path


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--fonts-json", type=Path, required=True)
    ap.add_argument("--missing-chars", action="store_true", help="remove missingChars from every font")
    ap.add_argument("--style-tags", action="store_true", help="remove styleTags from every font")
    args = ap.parse_args()

    if not args.missing_chars and not args.style_tags:
        ap.error("nothing to do — pass --missing-chars and/or --style-tags")

    data = json.loads(args.fonts_json.read_text(encoding="utf-8"))
    removed = {"missingChars": 0, "styleTags": 0}
    for font in data["fonts"]:
        if args.missing_chars and font.pop("missingChars", None) is not None:
            removed["missingChars"] += 1
        if args.style_tags and font.pop("styleTags", None) is not None:
            removed["styleTags"] += 1

    args.fonts_json.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")

    if args.missing_chars:
        print(f"removed missingChars from {removed['missingChars']} font(s)")
    if args.style_tags:
        print(f"removed styleTags from {removed['styleTags']} font(s)")


if __name__ == "__main__":
    main()