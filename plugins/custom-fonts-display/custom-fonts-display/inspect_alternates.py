# -*- coding: utf-8 -*-
"""
Discovers OpenType alternate-glyph features (GSUB Single/Alternate
Substitution — ss01-ss20, salt, aalt, cvXX, etc.) in a font, and reports
which characters have alternates and how many.

This is a standalone survey/discovery tool, not wired into the plugin's
display yet — see module-level notes at the bottom for how you'd actually
expose a found alternate once you know it's there (short version: CSS
font-feature-settings, letting the browser's own OpenType engine draw
the alternate — no glyph-rendering code needed on our end).

PURE PYTHON STANDARD LIBRARY (via font_io.py) — no fontTools, no pip.
Every piece of the GSUB parsing this depends on (font_io.parse_gsub(),
cmap_glyph_map(), the Single/Alternate Substitution subtable parsers) was
verified against fontTools' own GSUB interpretation on synthetic test
fonts with real ss01/aalt features and a named stylistic set, before
this was ever pointed at a real font — see chat, not reproduced here
since it needs fontTools as a test oracle.

Usage — inspect one font in detail:
    python3 inspect_alternates.py --font /path/to/ProtoCanaanite.woff

Usage — survey the whole catalogue for which fonts have ANY alternate-
glyph features at all (most won't; the ones that do are worth a look):
    python3 inspect_alternates.py --survey \
        --fonts-json data/fonts.json --fonts-dir /path/to/uploads/fonts

Usage — write discovered alternate forms into fonts.json's
`alternateForms` field for a deliberately-chosen set of fonts (requires
--only — no blanket mode, since most GSUB features across the catalogue
are ordinary typographic infrastructure, not curated historical variants
worth showing a reader; see MUNDANE_TAGS below):
    python3 inspect_alternates.py --write \
        --only shofar,taamey-ashkenaz,rommvilna \
        --fonts-json data/fonts.json --fonts-dir /path/to/uploads/fonts
"""
import argparse
import json
import sys
import unicodedata
from pathlib import Path

import font_io


def _char_label(cp: int) -> str:
    ch = chr(cp)
    try:
        name = unicodedata.name(ch)
    except ValueError:
        name = None
    return f"{ch} (U+{cp:04X}{' ' + name if name else ''})"


# Feature tags that are ordinary typographic infrastructure — ligatures,
# numeral-style switches, case forms, Indic/Arabic shaping — present in
# any well-built font and NOT what "curated historical alternate
# letterforms" means. Anything NOT in this set (hist, salt, jalt, ssXX,
# cvXX, ccmp, aalt, rtla, and anything nonstandard like Alef's zzXX) is
# treated as a candidate worth surfacing. See the chat discussion of the
# real --survey run against this catalogue for how this list was derived —
# it's the same categorization, not a fresh guess.
MUNDANE_TAGS = {
    "liga", "dlig", "hlig", "rlig", "clig",
    "onum", "lnum", "tnum", "pnum", "zero", "ordn",
    "frac", "numr", "dnom", "subs", "sups", "sinf",
    "smcp", "c2sc", "case", "locl",
    "abvs", "akhn", "blwf", "blws", "half", "haln", "init", "medi", "fina",
    "nukt", "pres", "pstf", "psts", "reph", "rphf", "vatu", "vert", "nalt",
}


def build_alternate_forms(path: Path, exclude_tags=MUNDANE_TAGS) -> list:
    """Returns a list of {baseChar, baseCodepoint, mechanism, featureTag,
    [trigger]} entries — the schema custom-fonts-display.php's
    cfd_render_alternate_forms() reads from fonts.json's `alternateForms`
    field. Two mechanisms, matching the two GSUB lookup types this tool
    understands:
      "sequence" (LookupType 4, e.g. Proto Canaanite's ccmp): `trigger` is
        the literal text (base char + combining mark(s)) that produces
        the alternate automatically, in ANY renderer — no CSS needed.
      "feature" (LookupType 1/3, e.g. salt/ssXX): no literal trigger text;
        the base character alone needs font-feature-settings with
        featureTag turned on to show the alternate.

    Two filters, both added after running this against real fonts beyond
    Proto Canaanite turned up two kinds of noise Proto Canaanite itself
    doesn't have:

    1. Degenerate single-component "ligatures" — a GSUB ligature-
       substitution rule whose sequence is just the base glyph alone,
       with no actual second trigger character (componentCount=1 in the
       raw table). Technically valid, but some font-building tools emit
       this as an alternate encoding of an ordinary single-glyph
       substitution, and it produces useless entries like "א[א]" — same
       character both places, since there's no real trigger to show.
       Proto Canaanite has zero of these (verified — every one of its 62
       entries is a genuine 2-character sequence); other Culmus fonts do.

    2. Standard letter+diacritic composition — many entries turn out to
       be exactly what any well-built Hebrew font needs for correct
       rendering (e.g. bet+dagesh -> the standard "bet with dagesh"
       glyph), NOT a curated alternate letterform the way Proto
       Canaanite's pictograph variants are. The tell: if the ligature's
       OUTPUT glyph is ALSO independently reachable by typing some other
       real character (Unicode has precomposed Hebrew-letter-with-dagesh
       codepoints in the FB30-FB4E range for exactly this reason), then
       the "alternate" is redundant with something the reader could
       already type directly — it's composition infrastructure, not a
       hidden form. Genuinely hidden alternates (Proto Canaanite's
       pictograph variants) are ONLY reachable via the trigger sequence,
       with no independent codepoint of their own — that's the real signal.

    Entries where the base (or, for sequences, any trigger character)
    isn't cmap-mapped to a real codepoint are skipped too — nothing to render.
    """
    glyph_map = font_io.font_glyph_map(path)
    reverse_map = {}
    for cp, gid in glyph_map.items():
        reverse_map.setdefault(gid, []).append(cp)

    features = font_io.font_gsub_features(path)
    entries = []
    for tag, info in features.items():
        if tag in exclude_tags:
            continue

        for seq, lig_glyph in info["ligatures"].items():
            if len(seq) < 2:
                continue  # filter 1: no real trigger character
            if lig_glyph in reverse_map:
                continue  # filter 2: output is just a standard precomposed character
            trigger_chars = []
            ok = True
            for gid in seq:
                cps = reverse_map.get(gid)
                if not cps:
                    ok = False
                    break
                trigger_chars.append(chr(cps[0]))
            if not ok:
                continue
            base_cp = ord(trigger_chars[0])
            entries.append({
                "baseChar": trigger_chars[0],
                "baseCodepoint": f"U+{base_cp:04X}",
                "mechanism": "sequence",
                "trigger": "".join(trigger_chars),
                "featureTag": tag,
            })

        for glyph_id, alt_ids in info["alternates"].items():
            base_cps = reverse_map.get(glyph_id)
            if not base_cps:
                continue
            base_cp = base_cps[0]
            for alt_id in alt_ids:
                if alt_id in reverse_map:
                    continue  # filter 2, applied here too for consistency
                entries.append({
                    "baseChar": chr(base_cp),
                    "baseCodepoint": f"U+{base_cp:04X}",
                    "mechanism": "feature",
                    "featureTag": tag,
                })

    seen, deduped = set(), []
    for e in entries:
        key = (e["baseCodepoint"], e["mechanism"], e.get("trigger"), e["featureTag"])
        if key in seen:
            continue
        seen.add(key)
        deduped.append(e)
    return deduped


def write_alternate_forms(fonts_json: Path, fonts_dir: Path, slugs: set):
    data = json.loads(fonts_json.read_text(encoding="utf-8"))
    written = []
    skipped = []
    for font in data["fonts"]:
        if font["slug"] not in slugs:
            continue
        font_path, _warning = font_io.find_font_file(fonts_dir, font["family"])
        if font_path is None:
            skipped.append(font["slug"])
            continue
        forms = build_alternate_forms(font_path)
        font["alternateForms"] = forms
        written.append((font["slug"], len(forms)))

    missing = slugs - {slug for slug, _ in written} - set(skipped)
    fonts_json.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"wrote alternateForms for {len(written)} font(s):")
    for slug, n in written:
        print(f"  {slug}: {n} form(s)")
    if skipped:
        print(f"\nskipped (no font file found): {skipped}", file=sys.stderr)
    if missing:
        print(f"\nslug(s) not found in fonts.json at all: {sorted(missing)}", file=sys.stderr)


def describe_font(path: Path):
    glyph_map = font_io.font_glyph_map(path)          # codepoint -> glyphID
    reverse_map = {}                                    # glyphID -> [codepoints]
    for cp, gid in glyph_map.items():
        reverse_map.setdefault(gid, []).append(cp)

    features = font_io.font_gsub_features(path)
    if not features:
        print(f"{path.name}: no GSUB Single/Alternate/Ligature Substitution features found "
              f"(no alternate-glyph mechanism, or none of the types this tool looks for — "
              f"see module docstring for which lookup types are in scope)")
        return

    names = font_io.font_name_table(path)

    print(f"{path.name}: {len(features)} alternate-glyph feature(s)\n")
    for tag, info in sorted(features.items()):
        ui_name = names.get(info["uiNameID"]) if info["uiNameID"] else None
        label = f"{tag} (\"{ui_name}\")" if ui_name else tag
        total = len(info["alternates"]) + len(info["ligatures"])
        print(f"  Feature {label} — {total} entry/entries:")

        # types 1/3: single glyph -> one or more alternates, an application-
        # toggled feature (needs font-feature-settings or similar to trigger)
        for glyph_id, alt_ids in sorted(info["alternates"].items()):
            cps = reverse_map.get(glyph_id)
            base_label = ", ".join(_char_label(cp) for cp in cps) if cps else f"glyph #{glyph_id}"
            print(f"    {base_label}: {len(alt_ids)} alternate(s) via feature toggle [glyph IDs {alt_ids}]")

        # type 4: a real, typeable sequence of characters triggers the
        # substitution automatically (if the feature is one shaping
        # engines apply by default, like ccmp) — no toggle needed at all
        for seq, lig_glyph in sorted(info["ligatures"].items()):
            parts = []
            for gid in seq:
                cps = reverse_map.get(gid)
                parts.append(" or ".join(_char_label(cp) for cp in cps) if cps else f"glyph #{gid}")
            print(f"    {' + '.join(parts)} -> alternate form [glyph ID {lig_glyph}]"
                  f"{' (automatic — no feature toggle needed)' if tag in ('ccmp', 'liga', 'rlig', 'clig') else ''}")
        print()


def survey(fonts_json: Path, fonts_dir: Path):
    data = json.loads(fonts_json.read_text(encoding="utf-8"))
    found = []
    skipped = []
    for font in data["fonts"]:
        font_path, _warning = font_io.find_font_file(fonts_dir, font["family"])
        if font_path is None:
            skipped.append(font["slug"])
            continue
        try:
            features = font_io.font_gsub_features(font_path)
        except Exception as e:
            skipped.append(f"{font['slug']} ({e})")
            continue
        if features:
            char_count = sum(len(f["alternates"]) + len(f["ligatures"]) for f in features.values())
            found.append((font["slug"], font["name"], sorted(features.keys()), char_count))

    if found:
        print(f"{len(found)} font(s) have alternate-glyph features:\n")
        for slug, name, tags, char_count in found:
            print(f"  {name} ({slug}): features {tags}, {char_count} character(s) with alternates")
        print(f"\nRun with --font <path> on any of these for full detail per character.")
    else:
        print("No fonts in the catalogue have GSUB Single/Alternate Substitution features.")
    if skipped:
        print(f"\n{len(skipped)} font(s) skipped (no font file found): {skipped}", file=sys.stderr)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--font", type=Path, help="inspect one font file in detail")
    ap.add_argument("--survey", action="store_true", help="scan the whole catalogue for alternate-glyph features")
    ap.add_argument("--write", action="store_true",
                     help="write discovered alternate forms into --fonts-json's `alternateForms` field, "
                          "for display in the plugin. Requires --only — deliberately no blanket "
                          "'do this for every font' mode, since most of the catalogue's GSUB features "
                          "are ordinary typographic infrastructure, not curated historical variants "
                          "worth showing a reader (see MUNDANE_TAGS / the chat discussion of the "
                          "--survey results for why).")
    ap.add_argument("--only", help="comma-separated slugs — required for --write")
    ap.add_argument("--fonts-json", type=Path)
    ap.add_argument("--fonts-dir", type=Path)
    args = ap.parse_args()

    if args.font:
        describe_font(args.font)
    elif args.write:
        if not args.only or not args.fonts_json or not args.fonts_dir:
            ap.error("--write requires --only, --fonts-json, and --fonts-dir")
        write_alternate_forms(args.fonts_json, args.fonts_dir, set(args.only.split(",")))
    elif args.survey:
        if not args.fonts_json or not args.fonts_dir:
            ap.error("--survey needs --fonts-json and --fonts-dir")
        survey(args.fonts_json, args.fonts_dir)
    else:
        ap.error("use --font <path> for one font, --write --only <slugs> ... to save alternate forms, "
                  "or --survey --fonts-json ... --fonts-dir ... for the whole catalogue")


if __name__ == "__main__":
    main()

# ------------------------------------------------------------------
# --write's two mechanisms, and how custom-fonts-display.php actually
# renders each (see cfd_render_alternate_forms()):
#
#   "sequence" (e.g. Proto Canaanite's ccmp) — render the literal trigger
#   text with the font's OWN family only (no fallback chain), and the
#   substitution happens automatically, no CSS feature toggle needed:
#     <span style="font-family:'ProtoCanaanite'">אּ</span>
#
#   "feature" (e.g. a stylistic-set salt/ssXX) — render the base
#   character alone, with the feature explicitly turned on:
#     <span style="font-family:'SomeFont'; font-feature-settings:'salt' 1;">א</span>
#
# Either way, a reference rendering of the base character in the site's
# usual fallback font stack follows in square brackets, so the alternate
# — which may not be immediately recognizable on its own — is always
# shown next to something legible it's an alternate OF.
# ------------------------------------------------------------------