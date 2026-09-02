<?php
/**
 * File: ccg-fields.php
 * Version: 1.1.0
 *
 * Shared field definitions and sanitizers for the Open Content License &
 * Public Domain Status plugin.
 *
 * These constants are read by both the admin meta box (ccg-post-options.php)
 * and the decision-logic engine (Stage 2) so the two never drift out of
 * sync on allowed values. See ccg-redesign-spec.md for the full design
 * rationale behind each field.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

/**
 * Source PD Status category. Single-valued per post (not split src/trans) —
 * it describes the overall basis for the post's PD/copyright claim, even
 * though the date math behind it may draw from either the source or
 * translation layer. See spec §2a / §3.
 */
function ccg_source_category_options() {
	return array(
		'unknown'                         => __( 'Unknown / not yet researched', 'ccg-domain' ),
		'public_domain_by_date'           => __( 'Public Domain (by date calculation)', 'ccg-domain' ),
		'public_domain_govt_work'         => __( 'Public Domain — U.S. Government work (17 U.S.C. §105)', 'ccg-domain' ),
		'public_domain_foreign_govt_edict'=> __( 'Public Domain — Foreign government edict', 'ccg-domain' ),
		'reproduction_right_108'          => __( 'Under copyright — Reproduction Right (17 U.S.C. §108)', 'ccg-domain' ),
		'fair_use_107'                    => __( 'Under copyright — Fair Use (17 U.S.C. §107)', 'ccg-domain' ),
	);
}

/**
 * Renewal status, evaluated only for the 1931–1964 window. Split src/trans:
 * the source work and its translation are frequently distinct copyrightable
 * works with independent renewal histories.
 */
function ccg_renewal_status_options() {
	return array(
		'unknown'     => __( 'Unknown / not researched', 'ccg-domain' ),
		'renewed'     => __( 'Renewed', 'ccg-domain' ),
		'not_renewed' => __( 'Not renewed', 'ccg-domain' ),
	);
}

/**
 * Notice/registration compliance, evaluated only for the 1964–1989 window.
 * No automatic PD date is computed from this in Stage 2 — see spec §4 —
 * but the flag is still collected so it's available for the audit table
 * and any future manual determination.
 */
function ccg_notice_registration_options() {
	return array(
		'unknown'      => __( 'Unknown / not researched', 'ccg-domain' ),
		'compliant'    => __( 'Compliant (notice and/or registration present)', 'ccg-domain' ),
		'noncompliant' => __( 'Non-compliant (no notice / not registered)', 'ccg-domain' ),
	);
}

/**
 * Country of publication. Deliberately a constrained list, not free text —
 * see spec §2a for why: a real select is safe to compute a 50-year default
 * PD term against; free text would require a full country/date-rule
 * database, which is out of scope. Limited to the countries that actually
 * come up in this project's corpus.
 */
function ccg_country_options() {
	return array(
		''                    => __( '— Unset —', 'ccg-domain' ),
		'usa'                 => __( 'United States', 'ccg-domain' ),
		'israel_post1948'     => __( 'State of Israel (post-1948)', 'ccg-domain' ),
		'palestine_mandate'   => __( 'British Mandate Palestine (1918–1948)', 'ccg-domain' ),
		'uk'                  => __( 'United Kingdom', 'ccg-domain' ),
		'argentina'           => __( 'Argentina', 'ccg-domain' ),
		'brazil'              => __( 'Brazil', 'ccg-domain' ),
		'mexico'              => __( 'Mexico', 'ccg-domain' ),
		'italy'               => __( 'Italy', 'ccg-domain' ),
		'france'              => __( 'France', 'ccg-domain' ),
		'netherlands'         => __( 'Netherlands', 'ccg-domain' ),
		'germany'             => __( 'Germany', 'ccg-domain' ),
		'poland'              => __( 'Poland', 'ccg-domain' ),
		'ukraine'             => __( 'Ukraine', 'ccg-domain' ),
		'ussr'                => __( 'USSR (historical)', 'ccg-domain' ),
		'romania'             => __( 'Romania', 'ccg-domain' ),
		'hungary'             => __( 'Hungary', 'ccg-domain' ),
		'other_prewar_europe' => __( 'Other pre-WWII European state (no longer exists)', 'ccg-domain' ),
	);
}

/**
 * Contributor License options. Deliberately excludes Fair Use — that stays
 * a Source PD Status category (it reflects the law's exception, not an
 * author/translator's expressed sharing intent). See spec §2a/§2b.
 *
 * Leads with an explicit '' ("not yet set") choice — this is the real
 * default (see ccg_get_contributor_license() below), so that a post
 * nobody has deliberately curated shows nothing on the frontend rather
 * than silently claiming CC BY-SA on their behalf.
 *
 * Per your clarification: this field is about the post's actual
 * author(s)/translator(s) — as distinct from a volunteer credited for a
 * transcription or digital-imaging role. On this project, a credited
 * name with a role in parentheses after it (e.g. "Jane Doe
 * (transcription)") identifies the volunteer, not the author; the
 * actual author/translator is the credited name with no such suffix.
 * The original spec's role-filter language (§2b) didn't fully capture
 * this — a post's primary/logged-in contributor is very often the
 * transcription/imaging volunteer, not the author, so neither the
 * license nor the name/URL should be defaulted from whoever's currently
 * editing the post (see ccg_get_contributor_license() below).
 */
function ccg_contributor_license_options() {
	return array(
		''      => __( '— Not yet set —', 'ccg-domain' ),
		'by-sa' => __( 'CC BY-SA — Attribution-ShareAlike 4.0 International', 'ccg-domain' ),
		'by'    => __( 'CC BY — Attribution 4.0 International', 'ccg-domain' ),
		'zero'  => __( 'CC0 — Public Domain Dedication', 'ccg-domain' ),
	);
}

/**
 * Site-wide display settings (ccg_settings option).
 *
 * show_title — whether the banner leads with the post's title.
 *
 * show_pd_estimate_sentence — whether the "The source content
 * displayed is estimated to have entered the Public Domain..." sentence
 * (the Fair Use/Reproduction Right date-estimate note) renders at all.
 * Separate from show_estimated_pd_date below, which only controls
 * whether that sentence's two computed years are included — this
 * controls the whole sentence's presence.
 *
 * show_estimated_pd_date — whether the low-confidence "estimated PD
 * date" wording (death-based / foreign-default basis) includes its two
 * computed years at all. Defaults off since those specific dates
 * aren't reliable pre-migration; the sentence itself (gated by
 * show_pd_estimate_sentence above) still shows without them.
 *
 * show_contributor_pd_note — whether the third banner sentence (the
 * contributor work's own 95-year-from-publish PD-by-age note) renders
 * at all. Defaults on, preserving the spec's original "always shown"
 * behavior.
 *
 * There is deliberately no per-contributor naming toggle — per your
 * decision to make this a project-level declaration rather than a
 * contributor-specific one, the banner's lead-in is always either the
 * post's title or the generic "This work is shared through the Open
 * Siddur Project", never a named individual.
 *
 * The legacy ACF open_content_license field is the unconditional
 * source for license/category display — see ccg_get_acf_derived_mapping()
 * in ccg-migration.php — so there's no toggle for that; CCG's own
 * stored fields are deprecated and are not read by the frontend at
 * all. Everything else the old settings page had (source_url,
 * more_url, active flag, DCMI format type) is confirmed deprecated
 * and not carried forward.
 */
function ccg_get_settings() {

	$defaults = array(
		'show_title'                => true,
		'show_pd_estimate_sentence' => true,
		'show_estimated_pd_date'    => false,
		'show_contributor_pd_note'  => true,
	);

	$stored = get_option( 'ccg_settings' );
	if ( ! is_array( $stored ) ) $stored = array();

	return array_merge( $defaults, $stored );
}

/**
 * Read the current Source PD Status values for a post, with safe defaults
 * for posts that don't have this meta yet. Shared by the admin UI
 * (ccg-post-options.php) and the decision-logic engine
 * (ccg-decision-engine.php) so both read the exact same shape of data.
 */
function ccg_get_source_status( $post_id ) {

	$defaults = array(
		'category'             => 'unknown',
		'renewal_status_src'   => 'unknown',
		'renewal_status_trans' => 'unknown',
		'notice_status_src'    => 'unknown',
		'notice_status_trans'  => 'unknown',
		'death_year_src'       => '',
		'death_year_trans'     => '',
		'country_src'          => '',
		'country_trans'        => '',
	);

	$stored = get_post_meta( $post_id, '_ccg_source_status', true );
	if ( ! is_array( $stored ) ) $stored = array();

	return array_merge( $defaults, $stored );
}

/**
 * Whether a post has a genuinely-chosen Contributor License license
 * value stored — as opposed to either no meta at all, or a stored
 * 'license' => '' from the (now-fixed) default. ccg_get_contributor_license()
 * no longer fabricates a 'by-sa' default, but this helper still matters
 * for two reasons: (1) existing posts saved before that fix may have an
 * explicitly-stored 'by-sa' nobody actually chose (see the ACF-fallback
 * suppression logic in ccg-frontend.php, which distrusts stored data
 * for exactly this reason), and (2) it's a clearer, more explicit check
 * at call sites than comparing a defaulted return value against ''.
 */
function ccg_has_stored_contributor_license( $post_id ) {
	$raw = get_post_meta( $post_id, '_ccg_contributor', true );
	return is_array( $raw ) && isset( $raw['license'] ) && $raw['license'] !== '';
}

/**
 * Read the current Contributor License values for a post, with safe
 * defaults for posts that don't have this meta yet.
 *
 * Neither the license nor the name/URL are defaulted from anything —
 * previously this filled in 'by-sa' and the current logged-in user's
 * name/URL, on the assumption that whoever's editing the post is its
 * author. Per your clarification, that's usually wrong on this project:
 * the person editing (and thus logged in when a post is first saved) is
 * very often the transcription/digital-imaging volunteer, not the
 * post's actual author/translator — who's identifiable by NOT having a
 * role in parentheses after their credited name, unlike the volunteer.
 * Auto-filling from the current user silently mislabeled the volunteer
 * as the rights-granting contributor. An empty default here means the
 * admin edit form now shows genuinely blank fields for an uncurated
 * post, and the frontend (via ccg_has_stored_contributor_license())
 * correctly shows nothing until someone deliberately enters the real
 * author/translator's name and license.
 */
function ccg_get_contributor_license( $post_id ) {

	$defaults = array(
		'license'            => '',
		'name'               => '',
		'url'                => '',
		'parent_resource_id' => '',
	);

	$stored = get_post_meta( $post_id, '_ccg_contributor', true );
	if ( is_array( $stored ) ) {
		return array_merge( $defaults, $stored );
	}

	return $defaults;
}

/**
 * Sanitize a value against a whitelist of allowed option keys (as produced
 * by the *_options() functions above). Returns $default if $value isn't
 * one of the allowed keys — never trusts raw POST data into stored meta
 * or, later, into rendered HTML attributes.
 */
function ccg_sanitize_choice( $value, array $allowed_options, $default = '' ) {
	$allowed_keys = array_keys( $allowed_options );
	return in_array( $value, $allowed_keys, true ) ? $value : $default;
}

/**
 * Sanitize a year value. Allows a leading '-' for BCE years, consistent
 * with the project's existing date_src_start_year / date_src_end_year
 * ACF convention (e.g. "-760"). Returns '' (not stored / treated as
 * unknown) if the value doesn't parse as an integer year.
 */
function ccg_sanitize_year( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' ) return '';
	return preg_match( '/^-?\d{1,4}$/', $value ) ? (string) (int) $value : '';
}

/**
 * Sanitize a parent_resource_id value: must be a positive integer
 * referring to an existing, published post. Returns '' otherwise —
 * silently dropping a bad/stale reference is safer than storing a
 * broken link, and the admin UI re-shows blank rather than an error
 * that could be missed.
 */
function ccg_sanitize_parent_resource_id( $value ) {
	$id = absint( $value );
	if ( $id <= 0 ) return '';
	if ( get_post_status( $id ) !== 'publish' ) return '';
	return (string) $id;
}