<?php
/**
 * Plugin Name:  Custom Fonts Display
 * Plugin URI:   https://opensiddur.org/
 * Description:  Renders the Open Siddur Hebrew font catalogue via [custom_fonts_display].
 *               Data source of truth is data/fonts.json in this plugin's own directory —
 *               no custom post type, no ACF, no GitHub dependency at render time.
 * Version:      0.26.0
 * Author:       Aharon Varady
 * Author URI:   https://aharon.varady.net/
 * License:      GNU Lesser General Public License v3.0 or later
 * License URI:  https://www.gnu.org/licenses/lgpl-3.0.html
 * Text Domain:  custom-fonts-display
 *
 * Copyright (C) 2026  Aharon N. Varady, for the Open Siddur Project
 * (https://opensiddur.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is
 * distributed WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * https://www.gnu.org/licenses/lgpl-3.0.html
 *
 * -------------------------------------------------------------------
 * Draft iteration log — bump CFD_VERSION with every handoff so we can
 * name which draft a bug report came from. Keep fonts.json's own
 * `_meta.schemaVersion` in step with this.
 *
 *   0.1.0  initial skeleton: shortcode, JSON loader, row renderer, toolbar
 *   0.2.0  per-script sample rows (numerals/punctuation/symbols/latin/
 *          yiddish/ladino); open-box unicode-range fallback (BMP + astral);
 *          admin-bar-aware sticky toolbar offset
 *   0.3.0  restored "failing-diacritic-support" category (general/systemic
 *          positioning errors, as opposed to a per-font flag); fallback
 *          fonts now served from this plugin's own /assets/fonts/ instead
 *          of /uploads/fonts/FallbackFont; LGPL 3.0+ header added
 *   0.4.0  renamed plugin/file/shortcode for consistency: the folder is
 *          custom-fonts-display, so the main file, Plugin Name, and
 *          shortcode tag all now read "custom-fonts-display" /
 *          "Custom Fonts Display" / [custom_fonts_display] throughout;
 *          fonts.json filled in with the full 126-font catalogue
 *   0.4.1  fixed missing .woff fallback source on the two FallbackFont
 *          @font-face rules (only .woff2 was declared — inconsistent
 *          with the per-font rule generator, which offers both)
 *   0.4.2  expand toggle moved out of the abgad row and into its own
 *          line directly under the font name (was sitting at the RTL
 *          start edge of the Hebrew line); fixed spurious vertical
 *          scroll arrows on the abgad/sample lines — overflow-x:auto
 *          with no overflow-y set computes to overflow-y:auto too, so
 *          any glyph taller than the line box triggered a scrollbar;
 *          added overflow-y:hidden and taller line-height throughout
 *   0.5.0  category anchor nav (cfd_render_catnav()) for multi-category
 *          views; per-character missing-glyph coloring (.cfd-missing)
 *          driven by an optional `coverage` map per font in fonts.json —
 *          see generate_coverage.py, a companion build script that reads
 *          each font's real cmap table. Renders in plain color with no
 *          per-character span wrapping for any font without coverage
 *          data yet, so this is fully backward-compatible with the
 *          existing 126-font fonts.json.
 *   0.5.1  font-name links get data-lightbox-type="iframe" (matches the
 *          existing lightbox script's requirement); diacriticSupport/
 *          flags/notes moved out of the always-visible row and into the
 *          expanded details panel, in the same plain dt/dd format as
 *          style/foundry/etc. rather than pill badges; .cfd-missing is
 *          now a gray background swatch rather than dimmed text;
 *          generate_coverage.py rewritten with zero dependencies (no
 *          fontTools/pip) — reads .woff/.ttf/.otf directly via stdlib
 *          struct+zlib, verified byte-for-byte against fontTools output
 *          on a test font before shipping
 *   0.6.0  missing-glyph treatment redesigned: font-stack is now
 *          primary font -> CFD_REFERENCE_FAMILY ("SimpleCLM", the
 *          site's default sans-serif) -> FallbackFont, so a character
 *          the primary font lacks renders as a real, readable letter
 *          rather than an abstract box, grayed via .cfd-missing (plain
 *          muted text color now, not a background swatch — a letterform
 *          doesn't read the same way a box glyph did). FallbackFont
 *          stays as the final safety net for anything Simple CLM itself
 *          doesn't cover either.
 *   0.6.1  .cfd-missing switched from a fixed gray to opacity:.35 — a
 *          hard-coded color wasn't light enough (missing letters read
 *          as legible as present ones) and opacity scales with whatever
 *          the inherited text color is, so it also needs no separate
 *          dark-mode override. See dark-mode.css for the rest of this
 *          version's color overrides (toolbar, catnav, cards, toggle).
 *   0.6.2  .cfd-missing opacity tuned to .15 (was .35, still not light
 *          enough); font-name links sized up to 1.3rem/700 weight —
 *          previously unset, so they inherited whatever small ambient
 *          size the theme's content area used; sample-lines (numerals/
 *          punctuation/symbols/latin/yiddish/ladino) moved from
 *          always-visible into the expandable details panel, alongside
 *          the metadata dl, so only the primary abgad shows by default;
 *          Yiddish/Ladino sample lines now text-align:left despite
 *          being dir="rtl" — kept the rtl direction (correct glyph
 *          shaping/bidi order) but left-anchored the line, matching the
 *          other sample lines, since a handful of space-separated
 *          glyphs doesn't need right-anchoring to read correctly
 *   0.7.0  also fixed the WP plugin-header `Version:` field, which had
 *          been stuck at 0.3.0 this whole time — only CFD_VERSION was
 *          being bumped, not what WP's Plugins list actually reads.
 *          Substantial round: (1) ZIP download link added as the last
 *          line of the expanded panel; (2) Numerals sample string
 *          extended (¼ ¾ and the full Ⅰ–Ⅿ Roman-numeral run, was just
 *          Ⅷ) — see CFD_SAMPLE_STRINGS_VERSION below; (3) style-filter
 *          checkboxes (cfd_render_style_filter()), driven by a new
 *          `styleTags` field per font in fonts.json; (4) "Show only"
 *          links next to Foundry/Typographer that filter the current
 *          view down to that person/foundry, with a clearable active-
 *          filter chip; (5) `flags` renamed/split: positioning-only
 *          entries become `positioningErrors` (still hand-curated,
 *          genuinely can't be derived), while "missing: X" entries are
 *          dropped in favor of an auto-computed "Missing Characters"
 *          row sourced from coverage data + stdlib unicodedata.name() —
 *          see migrate_fonts_schema.py and generate_coverage.py's new
 *          `missingChars` field. Coverage/missingChars are only trusted
 *          when fonts.json's `_meta.sampleStringsVersion` matches
 *          CFD_SAMPLE_STRINGS_VERSION below, so a fonts.json that hasn't
 *          been through a fresh generate_coverage.py run after a sample-
 *          string edit fails safe (plain rendering) instead of silently
 *          mis-flagging characters at the wrong index.
 *   0.7.1  download line now an icon + text sized like the font name,
 *          indented as a distinct action rather than another metadata
 *          row; catnav moved above the style filter (was below); the
 *          active-filters bar rebuilt: previously it only reflected the
 *          foundry/typographer filter and had a real bug — its own CSS
 *          declared `display:flex` unconditionally, which (being a
 *          class selector) outranks the browser's built-in
 *          `[hidden]{display:none}` rule, so the `hidden` attribute was
 *          never actually capable of hiding it, regardless of what the
 *          JS did. Fixed with an explicit `[hidden]{display:none
 *          !important}` override. The bar is now also entirely JS-owned
 *          (PHP renders an empty shell) so it reflects the style
 *          checkboxes too, not just identity filters, with one
 *          removable chip per active filter plus a single "Clear all"
 *          that returns to the exact fresh-load state.
 *   0.8.0  (1) fixed a real text-align:justify inheritance bug — the
 *          page's own `.english-sans` (or similar) ancestor class was
 *          leaking into every dd/label in here; added a scoped
 *          text-align:left reset early in the stylesheet so later,
 *          equal-or-higher-specificity RTL rules still win by source
 *          order. (2) font_io.py gains name-table parsing (nameID 5 =
 *          version string, same field Windows Explorer's font
 *          Properties reads; 8/9/13/14 = manufacturer/designer/license,
 *          available for later use) — generate_coverage.py now fills
 *          version/versionDate from the font file itself when those
 *          fields are empty (--force-version to overwrite instead),
 *          verified byte-for-byte against fontTools before shipping.
 *          (3) style-filter generalized into cfd_render_tag_filter_group(),
 *          a reusable multi-group system — added a second group, Script
 *          (Hebrew+Latin vs Hebrew-only), computed from real cmap
 *          coverage of the basic Latin alphabet in generate_coverage.py.
 *          (4) added a per-row "test your own text" field, live-previewed
 *          in that font's actual stack (including the missing-glyph
 *          fallback chain), right-justified for RTL input; the glyph-
 *          size slider now sets its CSS custom properties on .cfd-root
 *          rather than enumerating each consumer element, so this and
 *          any future element that wants to track the slider just works.
 *   0.8.1  "test your own text" field now pre-filled with real content
 *          (was just a placeholder hint) — Zephaniah 3:8, a traditional
 *          Hebrew pangram, at whichever diacritic tier the font's own
 *          category claims (full t'amim+niqqud / niqqud only / plain
 *          consonantal — see CFD_SAMPLE_VERSE_* and
 *          cfd_default_test_text()). Shown at reduced opacity as a
 *          suggestion rather than the user's own input, selected
 *          entirely on first focus so typing immediately replaces it,
 *          full opacity restored the moment they actually edit it.
 *   0.9.0  (a) fixed a wrong emoji codepoint in generate_coverage.py's
 *          Symbols sample string (had POSTBOX U+1F4EE where it should've
 *          been BOOK U+1F56E, which is what the PHP file already had —
 *          the two files had silently drifted on this one character);
 *          bumped CFD_SAMPLE_STRINGS_VERSION accordingly. (b) retired
 *          the `coverage` field entirely — it was fully redundant with
 *          `missingChars`, which PHP now checks directly via mb_ord()
 *          per character instead of needing precomputed positional
 *          arrays; smaller fonts.json, one source of truth instead of
 *          two that could drift. (c) CFD_REFERENCE_FAMILY generalized
 *          to CFD_REFERENCE_FAMILIES (a list) — FreeSerif added as a
 *          second reference tier after SimpleCLM, easy to extend
 *          further. (d) "Show only" buttons hidden for empty Foundry/
 *          Typographer fields. (e) category anchors no longer prefixed
 *          with "cfd-" (#failing-diacritic-support, not
 *          #cfd-failing-diacritic-support). (f) download line indent
 *          set to an explicit 30px.
 *   0.10.0 Real fix for the "diacritic not grayed out" bug: earlier
 *          versions wrapped every codepoint (including combining marks)
 *          in its own <span>, so a missing niqqud mark rendered via a
 *          fallback font — with no base letter in its own span — didn't
 *          reliably take on that span's own color/opacity in Chromium.
 *          cfd_split_graphemes()/cfd_is_combining_mark() now group a
 *          base letter with its combining marks into ONE cluster before
 *          cfd_render_glyph_string() decides whether to gray it, so
 *          there's never a styling boundary between a mark and the
 *          letter it belongs to. Verified end-to-end against a
 *          synthetic font missing only qamats qatan (U+05C7), not just
 *          reasoned about — see chat. Also fixed two more PHP/Python
 *          sample-string drift bugs found while auditing every string
 *          for this: Symbols had RAINBOW (U+1F308) in PHP vs. EARTH
 *          GLOBE EUROPE-AFRICA (U+1F30D) in Python, and Ladino had a
 *          single-digit typo (\ufb3e MEM WITH DAGESH instead of \ufb1e
 *          JUDEO-SPANISH VARIKA) repeated across the whole line in
 *          Python. Added VS15 (U+FE0E) after the 9 pictograph
 *          characters still needing a fallback (🔯🖖🧿🕮🗀🌈🗺🪐🌌),
 *          matching the site's own text-presentation convention;
 *          missing_chars_for() now skips variation selectors entirely
 *          (invisible selectors, not glyphs — same reasoning as
 *          skipping spaces). CFD_REFERENCE_FAMILIES gains
 *          NotoSansSymbols2 and NotoEmoji as tiers 3/4, for exactly
 *          those 9 characters — add their font files at the usual
 *          /uploads/fonts/{family}/ convention for this to take effect.
 *          Bumped CFD_SAMPLE_STRINGS_VERSION (3->4) for all of the
 *          above sample-string content changes.
 *   0.11.0 New "Alternate Forms" row, above Missing Characters, for
 *          historically-attested alternate letterforms — the discovery
 *          that started this: Proto Canaanite (Yoram Gnat) documents
 *          typing a base letter + Dagesh/Rafe (or the Phoenician-number
 *          equivalents) to reach a second attested pictograph form for
 *          the same consonant, implemented via a GSUB `ccmp` Ligature
 *          Substitution. font_io.py gained real GSUB parsing (LookupType
 *          1/3/4, with Type 7 Extension transparently unwrapped) to find
 *          this kind of thing generally — verified against fontTools on
 *          synthetic test fonts AND against the real Proto Canaanite
 *          file byte-for-byte before trusting it. inspect_alternates.py
 *          (--survey / --write --only) discovers and opt-in-writes
 *          `alternateForms` per font; deliberately no blanket "run this
 *          on everything" mode, since a --survey across the whole
 *          catalogue showed most GSUB features are ordinary typographic
 *          infrastructure (ligatures, numeral styles), not curated
 *          variants worth a reader's attention. cfd_render_alternate_forms()
 *          shows each entry as [alternate glyph, in the font's own
 *          family only] + [reference base letter, in the usual fallback
 *          stack, in square brackets] — the bracketed reference exists
 *          because an ancient/historical variant isn't always
 *          recognizable on its own.
 *   0.11.1 .cfd-altform-list is RTL (was inheriting the component's
 *          default left-align) — glyph, then its bracketed reference,
 *          now correctly reads right-to-left. inspect_alternates.py's
 *          build_alternate_forms() gained two filters after a real run
 *          across 19 Culmus fonts (not just Proto Canaanite) turned up
 *          noise Proto Canaanite itself never had: (1) degenerate
 *          single-component "ligature" rules some font tools emit
 *          instead of an ordinary single-glyph substitution — produced
 *          useless self-referential entries like "א[א]" with no real
 *          trigger character; now skipped. (2) entries whose output
 *          glyph turned out to be independently reachable via some
 *          other real codepoint — i.e. standard letter+dagesh
 *          composition (Unicode has precomposed codepoints for exactly
 *          this in the FB30-FB4E range), not a genuinely hidden
 *          alternate form. Verified the fix doesn't regress Proto
 *          Canaanite itself (still exactly 62 entries) before trusting it.
 *   0.11.2 "Show wide letterforms" checkbox — the 8 Hebrew wide-letter
 *          presentation forms (FB21-FB28) already interleaved into
 *          CFD_ABGAD are now individually taggable/hideable rather than
 *          fixed always-on. cfd_render_glyph_string() had an early-exit
 *          fast path that skipped cluster-walking entirely when there
 *          was no missing-glyph data to flag — removed, since wide-
 *          letter tagging needs to happen independent of that. Verified
 *          a wide letter that's ALSO missing from a given font correctly
 *          gets both .cfd-missing and .cfd-wide-letter at once, not one
 *          overwriting the other.
 *   0.12.0 Numerals/Punctuation/Symbols sample lines replaced with 7
 *          categories from a full punctuation/numerals/symbols audit:
 *          Numerals & Mathematical Operators, Punctuation, Miscellaneous
 *          Symbols, File Symbols, Religious Signifiers, Meteorology/
 *          Astronomy/Angelology, Gender Symbols. Content generated in
 *          Python and the exact PHP array literal derived FROM that
 *          source (not hand-transcribed twice) — after several rounds
 *          of catching subtle single-character drift bugs between the
 *          two files this way, generating one from the other removes
 *          that whole class of risk. Verified byte-for-byte identical
 *          across all 10 sample strings before trusting it, same as
 *          every previous round. New CFD_CHAR_NAMES constant (267
 *          entries, from Python's unicodedata.name() against the exact
 *          same source text) powers hover tooltips on sample-line
 *          glyphs — cfd_render_glyph_string() gained a $show_tooltips
 *          parameter, on for sample lines, off for the abgad line
 *          (Hebrew letters need it less than ⹝ or ﷻ do). Bumped
 *          CFD_SAMPLE_STRINGS_VERSION (4->5) for the content change.
 *   0.12.1 VS15 applied broadly across every pictograph/emoji-adjacent
 *          category (Misc/File/Religious/Meteorology/Gender Symbols),
 *          not just the 8 specific characters from 0.12.0 — per
 *          instruction to force line-art presentation everywhere it's
 *          meaningful, not selectively. Caught and fixed a real bug
 *          while doing this: the first broad-apply pass double-added
 *          VS15 after characters that already had it (the loop treated
 *          the VS15 codepoint itself as eligible for its own trailing
 *          VS15 on the next iteration) — rebuilt from clean source and
 *          verified, per-token, that every character has exactly one
 *          VS15, not zero or two, before trusting it. Numerals/Math and
 *          Punctuation deliberately left untouched — digits, currency
 *          signs, and punctuation aren't part of Unicode's emoji
 *          variation-sequence system at all, so VS15 there wouldn't be
 *          a harmless no-op, just a meaningless attachment. Bumped
 *          CFD_SAMPLE_STRINGS_VERSION again (5->6).
 *   0.12.2 Real bug found from live DOM (thank you for the DevTools
 *          dump — found this far faster than guessing would have):
 *          VS15 wasn't in cfd_is_combining_mark()'s ranges, so every
 *          VS15 in the new heavily-VS15'd content became its OWN
 *          grapheme cluster and its own <span> — confirmed VS15 is
 *          genuinely Unicode category Mn (Nonspacing_Mark) before
 *          fixing, not just patched blind. That span then also picked
 *          up its own tooltip ("VARIATION SELECTOR-15") since variation
 *          selectors DO have an assigned Unicode name (wrong assumption
 *          in this file's own 0.12.0 comment, now corrected) — added an
 *          explicit exclusion so a cluster's tooltip never includes it,
 *          verified against the exact CLOUD+VS15 case from the DOM dump.
 *          Also fixed the actual reported issue: .cfd-sample-line was
 *          align-items:baseline, which anchors near a wrapped multi-
 *          line label's FIRST line rather than its vertical center —
 *          changed to align-items:center so a short single-line sample
 *          value centers against the full height of a wrapped label
 *          instead of sitting near its top.
 *   0.12.3 .cfd-sample-label had no overflow-wrap/word-break protection
 *          at all, and its fixed 5.5rem column was sized for the old
 *          short single-word labels (Numerals, Punctuation, Symbols) —
 *          not "Miscellaneous Symbols" or "Religious Signifiers", and
 *          text-transform:uppercase made them wider still. Widened the
 *          column to 8rem (comfortably fits any single word in the new
 *          label set) and added explicit overflow-wrap:normal;
 *          word-break:normal;hyphens:none so wrapping can only ever
 *          happen at a real word boundary, never mid-word, regardless
 *          of whether the original cause was the narrow column or an
 *          inherited theme-level break rule. Multi-word labels still
 *          wrap across several lines where they need to (e.g.
 *          "Meteorology, Astronomy, and Angelology") — that's fine now
 *          that 0.12.2 centers the row against a taller wrapped label.
 *   0.13.0 Large batch: (a) styleTags is no longer a persisted field —
 *          cfd_derive_style_tags() computes it fresh from `style` every
 *          request, verified to match the old Python derivation exactly
 *          across all 132 fonts before trusting it; eliminates the
 *          "italki" style/styleTags-drift bug class structurally rather
 *          than just fixing that one instance. (b) category anchor nav
 *          row removed (cfd_render_catnav() retired); section IDs kept,
 *          so direct/bookmarked #category-slug links still work.
 *          (c) new "Diacritics" filter row, reusing the existing
 *          `categories` field via the same generic tag-filter-group
 *          mechanism as Style/Script — no new per-font data needed.
 *          (d) expand toggle moved to sit immediately beside the abgad
 *          row rather than its own separate line — DOM order is
 *          [button, abgad] but CSS order renders abgad first, landing
 *          the button adjacent to Alef since .cfd-abgad right-aligns
 *          flush to its own box edge. (e) missingChars restructured
 *          from one flat combined list into a dict keyed by sample-line
 *          label (generate_coverage.py) — each sample line is now
 *          independently clickable (only when it has something to
 *          reveal) showing just ITS missing characters, replacing the
 *          old single combined "Missing Characters" row before "Test
 *          your own text" entirely. Also fixed a real pre-existing gap
 *          while adding this: .cfd-font-row-main had role="button"
 *          tabindex="0" but no keydown handler at all, so Enter/Space
 *          never actually activated it — added one, using matches()
 *          rather than closest() so it can't double-fire when focus is
 *          on a nested interactive descendant (e.g. the font-name link)
 *          that already has its own native Enter behavior. (f) Yiddish/
 *          Ladino auto-detected as script tags when a font has COMPLETE
 *          coverage of that sample line (not partial — verified a font
 *          missing even one required codepoint correctly doesn't get
 *          tagged); "Hebrew + Latin" renamed to just "Latin" since every
 *          font already supports Hebrew by definition. New
 *          strip_derived_fields.py removes missingChars/styleTags from
 *          fonts.json for easier manual editing, restorable via
 *          generate_coverage.py (styleTags no longer needs restoring at
 *          all, per (a)). Bumped CFD_SAMPLE_STRINGS_VERSION (6->7) for
 *          the missingChars shape change — old flat-list data would
 *          otherwise be silently misread as the new dict shape rather
 *          than cleanly rejected.
 *   0.14.0 Fixed a real bug in the 0.13.0 Diacritics label: PHP
 *          single-quoted strings don't process \u escapes at all (that's
 *          only valid in double-quoted strings, and even there PHP's
 *          syntax is \u{XXXX} with braces, not bare \u2019 the way
 *          Python/JS write it) — so "\u2019" was rendering as that
 *          literal 6-character text instead of an apostrophe. Fixed by
 *          using the actual UTF-8 character directly, matching how
 *          every other string in this file already handles Unicode
 *          content; label simplified to "T'amim" per request. New
 *          Yiddish diagnostic: two ligature/non-ligature comparison
 *          pairs added to the Yiddish line (U+FB1F vs U+05F2+U+05B7,
 *          U+FB1D vs U+05D9+U+05B4, each shown as "ligature
 *          (non-ligature)") — a font handling Yiddish-specific
 *          positioning correctly should render both forms of each pair
 *          consistently; one that just applies generic Hebrew niqqud
 *          placement to Yiddish letters may visibly differ between
 *          them. These four codepoints are now also part of the
 *          "yiddish" script-tag requirement, not just decorative —
 *          verified a font with only the OLD (weaker) coverage no
 *          longer qualifies. No equivalent workaround exists for
 *          Ladino's varika positioning (no precomposed ligature form to
 *          compare against), so that stays as-is. "Latin" split into
 *          "Latin (simple)" (Ḥḥ removed) and a new "Latin (extended)"
 *          line for the transliteration-disambiguation character set.
 *          Regenerated CFD_CHAR_NAMES (267->306 entries) for all the
 *          new codepoints; parentheses excluded from the name table
 *          entirely (structural punctuation, not diagnostically
 *          interesting as a tooltip). Bumped CFD_SAMPLE_STRINGS_VERSION
 *          (7->8) for the content change.
 *   0.14.1 script_tags_for() restructured to add "Latin (simple)"/
 *          "Latin (extended)" as real Script-row checkbox options,
 *          deriving from the SAME named sample lines that power their
 *          display (like Yiddish/Ladino already did) rather than a
 *          separately-hardcoded BASIC_LATIN_LETTERS constant — one
 *          less thing that could drift out of sync with what's actually
 *          shown. Yiddish's yod+hiriq comparison pair moved from the
 *          end of the line to between ױ and ײ; added the missing Ūū to
 *          Latin (extended), between þ and Ẓẓ. Both edits done by
 *          extracting the real current line from the file first and
 *          transforming it programmatically, after catching myself
 *          twice in one sitting hand-retyping Hebrew/Yiddish content
 *          from memory instead of reading it — the exact failure mode
 *          this project has repeatedly hit before, just now on my
 *          str_replace attempts rather than in the final content itself.
 *   0.14.2 Reordered the expanded details panel: Diacritic Support,
 *          Positioning Errors, Style, Foundry, Typographer, Version,
 *          License, Alternate Forms, Notes, "Test your own text", and
 *          the download link now all appear ABOVE .cfd-sample-lines
 *          (the per-category glyph display), rather than below it —
 *          identity/metadata first, then the detailed character
 *          support breakdown.
 *   0.15.0 Two "conditional examples" ported from the legacy
 *          display-font-charmap.php page (which stays untouched — its
 *          eventual replacement is a separate future project): a
 *          traditional cantillation-marks table (each mark's name
 *          written bearing that same mark) and a bracket/quotation-mark
 *          example. Both extracted verbatim from the source file, not
 *          retyped — the bracket example needed string-boundary
 *          extraction specifically, since its angle-bracket phrase has
 *          literal unescaped < > inside the content that breaks a
 *          generic tag-matching regex. Each renders only with COMPLETE
 *          coverage (reusing the exact same missingChars mechanism as
 *          every sample line, just under labels that don't appear in
 *          cfd_sample_lines(), so they're an all-or-nothing example
 *          rather than a checkbox-filterable survey row) — verified the
 *          show/hide gate correctly handles complete coverage, missing
 *          even one character, and stale sampleStringsVersion data, all
 *          three as explicit test cases. Bumped CFD_SAMPLE_STRINGS_VERSION
 *          (8->9) for the missingChars shape change (two new keys).
 *   0.15.1 Real critical PHP error from 0.15.0, caught by the user (not
 *          me): CFD_DIACRITIC_TITLES's closing `) );` got moved to the
 *          wrong position while inserting CFD_CONDITIONAL_EXAMPLES —
 *          left that array never properly closed, with an orphaned
 *          `) );` later in the file. Had nothing to do with the angle-
 *          bracket content I'd flagged as the risky part — pure
 *          mechanical mistake in my own edit script, invisible to
 *          brace/paren COUNTING specifically because moving a bracket
 *          doesn't change the total count, only its position. Fixed by
 *          extracting the surrounding lines by index and splicing, not
 *          retyping the Hebrew content again. New check_php_structure.py
 *          added as a direct response to this gap: tracks paren depth
 *          character-by-character (skipping string contents and heredoc
 *          blocks) and flags a new top-level define( starting before
 *          the previous one closed — verified it catches an exact
 *          recreation of this real bug, at the real line numbers,
 *          before trusting it; also verified no false positive on
 *          well-formed nested arrays inside a function. No PHP CLI
 *          available in this sandbox (network blocked) for a real
 *          `php -l` check — this is a stopgap for that, not a
 *          replacement; run a real one too whenever possible.
 *   0.15.2 Real bug in the Bracket Example, reported by the user: the
 *          English gloss ("Shalom Olam") rendered in its own separate
 *          div below the four Hebrew phrases, instead of inline with
 *          them. Wrong assumption on my part — I'd read the original
 *          page's lack of class='ex4' on that one link as "not
 *          font-tested", but re-checking the CSS, that class only
 *          skipped the hover-to-SBL-Hebrew behavior; by default the
 *          gloss inherited the exact same font stack as everything
 *          else in the same .font container, sitting inline the whole
 *          time. Fixed: this feature now includes the gloss inline in
 *          its original position (5 tokens instead of 4), rendered via
 *          cfd_render_glyph_string() instead of plain esc_html() — so
 *          if a font genuinely lacks something the gloss needs, it
 *          falls back automatically like any sample line, rather than
 *          needing its own special handling. The GATING text (whether
 *          to show the example at all) deliberately stays the original
 *          4 Hebrew-only phrases, unchanged and still verified
 *          byte-identical to Python — a font missing Latin letters
 *          shouldn't hide a working Hebrew bracket-support example over
 *          an unrelated, incidental English caption.
 *   0.15.3 Download link moved from its own standalone <p> (with an SVG
 *          icon) below "Test your own text" into a normal <dt>/<dd> row
 *          in cfd-details-grid, right after License — "Download ⇩" as
 *          the label, the bare {family}.zip filename as the link text.
 *          Retired cfd_download_icon() (only call site was the removed
 *          markup) and the now-unused .cfd-download-line CSS.
 *          cfd_zip_url() unchanged — same absolute-URL construction as
 *          before, just consumed from a new location.
 *   0.16.0 Both conditional-example rows (Cantillation, Bracket) removed
 *          entirely — user feedback after seeing them in action: "Test
 *          your own text" already demonstrates the same thing, with the
 *          person's own eyes on it, making a dedicated always-or-never
 *          row redundant. The bracket/quotation example is folded into
 *          all three CFD_SAMPLE_VERSE_* placeholder tiers instead — the
 *          PLAIN tier gets a niqqud-stripped version (built
 *          programmatically, not retyped) matching that tier's own
 *          consonants-only register, rather than introducing missing-
 *          diacritic fallback noise into an otherwise-clean placeholder.
 *          Caught and fixed a real bug of my own while removing the
 *          Python-side CONDITIONAL_EXAMPLES computation: a leftover
 *          loop-body line with no matching `for` header — genuinely
 *          broken code, caught by actually running the import rather
 *          than trusting the removal script's reported success.
 *          Textarea: rows 3->2, removed a min-height CSS floor that was
 *          forcing extra height past what rows alone needs, and the
 *          border-top divider above .cfd-sample-lines is gone (was
 *          effectively "a line below the textarea" too, being directly
 *          adjacent to it). Sample-line hover background changed from a
 *          hardcoded light cream to a perceptually-neutral semi-
 *          transparent gray, fixing an illegible-text-on-white issue in
 *          dark mode without needing to coordinate with the separate
 *          dark-mode.css file — deliberately left the older, pre-
 *          existing hardcoded backgrounds elsewhere (the row card,
 *          several buttons) untouched, since those were very likely
 *          already covered by that file's existing overrides, and this
 *          hover rule specifically was added well after that pass.
 *          Bumped CFD_SAMPLE_STRINGS_VERSION (9->10) for the
 *          missingChars shape change (two keys removed).
 *   0.17.0 Fallback font replaced: LastResort+UnicodeBMPFallback (2
 *          files, open-box "tofu" glyphs) swapped for UnicodeHexMono by
 *          Pratnomenis (MIT, github.com/Pratnomenis/unicode-hex-mono) —
 *          20 files partitioned by unicode-range, each glyph showing
 *          its own hex codepoint instead of a blank box. Independently
 *          confirmed as a real, legitimate project via web search before
 *          trusting it, and verified the two sample WOFF2 files provided
 *          are genuinely well-formed (valid WOFF2 signature, correct
 *          table directory, cmap/CFF/OS2 tables present) — though full
 *          cmap coverage per range couldn't be verified directly, since
 *          WOFF2's table payloads are Brotli-compressed and no Brotli
 *          implementation is available in this sandbox (no network
 *          access to install one). Extracted and verified all 20
 *          unicode-range/filename pairs programmatically from the
 *          user's actual font.css rather than retyped, and confirmed
 *          they're genuinely gapless and non-overlapping across the
 *          entire valid Unicode space (U+0000-10FFFD) before trusting
 *          them. Only WOFF2 referenced — the matching OTF files weren't
 *          uploaded (20MB each) and would essentially never be
 *          requested by a real browser anyway, given woff2 is listed
 *          first and universally supported. Caught and fixed a real
 *          duplicate-define(CFD_FALLBACK_BASE_URL,...) bug of my own
 *          while making this edit — the str_replace that touched the
 *          constant's surrounding comment landed correctly, but left a
 *          second, stale define() further down the file, which would
 *          have silently overridden it at runtime with no warning or
 *          error. check_php_structure.py gained duplicate-constant
 *          detection directly in response — verified against a
 *          synthetic duplicate before trusting it, and confirmed no
 *          false positive on legitimate single-definition code.
 *   0.17.1 CFD_FALLBACK_BASE_URL briefly pointed at
 *          /uploads/fonts/FallbackFont during 0.17.0's development,
 *          reasoning the user had placed the 20 new files there — user
 *          clarified they'd actually put them in this plugin's own
 *          assets/fonts/, matching the ORIGINAL pre-existing convention.
 *          Reverted to CFD_URL . 'assets/fonts' (unchanged from before
 *          this whole fallback-font swap) and corrected the 0.17.0
 *          changelog text above, which had described a location change
 *          that turned out not to reflect where the files actually
 *          ended up.
 *   0.18.0 The "Test your own text" textarea now uses a shorter,
 *          separate font-stack (cfd_font_stack()'s new
 *          $include_reference_tier=false) — primary font straight to
 *          FallbackFont, skipping SimpleCLM/FreeSerif/Noto entirely.
 *          Root cause, from a user report initially framed as "why
 *          isn't FallbackFont being invoked": that reference tier is
 *          genuinely correct everywhere else (abgad, sample lines),
 *          since cfd_render_glyph_string() wraps each character in its
 *          own <span> and grays a fallback-tier hit via .cfd-missing —
 *          an honest, visible signal. A <textarea>'s value is plain
 *          text with no per-character wrapping possible, so a character
 *          missing from the tested font that fell through to the
 *          reference tier rendered at full opacity, indistinguishable
 *          from one genuinely supported — quietly defeating the exact
 *          thing this field exists to reveal. Going straight to
 *          FallbackFont means a missing character now shows an
 *          unmistakable hex-code box instead, with nothing convincing-
 *          looking in between to mask it.
 *   0.19.0 Font name is plain text again, not a link — the charmap page
 *          link moved into the Notes row instead ("Diacritic positioning
 *          across the AlefBet"), appended after any existing curated
 *          notes text with an em-dash separator. Fixes a real visual
 *          bug (the link's clickable area extended past the visible
 *          name into empty space) as a side effect of a bigger change:
 *          the +/− expand button is gone entirely, and the WHOLE visible
 *          row (name + abgad) is now the click target — nothing left
 *          inside .cfd-font-row-main to intercept a click via its own
 *          default behavior (a real <a> tag's navigation) the way the
 *          old name-link did, so the existing role="button" handler on
 *          the row now fires cleanly for any click within it. Fixed two
 *          real latent bugs this exposed: two separate
 *          `.querySelector('.cfd-expand-toggle').textContent = ...`
 *          lines (single-row toggle, and the expand-all/collapse-all
 *          handler) would have thrown null-reference errors the moment
 *          that button no longer existed in the DOM — removed both,
 *          along with the now-fully-unused .cfd-abgad-row wrapper
 *          (existed solely to position the button next to Alef) and all
 *          its CSS.
 *   0.20.0 New "Semi-Cursive" style tag, replacing "mashkit" and
 *          "symbols-dingbats" in CFD_STYLE_TAG_MAP/CFD_STYLE_TAG_TITLES.
 *          New cfd_filtered_abgad(): fonts whose style is Sans-Sofit,
 *          Cursive, or ktav-yad no longer show the 8 wide-letterform
 *          presentation forms, alternative ayin, or backward nun in
 *          their abgad — those forms represent a scribal/justification
 *          tradition that doesn't apply the same way to those letter
 *          styles. Filters CHARACTER BY CHARACTER within each token
 *          rather than by whole-token match — CFD_ABGAD's final token
 *          is "ﬨ׃" (wide tav glued directly to sof pasuq, no separating
 *          space), so removing wide tav needed to leave sof pasuq
 *          behind rather than dropping the whole token. Sof pasuq
 *          staying at the end either way is intentional — it's its own
 *          visual support test, placed where it conventionally sits at
 *          the end of a verse or liturgical paragraph. Verified the
 *          filtering against the real CFD_ABGAD content (which token
 *          needed character-level vs. whole-token handling), and
 *          verified the pass-through case (a font matching none of the
 *          three styles) returns the original string completely
 *          unchanged, byte-identical.
 *          Also: a fonts.json shared this round had every font's
 *          missingChars key entirely absent, breaking .cfd-missing
 *          graying everywhere — not a code bug, just the file being in
 *          strip_derived_fields.py's "stripped for editing" state,
 *          with generate_coverage.py never re-run afterward to restore
 *          it. Confirmed via diff against the previous known-good file
 *          (which needed normalizing CRLF line endings first — a
 *          near-total block-level diff from line-ending differences
 *          alone, resolved before the real 3-line substantive change
 *          — the style-tag swap above — became visible at all).
 *   0.20.1 CFD_ABGAD_EXCLUDED_FOR_STYLES expanded from 3 styles to 8:
 *          added inscription, scribal, ancient, semi-cursive, and
 *          monospaced alongside the original sans-sofit, cursive, and
 *          ktav-yad. Only 5 new canonical values, not 6 — "ancient" and
 *          "antiquity" were both requested, but CFD_STYLE_TAG_MAP
 *          already aliases "antiquity" source text to the same
 *          canonical "ancient" tag, so there's nothing separate to add.
 *          Verified against the real style-derivation logic that both
 *          "Ancient" and "Antiquity" as source `style` text trigger
 *          filtering identically, and that an unrelated style (Serif)
 *          correctly doesn't.
 *   0.21.0 abgad filtering restructured from one rule to three
 *          independent ones: (1) wide letterforms + backward nun,
 *          unchanged 8-style trigger list; (2) alternative ayin
 *          (U+FB20), now its OWN rule — excluded for any "display-only"
 *          category font regardless of style (new), or for 7 of the
 *          original 8 styles regardless of category (unchanged) —
 *          "scribal" deliberately removed from that style list, since a
 *          non-display-only scribal font may have diacritics turned on
 *          and specifically need this form for correct positioning
 *          alongside them; (3) new — the 5 sofit (final-letter) forms
 *          ך/ם/ן/ף/ץ, excluded only for "sans-sofit" fonts. Real
 *          complication in rule 3: CFD_ABGAD's "מ־ם" token (regular
 *          mem + maqaf + final mem, used to show the contrast compactly)
 *          has no OTHER standalone "מ" token anywhere else in the
 *          string — removing final mem naively would either lose
 *          regular mem entirely (dropping the whole token) or leave a
 *          dangling "מ־" with a maqaf connecting to nothing. Added
 *          cfd_strip_edge_maqaf(), using mb_substr in a loop rather
 *          than PHP's byte-oriented (and therefore UTF-8-unsafe)
 *          rtrim()/ltrim(), to correctly reduce it to bare "מ" only
 *          when sofit filtering actually applies. Verified 5 scenarios
 *          against the real CFD_ABGAD content — each rule alone, the
 *          display-only-overrides-scribal interaction specifically, and
 *          full pass-through — by transcribing the ACTUAL final PHP
 *          function into Python and confirming identical output to the
 *          independently-designed-and-validated version, not just
 *          checking the design once and assuming the implementation
 *          matched it.
 *   0.22.0 New Rule 4: qamats qatan under kaf — the "כׇ" token
 *          specifically — excluded for "sans-diacritics" category fonts
 *          (a niqqud-positioning test, meaningless with no niqqud
 *          support at all). Whole-token removal, not character-level —
 *          bare kaf already has its own separate token earlier in
 *          CFD_ABGAD, so stripping just the combining mark would leave
 *          a duplicate bare kaf behind, unlike Rule 3's "מ־ם" case
 *          (where mem has no other standalone token to fall back on).
 *          The token constant extracted directly from CFD_ABGAD and
 *          confirmed byte-identical, not retyped.
 *          Renamed "display-only" to "sans-diacritics" throughout —
 *          the category was easily confused with the unrelated "Display"
 *          style. Updated everywhere it's live/functional: the category
 *          check in cfd_filtered_abgad() (Rules 2 and 4), CFD_DIACRITIC_TITLES,
 *          and the usage-example comments — left untouched everywhere
 *          it's historical changelog text describing what was
 *          accurate terminology at the time. fonts.json's rename done
 *          programmatically (both the `categories` structure's key and
 *          every affected font's `categories` array — 60 fonts), not
 *          via find-and-replace on raw text, then verified by
 *          serializing the whole document and confirming zero remaining
 *          occurrences of the old slug anywhere in it, not just in the
 *          fields expected to contain it.
 *   0.23.0 Style names renamed at the source: "Display" -> "Fancy",
 *          "Monospaced" -> "Fixed-width" — CFD_STYLE_TAG_MAP/TITLES
 *          updated to match (both canonical tag AND source-text key
 *          renamed, not just the display label), and the two abgad-
 *          filtering rules that referenced "monospaced" as a trigger
 *          style updated to "fixed-width" so the exclusion behavior
 *          carries over unchanged. Verified every real style value from
 *          the actual fonts.json ("Fancy", "Fancy / Sans-Serif", "Fancy
 *          / ktav-yad", "Fixed-width / Sans-Serif") still derives at
 *          least one tag — the failure mode being guarded against is a
 *          silent empty derivation, which would have made a font's
 *          entire style invisible to both the checkbox filter and the
 *          abgad-filtering rules with no error or warning anywhere.
 *          Checkbox ordering: cfd_render_tag_filter_group() rendered
 *          discovered tags via ksort() (alphabetical) — rewritten to
 *          render in the passed-in $tag_titles array's OWN key order
 *          instead, with any present-but-unlisted tag falling through
 *          to the end (alphabetically) as a safety net rather than
 *          silently vanishing. CFD_STYLE_TAG_TITLES and
 *          CFD_DIACRITIC_TITLES reordered to the requested sequence.
 *          New CFD_SCRIPT_TAG_TITLES constant — script tag titles used
 *          to come from fonts.json's _meta.scriptTagTitles, whose
 *          ordering (a JSON object's own key order) is one stray
 *          re-serialization away from silently breaking; moved to a PHP
 *          constant for the same reason 0.13.0 already did this for
 *          style tags. fonts.json's _meta.scriptTagTitles is now
 *          unused (harmless to leave, safe to remove whenever
 *          convenient — not touched here). Verified all three groups'
 *          final rendering order (Style, Script, Diacritics) by
 *          transcribing the actual final PHP function into Python and
 *          running it against realistic discovered-tag sets in
 *          scrambled order, confirming exact match to the requested
 *          sequence in every case, plus the unknown-tag safety-net path
 *          specifically.
 *   0.23.1 Dark-mode fix: .cfd-filter-chip (the "Style: 0 of 13 shown"
 *          active-filter summary) had no explicit color of its own —
 *          only its inner <button> did — so its text inherited whatever
 *          the ambient text color was, landing white-on-cream in dark
 *          mode. Same class of bug as the sample-line hover fix a few
 *          versions back: a newer element hadn't caught up to a pattern
 *          its older siblings already followed. .cfd-filter-toggle-all,
 *          .cfd-clear-all, and .cfd-filter-link all already set their
 *          own explicit color — added the same to .cfd-filter-chip,
 *          matching .cfd-filter-toggle-all/.cfd-filter-link's existing
 *          #6b6152 rather than introducing a new tone.
 *   0.24.0 Rule 1 split in two: wide-letterform exclusion (Rule 1)
 *          stays on all 8 original styles unchanged; backward nun
 *          (U+05C6) is now its OWN rule (1b) with "scribal" and
 *          "fixed-width" removed from its trigger list — user feedback
 *          that backward nun's own tradition doesn't line up with
 *          those two styles the way the wide letterforms' does, on
 *          reflection. The other 6 styles (sans-sofit, cursive,
 *          ktav-yad, inscription, ancient, semi-cursive) still exclude
 *          backward nun exactly as before. Verified via all four
 *          relevant scenarios — scribal alone, fixed-width alone (both
 *          should now show backward nun, with wide letters still
 *          correctly absent), and cursive/sans-sofit alone (both should
 *          be completely unaffected, backward nun still excluded) — by
 *          transcribing the actual final PHP function into Python
 *          before trusting the change.
 *   0.25.0 URL-based deep-linking: crafted URLs can now filter by a
 *          single font (cfd_font), any of the three checkbox groups
 *          (cfd_style/cfd_script/cfd_category — the last one named to
 *          match the [category=""] shortcode attribute, not the
 *          "Diacritics" checkbox label), or foundry/typographer
 *          (cfd_foundry/cfd_typographer) — see the Usage docblock above
 *          for the full reference. State reads from the URL on load and
 *          writes back via history.replaceState on every filter change
 *          (never pushState, so filtering doesn't pile up history
 *          entries), making any filtered view directly copy-paste
 *          shareable from the address bar. New data-slug attribute on
 *          each .cfd-font-row (didn't exist before — needed for the
 *          single-font filter to have anything to match against).
 *          Genuinely new architecture (state.fontFilter alongside the
 *          existing groups/identityFilter, new readUrlState()/
 *          applyUrlState()/writeUrlState() functions), so verified far
 *          more thoroughly than a syntax check: built a hand-written
 *          DOM mock (jsdom unavailable — no network access in this
 *          sandbox to install it) faithful enough to actually EXECUTE
 *          the real extracted script via Node's vm module against a
 *          realistic 3-font catalog, across 7 scenarios — no filter,
 *          single font, single style, combined style+script (verified
 *          AND-across-groups logic specifically), single category,
 *          identity filter, and a deliberately adversarial malformed
 *          cfd_font containing a literal double-quote. That last one
 *          caught a real bug before it shipped: the first draft of the
 *          active-filter chip's font-name lookup built a CSS attribute
 *          selector via string concatenation
 *          ('.cfd-font-row[data-slug="' + state.fontFilter + '"]') —
 *          a value taken directly from the URL containing a stray "
 *          would have thrown a DOMException and aborted the entire
 *          refresh(), including the filtering itself. Replaced with a
 *          direct value-comparison Array.find(), which the mock test
 *          confirmed handles the same malformed input cleanly with no
 *          crash, correctly escaped in the rendered chip.
 *   0.25.1 URL params un-prefixed per user request: cfd_font/cfd_style/
 *          cfd_script/cfd_category/cfd_foundry/cfd_typographer ->
 *          font/style/script/category/foundry/typographer. The cfd_
 *          prefix existed specifically to avoid colliding with some
 *          other plugin or WordPress behavior using the same generic
 *          name on the same page — a real but low-probability risk,
 *          and a site-specific judgment call better made by whoever's
 *          embedding this than by this file. Re-ran the full 7-scenario
 *          mock-DOM execution suite from 0.25.0 with the new unprefixed
 *          URLs before trusting the change — identical results across
 *          the board, including the adversarial malformed-input case.
 *   0.26.0 The "Show wide letterforms" toggle joins the URL-sync system:
 *          ?wideletterforms=off (checked/"on" is the default, so that's
 *          the only value ever worth writing). New setWideLettersVisible()
 *          shared between applyUrlState() and the checkbox's own change
 *          handler, replacing two copies of the same class-toggling
 *          logic with one. Unlike the checkbox groups, this toggle was
 *          never part of the shared state/refresh() system — it only
 *          ever flips a CSS class, never row visibility — so
 *          writeUrlState() reads the checkbox directly rather than
 *          tracking a redundant state.hideWide. Extended the existing
 *          mock-DOM execution suite with 4 more scenarios rather than
 *          writing a separate one: default/no-param, explicit off,
 *          typo'd value (confirmed treated as "on" and self-correcting
 *          out of the rewritten URL, same pattern as an unrecognized
 *          tag), and combined with another active filter simultaneously
 *          — all passing, plus re-ran the original 7 to confirm nothing
 *          regressed.
 * -------------------------------------------------------------------
 *
 * Usage:
 *   [custom_fonts_display]                                            -> everything
 *   [custom_fonts_display category="full-diacritic-support"]          -> one category
 *   [custom_fonts_display category="partial-diacritic-support,sans-diacritics"] -> several
 *   [custom_fonts_display font="heebo"]                                -> a single font,
 *                                                                         no headings/toolbar
 *
 * URL query parameters (client-side, read on load, kept in sync via
 * history.replaceState as filters change — so any filtered view is
 * copy-paste shareable straight from the address bar). These are a
 * DIFFERENT mechanism from the [font=""]/[category=""] shortcode
 * attributes above, despite sharing the same two names — that one is
 * server-side and strips the toolbar/headings entirely for a minimal
 * single-font embed; these apply on top of the FULL page (toolbar,
 * headings, and all), just hiding whichever rows don't match — for
 * sharing a filtered VIEW of the regular catalog page, not a standalone
 * embed. Deliberately UNPREFIXED (font/style/script/category/foundry/
 * typographer, not cfd_font/cfd_style/etc — an earlier version used a
 * cfd_ prefix specifically to avoid colliding with some other plugin or
 * WordPress behavior using the same generic name on the same page;
 * dropped on request, since that's a site-specific judgment call the
 * person embedding this plugin is better placed to make than this file
 * is). Multiple params combine with AND; multiple values within one
 * param combine with OR (e.g. style=cursive,scribal means "cursive OR
 * scribal", same as checking both boxes):
 *   ?font=keter-aram-sova          -> only that one font shown
 *   ?style=cursive,scribal         -> Style checkboxes limited to these
 *   ?script=yiddish                -> Script checkboxes limited to this
 *   ?category=sans-diacritics      -> Diacritics checkboxes limited to this
 *                                      ("category" here to match the
 *                                      shortcode attribute's name above,
 *                                      even though the checkbox group
 *                                      itself is labeled "Diacritics")
 *   ?foundry=Culmus+Project        -> the foundry "Show only" filter
 *   ?typographer=Maxim+Iorsh       -> the typographer "Show only" filter
 *   ?wideletterforms=off           -> unchecks "Show wide letterforms"
 *                                      (checked/"on" is the default, so
 *                                      only "off" ever needs writing out;
 *                                      any other value, including a typo,
 *                                      is treated the same as absent)
 *   ?style=cursive&script=yiddish  -> combined: cursive-style AND
 *                                      Yiddish-script fonts only
 *
 * Values are tag slugs (matching data-tag-value / fonts.json's own
 * categories/styleTags/scriptTags values), not display labels — stable
 * identifiers that won't break if a checkbox's wording changes later.
 *
 * Font files themselves are unchanged: still expected at
 *   /wp-content/uploads/fonts/{family}/{family}.woff2 (and .woff)
 * exactly as abgad.php already assumed. This plugin only replaces how the
 * catalogue is *assembled and displayed*, not where the per-font binaries
 * live. The four SHARED fallback fonts (used across every row, not tied to
 * any one typeface) are the one exception — see CFD_FALLBACK_BASE_URL below.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CFD_VERSION', '0.26.0' );
define( 'CFD_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFD_URL', plugin_dir_url( __FILE__ ) );
define( 'CFD_DATA_FILE', CFD_DIR . 'data/fonts.json' );

/* Where per-font binaries live — unchanged from the current abgad.php convention. */
define( 'CFD_FONT_BASE_URL', content_url( 'uploads/fonts' ) );

/* Where the shared fallback font lives: UnicodeHexMono (Pratnomenis,
   MIT licensed — github.com/Pratnomenis/unicode-hex-mono), 20 files
   partitioned by unicode-range across the ENTIRE valid Unicode space
   (U+0000-10FFFD, verified gapless/non-overlapping before trusting it —
   see chat), each glyph showing its own hex codepoint rather than a
   blank box. Replaces the earlier LastResort+UnicodeBMPFallback pair —
   still an open-box-style "something is missing here" signal, but a
   genuinely more informative one: which codepoint, not just that one
   exists. Only WOFF2 uploaded (the matching OTF files are ~20MB each
   and effectively never requested anyway — format('woff2') means a
   browser satisfied by the first, universally-supported source in this
   list has no reason to fall through to a second entry, so there was
   nothing worth referencing here even if the OTFs existed).
   Lives in this plugin's own assets/fonts/ — plugin behavior (how
   missing glyphs render), not catalogue content, matching the same
   reasoning an earlier session already established for this constant. */
define( 'CFD_FALLBACK_BASE_URL', CFD_URL . 'assets/fonts' );

/* Readable-reference tier(s) used as the "here's what this character
   actually looks like" fallback for characters a font is missing — real
   letterforms from broad-coverage fonts, rather than an abstract box
   glyph. Tried in this order, before finally falling back to the
   open-box FallbackFont pair. SimpleCLM is the site's own default
   sans-serif; FreeSerif is a second-tier fallback for broader Hebrew/
   Latin coverage. NotoSansSymbols2 and NotoEmoji cover the remaining
   pictograph/emoji characters in the Symbols sample line (🔯🖖🧿🕮🗀🌈🗺🪐🌌)
   that neither of the first two fonts has — Noto Sans Symbols2 first
   since it's purely monochrome/outline by design (matches the site's
   own text-presentation convention, no color variant to worry about),
   Noto Emoji second as a narrower-but-still-monochrome backup for
   anything Symbols2 doesn't cover. Both SIL OFL, actively maintained.
   NOTE: unlike SimpleCLM/FreeSerif, these two are not yet part of the
   font catalogue — add their files at the same /uploads/fonts/{family}/
   convention as everything else (NotoSansSymbols2/NotoSansSymbols2.woff2,
   NotoEmoji/NotoEmoji.woff2) for this tier to actually take effect;
   until then they just 404 harmlessly and fall through to the next tier.
   Each one's @font-face is declared once in cfd_inline_css() (like
   FallbackFont) since every row's font-stack references them, not just
   their own row when one happens to be on display. */
define( 'CFD_REFERENCE_FAMILIES', array( 'SimpleCLM', 'FreeSerif', 'NotoSansSymbols2', 'NotoEmoji' ) );

/* Bump alongside SAMPLE_STRINGS_VERSION in generate_coverage.py whenever
   CFD_ABGAD or cfd_sample_lines() changes below, OR whenever the SHAPE of
   the derived data itself changes (e.g. the 0.13.0 missingChars restructure
   from a flat list to a per-line dict) — a mismatch here means the
   fonts.json on disk was computed against something this file no longer
   expects, in content or in shape, and must not be trusted for glyph
   coloring / missingChars. See cfd_missing_codepoint_set(). */
define( 'CFD_SAMPLE_STRINGS_VERSION', 10 );

/* Kill switch for the "Alternate Forms" row (0.11.0+). Off by default —
   a real run across 19 Culmus fonts turned out to be mostly standard
   letter+dagesh composition infrastructure rather than curated
   historical variants worth a reader's attention (see chat). Data
   already written into fonts.json's `alternateForms` field is left
   alone either way; this only controls whether it renders. */
define( 'CFD_SHOW_ALTERNATE_FORMS', false );

/* U+FB21-FB28: the eight Hebrew "wide letter" presentation forms
   (alef/dalet/he/kaf/lamed/final-mem/resh/tav), already interleaved
   into CFD_ABGAD after their regular counterpart. cfd_render_glyph_string()
   tags any cluster whose base character is one of these with
   .cfd-wide-letter, toggled by the "Show wide letterforms" checkbox —
   see cfd_render_toolbar(). Shown by default (matches prior behavior;
   this only adds an opt-out, doesn't change what anyone sees by default). */
define( 'CFD_WIDE_LETTER_CODEPOINTS', array( 0xFB21, 0xFB22, 0xFB23, 0xFB24, 0xFB25, 0xFB26, 0xFB27, 0xFB28 ) );

/* Style-tag taxonomy — ported from migrate_fonts_schema.py's
   STYLE_TAG_MAP/STYLE_TAG_TITLES (0.13.0+). `styleTags` is no longer a
   persisted fonts.json field: it's cheap and fully deterministic to
   derive from `style` (a hand-edited text field), so persisting it
   separately only created a way for the two to silently drift — which
   is exactly what happened with Farissol CLM's "italki" edit. Computed
   fresh for every displayed font in cfd_render_shortcode(), same
   function every time, so it can never go stale the way a persisted
   copy could. Contrast with `scriptTags`, which DOES stay persisted —
   that one comes from actually reading the font file's cmap, not
   something PHP can cheaply redo on every page load. */
define( 'CFD_STYLE_TAG_MAP', array(
	'serif'              => 'serif',
	'sans-serif'         => 'sans-serif',
	'cursive'            => 'cursive',
	'fancy'              => 'fancy',
	'fixed-width'        => 'fixed-width',
	'scribal'            => 'scribal',
	'ktav-yad'           => 'ktav-yad',
	'semi-cursive'       => 'semi-cursive',
	'ashkenaz'           => 'ashkenaz',
	'sepharadi'          => 'sepharadi',
	'ancient'            => 'ancient',
	'antiquity'          => 'ancient',
	'inscription'        => 'inscription',
	'sans-sofit'         => 'sans-sofit',
) );

/* Order here is the checkbox display order, not alphabetical — see
   cfd_render_tag_filter_group(), which renders in THIS array's key
   order rather than sorting discovered tags alphabetically. */
define( 'CFD_STYLE_TAG_TITLES', array(
	'serif'            => 'Serif',
	'sans-serif'       => 'Sans-Serif',
	'fixed-width'      => 'Fixed-width',
	'scribal'          => 'Scribal',
	'semi-cursive'     => 'Semi-Cursive',
	'cursive'          => 'Cursive',
	'ktav-yad'         => 'Ktav-Yad',
	'fancy'            => 'Fancy',
	'ashkenaz'         => 'Ashkenaz',
	'sepharadi'        => 'Sepharadi',
	'ancient'          => 'Ancient/Antiquity',
	'inscription'      => 'Inscription',
	'sans-sofit'       => 'Sans-Sofit',
) );

/* Moved out of fonts.json's _meta.scriptTagTitles (0.13.0 already did
   this for style tags, for the same reason): a PHP constant guarantees
   this exact order persists regardless of how fonts.json gets
   reformatted or regenerated later, whereas relying on a JSON object's
   own key order is one stray `sort_keys=True` away from silently
   breaking. fonts.json's _meta.scriptTagTitles is now unused — safe to
   remove in a future cleanup, not touched here since that's your data
   to manage on your own schedule. */
define( 'CFD_SCRIPT_TAG_TITLES', array(
	'hebrew-only'    => 'Hebrew only',
	'hebrew-latin'   => 'Latin (simple)',
	'latin-extended' => 'Latin (extended)',
	'ladino'         => 'Ladino',
	'yiddish'        => 'Yiddish',
) );

/**
 * Splits a `style` string like "sans-sofit / inscription / ancient" on
 * "/" and maps each fragment to its canonical tag via CFD_STYLE_TAG_MAP.
 * An unrecognized fragment (e.g. a style value mid-decision, like
 * Farissol CLM's "italki") is silently dropped, not erred on — matches
 * migrate_fonts_schema.py's prior behavior of filtering out
 * "UNRECOGNIZED:" entries before ever persisting them.
 */
function cfd_derive_style_tags( $style_text ) {
	$tags = array();
	foreach ( explode( '/', (string) $style_text ) as $part ) {
		$key = strtolower( trim( $part ) );
		if ( $key === '' ) {
			continue;
		}
		if ( isset( CFD_STYLE_TAG_MAP[ $key ] ) ) {
			$tag = CFD_STYLE_TAG_MAP[ $key ];
			if ( ! in_array( $tag, $tags, true ) ) {
				$tags[] = $tag;
			}
		}
	}
	return $tags;
}

/* Diacritic-support category display titles, for the "Diacritics"
   filter row — reuses the existing `categories` field directly (no new
   per-font data needed) via the same generic tag-filter mechanism as
   Style/Script. */
define( 'CFD_DIACRITIC_TITLES', array(
	'full-diacritic-support'    => 'T’amim',
	'partial-diacritic-support' => 'Niqqud only',
	'failing-diacritic-support' => 'Positioning fail',
	'sans-diacritics'           => 'Sans-Diacritics',
) );


/* Unicode character names for every codepoint used across CFD_ABGAD and
   cfd_sample_lines() — powers the hover tooltip on sample-line glyphs
   (see cfd_render_glyph_string()'s $show_tooltips parameter). Generated
   from Python's unicodedata.name() against the exact same source text
   as the sample strings themselves, not hand-typed — see chat. Update
   this alongside any future sample-string edit, same discipline as
   SAMPLE_STRINGS_VERSION. Codepoints with no real name (variation
   selectors) are simply absent — cfd_render_glyph_string() falls back
   to no tooltip for those, not an error. */
define( 'CFD_CHAR_NAMES', array(
	0x0030 => 'DIGIT ZERO',
	0x0031 => 'DIGIT ONE',
	0x0032 => 'DIGIT TWO',
	0x0033 => 'DIGIT THREE',
	0x0034 => 'DIGIT FOUR',
	0x0035 => 'DIGIT FIVE',
	0x0036 => 'DIGIT SIX',
	0x0037 => 'DIGIT SEVEN',
	0x0038 => 'DIGIT EIGHT',
	0x0039 => 'DIGIT NINE',
	0x003A => 'COLON',
	0x003B => 'SEMICOLON',
	0x0041 => 'LATIN CAPITAL LETTER A',
	0x0042 => 'LATIN CAPITAL LETTER B',
	0x0043 => 'LATIN CAPITAL LETTER C',
	0x0044 => 'LATIN CAPITAL LETTER D',
	0x0045 => 'LATIN CAPITAL LETTER E',
	0x0046 => 'LATIN CAPITAL LETTER F',
	0x0047 => 'LATIN CAPITAL LETTER G',
	0x0048 => 'LATIN CAPITAL LETTER H',
	0x0049 => 'LATIN CAPITAL LETTER I',
	0x004A => 'LATIN CAPITAL LETTER J',
	0x004B => 'LATIN CAPITAL LETTER K',
	0x004C => 'LATIN CAPITAL LETTER L',
	0x004D => 'LATIN CAPITAL LETTER M',
	0x004E => 'LATIN CAPITAL LETTER N',
	0x004F => 'LATIN CAPITAL LETTER O',
	0x0050 => 'LATIN CAPITAL LETTER P',
	0x0051 => 'LATIN CAPITAL LETTER Q',
	0x0052 => 'LATIN CAPITAL LETTER R',
	0x0053 => 'LATIN CAPITAL LETTER S',
	0x0054 => 'LATIN CAPITAL LETTER T',
	0x0055 => 'LATIN CAPITAL LETTER U',
	0x0056 => 'LATIN CAPITAL LETTER V',
	0x0057 => 'LATIN CAPITAL LETTER W',
	0x0058 => 'LATIN CAPITAL LETTER X',
	0x0059 => 'LATIN CAPITAL LETTER Y',
	0x005A => 'LATIN CAPITAL LETTER Z',
	0x0061 => 'LATIN SMALL LETTER A',
	0x0062 => 'LATIN SMALL LETTER B',
	0x0063 => 'LATIN SMALL LETTER C',
	0x0064 => 'LATIN SMALL LETTER D',
	0x0065 => 'LATIN SMALL LETTER E',
	0x0066 => 'LATIN SMALL LETTER F',
	0x0067 => 'LATIN SMALL LETTER G',
	0x0068 => 'LATIN SMALL LETTER H',
	0x0069 => 'LATIN SMALL LETTER I',
	0x006A => 'LATIN SMALL LETTER J',
	0x006B => 'LATIN SMALL LETTER K',
	0x006C => 'LATIN SMALL LETTER L',
	0x006D => 'LATIN SMALL LETTER M',
	0x006E => 'LATIN SMALL LETTER N',
	0x006F => 'LATIN SMALL LETTER O',
	0x0070 => 'LATIN SMALL LETTER P',
	0x0071 => 'LATIN SMALL LETTER Q',
	0x0072 => 'LATIN SMALL LETTER R',
	0x0073 => 'LATIN SMALL LETTER S',
	0x0074 => 'LATIN SMALL LETTER T',
	0x0075 => 'LATIN SMALL LETTER U',
	0x0076 => 'LATIN SMALL LETTER V',
	0x0077 => 'LATIN SMALL LETTER W',
	0x0078 => 'LATIN SMALL LETTER X',
	0x0079 => 'LATIN SMALL LETTER Y',
	0x007A => 'LATIN SMALL LETTER Z',
	0x00A7 => 'SECTION SIGN',
	0x00AB => 'LEFT-POINTING DOUBLE ANGLE QUOTATION MARK',
	0x00B0 => 'DEGREE SIGN',
	0x00B6 => 'PILCROW SIGN',
	0x00B7 => 'MIDDLE DOT',
	0x00BB => 'RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK',
	0x00BC => 'VULGAR FRACTION ONE QUARTER',
	0x00BD => 'VULGAR FRACTION ONE HALF',
	0x00BE => 'VULGAR FRACTION THREE QUARTERS',
	0x00C2 => 'LATIN CAPITAL LETTER A WITH CIRCUMFLEX',
	0x00C8 => 'LATIN CAPITAL LETTER E WITH GRAVE',
	0x00C9 => 'LATIN CAPITAL LETTER E WITH ACUTE',
	0x00CD => 'LATIN CAPITAL LETTER I WITH ACUTE',
	0x00D7 => 'MULTIPLICATION SIGN',
	0x00E2 => 'LATIN SMALL LETTER A WITH CIRCUMFLEX',
	0x00E8 => 'LATIN SMALL LETTER E WITH GRAVE',
	0x00E9 => 'LATIN SMALL LETTER E WITH ACUTE',
	0x00ED => 'LATIN SMALL LETTER I WITH ACUTE',
	0x00FE => 'LATIN SMALL LETTER THORN',
	0x0102 => 'LATIN CAPITAL LETTER A WITH BREVE',
	0x0103 => 'LATIN SMALL LETTER A WITH BREVE',
	0x014A => 'LATIN CAPITAL LETTER ENG',
	0x014B => 'LATIN SMALL LETTER ENG',
	0x014C => 'LATIN CAPITAL LETTER O WITH MACRON',
	0x014D => 'LATIN SMALL LETTER O WITH MACRON',
	0x015A => 'LATIN CAPITAL LETTER S WITH ACUTE',
	0x015B => 'LATIN SMALL LETTER S WITH ACUTE',
	0x0160 => 'LATIN CAPITAL LETTER S WITH CARON',
	0x0161 => 'LATIN SMALL LETTER S WITH CARON',
	0x018F => 'LATIN CAPITAL LETTER SCHWA',
	0x0190 => 'LATIN CAPITAL LETTER OPEN E',
	0x0198 => 'LATIN CAPITAL LETTER K WITH HOOK',
	0x0199 => 'LATIN SMALL LETTER K WITH HOOK',
	0x021C => 'LATIN CAPITAL LETTER YOGH',
	0x021D => 'LATIN SMALL LETTER YOGH',
	0x0259 => 'LATIN SMALL LETTER SCHWA',
	0x025B => 'LATIN SMALL LETTER OPEN E',
	0x02B0 => 'MODIFIER LETTER SMALL H',
	0x058D => 'RIGHT-FACING ARMENIAN ETERNITY SIGN',
	0x058E => 'LEFT-FACING ARMENIAN ETERNITY SIGN',
	0x05B4 => 'HEBREW POINT HIRIQ',
	0x05B7 => 'HEBREW POINT PATAH',
	0x05B8 => 'HEBREW POINT QAMATS',
	0x05BE => 'HEBREW PUNCTUATION MAQAF',
	0x05BF => 'HEBREW POINT RAFE',
	0x05C3 => 'HEBREW PUNCTUATION SOF PASUQ',
	0x05C6 => 'HEBREW PUNCTUATION NUN HAFUKHA',
	0x05C7 => 'HEBREW POINT QAMATS QATAN',
	0x05D0 => 'HEBREW LETTER ALEF',
	0x05D1 => 'HEBREW LETTER BET',
	0x05D2 => 'HEBREW LETTER GIMEL',
	0x05D3 => 'HEBREW LETTER DALET',
	0x05D4 => 'HEBREW LETTER HE',
	0x05D5 => 'HEBREW LETTER VAV',
	0x05D6 => 'HEBREW LETTER ZAYIN',
	0x05D7 => 'HEBREW LETTER HET',
	0x05D8 => 'HEBREW LETTER TET',
	0x05D9 => 'HEBREW LETTER YOD',
	0x05DA => 'HEBREW LETTER FINAL KAF',
	0x05DB => 'HEBREW LETTER KAF',
	0x05DC => 'HEBREW LETTER LAMED',
	0x05DD => 'HEBREW LETTER FINAL MEM',
	0x05DE => 'HEBREW LETTER MEM',
	0x05DF => 'HEBREW LETTER FINAL NUN',
	0x05E0 => 'HEBREW LETTER NUN',
	0x05E1 => 'HEBREW LETTER SAMEKH',
	0x05E2 => 'HEBREW LETTER AYIN',
	0x05E3 => 'HEBREW LETTER FINAL PE',
	0x05E4 => 'HEBREW LETTER PE',
	0x05E5 => 'HEBREW LETTER FINAL TSADI',
	0x05E6 => 'HEBREW LETTER TSADI',
	0x05E7 => 'HEBREW LETTER QOF',
	0x05E8 => 'HEBREW LETTER RESH',
	0x05E9 => 'HEBREW LETTER SHIN',
	0x05EA => 'HEBREW LETTER TAV',
	0x05EF => 'HEBREW YOD TRIANGLE',
	0x05F0 => 'HEBREW LIGATURE YIDDISH DOUBLE VAV',
	0x05F1 => 'HEBREW LIGATURE YIDDISH VAV YOD',
	0x05F2 => 'HEBREW LIGATURE YIDDISH DOUBLE YOD',
	0x05F3 => 'HEBREW PUNCTUATION GERESH',
	0x05F4 => 'HEBREW PUNCTUATION GERSHAYIM',
	0x06DE => 'ARABIC START OF RUB EL HIZB',
	0x1E06 => 'LATIN CAPITAL LETTER B WITH LINE BELOW',
	0x1E07 => 'LATIN SMALL LETTER B WITH LINE BELOW',
	0x1E24 => 'LATIN CAPITAL LETTER H WITH DOT BELOW',
	0x1E25 => 'LATIN SMALL LETTER H WITH DOT BELOW',
	0x1E32 => 'LATIN CAPITAL LETTER K WITH DOT BELOW',
	0x1E33 => 'LATIN SMALL LETTER K WITH DOT BELOW',
	0x1E6C => 'LATIN CAPITAL LETTER T WITH DOT BELOW',
	0x1E6D => 'LATIN SMALL LETTER T WITH DOT BELOW',
	0x1E92 => 'LATIN CAPITAL LETTER Z WITH DOT BELOW',
	0x1E93 => 'LATIN SMALL LETTER Z WITH DOT BELOW',
	0x2012 => 'FIGURE DASH',
	0x2013 => 'EN DASH',
	0x2014 => 'EM DASH',
	0x201C => 'LEFT DOUBLE QUOTATION MARK',
	0x201D => 'RIGHT DOUBLE QUOTATION MARK',
	0x201E => 'DOUBLE LOW-9 QUOTATION MARK',
	0x203D => 'INTERROBANG',
	0x2042 => 'ASTERISM',
	0x20AA => 'NEW SHEQEL SIGN',
	0x2116 => 'NUMERO SIGN',
	0x2135 => 'ALEF SYMBOL',
	0x2136 => 'BET SYMBOL',
	0x2137 => 'GIMEL SYMBOL',
	0x2138 => 'DALET SYMBOL',
	0x2160 => 'ROMAN NUMERAL ONE',
	0x2161 => 'ROMAN NUMERAL TWO',
	0x2162 => 'ROMAN NUMERAL THREE',
	0x2163 => 'ROMAN NUMERAL FOUR',
	0x2164 => 'ROMAN NUMERAL FIVE',
	0x2165 => 'ROMAN NUMERAL SIX',
	0x2166 => 'ROMAN NUMERAL SEVEN',
	0x2167 => 'ROMAN NUMERAL EIGHT',
	0x2168 => 'ROMAN NUMERAL NINE',
	0x2169 => 'ROMAN NUMERAL TEN',
	0x216C => 'ROMAN NUMERAL FIFTY',
	0x216D => 'ROMAN NUMERAL ONE HUNDRED',
	0x216E => 'ROMAN NUMERAL FIVE HUNDRED',
	0x216F => 'ROMAN NUMERAL ONE THOUSAND',
	0x2205 => 'EMPTY SET',
	0x221E => 'INFINITY',
	0x2248 => 'ALMOST EQUAL TO',
	0x2260 => 'NOT EQUAL TO',
	0x2318 => 'PLACE OF INTEREST SIGN',
	0x25AA => 'BLACK SMALL SQUARE',
	0x25CC => 'DOTTED CIRCLE',
	0x25E6 => 'WHITE BULLET',
	0x2600 => 'BLACK SUN WITH RAYS',
	0x2601 => 'CLOUD',
	0x2604 => 'COMET',
	0x2609 => 'SUN',
	0x2614 => 'UMBRELLA WITH RAIN DROPS',
	0x261C => 'WHITE LEFT POINTING INDEX',
	0x261D => 'WHITE UP POINTING INDEX',
	0x261E => 'WHITE RIGHT POINTING INDEX',
	0x261F => 'WHITE DOWN POINTING INDEX',
	0x2620 => 'SKULL AND CROSSBONES',
	0x2624 => 'CADUCEUS',
	0x2625 => 'ANKH',
	0x2629 => 'CROSS OF JERUSALEM',
	0x262C => 'ADI SHAKTI',
	0x262F => 'YIN YANG',
	0x2638 => 'WHEEL OF DHARMA',
	0x263D => 'FIRST QUARTER MOON',
	0x263E => 'LAST QUARTER MOON',
	0x263F => 'MERCURY',
	0x2640 => 'FEMALE SIGN',
	0x2641 => 'EARTH',
	0x2642 => 'MALE SIGN',
	0x2643 => 'JUPITER',
	0x2644 => 'SATURN',
	0x2645 => 'URANUS',
	0x2646 => 'NEPTUNE',
	0x2647 => 'PLUTO',
	0x2648 => 'ARIES',
	0x2649 => 'TAURUS',
	0x264A => 'GEMINI',
	0x264B => 'CANCER',
	0x264C => 'LEO',
	0x264D => 'VIRGO',
	0x264E => 'LIBRA',
	0x264F => 'SCORPIUS',
	0x2650 => 'SAGITTARIUS',
	0x2651 => 'CAPRICORN',
	0x2652 => 'AQUARIUS',
	0x2653 => 'PISCES',
	0x265B => 'BLACK CHESS QUEEN',
	0x2694 => 'CROSSED SWORDS',
	0x2695 => 'STAFF OF AESCULAPIUS',
	0x269A => 'STAFF OF HERMES',
	0x26A1 => 'HIGH VOLTAGE SIGN',
	0x26A5 => 'MALE AND FEMALE SIGN',
	0x26A7 => 'MALE WITH STROKE AND MALE AND FEMALE SIGN',
	0x26B2 => 'NEUTER',
	0x26C8 => 'THUNDER CLOUD AND RAIN',
	0x26CE => 'OPHIUCHUS',
	0x270D => 'WRITING HAND',
	0x2721 => 'STAR OF DAVID',
	0x2766 => 'FLORAL HEART',
	0x2767 => 'ROTATED FLORAL HEART BULLET',
	0x2E17 => 'DOUBLE OBLIQUE HYPHEN',
	0x2E3F => 'CAPITULUM',
	0x2E42 => 'DOUBLE LOW-REVERSED-9 QUOTATION MARK',
	0x2E5D => 'OBLIQUE HYPHEN',
	0xA673 => 'SLAVONIC ASTERISK',
	0xFB1D => 'HEBREW LETTER YOD WITH HIRIQ',
	0xFB1E => 'HEBREW POINT JUDEO-SPANISH VARIKA',
	0xFB1F => 'HEBREW LIGATURE YIDDISH YOD YOD PATAH',
	0xFB20 => 'HEBREW LETTER ALTERNATIVE AYIN',
	0xFB21 => 'HEBREW LETTER WIDE ALEF',
	0xFB22 => 'HEBREW LETTER WIDE DALET',
	0xFB23 => 'HEBREW LETTER WIDE HE',
	0xFB24 => 'HEBREW LETTER WIDE KAF',
	0xFB25 => 'HEBREW LETTER WIDE LAMED',
	0xFB26 => 'HEBREW LETTER WIDE FINAL MEM',
	0xFB27 => 'HEBREW LETTER WIDE RESH',
	0xFB28 => 'HEBREW LETTER WIDE TAV',
	0xFB29 => 'HEBREW LETTER ALTERNATIVE PLUS SIGN',
	0xFB4F => 'HEBREW LIGATURE ALEF LAMED',
	0xFDFB => 'ARABIC LIGATURE JALLAJALALOUHOU',
	0xFE0E => 'VARIATION SELECTOR-15',
	0x1F300 => 'CYCLONE',
	0x1F308 => 'RAINBOW',
	0x1F30C => 'MILKY WAY',
	0x1F310 => 'GLOBE WITH MERIDIANS',
	0x1F311 => 'NEW MOON SYMBOL',
	0x1F312 => 'WAXING CRESCENT MOON SYMBOL',
	0x1F313 => 'FIRST QUARTER MOON SYMBOL',
	0x1F314 => 'WAXING GIBBOUS MOON SYMBOL',
	0x1F315 => 'FULL MOON SYMBOL',
	0x1F316 => 'WANING GIBBOUS MOON SYMBOL',
	0x1F317 => 'LAST QUARTER MOON SYMBOL',
	0x1F318 => 'WANING CRESCENT MOON SYMBOL',
	0x1F31F => 'GLOWING STAR',
	0x1F3A1 => 'FERRIS WHEEL',
	0x1F442 => 'EAR',
	0x1F4AC => 'SPEECH BALLOON',
	0x1F4C3 => 'PAGE WITH CURL',
	0x1F4D6 => 'OPEN BOOK',
	0x1F4DA => 'BOOKS',
	0x1F4DC => 'SCROLL',
	0x1F525 => 'FIRE',
	0x1F52F => 'SIX POINTED STAR WITH MIDDLE DOT',
	0x1F54D => 'SYNAGOGUE',
	0x1F54E => 'MENORAH WITH NINE BRANCHES',
	0x1F56E => 'BOOK',
	0x1F596 => 'RAISED HAND WITH PART BETWEEN MIDDLE AND RING FINGERS',
	0x1F5C0 => 'FOLDER',
	0x1F5CD => 'EMPTY PAGES',
	0x1F5CE => 'DOCUMENT',
	0x1F5D0 => 'PAGES',
	0x1F5E3 => 'SPEAKING HEAD IN SILHOUETTE',
	0x1F5FA => 'WORLD MAP',
	0x1F7CC => 'HEAVY SIX POINTED BLACK STAR',
	0x1F926 => 'FACE PALM',
	0x1F9CE => 'KNEELING PERSON',
	0x1F9CF => 'DEAF PERSON',
	0x1F9D8 => 'PERSON IN LOTUS POSITION',
	0x1F9FF => 'NAZAR AMULET',
	0x1FA90 => 'RINGED PLANET',
	0x1FAAC => 'HAMSA',
) );


/* ------------------------------------------------------------------ *
 *  Data loading
 * ------------------------------------------------------------------ */

/**
 * Reads and decodes data/fonts.json once per request.
 * Returns ['categories' => [...], 'fonts' => [...]] or WP_Error on failure.
 */
function cfd_get_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	if ( ! file_exists( CFD_DATA_FILE ) ) {
		return new WP_Error( 'cfd_missing_file', 'fonts.json not found at ' . CFD_DATA_FILE );
	}

	$raw     = file_get_contents( CFD_DATA_FILE );
	$decoded = json_decode( $raw, true );

	if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
		return new WP_Error( 'cfd_bad_json', 'fonts.json failed to parse: ' . json_last_error_msg() );
	}

	$data = $decoded;
	return $data;
}

/* ------------------------------------------------------------------ *
 *  Shortcode
 * ------------------------------------------------------------------ */

add_shortcode( 'custom_fonts_display', 'cfd_render_shortcode' );

function cfd_render_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category' => '',   // e.g. "full-diacritic-support" or "partial-diacritic-support,sans-diacritics"
			'font'     => '',   // e.g. "heebo" or "heebo,cardo" — takes priority over category
		),
		$atts,
		'custom_fonts_display'
	);

	$data = cfd_get_data();
	if ( is_wp_error( $data ) ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<p><em>Custom Font Display: ' . esc_html( $data->get_error_message() ) . '</em></p>';
		}
		return '';
	}

	$fonts      = $data['fonts'] ?? array();
	$categories = $data['categories'] ?? array();

	// Coverage/missingChars are only trusted when fonts.json was generated
	// against the same sample strings this file expects — see
	// CFD_SAMPLE_STRINGS_VERSION and cfd_missing_codepoint_set() above.
	$sample_strings_current = ( ( $data['_meta']['sampleStringsVersion'] ?? null ) === CFD_SAMPLE_STRINGS_VERSION );

	// styleTags computed fresh every request from `style` — see
	// CFD_STYLE_TAG_MAP/cfd_derive_style_tags() above for why this isn't
	// a persisted field.
	foreach ( $fonts as &$font ) {
		$font['styleTags'] = cfd_derive_style_tags( $font['style'] ?? '' );
	}
	unset( $font );

	// --- filter ---
	if ( '' !== trim( $atts['font'] ) ) {
		$wanted = array_map( 'sanitize_title', explode( ',', $atts['font'] ) );
		$fonts  = array_values( array_filter( $fonts, fn( $f ) => in_array( $f['slug'], $wanted, true ) ) );
		$single = ( count( $wanted ) === 1 );
	} elseif ( '' !== trim( $atts['category'] ) && 'all' !== trim( $atts['category'] ) ) {
		$wanted = array_map( 'sanitize_title', explode( ',', $atts['category'] ) );
		$fonts  = array_values( array_filter( $fonts, fn( $f ) => array_intersect( $wanted, $f['categories'] ?? array() ) ) );
		$single = false;
	} else {
		$single = false; // "all"
	}

	if ( empty( $fonts ) ) {
		return '<p><em>No fonts matched that selection.</em></p>';
	}

	cfd_enqueue_assets();

	ob_start();
	echo '<div class="cfd-root' . ( $single ? ' cfd-single' : '' ) . '">';

	echo cfd_render_font_face_css( $fonts );

	if ( ! $single ) {
		echo cfd_render_toolbar();
		echo cfd_render_tag_filter_group( $fonts, 'styleTags', 'style', 'Style', CFD_STYLE_TAG_TITLES );
		echo cfd_render_tag_filter_group( $fonts, 'scriptTags', 'script', 'Script', CFD_SCRIPT_TAG_TITLES );
		echo cfd_render_tag_filter_group( $fonts, 'categories', 'diacritics', 'Diacritics', CFD_DIACRITIC_TITLES );
		echo cfd_render_active_filter_chip();
	}

	if ( $single ) {
		foreach ( $fonts as $font ) {
			echo cfd_render_font_row( $font, $sample_strings_current );
		}
	} else {
		// group the filtered fonts by category, in the order categories are defined in the JSON
		foreach ( $categories as $cat_slug => $cat_meta ) {
			$in_cat = array_filter( $fonts, fn( $f ) => in_array( $cat_slug, $f['categories'] ?? array(), true ) );
			if ( empty( $in_cat ) ) {
				continue;
			}
			echo '<section class="cfd-category" id="' . esc_attr( $cat_slug ) . '">';
			echo '<h2>' . esc_html( $cat_meta['title'] ?? $cat_slug ) . '</h2>';
			if ( ! empty( $cat_meta['description'] ) ) {
				echo '<p class="cfd-cat-note">' . esc_html( $cat_meta['description'] ) . '</p>';
			}
			echo '<div class="cfd-font-list">';
			foreach ( $in_cat as $font ) {
				echo cfd_render_font_row( $font, $sample_strings_current );
			}
			echo '</div></section>';
		}
	}

	echo '</div>';
	return ob_get_clean();
}

/* ------------------------------------------------------------------ *
 *  Rendering
 * ------------------------------------------------------------------ */

/** Sample strings, identical to the current page's intro legend. */
function cfd_sample_lines() {
	return array(
		array( 'label' => 'Numerals & Mathematical Operators', 'dir' => 'ltr', 'text' => '№ ∅ ☉ 0 ¼ ½ ¾ 1 2 3 4 5 6 7 8 9 Ⅰ Ⅱ Ⅲ Ⅳ Ⅴ Ⅵ Ⅶ Ⅷ Ⅸ Ⅹ Ⅼ Ⅽ Ⅾ Ⅿ ℸ ℷ ℶ ℵ ∞ ≈ ﬩ × ° ≠ ₪' ),
		array( 'label' => 'Punctuation', 'dir' => 'ltr', 'text' => '« » “ ⹂ „ ” ‽ ; : ⸿ § ¶ ☜ ☞ ☝ ☟ ◦ · ▪ ⹝ ⸗ — – ‒ ⁂ ◌ ❦ ❧' ),
		array( 'label' => 'Miscellaneous Symbols', 'dir' => 'ltr', 'text' => '✍︎ 👂︎ 💬︎ 🧏︎ 🗣︎ 🤦︎ 🧎︎ 🧘︎ ☠︎' ),
		array( 'label' => 'File Symbols', 'dir' => 'ltr', 'text' => '🗀︎ 🕮︎ 📖︎ 📜︎ 📃︎ 🗍︎ 📚︎ 🗎︎ 🗐︎' ),
		array( 'label' => 'Religious Signifiers', 'dir' => 'ltr', 'text' => '🖖︎ 🪬︎ ✡︎ 🔯︎ 🟌︎ ꙳︎ 🕍︎ 🕎︎ 🧿︎ ⌘︎ ☥︎ ⚕︎ ☤︎ ⚚︎ ☬︎ ☸︎ ֍︎ ֎︎ ☩︎ ۞︎ ﷻ︎ ☯︎ 🔥︎ ♛︎ ⚡︎' ),
		array( 'label' => 'Meteorology, Astronomy, and Angelology', 'dir' => 'ltr', 'text' => '☁︎ ⛈︎ ☔︎ 🌈︎ 🌀︎ ☄︎ 🪐︎ 🌌︎ 🎡︎ 🌐︎ 🗺︎ ☀︎ 🌑︎ 🌒︎ 🌓︎ 🌔︎ 🌕︎ 🌖︎ 🌗︎ 🌘︎ ☽︎ ☾︎ ☿︎ 🌟︎ ♁︎ ⚔︎ ♃︎ ♄︎ ♅︎ ♆︎ ♇︎ ♈︎ ♉︎ ♊︎ ♋︎ ♌︎ ♍︎ ♎︎ ♏︎ ♐︎ ♑︎ ♒︎ ♓︎ ⛎︎' ),
		array( 'label' => 'Gender Symbols', 'dir' => 'ltr', 'text' => '⚲︎ ♀︎ ♂︎ ⚥︎ ⚧︎' ),
		array( 'label' => 'Latin (simple)', 'dir' => 'ltr', 'text' => 'Aa Bb Cc Dd Ee Ff Gg Hh Ii Jj Kk Ll Mm Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz' ),
		array( 'label' => 'Latin (extended)', 'dir' => 'ltr', 'text' => 'Ăă Ââ Ḇḇ Éé Èè Ɛɛ Əə Ȝȝ Ŋŋ Ḥḥ ʰ Ḳḳ Ƙƙ Íí Ōō Šš Śś Ṭṭ þ Ūū Ẓẓ' ),
		array( 'label' => 'Yiddish', 'dir' => 'rtl', 'text' => 'אַ אָ װ ױ יִ (יִ) ײ ײַ (ײַ) בֿ כֿ פֿ' ),
		array( 'label' => 'Ladino', 'dir' => 'rtl', 'text' => 'ﭏ בﬞ גﬞ דﬞ זﬞ טﬞ פﬞ ףﬞ קﬞ שﬞ' ),
	);
}

/**
 * Builds a {codepoint_int: true} lookup from $font['missingChars'] — the
 * sole source of truth (as of schemaVersion 0.9.0) for both the "Missing
 * Characters" line and per-character gray-out. Only trusted when
 * fonts.json's sampleStringsVersion matches CFD_SAMPLE_STRINGS_VERSION,
 * so stale data (from before a sample-string edit) degrades to "nothing
 * flagged" rather than misapplying old positions to new characters.
 */
function cfd_missing_codepoint_set( $font, $sample_strings_current ) {
	if ( ! $sample_strings_current ) {
		return array();
	}
	$set = array();
	// missingChars is a dict keyed by sample-line label (plus "abgad") as
	// of 0.13.0 — flatten across every line for this purpose, since
	// graying doesn't care WHICH line a codepoint came from, only
	// whether it's missing from the font at all. The per-line structure
	// itself is what powers the per-line reveal — see cfd_render_font_row().
	foreach ( $font['missingChars'] ?? array() as $line_missing ) {
		if ( ! is_array( $line_missing ) ) {
			continue;
		}
		foreach ( $line_missing as $m ) {
			if ( isset( $m['codepoint'] ) ) {
				$set[ $m['codepoint'] ] = true; // e.g. "U+05EF" as the key
			}
		}
	}
	return $set;
}

const CFD_ABGAD = 'א ﬡ ב׳ ג ד ﬢ ה ﬣ ו ז ח ט י ׯ כ כׇ ﬤ ך ל ﬥ מ־ם ﬦ נ ׆ ן ס ﬠ ע פ ף צ ץ ק״ ר ﬧ ש ת ﬨ׃';

/* Rule 1: wide-letterform presentation forms (FB21-FB28) and backward
   nun (U+05C6) were originally one combined rule; now two separate
   ones, since backward nun's own tradition doesn't line up with wide
   letterforms' quite as closely as first assumed — see Rule 1b below.
   Wide letterforms: excluded for fonts with one of these styles, since
   those forms represent a justified-line-stretching tradition that
   doesn't apply the same way to sans-sofit, cursive, ktav-yad
   (handwriting-style), inscription, scribal, ancient, semi-cursive, or
   fixed-width letterforms. "ancient" covers both "Ancient" and
   "Antiquity" as source `style` text — CFD_STYLE_TAG_MAP already
   aliases them to the same canonical tag, so there's only one value to
   list here, not two. */
define( 'CFD_ABGAD_WIDE_EXCLUDED_FOR_STYLES', array(
	'sans-sofit', 'cursive', 'ktav-yad',
	'inscription', 'scribal', 'ancient', 'semi-cursive', 'fixed-width',
) );
define( 'CFD_ABGAD_WIDE_CHARS', array( 0xFB21, 0xFB22, 0xFB23, 0xFB24, 0xFB25, 0xFB26, 0xFB27, 0xFB28 ) );

/* Rule 1b: backward nun (U+05C6) — split out from Rule 1 above.
   "scribal" and "fixed-width" deliberately removed from this trigger
   list (unlike Rule 1's wide-letterform list, which keeps all 8) —
   backward nun's own tradition doesn't apply the same way to those two
   styles specifically, on reflection. */
define( 'CFD_BACKWARD_NUN_EXCLUDED_FOR_STYLES', array(
	'sans-sofit', 'cursive', 'ktav-yad',
	'inscription', 'ancient', 'semi-cursive',
) );
define( 'CFD_BACKWARD_NUN_CHAR', 0x05C6 );

/* Rule 2: alternative ayin (U+FB20) — its own, more nuanced rule,
   deliberately separate from Rule 1. Excluded for any "sans-diacritics"
   category font regardless of style (a font with no diacritic support
   at all has no positioning context that would call for this form),
   OR for one of these 7 styles regardless of diacritic support.
   "scribal" is NOT in this style list — a scribal font that ISN'T
   sans-diacritics may have its diacritics turned on and specifically
   need the alternative ayin form for correct positioning alongside
   them, so only sans-diacritics scribal fonts lose it (via the category
   check above), not scribal fonts generally. */
define( 'CFD_AYIN_EXCLUDED_FOR_STYLES', array(
	'sans-sofit', 'cursive', 'ktav-yad',
	'inscription', 'ancient', 'semi-cursive', 'fixed-width',
) );
define( 'CFD_ALT_AYIN_CHAR', 0xFB20 );

/* Rule 3: the 5 sofit (final-letter) forms — excluded only for
   "sans-sofit" fonts, since that style name literally means "without
   sofit [forms]". Verified against unicodedata.name() before use. */
define( 'CFD_SOFIT_EXCLUDED_FOR_STYLES', array( 'sans-sofit' ) );
define( 'CFD_SOFIT_CHARS', array( 0x05DA, 0x05DD, 0x05DF, 0x05E3, 0x05E5 ) ); // ך ם ן ף ץ

/* Rule 4: qamats qatan under kaf, i.e. the "כׇ" token specifically —
   a niqqud-positioning test, meaningless for a sans-diacritics font.
   Whole-token removal, not character-level (see cfd_filtered_abgad()'s
   handling of it) — bare kaf already has its own separate token earlier
   in CFD_ABGAD, so stripping just the combining mark would leave a
   duplicate bare kaf behind. Extracted directly from the real CFD_ABGAD
   content, not retyped, and confirmed it's exactly bare-kaf's own
   codepoint plus U+05C7 before relying on this — see chat. */
define( 'CFD_QAMATS_QATAN_KAF_TOKEN', 'כׇ' );

/**
 * True if any of $needles appears in $haystack.
 */
function cfd_any_in( $needles, $haystack ) {
	foreach ( $needles as $n ) {
		if ( in_array( $n, $haystack, true ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Returns CFD_ABGAD with whichever of Rules 1, 1b, 2, 3, and 4 apply
 * removed, based on this font's (already-computed) styleTags and
 * categories — every font matching none of the five rules gets the
 * original string back completely unchanged.
 *
 * Filters CHARACTER BY CHARACTER within each token, not by whole-token
 * match, then strips a now-dangling leading/trailing maqaf (U+05BE) —
 * two of CFD_ABGAD's tokens aren't single characters: "ﬨ׃" (wide tav
 * glued directly to sof pasuq) and "מ־ם" (mem + maqaf + final mem, used
 * to show the regular/sofit contrast compactly). Removing wide tav from
 * the first must leave sof pasuq behind; removing final mem (Rule 3)
 * from the second must leave plain mem behind, not "מ־" with a dangling
 * connector that no longer connects anything — regular mem has no OTHER
 * standalone token of its own in CFD_ABGAD, so simply dropping the
 * whole "מ־ם" token would lose it entirely, which sans-sofit fonts
 * still need to show. Uses mb_substr in a loop rather than PHP's
 * rtrim()/ltrim(), which are byte-oriented and unsafe on multi-byte
 * UTF-8 characters like maqaf. Rule 4 (qamats qatan under kaf) is the
 * one exception to character-level filtering — see cfd_filtered_abgad()
 * itself for why that one needs whole-token removal instead. Every
 * scenario (each rule alone, rules combined, the sans-diacritics-
 * overrides-scribal interaction, and the complete pass-through case)
 * verified against the real CFD_ABGAD content before trusting this —
 * see chat.
 */
function cfd_strip_edge_maqaf( $token ) {
	$maqaf = "\u{05BE}";
	while ( mb_substr( $token, 0, 1, 'UTF-8' ) === $maqaf ) {
		$token = mb_substr( $token, 1, null, 'UTF-8' );
	}
	while ( mb_substr( $token, -1, 1, 'UTF-8' ) === $maqaf ) {
		$token = mb_substr( $token, 0, -1, 'UTF-8' );
	}
	return $token;
}

function cfd_filtered_abgad( $style_tags, $categories ) {
	$style_tags = $style_tags ?? array();
	$categories = $categories ?? array();
	$is_sans_diacritics = in_array( 'sans-diacritics', $categories, true );

	$excluded = array();

	if ( cfd_any_in( CFD_ABGAD_WIDE_EXCLUDED_FOR_STYLES, $style_tags ) ) {
		$excluded = array_merge( $excluded, CFD_ABGAD_WIDE_CHARS );
	}

	if ( cfd_any_in( CFD_BACKWARD_NUN_EXCLUDED_FOR_STYLES, $style_tags ) ) {
		$excluded[] = CFD_BACKWARD_NUN_CHAR;
	}

	if ( $is_sans_diacritics || cfd_any_in( CFD_AYIN_EXCLUDED_FOR_STYLES, $style_tags ) ) {
		$excluded[] = CFD_ALT_AYIN_CHAR;
	}

	if ( cfd_any_in( CFD_SOFIT_EXCLUDED_FOR_STYLES, $style_tags ) ) {
		$excluded = array_merge( $excluded, CFD_SOFIT_CHARS );
	}

	if ( empty( $excluded ) && ! $is_sans_diacritics ) {
		return CFD_ABGAD;
	}

	$tokens   = explode( ' ', CFD_ABGAD );
	$filtered = array();
	foreach ( $tokens as $token ) {
		// Rule 4: qamats qatan under kaf (the "כׇ" token specifically) is
		// a niqqud-positioning test — meaningless for a sans-diacritics
		// font, and bare kaf already has its OWN separate token earlier
		// in CFD_ABGAD, so this needs to drop the WHOLE token rather than
		// just the combining mark (which would leave a duplicate bare
		// kaf behind, unlike Rule 3's "מ־ם" case, where mem has no other
		// standalone token to fall back on).
		if ( $is_sans_diacritics && $token === CFD_QAMATS_QATAN_KAF_TOKEN ) {
			continue;
		}

		$new_token = '';
		foreach ( mb_str_split( $token, 1, 'UTF-8' ) as $ch ) {
			$cp = mb_ord( $ch, 'UTF-8' );
			if ( in_array( $cp, $excluded, true ) ) {
				continue; // drop just this character, keep the rest of the token
			}
			$new_token .= $ch;
		}
		$new_token = cfd_strip_edge_maqaf( $new_token );
		if ( $new_token !== '' ) {
			$filtered[] = $new_token;
		}
	}
	return implode( ' ', $filtered );
}

/**
 * Zephaniah 3:8 — a traditional Hebrew pangram (contains every letter of
 * the alphabet) — plus the bracket/quotation example (formerly its own
 * separate conditional-example row, folded in here per user feedback:
 * this textarea already demonstrates the same thing, in context, with
 * the person's own eyes on it, making a dedicated always-or-never row
 * redundant). Pre-filled at the diacritic tier that font actually
 * claims: full t'amim+niqqud, niqqud only, or plain consonantal — the
 * bracket example's OWN niqqud is kept intact for the first two tiers,
 * but stripped for the plain tier, matching that tier's own register
 * rather than introducing missing-niqqud fallback noise into what's
 * otherwise a clean, always-renders-correctly placeholder. Purely a
 * starting example — fully editable, selected on first focus so typing
 * immediately replaces it.
 */
const CFD_SAMPLE_VERSE_FULL   = 'לָכֵ֤ן חַכּוּ־לִי֙ נְאֻם־יְהוָ֔ה לְי֖וֹם קוּמִ֣י לְעַ֑ד כִּ֣י מִשְׁפָּטִי֩ לֶאֱסֹ֨ף גּוֹיִ֜ם לְקָבְצִ֣י מַמְלָכ֗וֹת לִשְׁפֹּ֨ךְ עֲלֵיהֶ֤ם זַעְמִי֙ כֹּ֚ל חֲר֣וֹן אַפִּ֔י כִּ֚י בְּאֵ֣שׁ קִנְאָתִ֔י תֵּאָכֵ֖ל כָּל־הָאָֽרֶץ׃‏ (”שָׁלוֹם עוֹלַם“) “Shalom Olam” <שָׁלוֹם עוֹלַם?> {שָׁלוֹם עוֹלַם} [שָׁלוֹם עוֹלַם!]';
const CFD_SAMPLE_VERSE_NIQQUD = 'לָכֵן חַכּוּ לִי נְאֻם יְהוָה לְיוֹם קוּמִי לְעַד כִּי מִשְׁפָּטִי לֶאֱסֹף גּוֹיִם לְקָבְצִי מַמְלָכוֹת לִשְׁפֹּךְ עֲלֵיהֶם זַעְמִי כֹּל חֲרוֹן אַפִּי כִּי בְּאֵשׁ קִנְאָתִי תֵּאָכֵל כָּל הָאָרֶץ.‏‏ (”שָׁלוֹם עוֹלַם“) “Shalom Olam” <שָׁלוֹם עוֹלַם?> {שָׁלוֹם עוֹלַם} [שָׁלוֹם עוֹלַם!]';
const CFD_SAMPLE_VERSE_PLAIN  = 'לכן חכו לי נאם יהוה ליום קומי לעד כי משפטי לאסף גוים לקבצי ממלכות לשפך עליהם זעמי כל חרון אפי כי באש קנאתי תאכל כל הארץ‏ (”שלום עולם“) “Shalom Olam” <שלום עולם?> {שלום עולם} [שלום עולם!]';

/**
 * Picks the verse tier matching what this font actually claims to
 * support. failing-diacritic-support fonts still nominally claim niqqud
 * (their issue is positioning, not absence), so they get the niqqud
 * tier too — showing the verse with points is the whole point of that
 * category, letting the positioning bugs actually be visible.
 */
function cfd_default_test_text( $font ) {
	$cats = $font['categories'] ?? array();
	if ( in_array( 'full-diacritic-support', $cats, true ) ) {
		return CFD_SAMPLE_VERSE_FULL;
	}
	if ( in_array( 'partial-diacritic-support', $cats, true ) || in_array( 'failing-diacritic-support', $cats, true ) ) {
		return CFD_SAMPLE_VERSE_NIQQUD;
	}
	return CFD_SAMPLE_VERSE_PLAIN;
}

function cfd_font_stack( $family, $include_reference_tier = true ) {
	// primary font -> each CFD_REFERENCE_FAMILIES tier in order (real
	// letterforms, grayed via .cfd-missing where coverage says the
	// primary lacks them) -> open-box safety net for anything none of
	// the reference fonts have either.
	//
	// $include_reference_tier = false skips straight from the primary
	// font to FallbackFont, bypassing SimpleCLM/FreeSerif/Noto entirely.
	// Used for the "Test your own text" textarea specifically: a plain
	// <textarea> can't wrap individual characters in <span> the way
	// cfd_render_glyph_string() does elsewhere, so a character that fell
	// through to the reference tier would render at full opacity,
	// visually identical to one genuinely supported by the tested font —
	// exactly the thing this field exists to reveal, silently defeated.
	// Skipping straight to FallbackFont means a missing character shows
	// up as an unmistakable hex-code box instead, with nothing
	// convincing-looking in between to mask it.
	$stack = array( "'" . esc_attr( $family ) . "'" );
	if ( $include_reference_tier ) {
		foreach ( CFD_REFERENCE_FAMILIES as $ref ) {
			$stack[] = "'" . esc_attr( $ref ) . "'";
		}
	}
	$stack[] = "'FallbackFont'";
	return implode( ', ', $stack );
}

/**
 * True if $codepoint is a combining mark used in our sample strings —
 * Hebrew niqqud/t'amim points and the Judeo-Spanish varika mark — plus
 * the general Unicode Combining Diacritical Marks block as a safety net
 * for anything added later (e.g. Latin diacritics). Hardcoded rather
 * than relying on the intl/ICU PHP extension, which may not be present
 * on shared hosting. Verified against the ACTUAL codepoints in
 * CFD_ABGAD/cfd_sample_lines() (0x5B7, 0x5B8, 0x5BF, 0x5C7, 0xFB1E)
 * before writing this, not guessed from memory.
 */
function cfd_is_combining_mark( $codepoint ) {
	if ( $codepoint >= 0x0591 && $codepoint <= 0x05BD ) return true; // Hebrew accents/points
	if ( $codepoint === 0x05BF ) return true;                        // rafe
	if ( $codepoint >= 0x05C1 && $codepoint <= 0x05C2 ) return true;  // shin/sin dot
	if ( $codepoint >= 0x05C4 && $codepoint <= 0x05C5 ) return true;  // upper/lower dot (deprecated but assigned)
	if ( $codepoint === 0x05C7 ) return true;                         // qamats qatan
	if ( $codepoint === 0xFB1E ) return true;                         // Judeo-Spanish varika
	if ( $codepoint >= 0x0300 && $codepoint <= 0x036F ) return true;  // general combining diacriticals
	if ( $codepoint >= 0xFE00 && $codepoint <= 0xFE0F ) return true;  // variation selectors (VS1-16, e.g. VS15 line-art)
	if ( $codepoint >= 0xE0100 && $codepoint <= 0xE01EF ) return true; // supplementary variation selectors (VS17-256)
	return false;
}

/**
 * Splits $text into grapheme-like clusters: each base character plus any
 * immediately-following combining marks (per cfd_is_combining_mark())
 * stay together in one array element, rather than one element per
 * codepoint. This exists specifically so cfd_render_glyph_string() never
 * has to style an isolated combining mark in its own <span>, separate
 * from the base letter it's attached to — see that function's docblock
 * for why that split caused a real rendering bug.
 */
function cfd_split_graphemes( $text ) {
	$chars    = mb_str_split( $text, 1, 'UTF-8' );
	$clusters = array();
	foreach ( $chars as $ch ) {
		$cp = mb_ord( $ch, 'UTF-8' );
		if ( cfd_is_combining_mark( $cp ) && ! empty( $clusters ) ) {
			$clusters[ count( $clusters ) - 1 ] .= $ch; // attach to the previous cluster
		} else {
			$clusters[] = $ch; // start a new cluster
		}
	}
	return $clusters;
}

/**
 * Renders $text as grapheme clusters (see cfd_split_graphemes()),
 * coloring an ENTIRE cluster with .cfd-missing if any codepoint within
 * it is in $missing_codepoints (see cfd_missing_codepoint_set()).
 *
 * Earlier versions wrapped each individual codepoint in its own <span>,
 * including combining marks separately from their base letter. That
 * caused a real bug: a combining mark (e.g. qamats qatan) rendered via
 * the fallback font in its OWN span — with no base character in that
 * span — didn't reliably take on that span's own color/opacity styling
 * in Chromium-based browsers, so a genuinely-missing mark could render
 * at full ink color instead of grayed, with no visible indication it
 * had fallen back to a different font at all. Grouping base+marks into
 * one cluster means there's never a styling boundary between a mark and
 * the letter it belongs to, which sidesteps the bug regardless of its
 * exact underlying cause. Trade-off: a supported base letter with an
 * unsupported diacritic now grays as one visual unit rather than just
 * the diacritic alone — arguably clearer anyway, since a floating gray
 * mark with no visual tie to its base letter was ambiguous to read.
 *
 * Falls back to plain output when $missing_codepoints is empty (no data
 * yet, or a sampleStringsVersion mismatch) — never blocks rendering.
 *
 * @param string $text               The sample string (abgad or one script line).
 * @param array  $missing_codepoints {codepoint_string: true} lookup, e.g. {"U+05EF": true}.
 */
function cfd_render_glyph_string( $text, $missing_codepoints, $show_tooltips = false ) {
	$clusters = cfd_split_graphemes( $text );
	$out      = '';
	foreach ( $clusters as $cluster ) {
		$is_missing = false;
		$is_wide    = false;
		$title_bits = array();
		foreach ( mb_str_split( $cluster, 1, 'UTF-8' ) as $ch ) {
			if ( $ch === ' ' ) {
				continue;
			}
			$cp_int = mb_ord( $ch, 'UTF-8' );
			if ( ! empty( $missing_codepoints ) ) {
				$cp = sprintf( 'U+%04X', $cp_int );
				if ( isset( $missing_codepoints[ $cp ] ) ) {
					$is_missing = true;
				}
			}
			if ( in_array( $cp_int, CFD_WIDE_LETTER_CODEPOINTS, true ) ) {
				$is_wide = true;
			}
			$is_variation_selector = ( $cp_int >= 0xFE00 && $cp_int <= 0xFE0F ) || ( $cp_int >= 0xE0100 && $cp_int <= 0xE01EF );
			if ( $show_tooltips && ! $is_variation_selector && isset( CFD_CHAR_NAMES[ $cp_int ] ) ) {
				$title_bits[] = CFD_CHAR_NAMES[ $cp_int ];
			}
		}
		$classes = array();
		if ( $is_missing ) {
			$classes[] = 'cfd-missing';
		}
		if ( $is_wide ) {
			$classes[] = 'cfd-wide-letter';
		}
		$title_attr = $title_bits ? ' title="' . esc_attr( implode( ', ', $title_bits ) ) . '"' : '';
		$out       .= ( $classes || $title_attr )
			? '<span' . ( $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '' ) . $title_attr . '>' . esc_html( $cluster ) . '</span>'
			: esc_html( $cluster );
	}
	return $out;
}

/**
 * Renders the "Alternate Forms" row — historically-attested alternate
 * letterforms discovered via GSUB parsing (see inspect_alternates.py
 * --write, which populates $font['alternateForms']). Deliberately only
 * present for fonts someone chose to run that tool against — most of
 * the catalogue's GSUB features are ordinary typographic infrastructure
 * (ligatures, numeral styles), not curated variants worth a reader's
 * attention, so this row simply doesn't appear for those.
 *
 * Each entry shows the alternate glyph itself, rendered in the font's
 * OWN family only (no fallback chain — we want to see THIS font's own
 * substitution behavior, and since GSUB parsing already confirmed the
 * substitution exists, there's nothing for a fallback to rescue here),
 * followed by a reference rendering of the base character it's an
 * alternate of, in the site's usual fallback stack, in square brackets
 * — since an ancient pictograph or historical variant may not be
 * immediately recognizable without that anchor alongside it.
 */
function cfd_render_alternate_forms( $font ) {
	if ( ! CFD_SHOW_ALTERNATE_FORMS ) {
		return '';
	}
	$forms = $font['alternateForms'] ?? array();
	if ( empty( $forms ) ) {
		return '';
	}

	$own_family = "'" . esc_attr( $font['family'] ) . "'";
	$ref_family = implode( ', ', array_map( fn( $f ) => "'" . esc_attr( $f ) . "'", CFD_REFERENCE_FAMILIES ) );

	$items = array();
	foreach ( $forms as $form ) {
		if ( ( $form['mechanism'] ?? '' ) === 'sequence' ) {
			// literal base+trigger text — the substitution happens
			// automatically in any renderer, no feature toggle needed
			$glyph_style = 'font-family:' . $own_family . ';';
			$glyph_text  = $form['trigger'] ?? $form['baseChar'];
		} else {
			// single/alternate substitution — needs the feature explicitly on
			$glyph_style = 'font-family:' . $own_family . ";font-feature-settings:'" . esc_attr( $form['featureTag'] ) . "' 1;";
			$glyph_text  = $form['baseChar'];
		}
		$glyph_html = '<span class="cfd-altform-glyph" dir="rtl" style="' . esc_attr( $glyph_style ) . '">' . esc_html( $glyph_text ) . '</span>';
		$ref_html   = '<span class="cfd-altform-ref" dir="rtl" style="font-family:' . esc_attr( $ref_family ) . ';">[' . esc_html( $form['baseChar'] ) . ']</span>';
		$items[]    = '<span class="cfd-altform">' . $glyph_html . $ref_html . '</span>';
	}

	return '<div class="cfd-notes-cell"><dt>Alternate Forms</dt><dd><div class="cfd-altform-list">' . implode( '', $items ) . '</div></dd></div>';
}


function cfd_render_font_row( $font, $sample_strings_current = false ) {
	$style          = 'font-family:' . cfd_font_stack( $font['family'] ) . ';';
	$textarea_style = 'font-family:' . cfd_font_stack( $font['family'], false ) . ';';
	$missing  = cfd_missing_codepoint_set( $font, $sample_strings_current );

	$row_attrs  = ' data-slug="' . esc_attr( $font['slug'] ) . '"';
	$row_attrs .= ' data-tags-style="' . esc_attr( implode( ' ', $font['styleTags'] ?? array() ) ) . '"';
	$row_attrs .= ' data-tags-script="' . esc_attr( implode( ' ', $font['scriptTags'] ?? array() ) ) . '"';
	$row_attrs .= ' data-tags-diacritics="' . esc_attr( implode( ' ', $font['categories'] ?? array() ) ) . '"';
	$row_attrs .= ' data-foundry="' . esc_attr( $font['foundry'] ?? '' ) . '"';
	$row_attrs .= ' data-typographer="' . esc_attr( $font['typographer'] ?? '' ) . '"';

	$out  = '<div class="cfd-font-row"' . $row_attrs . '>';
	$out .= '<div class="cfd-font-row-main" role="button" tabindex="0" aria-expanded="false">';
	$out .= '<div class="cfd-font-row-id">';
	$out .= '<span class="cfd-font-name">' . esc_html( $font['name'] ) . '</span>';

	$out .= '<div class="cfd-abgad" style="' . esc_attr( $style ) . '" lang="he" dir="rtl">' . cfd_render_glyph_string( cfd_filtered_abgad( $font['styleTags'] ?? array(), $font['categories'] ?? array() ), $missing ) . '</div>';

	$out .= '</div>'; // .cfd-font-row-id
	$out .= '</div>'; // .cfd-font-row-main

	$out .= '<div class="cfd-font-row-details"><div class="cfd-font-row-details-inner">';

	$out .= '<dl class="cfd-details-grid">';
	$out .= '<div><dt>Diacritic Support</dt><dd>' . esc_html( $font['diacriticSupport'] ) . '</dd></div>';

	$positioning_errors = $font['positioningErrors'] ?? $font['flags'] ?? array();
	if ( ! empty( $positioning_errors ) ) {
		$out .= '<div><dt>Positioning Errors</dt><dd>' . esc_html( implode( ', ', $positioning_errors ) ) . '</dd></div>';
	}

	$out .= '<div><dt>Style</dt><dd>' . esc_html( $font['style'] ) . '</dd></div>';

	if ( ! empty( $font['foundry'] ) ) {
		$out .= '<div><dt>Foundry</dt><dd><a href="' . esc_url( $font['foundryUrl'] ) . '" target="_blank" rel="noopener">' . esc_html( $font['foundry'] ) . '</a> '
			. '<button type="button" class="cfd-filter-link" data-cfd-filter="foundry" data-cfd-filter-value="' . esc_attr( $font['foundry'] ) . '">Show only</button></dd></div>';
	}
	if ( ! empty( $font['typographer'] ) ) {
		$out .= '<div><dt>Typographer</dt><dd>' . esc_html( $font['typographer'] ) . ' '
			. '<button type="button" class="cfd-filter-link" data-cfd-filter="typographer" data-cfd-filter-value="' . esc_attr( $font['typographer'] ) . '">Show only</button></dd></div>';
	}

	$out .= '<div><dt>Version</dt><dd>' . esc_html( $font['version'] ) . ( ! empty( $font['versionDate'] ) ? ' (' . esc_html( $font['versionDate'] ) . ')' : '' ) . '</dd></div>';
	$out .= '<div><dt>License</dt><dd><a href="' . esc_url( $font['licenseUrl'] ) . '" target="_blank" rel="noopener">' . esc_html( $font['license'] ) . '</a></dd></div>';
	$out .= '<div><dt>Download ⇩</dt><dd><a href="' . esc_url( cfd_zip_url( $font ) ) . '">' . esc_html( $font['family'] ) . '.zip</a></dd></div>';

	$out .= cfd_render_alternate_forms( $font );

	$out .= '<div class="cfd-notes-cell"><dt>Notes</dt><dd>';
	if ( ! empty( $font['notes'] ) ) {
		$out .= esc_html( $font['notes'] ) . ' — ';
	}
	$out .= '<a href="' . esc_url( cfd_charmap_url( $font ) ) . '" data-lightbox-type="iframe" target="_blank" rel="noopener">Diacritic positioning across the AlefBet</a>';
	$out .= '</dd></div>';
	$out .= '</dl>';

	$out .= '<div class="cfd-custom-text">';
	$out .= '<label class="cfd-sample-label" for="cfd-custom-' . esc_attr( $font['slug'] ) . '">Test your own text</label>';
	$out .= '<textarea class="cfd-custom-textarea is-example" id="cfd-custom-' . esc_attr( $font['slug'] ) . '" '
		. 'style="' . esc_attr( $textarea_style ) . '" dir="rtl" rows="2">' . esc_textarea( cfd_default_test_text( $font ) ) . '</textarea>';
	$out .= '</div>';

	$out .= '<div class="cfd-sample-lines">';
	foreach ( cfd_sample_lines() as $line ) {
		$line_missing = ( $sample_strings_current && ! empty( $font['missingChars'][ $line['label'] ] ) )
			? $font['missingChars'][ $line['label'] ]
			: array();
		$has_missing = ! empty( $line_missing );

		$out .= '<div class="cfd-sample-line cfd-' . $line['dir'] . '"' . ( $has_missing ? ' role="button" tabindex="0" aria-expanded="false"' : '' ) . '>';
		$out .= '<span class="cfd-sample-label">' . esc_html( $line['label'] ) . '</span>';
		$out .= '<span class="cfd-sample-text" style="' . esc_attr( $style ) . '" dir="' . $line['dir'] . '">' . cfd_render_glyph_string( $line['text'], $missing, true ) . '</span>';

		if ( $has_missing ) {
			$missing_bits = array_map( function ( $m ) {
				$label = $m['name'] ?? $m['codepoint'];
				return $m['char'] . ' (' . $label . ')';
			}, $line_missing );
			$out .= '<div class="cfd-sample-missing" hidden>Missing from this line: ' . esc_html( implode( ', ', $missing_bits ) ) . '</div>';
		}
		$out .= '</div>';
	}
	$out .= '</div>'; // .cfd-sample-lines


	$out .= '</div></div>'; // details-inner, details

	$out .= '</div>'; // .cfd-font-row
	return $out;
}

/** ZIP download URL, same /{family}/{family}.zip convention as the font binaries. */
function cfd_zip_url( $font ) {
	$family = rawurlencode( $font['family'] );
	return trailingslashit( CFD_FONT_BASE_URL ) . $family . '/' . $family . '.zip';
}

/** Generic download icon (tray + arrow) — currentColor so it inherits the
 * link's color automatically, including in dark mode, no separate SVG
 * asset to host. */
/** Link target for the font name — same charmap lightbox script as today. */
function cfd_charmap_url( $font ) {
	return content_url( 'uploads/fonts/display-font-charmap.php?fnt=' . rawurlencode( $font['family'] ) );
}

function cfd_render_toolbar() {
	return '
	<div class="cfd-toolbar" id="cfd-toolbar">
		<div class="cfd-size-control">
			<label for="cfdSizeSlider">Glyph size</label>
			<input id="cfdSizeSlider" type="range" min="20" max="72" step="2" value="34">
			<span class="cfd-size-readout" id="cfdSizeReadout">34px</span>
		</div>
		<label class="cfd-wide-toggle"><input type="checkbox" id="cfdShowWideLetters" checked> Show wide letterforms</label>
		<div class="cfd-toolbar-actions">
			<button type="button" data-cfd-action="expand-all">Expand all</button>
			<button type="button" data-cfd-action="collapse-all">Collapse all</button>
		</div>
	</div>';
}

/**
 * Generic checkbox filter panel for a tag field on $font (e.g.
 * `styleTags`, `scriptTags`) — only for tags actually present in the
 * current filtered set, and only if there's more than one (a single
 * checkbox that can't meaningfully narrow anything isn't useful).
 *
 * @param string $font_field  The per-font array field, e.g. 'styleTags'.
 * @param string $group_key   Short slug identifying this filter group in
 *                             the DOM (data-tag-group / data-tags-{key}) —
 *                             must be unique across all filter groups on
 *                             the page and safe as an HTML id fragment.
 * @param string $label       Human-readable group label, e.g. "Style".
 * @param array  $tag_titles  tag => display title map (from fonts.json's
 *                             `_meta`), falls back to a titleized tag.
 */
/**
 * Renders a checkbox filter row (Style/Script/Diacritics) for whichever
 * tags actually appear among $fonts. Checkboxes render in $tag_titles'
 * OWN key order — a deliberately curated sequence, not alphabetical —
 * so the display order is whatever CFD_STYLE_TAG_TITLES/
 * CFD_SCRIPT_TAG_TITLES/CFD_DIACRITIC_TITLES was written in. Any
 * present tag NOT in $tag_titles (e.g. a brand new style not yet added
 * to the map) falls through to the end, sorted alphabetically as a
 * safety net — so a forgotten tag still shows up somewhere, rather than
 * silently vanishing, while never displacing the curated order for
 * everything that IS accounted for.
 */
function cfd_render_tag_filter_group( $fonts, $font_field, $group_key, $label, $tag_titles ) {
	$present = array();
	foreach ( $fonts as $f ) {
		foreach ( $f[ $font_field ] ?? array() as $tag ) {
			$present[ $tag ] = true;
		}
	}
	if ( count( $present ) < 2 ) {
		return '';
	}

	$ordered = array();
	foreach ( array_keys( $tag_titles ) as $tag ) {
		if ( isset( $present[ $tag ] ) ) {
			$ordered[] = $tag;
			unset( $present[ $tag ] );
		}
	}
	$leftover = array_keys( $present ); // anything present but not in $tag_titles
	sort( $leftover );
	$ordered = array_merge( $ordered, $leftover );

	$out = '<div class="cfd-style-filter" role="group" aria-label="Filter by ' . esc_attr( $label ) . '">';
	$out .= '<span class="cfd-filter-label">' . esc_html( $label ) . '</span>';
	foreach ( $ordered as $tag ) {
		$title = $tag_titles[ $tag ] ?? ucfirst( str_replace( '-', ' ', $tag ) );
		$id    = 'cfd-' . sanitize_title( $group_key ) . '-' . sanitize_title( $tag );
		$out  .= '<label class="cfd-style-check" for="' . esc_attr( $id ) . '">'
			. '<input type="checkbox" checked id="' . esc_attr( $id ) . '" '
			. 'data-tag-group="' . esc_attr( $group_key ) . '" data-tag-value="' . esc_attr( $tag ) . '"> '
			. esc_html( $title ) . '</label>';
	}
	$out .= '<button type="button" class="cfd-filter-toggle-all" data-cfd-group-action="all" data-cfd-group="' . esc_attr( $group_key ) . '">All</button>';
	$out .= '<button type="button" class="cfd-filter-toggle-all" data-cfd-group-action="none" data-cfd-group="' . esc_attr( $group_key ) . '">None</button>';
	$out .= '</div>';
	return $out;
}

/** Empty shell for the active-filters summary — content and visibility
 * are entirely owned by JS (see refreshActiveFilterBar() in
 * cfd_inline_js()) since it needs to reflect live checkbox state, not
 * just the identity filter. */
function cfd_render_active_filter_chip() {
	return '<div class="cfd-active-filter" id="cfdActiveFilter" hidden></div>';
}

/**
 * Emits @font-face rules only for the fonts actually being rendered in this
 * shortcode call — a single-font embed in a blog post loads exactly one
 * font's face declaration, not the whole catalogue's.
 */
function cfd_render_font_face_css( $fonts ) {
	$css = '<style>';
	foreach ( $fonts as $f ) {
		$family = esc_attr( $f['family'] );
		$base   = trailingslashit( CFD_FONT_BASE_URL ) . rawurlencode( $f['family'] );
		$css   .= "@font-face{font-family:'{$family}';src:url('{$base}/{$family}.woff2') format('woff2'),url('{$base}/{$family}.woff') format('woff');font-display:swap;}";
	}
	$css .= '</style>';
	return $css;
}

/* ------------------------------------------------------------------ *
 *  Assets
 * ------------------------------------------------------------------ */

add_action( 'wp_enqueue_scripts', 'cfd_maybe_register_assets' );
function cfd_maybe_register_assets() {
	// Registered always (cheap), enqueued only when the shortcode is
	// actually present on the current post — see cfd_enqueue_assets().
	wp_register_style( 'cfd-style', false, array(), CFD_VERSION );
	wp_add_inline_style( 'cfd-style', cfd_inline_css() );

	wp_register_script( 'cfd-script', false, array(), CFD_VERSION, true );
	wp_add_inline_script( 'cfd-script', cfd_inline_js() );
}

function cfd_enqueue_assets() {
	wp_enqueue_style( 'cfd-style' );
	wp_enqueue_script( 'cfd-script' );
}

function cfd_inline_css() {
	$fallback = CFD_FALLBACK_BASE_URL;

	$reference_font_faces = '';
	foreach ( CFD_REFERENCE_FAMILIES as $ref ) {
		$ref_base               = trailingslashit( CFD_FONT_BASE_URL ) . rawurlencode( $ref );
		$reference_font_faces  .= "@font-face{font-family:'{$ref}';src:url('{$ref_base}/{$ref}.woff2') format('woff2'),url('{$ref_base}/{$ref}.woff') format('woff');font-display:swap;}\n\t";
	}

	return <<<CSS
	/* Defensive reset: the surrounding page (e.g. Atahualpa's .english-sans
	   wrapper) can apply text-align:justify or similar to ancestor
	   elements, which we'd otherwise silently inherit — every element in
	   here defaults to left, and the RTL-specific rules further down
	   (.cfd-abgad, the Yiddish/Ladino sample lines) override it by source
	   order below, since they're equal-or-higher specificity and come later. */
	.cfd-root, .cfd-root *{text-align:left;}
	.cfd-root{--cfd-abgad-size:34px;--cfd-sample-size:24px;}

	/* Readable-reference tier(s) — see CFD_REFERENCE_FAMILIES above.
	   Declared once here since every row's font-stack references them,
	   not just their own row when one happens to be on display. */
	{$reference_font_faces}

	/* --- missing-glyph fallback: single family, two unicode-range sources ---
	   BMP gap -> UnicodeBMPFallback's boxed-hex tofu; astral plane (symbols/
	   emoji) -> Last Resort's open box, so nothing renders as an accidental
	   color emoji and nothing renders as invisible. AdobeBlank and unifont
	   are intentionally NOT part of this chain by default:
	     - AdobeBlank was the previous invisible catch-all; superseded now
	       that the goal is to always show missing glyphs, not hide them.
	     - unifont draws real (if crude) glyphs for most of Unicode, which
	       would make a font look like it supports characters it doesn't —
	       useful for a separate "what does this character normally look
	       like" reference view, but wrong for this missing-glyph indicator. */
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_00000_000FF.woff2') format('woff2');
		unicode-range:U+00000-000FF;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_00100_0F35F.woff2') format('woff2');
		unicode-range:U+00100-0F35F;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_0F360_1DDE1.woff2') format('woff2');
		unicode-range:U+0F360-1DDE1;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_1DDE2_2C843.woff2') format('woff2');
		unicode-range:U+1DDE2-2C843;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_2C844_3B2A5.woff2') format('woff2');
		unicode-range:U+2C844-3B2A5;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_3B2A6_49D07.woff2') format('woff2');
		unicode-range:U+3B2A6-49D07;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_49D08_58769.woff2') format('woff2');
		unicode-range:U+49D08-58769;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_5876A_671CB.woff2') format('woff2');
		unicode-range:U+5876A-671CB;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_671CC_75C2D.woff2') format('woff2');
		unicode-range:U+671CC-75C2D;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_75C2E_8468F.woff2') format('woff2');
		unicode-range:U+75C2E-8468F;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_84690_930F1.woff2') format('woff2');
		unicode-range:U+84690-930F1;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_930F2_A1B53.woff2') format('woff2');
		unicode-range:U+930F2-A1B53;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_A1B54_B05B5.woff2') format('woff2');
		unicode-range:U+A1B54-B05B5;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_B05B6_BF015.woff2') format('woff2');
		unicode-range:U+B05B6-BF015;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_BF016_CDA77.woff2') format('woff2');
		unicode-range:U+BF016-CDA77;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_CDA78_DC4D9.woff2') format('woff2');
		unicode-range:U+CDA78-DC4D9;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_DC4DA_EAF3B.woff2') format('woff2');
		unicode-range:U+DC4DA-EAF3B;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_EAF3C_F999D.woff2') format('woff2');
		unicode-range:U+EAF3C-F999D;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_F999E_1083FF.woff2') format('woff2');
		unicode-range:U+F999E-1083FF;
	}
	@font-face{
		font-family:'FallbackFont';
		src:url('{$fallback}/UnicodeHexMono_108400_10FFFD.woff2') format('woff2');
		unicode-range:U+108400-10FFFD;
	}

	.cfd-toolbar{position:sticky;top:0;z-index:20;background:rgba(251,249,245,.92);
		backdrop-filter:saturate(140%) blur(6px);border-bottom:1px solid #e3dccd;
		padding:.7rem 1rem;display:flex;flex-wrap:wrap;align-items:center;gap:.9rem 1.5rem;}
	/* keep the toolbar below the WP admin bar for logged-in editors */
	body.admin-bar .cfd-toolbar{top:32px;}
	@media screen and (max-width:782px){ body.admin-bar .cfd-toolbar{top:46px;} }

	.cfd-size-control{display:flex;align-items:center;gap:.6rem;flex:1 1 240px;}
	.cfd-wide-toggle{display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;white-space:nowrap;cursor:pointer;}
	.cfd-hide-wide .cfd-wide-letter{display:none;}
	.cfd-size-control input[type=range]{flex:1;}
	.cfd-toolbar-actions{display:flex;gap:.5rem;margin-left:auto;}
	.cfd-toolbar-actions button{font-size:.8rem;border:1px solid #e3dccd;background:#fff;
		padding:.4rem .7rem;border-radius:999px;cursor:pointer;}

	.cfd-style-filter{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem .8rem;
		padding:.7rem 1rem 0;font-size:.82rem;}
	.cfd-filter-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#6b6152;}
	.cfd-style-check{display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;white-space:nowrap;}
	.cfd-filter-toggle-all{font-size:.72rem;border:1px solid #e3dccd;background:#fff;
		padding:.15rem .5rem;border-radius:999px;cursor:pointer;color:#6b6152;}
	.cfd-filter-toggle-all:hover{color:#b8631f;border-color:#b8631f;}

	.cfd-active-filter{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;margin:.7rem 1rem 0;}
	.cfd-active-filter[hidden]{display:none !important;}
	.cfd-filter-chip{display:inline-flex;align-items:center;gap:.4rem;
		padding:.3rem .5rem .3rem .8rem;border-radius:999px;background:#fbf1e4;font-size:.82rem;
		color:#6b6152;}
	.cfd-filter-chip button{border:none;background:none;color:#b8631f;cursor:pointer;
		font-size:.9em;line-height:1;padding:0;}
	.cfd-clear-all{font-size:.78rem;border:1px solid #b8631f;background:#fff;
		color:#b8631f;padding:.25rem .6rem;border-radius:999px;cursor:pointer;}

	.cfd-font-row[hidden],.cfd-category[hidden]{display:none !important;}

	.cfd-filter-link{font-size:.68rem;border:1px solid #e3dccd;background:#fff;color:#6b6152;
		padding:.05rem .4rem;border-radius:999px;cursor:pointer;vertical-align:middle;}
	.cfd-filter-link:hover{color:#b8631f;border-color:#b8631f;}

	.cfd-custom-text{padding:.6rem 1rem 0;}
	.cfd-custom-textarea{display:block;width:100%;box-sizing:border-box;
		direction:rtl;text-align:right;font-size:var(--cfd-abgad-size);line-height:1.6;
		border:1px solid #e3dccd;border-radius:8px;padding:.5rem .75rem;
		resize:vertical;background:transparent;color:inherit;}
	/* pre-filled example verse (see cfd_default_test_text()) reads as a
	   suggestion, not the user's own input — lightened until they
	   actually edit it, at which point cfd_inline_js() drops this class */
	.cfd-custom-textarea.is-example{opacity:.5;}

	.cfd-category{margin-top:2.2rem;scroll-margin-top:5rem;}
	.cfd-category h2{border-bottom:2px solid #b8631f;padding-bottom:.4rem;}
	.cfd-cat-note{font-size:.85rem;color:#6b6152;}
	/* keep anchor-jump targets clear of the sticky toolbar + admin bar */
	body.admin-bar .cfd-category{scroll-margin-top:calc(5rem + 32px);}
	@media screen and (max-width:782px){ body.admin-bar .cfd-category{scroll-margin-top:calc(5rem + 46px);} }

	.cfd-font-list{display:flex;flex-direction:column;gap:.6rem;}
	.cfd-font-row{border:1px solid #e3dccd;border-radius:14px;background:#fff;
		content-visibility:auto;contain-intrinsic-size:160px;}
	.cfd-font-row-main{display:flex;flex-direction:column;align-items:flex-start;gap:.35rem;
		padding:.85rem 1rem;cursor:pointer;}
	.cfd-font-row-id{display:flex;flex-direction:column;gap:.35rem;min-width:0;width:100%;}
	.cfd-font-name{font-size:1.3rem;font-weight:700;line-height:1.3;}
	.cfd-abgad{direction:rtl;text-align:right;font-size:var(--cfd-abgad-size);
		line-height:1.6;overflow-x:auto;overflow-y:hidden;white-space:nowrap;}
	/* characters the font's own cmap doesn't cover (i.e. rendered via the
	   CFD_REFERENCE_FAMILIES tier, not the font itself) — opacity rather
	   than a fixed gray: dims whatever the inherited text color already
	   is, so it stays legibly "quieter" without being mistaken for a
	   present character, and needs no separate dark-mode override since
	   it scales with whatever color dark mode already sets. */
	.cfd-missing{opacity:.15;}

	.cfd-sample-lines{display:flex;flex-direction:column;gap:.35rem;padding:0 1rem .8rem;}

	.cfd-sample-line{display:grid;grid-template-columns:8rem minmax(0,1fr);align-items:center;gap:.6rem;}
	.cfd-sample-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#6b6152;
		overflow-wrap:normal;word-break:normal;hyphens:none;}
	.cfd-sample-text{font-size:var(--cfd-sample-size);line-height:1.7;
		overflow-x:auto;overflow-y:hidden;white-space:nowrap;}
	.cfd-sample-line.cfd-rtl .cfd-sample-text{direction:rtl;text-align:left;}
	.cfd-sample-line.cfd-ltr .cfd-sample-text{direction:ltr;text-align:left;}
	.cfd-sample-text [title]{cursor:help;}
	.cfd-sample-line[role="button"]{cursor:pointer;border-radius:6px;}
	.cfd-sample-line[role="button"]:hover{background:rgba(128,128,128,.18);}
	.cfd-sample-missing{grid-column:1/-1;font-size:.8rem;color:#6b6152;
		padding-top:.4rem;margin-top:.2rem;border-top:1px dashed #e3dccd;}
	@media (max-width:520px){ .cfd-sample-line{grid-template-columns:1fr;gap:.1rem;} }

	.cfd-font-row-details{display:grid;grid-template-rows:0fr;transition:grid-template-rows .22s ease;}
	.cfd-font-row.is-expanded .cfd-font-row-details{grid-template-rows:1fr;}
	.cfd-font-row-details-inner{overflow:hidden;}
	.cfd-details-grid{padding:.8rem 1rem 1rem;border-top:1px solid #e3dccd;margin:0;
		display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem 1.2rem;font-size:.82rem;}
	.cfd-details-grid dt{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#6b6152;}
	.cfd-details-grid dd{margin:0;}
	.cfd-notes-cell{grid-column:1/-1;}

	.cfd-altform-list{display:flex;flex-wrap:wrap;gap:1rem;direction:rtl;text-align:right;}
	.cfd-altform{display:inline-flex;align-items:baseline;gap:.3rem;}
	.cfd-altform-glyph{font-size:1.7rem;}
	.cfd-altform-ref{font-size:1.1rem;color:#6b6152;}

	.cfd-single .cfd-font-row{border-width:1px;}
	@media (prefers-reduced-motion: reduce){
		.cfd-font-row-details{transition:none;}
	}
	CSS;
}

function cfd_inline_js() {
	return <<<'JS'
	(function(){
		var state = { groups: null, identityFilter: null, fontFilter: null };

		// URL query param name for each filter group's internal key — "category"
		// is used here rather than "diacritics" specifically to match the
		// existing [custom_fonts_display category="..."] shortcode attribute's
		// established public name, even though the group's own internal key
		// (used in data-tag-group/data-tags-*) is "diacritics".
		var GROUP_URL_PARAM = { style: 'style', script: 'script', diacritics: 'category' };

		function initGroups(){
			state.groups = {};
			document.querySelectorAll('[data-tag-group]').forEach(function(cb){
				var g = cb.dataset.tagGroup;
				if (!state.groups[g]) state.groups[g] = new Set();
				if (cb.checked) state.groups[g].add(cb.dataset.tagValue);
			});
		}

		function applyFilters(){
			if (!state.groups) initGroups();
			document.querySelectorAll('.cfd-font-row').forEach(function(row){
				var groupsOk = true;
				for (var g in state.groups) {
					var tags = (row.getAttribute('data-tags-' + g) || '').split(' ').filter(Boolean);
					var thisGroupOk = tags.length === 0 || tags.some(function(t){ return state.groups[g].has(t); });
					if (!thisGroupOk) { groupsOk = false; break; }
				}
				var identityOk = true;
				if (state.identityFilter) {
					identityOk = row.dataset[state.identityFilter.type] === state.identityFilter.value;
				}
				var fontOk = true;
				if (state.fontFilter) {
					fontOk = (row.dataset.slug || '').toLowerCase() === state.fontFilter.toLowerCase();
				}
				row.hidden = !(groupsOk && identityOk && fontOk);
			});
			document.querySelectorAll('.cfd-category').forEach(function(cat){
				cat.hidden = !cat.querySelector('.cfd-font-row:not([hidden])');
			});
		}

		function escapeHtml(s){
			return String(s).replace(/[&<>"']/g, function(c){
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		}

		// Reads style/script/category/foundry/typographer/font/wideletterforms
		// from the current URL's query string into a plain object — does NOT
		// touch `state` or the DOM itself (see applyUrlState() for that).
		function readUrlState(){
			var params = new URLSearchParams(location.search);
			var result = { groups: {}, identityFilter: null, fontFilter: null, hideWide: false };

			for (var g in GROUP_URL_PARAM) {
				var raw = params.get(GROUP_URL_PARAM[g]);
				if (raw !== null) {
					result.groups[g] = raw.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
				}
			}

			var foundry = params.get('foundry');
			var typographer = params.get('typographer');
			if (foundry) {
				result.identityFilter = { type: 'foundry', value: foundry };
			} else if (typographer) {
				result.identityFilter = { type: 'typographer', value: typographer };
			}

			var font = params.get('font');
			if (font) result.fontFilter = font;

			// "on" is the default (checkbox starts checked) — only an explicit
			// "off" changes anything; any other value (including a typo) is
			// silently treated as "on", same as the param being absent.
			result.hideWide = ( params.get('wideletterforms') === 'off' );

			return result;
		}

		// Toggles the wide-letterforms checkbox AND the .cfd-hide-wide class
		// on every .cfd-root together, so applyUrlState() and the checkbox's
		// own change handler share one implementation instead of two copies
		// of the same class-toggling logic drifting apart later.
		function setWideLettersVisible(visible){
			var cb = document.getElementById('cfdShowWideLetters');
			if (cb) cb.checked = visible;
			document.querySelectorAll('.cfd-root').forEach(function(root){
				root.classList.toggle('cfd-hide-wide', !visible);
			});
		}

		// Applies a readUrlState()-shaped object to the actual checkboxes and
		// `state` — separate from readUrlState() so the initial-load path and
		// any future "restore a saved view" path could both reuse this half.
		function applyUrlState(urlState){
			for (var g in urlState.groups) {
				var wanted = urlState.groups[g];
				var wantedSet = new Set(wanted);
				document.querySelectorAll('[data-tag-group="' + g + '"]').forEach(function(cb){
					cb.checked = wantedSet.has(cb.dataset.tagValue);
				});
			}
			initGroups();
			state.identityFilter = urlState.identityFilter;
			state.fontFilter = urlState.fontFilter;
			setWideLettersVisible( ! urlState.hideWide );
		}

		// Mirrors the current `state` back into the URL via replaceState — never
		// pushState, so filtering doesn't pile up browser-history entries the
		// person would have to click "back" through repeatedly to leave the
		// page. A group is only written when it's NOT fully checked (matching
		// refreshActiveFilterBar()'s own "only show a chip when filtered" test),
		// so an unfiltered page keeps a clean URL with no query string at all.
		function writeUrlState(){
			var params = new URLSearchParams();

			for (var g in state.groups) {
				var total = document.querySelectorAll('[data-tag-group="' + g + '"]').length;
				if (total > 0 && state.groups[g].size < total) {
					params.set(GROUP_URL_PARAM[g], Array.from(state.groups[g]).join(','));
				}
			}

			if (state.identityFilter) {
				params.set(state.identityFilter.type, state.identityFilter.value);
			}

			if (state.fontFilter) {
				params.set('font', state.fontFilter);
			}

			// Read directly from the checkbox rather than tracking a
			// state.hideWide — this toggle isn't part of the shared
			// `state`/refresh() system the other filters use (it only ever
			// touches a CSS class, never row visibility), so there's nothing
			// else that needs a persistent copy of this one boolean.
			var wideCb = document.getElementById('cfdShowWideLetters');
			if (wideCb && !wideCb.checked) {
				params.set('wideletterforms', 'off');
			}

			var search  = params.toString();
			var newUrl  = location.pathname + (search ? '?' + search : '') + location.hash;
			history.replaceState(null, '', newUrl);
		}

		function groupLabel(g){
			var labelEl = document.querySelector('[data-tag-group="' + g + '"]');
			var groupEl = labelEl ? labelEl.closest('.cfd-style-filter') : null;
			var span = groupEl ? groupEl.querySelector('.cfd-filter-label') : null;
			return span ? span.textContent : g;
		}

		function refreshActiveFilterBar(){
			var bar = document.getElementById('cfdActiveFilter');
			if (!bar) return;
			if (!state.groups) initGroups();

			var chips = [];
			for (var g in state.groups) {
				var total = document.querySelectorAll('[data-tag-group="' + g + '"]').length;
				var checkedCount = state.groups[g].size;
				if (total > 0 && checkedCount < total) {
					chips.push('<span class="cfd-filter-chip">' + escapeHtml(groupLabel(g)) + ': ' + checkedCount + ' of ' + total
						+ ' shown <button type="button" data-cfd-clear="group" data-cfd-clear-group="' + g + '" aria-label="Clear ' + escapeHtml(groupLabel(g)) + ' filter">\u2715</button></span>');
				}
			}
			if (state.identityFilter) {
				var label = state.identityFilter.type === 'foundry' ? 'Foundry' : 'Typographer';
				chips.push('<span class="cfd-filter-chip">' + label + ': ' + escapeHtml(state.identityFilter.value)
					+ ' <button type="button" data-cfd-clear="identity" aria-label="Clear ' + label.toLowerCase() + ' filter">\u2715</button></span>');
			}
			if (state.fontFilter) {
				// Array.find() by direct value comparison, not a
				// string-concatenated querySelector attribute selector —
				// state.fontFilter comes from a URL someone could craft with
				// an unexpected character (e.g. a stray "), which would
				// otherwise throw a DOMException and abort the rest of
				// this refresh() entirely.
				var fontRow = Array.prototype.find.call(document.querySelectorAll('.cfd-font-row'), function(row){
					return (row.dataset.slug || '').toLowerCase() === state.fontFilter.toLowerCase();
				});
				var fontName = fontRow ? fontRow.querySelector('.cfd-font-name').textContent : state.fontFilter;
				chips.push('<span class="cfd-filter-chip">Font: ' + escapeHtml(fontName)
					+ ' <button type="button" data-cfd-clear="font" aria-label="Clear font filter">\u2715</button></span>');
			}

			if (chips.length === 0) {
				bar.hidden = true;
				bar.innerHTML = '';
				return;
			}
			bar.innerHTML = chips.join('') + '<button type="button" id="cfdClearAllFilters" class="cfd-clear-all">Clear all</button>';
			bar.hidden = false;
		}

		function refresh(){
			applyFilters();
			refreshActiveFilterBar();
			writeUrlState();
		}

		function resetGroup(g){
			document.querySelectorAll('[data-tag-group="' + g + '"]').forEach(function(cb){ cb.checked = true; });
			initGroups();
		}

		function resetAllGroups(){
			document.querySelectorAll('[data-tag-group]').forEach(function(cb){ cb.checked = true; });
			initGroups();
		}

		document.addEventListener('change', function(e){
			if (e.target.matches('[data-tag-group]')) {
				initGroups();
				refresh();
			}
		});

		document.addEventListener('click', function(e){
			var main = e.target.closest('.cfd-font-row-main');
			var row  = main ? main.closest('.cfd-font-row') : null;
			if (row) {
				var expanded = row.classList.toggle('is-expanded');
				row.querySelector('.cfd-font-row-main').setAttribute('aria-expanded', String(expanded));
				return;
			}

			var sampleLine = e.target.closest('.cfd-sample-line[role="button"]');
			if (sampleLine) {
				var missingBox = sampleLine.querySelector('.cfd-sample-missing');
				if (missingBox) {
					var revealed = missingBox.hidden;
					missingBox.hidden = !revealed;
					sampleLine.setAttribute('aria-expanded', String(revealed));
				}
				return;
			}

			var action = e.target.closest('[data-cfd-action]');
			if (action) {
				var expand = action.dataset.cfdAction === 'expand-all';
				document.querySelectorAll('.cfd-font-row').forEach(function(r){
					r.classList.toggle('is-expanded', expand);
					r.querySelector('.cfd-font-row-main').setAttribute('aria-expanded', String(expand));
				});
				return;
			}

			var groupAction = e.target.closest('[data-cfd-group-action]');
			if (groupAction) {
				var g = groupAction.dataset.cfdGroup;
				var checked = groupAction.dataset.cfdGroupAction === 'all';
				document.querySelectorAll('[data-tag-group="' + g + '"]').forEach(function(cb){ cb.checked = checked; });
				initGroups();
				refresh();
				return;
			}

			var filterBtn = e.target.closest('[data-cfd-filter]');
			if (filterBtn) {
				state.identityFilter = { type: filterBtn.dataset.cfdFilter, value: filterBtn.dataset.cfdFilterValue };
				refresh();
				return;
			}

			var clearBtn = e.target.closest('[data-cfd-clear]');
			if (clearBtn) {
				if (clearBtn.dataset.cfdClear === 'group') {
					resetGroup(clearBtn.dataset.cfdClearGroup);
				} else if (clearBtn.dataset.cfdClear === 'identity') {
					state.identityFilter = null;
				} else if (clearBtn.dataset.cfdClear === 'font') {
					state.fontFilter = null;
				}
				refresh();
				return;
			}

			if (e.target.closest('#cfdClearAllFilters')) {
				resetAllGroups();
				state.identityFilter = null;
				state.fontFilter = null;
				refresh();
			}
		});

		document.addEventListener('input', function(e){
			if (e.target.id !== 'cfdSizeSlider') return;
			var px = e.target.value + 'px';
			document.querySelectorAll('.cfd-root').forEach(function(root){
				root.style.setProperty('--cfd-abgad-size', px);
				root.style.setProperty('--cfd-sample-size', (e.target.value * 0.71) + 'px');
			});
			var readout = document.getElementById('cfdSizeReadout');
			if (readout) readout.textContent = px;
		});

		// pre-filled example verse (cfd_default_test_text()): select it all
		// on first focus so typing immediately replaces it, and drop the
		// lightened "is-example" styling the moment the user actually edits.
		document.addEventListener('focusin', function(e){
			var ta = e.target.closest && e.target.closest('.cfd-custom-textarea.is-example');
			if (ta) ta.select();
		});
		document.addEventListener('input', function(e){
			if (e.target.matches && e.target.matches('.cfd-custom-textarea')) {
				e.target.classList.remove('is-example');
			}
		});

		document.addEventListener('change', function(e){
			if (e.target.id !== 'cfdShowWideLetters') return;
			setWideLettersVisible(e.target.checked);
			writeUrlState();
		});

		// keyboard activation for role="button" elements (.cfd-font-row-main,
		// .cfd-sample-line) — these are plain <div>s, which don't natively
		// fire click on Enter/Space the way a real <button> would; without
		// this, tabindex="0" + role="button" alone isn't actually
		// keyboard-operable, just keyboard-focusable.
		document.addEventListener('keydown', function(e){
			if (e.key !== 'Enter' && e.key !== ' ') return;
			// matches(), not closest() — only fire when the focused element
			// IS ITSELF the role="button" target, not when focus happens to
			// be on some nested interactive descendant (e.g. the font-name
			// link inside .cfd-font-row-main, which has its own native
			// Enter behavior already; closest() here would double-fire both).
			if (e.target.matches && e.target.matches('[role="button"]')) {
				e.preventDefault();
				e.target.click();
			}
		});

		// Apply whatever filters a crafted URL specifies (style, script,
		// category, foundry, typographer, font — see
		// readUrlState()) before the first paint-relevant refresh, so a shared
		// link lands already filtered rather than flashing unfiltered first.
		// refresh() here also normalizes the URL via writeUrlState() — e.g. a
		// hand-typed tag that doesn't match anything real simply won't appear
		// in the rewritten URL, self-correcting immediately.
		applyUrlState(readUrlState());
		refresh();
	})();
	JS;
}