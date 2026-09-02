<?php
/**
 * File: ccg-frontend.php
 * Version: 1.5.0
 *
 * Public banner rendering.
 *
 * Composes a single banner paragraph, built around one unified "terms
 * of sharing" sentence for whichever of the 7 ACF open_content_license
 * choices applies — an open license (CC BY-SA/BY/CC0) and a Fair Use/
 * Reproduction Right/government-work/foreign-edict exception are all
 * treated identically here: same lead-in, same "is shared under..."
 * structure, same badge treatment. Per your correction, a Fair Use
 * claim describes the terms under which a work is shared exactly as
 * much as an open license does, and the two are never given different
 * framing. Up to two further sentences can follow:
 *   2. A Public Domain date estimate — only for the two exception
 *      categories (Fair Use/Reproduction Right), and only when the
 *      decision engine's date math has something to add beyond what
 *      the ACF field's own citation already states (a likely PD-entry
 *      date, or a "this is actually already PD by age" note)
 *   3. Contributor work's own PD-by-age note — shown once computable
 * ...plus an optional trailing "Extracted from {parent}" line, kept
 * separate from the merged paragraph since it's an independent fact.
 *
 * Each sentence is independently optional; the banner only renders a
 * wrapper div if at least one sentence (or the parent note) has
 * something to say. Nothing here writes to post meta — this file only
 * reads and renders.
 *
 * Note: the original plugin's per-post source_url, more_url, title-display
 * toggle, site-wide active flag, and DCMI format type are intentionally
 * not carried forward here — confirmed deprecated, not a gap.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

require_once( plugin_dir_path( __FILE__ ) . 'ccg-fields.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ccg-decision-engine.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ccg-acf-mapping.php' ); // for ccg_get_acf_derived_mapping()

add_filter( 'the_content', 'ccg_display', 99999999999 );
add_shortcode( 'ccg_banner', 'ccg_banner_shortcode' );

/**
 * Auto-appends the banner to single-post content, as the plugin has
 * always done. Skips appending when the post's content already
 * contains the [ccg_banner] shortcode (see ccg_banner_shortcode()
 * below) — otherwise a post using the shortcode for manual placement
 * would get the banner twice: once where the shortcode was placed, and
 * once appended automatically here.
 */
function ccg_display( $content ) {
	if ( is_single() && ! has_shortcode( $content, 'ccg_banner' ) ) {
		global $post;
		$content .= ccg_get_banner_html( $post->ID );
	}
	return $content;
}

/**
 * [ccg_banner] shortcode — renders the banner at a specific point in a
 * post's content, or in a page/widget/template via do_shortcode(),
 * instead of relying solely on the automatic the_content append above.
 * Defaults to the current post; pass post_id="123" for a specific one
 * (e.g. embedding one post's banner elsewhere).
 */
function ccg_banner_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'post_id' => 0 ), $atts, 'ccg_banner' );

	$post_id = (int) $atts['post_id'];
	if ( $post_id <= 0 ) {
		$post_id = get_the_ID();
	}
	if ( ! $post_id ) return '';

	return ccg_get_banner_html( $post_id );
}

/**
 * Assemble the full banner for a post. Returns '' if nothing has
 * anything to show — fails closed, never renders an empty box.
 */
function ccg_get_banner_html( $post_id ) {

	// ACF is the unconditional source for the license/category choice.
	// CCG's own stored fields are deprecated per your instruction and
	// are never consulted here. See ccg_get_acf_derived_mapping() in
	// ccg-migration.php.
	$acf_mapping = ccg_get_acf_derived_mapping( $post_id );

	$category_override = ( $acf_mapping && $acf_mapping['target'] === 'source_category' )
		? $acf_mapping['value'] : null;
	$license_override = ( $acf_mapping && $acf_mapping['target'] === 'contributor_license' )
		? $acf_mapping['value'] : null;

	$sentences = array(
		ccg_strip_outer_p( ccg_format_terms_sentence_html( $post_id, $acf_mapping ) ),
		ccg_strip_outer_p( ccg_format_pd_estimate_sentence_html( $post_id, $category_override ) ),
		ccg_strip_outer_p( ccg_format_contributor_pd_note_html( $post_id, $license_override ) ),
	);
	$sentences = array_filter( $sentences, function( $s ) { return trim( $s ) !== ''; } );

	$lines = array();
	if ( ! empty( $sentences ) ) {

		$text_html = '<span style="flex:1;">' . implode( ' ', $sentences ) . '</span>';

		// Badge comes directly from the ACF mapping now — it's the
		// single source for all 7 choices (open licenses and the
		// Fair Use/Reproduction Right/govt-work/foreign-edict
		// exceptions alike), so there's no separate fallback path.
		$badge_html = ( $acf_mapping && ! empty( $acf_mapping['badge'] ) )
			? ccg_format_acf_badge_html( $acf_mapping ) : '';

		if ( $badge_html !== '' ) {
			// Flex row: text first (grows to fill the row via flex:1),
			// badge second (flex-shrink:0, so it keeps its natural size
			// and lands at the right edge) — vertically centered against
			// each other via align-items:center on the row.
			$lines[] = '<p class="ccg-banner-text" style="display:flex; align-items:center; gap:1em; margin:0;">'
				. $text_html . $badge_html . '</p>';
		} else {
			$lines[] = '<p class="ccg-banner-text" style="margin:0;">' . implode( ' ', $sentences ) . '</p>';
		}
	}

	$parent_note = ccg_format_contributor_parent_note_html( $post_id );
	if ( trim( $parent_note ) !== '' ) {
		$lines[] = $parent_note;
	}

	if ( empty( $lines ) ) return '';

	return '<div class="ccg-license-box"><a name="license-info"></a>'
		. '<div class="print-only copyright delete-no ccg-banner">'
		. implode( '', $lines )
		. '</div></div>';
}

/**
 * Strip a single outer <p ...>...</p> wrapper from one of the line
 * formatter functions' output, so its content can be joined into the
 * merged paragraph above instead of rendering as its own block. Each
 * formatter always returns either '' or exactly one top-level <p>
 * element (no nested <p>s), so a simple anchored regex is safe here —
 * this is not a general HTML parser.
 */
function ccg_strip_outer_p( $html ) {
	$html = trim( $html );
	if ( $html === '' ) return '';
	if ( preg_match( '/^<p[^>]*>(.*)<\/p>$/s', $html, $m ) ) {
		return $m[1];
	}
	return $html; // Fallback: return as-is if it doesn't match the expected shape.
}

/**
 * Public Domain date estimate — an ADDITIONAL sentence, shown only for
 * the two Fair Use/Reproduction Right exception categories, and only
 * when the decision engine's date math produces something genuinely
 * new beyond what ccg_format_terms_sentence_html() already states via
 * the ACF field's own citation text. Two of the four possible bases
 * from ccg_compute_source_pd_status() are handled: rolling_95
 * (confident) and death_based/foreign_default (non-committal
 * estimates, both rendered via ccg_format_estimated_pd_sentence()).
 * 'no_date_data' is deliberately NOT rendered here (nothing to say
 * until an editor enters publication_year for this post) — those cases
 * add nothing the unified terms sentence hasn't already said (raw ACF
 * text already states "under copyright, Fair Use exception applies"),
 * so re-stating them in different words would just be redundant, not
 * informative.
 *
 * Gated on show_pd_estimate_sentence (the whole sentence's visibility)
 * separately from show_estimated_pd_date (whether the death_based/
 * foreign_default variant includes its two computed years) — the two
 * settings control different things.
 */
function ccg_format_pd_estimate_sentence_html( $post_id, $category_override = null ) {

	if ( ! ccg_get_settings()['show_pd_estimate_sentence'] ) return '';
	if ( ! in_array( $category_override, array( 'reproduction_right_108', 'fair_use_107' ), true ) ) return '';

	$r = ccg_compute_source_pd_status( $post_id, $category_override );

	switch ( $r['basis'] ) {

		case 'rolling_95':
			$cutoff_year = (int) current_time( 'Y' ) - CCG_PD_TERM_ROLLING;
			return '<p class="ccg-pd-estimate">' . sprintf(
				esc_html__(
					'This source content is believed to be in the Public Domain (works published before 1 January %d).',
					'ccg-domain'
				),
				$cutoff_year
			) . '</p>';

		case 'death_based':
		case 'foreign_default':
			return '<p class="ccg-pd-estimate">' . ccg_format_estimated_pd_sentence( $r['computed_year'] ) . '</p>';

		default:
			return '';
	}
}

/**
 * Sentence for the 'death_based' / 'foreign_default' basis — the
 * low-confidence estimate case. Built as two full-sentence variants
 * (rather than assembling optional word-fragments at runtime) so each
 * is a single, natural, fully-translatable string rather than a sentence
 * with holes in awkward places for translators to work around.
 *
 * $computed_year is the decision engine's already-Jan-1-adjusted PD-entry
 * year (see ccg-decision-engine.php — it's computed as
 * raw_expiration_year + 1). The raw term-expiration year shown in the
 * "due to... expiration... in YEAR" clause is therefore $computed_year - 1.
 */
function ccg_format_estimated_pd_sentence( $computed_year ) {

	$settings = ccg_get_settings();

	if ( ! $settings['show_estimated_pd_date'] ) {
		return esc_html__(
			'The source content displayed is estimated to have entered the Public Domain due to either the expiration of its term of copyright or non-conformity with established copyright registration rules when (or for a period after) it was initially published.',
			'ccg-domain'
		);
	}

	$pd_entry_year        = (int) $computed_year;
	$term_expiration_year = $pd_entry_year - 1;

	return sprintf(
		/* translators: %1$d: Public Domain entry year (Jan 1 of this year). %2$d: raw copyright term expiration year (one year earlier). */
		esc_html__(
			'The source content displayed is estimated to have entered the Public Domain on 1 January %1$d due to either the expiration of its term of copyright in %2$d or non-conformity with established copyright registration rules when (or for a period after) it was initially published.',
			'ccg-domain'
		),
		$pd_entry_year,
		$term_expiration_year
	);
}

/**
 * Badge image driven directly by an ACF mapping entry (see
 * ccg_get_acf_derived_mapping() in ccg-migration.php) — the single
 * badge source for all 7 open_content_license choices now, open
 * license or exception category alike. Links to the mapping's own URL
 * (the legal citation or CC license page the ACF choice pointed to).
 * Returns '' only if the ACF mapping itself has no badge — in practice
 * this never happens now, since all 7 choices have one, but the check
 * stays as a safe default for any future choice added without one.
 */
function ccg_format_acf_badge_html( $mapping ) {

	if ( empty( $mapping['badge'] ) ) return '';

	$image_url = plugins_url( 'images/' . $mapping['badge'], __FILE__ );

	return '<a target="_blank" rel="license nofollow" href="' . esc_url( $mapping['url'] ) . '" style="flex-shrink:0;">'
		. '<img alt="' . esc_attr( $mapping['badge_alt'] ) . '" style="display:block; border-width:0;" '
		. 'src="' . esc_url( $image_url ) . '" /></a>';
}

/**
 * The unified "terms of sharing" sentence — the one place where a work
 * shared under an open license and a work shared under a Fair Use/
 * Reproduction Right/government-work/foreign-edict exception get
 * exactly the same treatment: same lead-in, same "is shared under..."
 * structure, no framing that treats one as more legitimate or more
 * fully described than the other. Uses the ACF field's own stored
 * value verbatim as the terms clause (already a correctly formatted
 * "<a href>label</a> description" string) rather than a hand-written
 * re-description per category, so wording is automatically consistent
 * with whatever the field actually says. wp_kses_post() sanitizes it
 * as defense-in-depth, since it's raw admin-entered HTML.
 *
 * Lead-in: title when show_title is on and the post has one; otherwise
 * the generic "This work is shared through the Open Siddur Project...".
 * Per your decision, this is a project-level declaration, not a
 * contributor-specific one — the lead-in never names an individual;
 * your bio boxes handle per-contributor attribution separately.
 *
 * Returns '' if there's no ACF mapping for this post at all.
 */
function ccg_format_terms_sentence_html( $post_id, $acf_mapping ) {

	if ( ! $acf_mapping || empty( $acf_mapping['raw_html'] ) ) return '';

	$settings = ccg_get_settings();
	$title    = $settings['show_title'] ? get_the_title( $post_id ) : '';

	if ( trim( $title ) !== '' ) {
		$lead = '&#8220;' . esc_html( $title ) . '&#8221;'
			. ' ' . esc_html__( 'is shared through the Open Siddur Project', 'ccg-domain' );
	} else {
		$lead = esc_html__( 'This work is shared through the Open Siddur Project', 'ccg-domain' );
	}

	return '<p class="ccg-terms">' . $lead . ' '
		. esc_html__( 'under the', 'ccg-domain' ) . ' '
		. wp_kses_post( $acf_mapping['raw_html'] ) . '.</p>';
}

/**
 * The "Extracted from {parent}" note, when a parent/compiled work is set.
 * Split out from ccg_format_contributor_license_html() so it stays its
 * own trailing line after the merged three-sentence paragraph, rather
 * than getting folded into one of the three merged sentences — it's a
 * distinct, optional fact rather than part of the license statement
 * itself.
 */
function ccg_format_contributor_parent_note_html( $post_id ) {

	$contributor = ccg_get_contributor_license( $post_id );
	if ( trim( $contributor['parent_resource_id'] ) === '' ) return '';

	$parent_id = (int) $contributor['parent_resource_id'];
	if ( get_post_status( $parent_id ) !== 'publish' ) return '';

	return '<p class="ccg-parent-work">' . sprintf(
		/* translators: %s: linked title of the parent/compiled work, already-escaped HTML. */
		esc_html__( 'Extracted from %s.', 'ccg-domain' ),
		'<a href="' . esc_url( get_permalink( $parent_id ) ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a>'
	) . '</p>';
}

/**
 * Line 3: the contributor work's own PD-by-age note (spec §2b/§4.3).
 * Always shown once computable, per your original confirmation — no
 * display horizon — but gated behind the show_contributor_pd_note
 * setting, so the entire sentence (including the timestamp-derived date)
 * can be switched off site-wide. Also gated on $license_override being
 * set (i.e. ACF genuinely assigns this post a Contributor License), as
 * a proxy for "there is original contributed text here" to speak of —
 * per the CCG-data-deprecation directive, this no longer reads CCG's
 * own stored data at all.
 */
function ccg_format_contributor_pd_note_html( $post_id, $license_override = null ) {

	if ( ! ccg_get_settings()['show_contributor_pd_note'] ) return '';
	if ( $license_override === null ) return '';

	$post = get_post( $post_id );
	if ( ! $post ) return '';

	$pub_timestamp = strtotime( $post->post_date_gmt ?: $post->post_date );
	if ( ! $pub_timestamp ) return '';

	$pub_year = (int) gmdate( 'Y', $pub_timestamp );
	$pd_year  = $pub_year + CCG_PD_TERM_ROLLING + 1; // Same +1 convention as the source rolling rule.

	$pd_timestamp = strtotime( $pd_year . '-01-01' );
	$now          = current_time( 'timestamp' );

	if ( $now >= $pd_timestamp ) {
		$text = sprintf(
			esc_html__( 'This resource is estimated to have entered the Public Domain on 1 January %d, excepting any content that had already entered the Public Domain.', 'ccg-domain' ),
			$pd_year
		);
	} else {
		$text = sprintf(
			esc_html__( 'This resource is estimated to enter the Public Domain on 1 January %d (95 years after publication), excepting any content that has already entered the Public Domain.', 'ccg-domain' ),
			$pd_year
		);
	}

	return '<p class="ccg-contributor-pd-note">' . $text . '</p>';
}