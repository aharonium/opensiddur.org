# -*- coding: utf-8 -*-
"""
Estimates, per font, the newest Unicode version whose characters the font
actually supports — by cross-referencing the font's real cmap coverage
(via font_io.py) against Unicode's own DerivedAge.txt, which records the
version every codepoint was assigned in.

This is a heuristic, not a certainty, and says so in its own output:
- PRESENCE of a character introduced in Unicode 11.0 is real evidence the
  font was updated on or after 11.0.
- ABSENCE proves nothing — a font might simply never have needed that
  character (e.g. it's a Latin-only font that will never contain Hebrew
  presentation forms, Unicode version notwithstanding).
So this reports "newest version WITH EVIDENCE of support" and separately
lists which specific characters constitute that evidence, so you can
sanity-check the claim yourself rather than trust a bare version number —
exactly the U+05EF-style reasoning from the original discussion.

DATA YOU NEED TO SUPPLY: DerivedAge.txt from the Unicode Character
Database. Download it yourself (this script can't fetch it):
    https://www.unicode.org/Public/UCD/latest/ucd/DerivedAge.txt
(a few hundred KB, plain text, one Unicode release's worth of "when was
this codepoint added" data — that URL always points at the current
latest release.)

Usage:
    python3 estimate_unicode_version.py \
        --derived-age  DerivedAge.txt \
        --fonts-json   data/fonts.json \
        --fonts-dir    /path/to/local/mirror/of/wp-content/uploads/fonts

Writes an `unicodeVersionEstimate` field per font into fonts.json:
    {
      "maxVersionSeen": "11.0",
      "evidence": [
        {"char": "ׯ", "codepoint": "U+05EF", "version": "11.0",
         "name": "HEBREW YOD WITH VARIKA"}
      ]
    }
`evidence` lists only the character(s) that justify the MAX version
reported — not every character in the font — so it stays short and
directly checkable.

NOT wired into the plugin's display yet — this is a standalone data pass,
same as generate_coverage.py. Once you've run it and eyeballed a few
fonts' output for sanity, let me know and I'll add a details-panel line
for it (clearly labeled as an estimate, per the caveat above).
"""
import argparse
import bisect
import json
import re
import sys
import unicodedata
from pathlib import Path

import font_io

_RANGE_RE = re.compile(
    r"^([0-9A-Fa-f]{4,6})(?:\.\.([0-9A-Fa-f]{4,6}))?\s*;\s*([\d.]+)\s*#"
)


def parse_derived_age(path: Path):
    """Returns a sorted list of (start, end, version_string) tuples."""
    ranges = []
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#"):
            continue
        m = _RANGE_RE.match(line)
        if not m:
            continue
        start = int(m.group(1), 16)
        end = int(m.group(2), 16) if m.group(2) else start
        version = m.group(3)
        ranges.append((start, end, version))
    ranges.sort(key=lambda r: r[0])
    return ranges


def version_of(ranges, starts, cp: int):
    """Binary search `ranges` (sorted, non-overlapping by construction in
    DerivedAge.txt) for the range containing `cp`. `starts` is a
    precomputed list of range-start values, parallel to `ranges`, so we
    don't re-derive it on every lookup."""
    i = bisect.bisect_right(starts, cp) - 1
    if i < 0:
        return None
    start, end, version = ranges[i]
    if start <= cp <= end:
        return version
    return None


def _version_key(v: str):
    """'11.0' -> (11, 0) for numeric comparison, since string comparison
    would put '9.0' after '11.0'."""
    return tuple(int(p) for p in v.split("."))


def estimate_for_font(codepoints: set, ranges, starts):
    best_version = None
    best_key = None
    evidence_by_version = {}
    for cp in codepoints:
        v = version_of(ranges, starts, cp)
        if v is None:
            continue
        evidence_by_version.setdefault(v, []).append(cp)
        key = _version_key(v)
        if best_key is None or key > best_key:
            best_key, best_version = key, v

    if best_version is None:
        return None

    evidence = []
    for cp in sorted(evidence_by_version[best_version])[:5]:  # cap for readability
        ch = chr(cp)
        try:
            name = unicodedata.name(ch)
        except ValueError:
            name = None
        evidence.append({"char": ch, "codepoint": f"U+{cp:04X}", "version": best_version, "name": name})

    return {"maxVersionSeen": best_version, "evidence": evidence}


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--derived-age", required=True, type=Path)
    ap.add_argument("--fonts-json", required=True, type=Path)
    ap.add_argument("--fonts-dir", required=True, type=Path)
    ap.add_argument("--only", help="comma-separated slugs to (re)compute; default: all")
    args = ap.parse_args()

    ranges = parse_derived_age(args.derived_age)
    if not ranges:
        sys.exit(f"No version ranges parsed from {args.derived_age} — is this really DerivedAge.txt?")
    starts = [r[0] for r in ranges]
    print(f"loaded {len(ranges)} version ranges from {args.derived_age}")

    data = json.loads(args.fonts_json.read_text(encoding="utf-8"))
    only = set(args.only.split(",")) if args.only else None

    updated, skipped = 0, []
    for font in data["fonts"]:
        if only and font["slug"] not in only:
            continue

        font_path, _warning = font_io.find_font_file(args.fonts_dir, font["family"])
        if font_path is None:
            skipped.append(font["slug"])
            continue

        try:
            codepoints = font_io.font_codepoints(font_path)
        except Exception as e:
            skipped.append(f"{font['slug']} ({e})")
            continue

        estimate = estimate_for_font(codepoints, ranges, starts)
        if estimate:
            font["unicodeVersionEstimate"] = estimate
            updated += 1

    args.fonts_json.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"estimated Unicode version for {updated} font(s)")
    if skipped:
        print(f"skipped {len(skipped)}: {skipped}", file=sys.stderr)


if __name__ == "__main__":
    main()
