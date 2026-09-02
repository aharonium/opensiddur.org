<?php
/**
 * File: ccg-acf-mapping.php
 * Version: 1.0.0
 *
 * ACF `open_content_license` field lookup, used by ccg-frontend.php.
 *
 * This replaces the old ccg-migration.php, which also contained a
 * one-time WP-CLI migration command (writing ACF-derived values into
 * CCG's own _ccg_source_status/_ccg_contributor postmeta), plus a
 * backup/restore mechanism for that command. Since the site is no
 * longer pursuing that migration — ACF is the permanent source of
 * truth, and CCG's own stored fields are deprecated — all of that
 * migration-writing machinery has been removed. Only the read-only
 * mapping logic below survives, since ccg-frontend.php's banner
 * rendering depends on it directly.
 *
 * If a full site backup/restore of ACF data is ever needed again in
 * the future, that's a job for standard WordPress/database backups,
 * not plugin-specific machinery — the old backup system here only
 * ever protected CCG's own postmeta, which nothing reads anymore.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

// Maps the exact href values used in the ACF "Open Content License"
// field's choices to a target/value pair, plus display metadata used
// directly by ccg-frontend.php:
//   'target' — which concern this choice represents: 'contributor_license'
//              (CC BY-SA/BY/CC0) or 'source_category' (govt-work,
//              foreign-edict, Reproduction Right, Fair Use)
//   'badge' / 'badge_alt' — badge image filename and alt text
// Every one of the 7 choices has a badge: reproduction_right_108 and
// fair_use_107 share the same C-FU badge (both are Fair Use/library-
// exception categories under the same visual mark), matching the ACF
// field's own choice list.
function ccg_acf_license_url_map() {
	return array(
		'https://creativecommons.org/licenses/by-sa/4.0/' => array(
			'target' => 'contributor_license', 'value' => 'by-sa',
			'badge' => 'CC-BY-SA.svg.50x150.png',
			'badge_alt' => __( 'Creative Commons Attribution-ShareAlike 4.0 International', 'ccg-domain' ),
		),
		'https://creativecommons.org/licenses/by/4.0/'    => array(
			'target' => 'contributor_license', 'value' => 'by',
			'badge' => 'CC-BY.svg.50x150.png',
			'badge_alt' => __( 'Creative Commons Attribution 4.0 International', 'ccg-domain' ),
		),
		'https://creativecommons.org/publicdomain/zero/1.0/' => array(
			'target' => 'contributor_license', 'value' => 'zero',
			'badge' => 'CC-0-PD.svg.50x150.png',
			'badge_alt' => __( 'Creative Commons Zero — Public Domain Dedication', 'ccg-domain' ),
		),
		'https://www.law.cornell.edu/uscode/text/17/105'  => array(
			'target' => 'source_category', 'value' => 'public_domain_govt_work',
			'badge' => 'CC-0-PD.svg.50x150.png',
			'badge_alt' => __( 'Public Domain — U.S. Government Work (17 U.S. Code §105)', 'ccg-domain' ),
		),
		'https://web.archive.org/web/20110211214553/http://ipmall.info/hosted_resources/CopyrightCompendium/chapter_0200.asp' => array(
			'target' => 'source_category', 'value' => 'public_domain_foreign_govt_edict',
			'badge' => 'CC-0-PD.svg.50x150.png',
			'badge_alt' => __( 'Public Domain — Government Edict', 'ccg-domain' ),
		),
		'https://www.law.cornell.edu/uscode/text/17/108'  => array(
			'target' => 'source_category', 'value' => 'reproduction_right_108',
			'badge' => 'C-FU.svg.50x150.png',
			'badge_alt' => __( 'Reproduction Right (17 U.S. Code §108)', 'ccg-domain' ),
		),
		'https://www.law.cornell.edu/uscode/text/17/107'  => array(
			'target' => 'source_category', 'value' => 'fair_use_107',
			'badge' => 'C-FU.svg.50x150.png',
			'badge_alt' => __( 'Fair Use Right (17 U.S. Code §107)', 'ccg-domain' ),
		),
	);
}

/**
 * Look up the ACF "Open Content License" field's effective mapping for
 * one post, for use by ccg-frontend.php's banner rendering. Returns
 * the matched map entry (with 'url' and 'raw_html' added) or null if
 * the field is unset, unreadable, or doesn't match a known choice.
 *
 * 'raw_html' is the field's own stored value verbatim — already a
 * correctly formatted "<a href='...'>label</a> description" string —
 * used directly by ccg-frontend.php as the "terms of sharing" clause
 * for ANY of the 7 choices uniformly (open license or Fair Use/
 * Reproduction Right exception alike — the two are never given
 * different framing).
 */
function ccg_get_acf_derived_mapping( $post_id ) {

	$acf_raw = function_exists( 'get_field' )
		? get_field( 'open_content_license', $post_id )
		: get_post_meta( $post_id, 'open_content_license', true );

	$acf_url = ccg_extract_license_url_from_acf( $acf_raw );
	if ( ! $acf_url ) return null;

	$url_map = ccg_acf_license_url_map();
	if ( ! isset( $url_map[ $acf_url ] ) ) return null;

	return array_merge( $url_map[ $acf_url ], array( 'url' => $acf_url, 'raw_html' => $acf_raw ) );
}

/**
 * Extract the href from the ACF field's HTML-anchor value. Mirrors the
 * theme's existing extract_license_url() by design — same input shape,
 * kept as a small local copy rather than a cross-file dependency on
 * theme code from a plugin.
 */
function ccg_extract_license_url_from_acf( $html ) {
	if ( preg_match( '/<a\s+href=[\'"]([^\'"]+)[\'"]/', (string) $html, $matches ) ) {
		return $matches[1];
	}
	return false;
}