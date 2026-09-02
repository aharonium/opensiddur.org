<?php
/**
 * File: ccg-decision-engine.php
 * Version: 2.0.0
 *
 * Public Domain date-estimate engine for the two Fair Use/Reproduction
 * Right exception categories (fair_use_107, reproduction_right_108).
 *
 * This is a substantial rewrite from the original general-purpose
 * "Source PD Status" engine (which handled all four ACF source
 * categories via a src/trans-split CCG-meta model). Since govt-work
 * and foreign-govt-edict now get their status entirely from the
 * unified terms sentence's raw ACF text (see ccg-frontend.php), and
 * since CCG's own postmeta model is deprecated, this file's only
 * remaining job is the estimate math for the two exception categories
 * — using three ACF fields ("Copyright Determination" field group)
 * instead of the old CCG-meta/src-trans model:
 *
 *   publication_year     — a single operative year (replaces the old
 *                          max(date_src_end_year, date_trans_end_year)
 *                          computation — an editor now determines this
 *                          directly, case by case, rather than the
 *                          code inferring it from the general Date
 *                          Ranges fields)
 *   publication_country   — a single country (replaces the old split
 *                          country_src/country_trans CCG fields)
 *   publication_author     — a User field (ACF Free-compatible, wired to
 *                          Co-Authors Plus — see ccg-acf-editor.php)
 *                          identifying whose death_date (see
 *                          custom-contributor-fields.php) is operative
 *                          for the 70-years-after-death term
 *
 * Per your confirmation, copyright renewal status is no longer tracked
 * at all: this engine's scope is specifically the limited set of
 * post-1964 cases where the Fair Use/Reproduction Right exception is
 * used, for which renewal status isn't a relevant consideration — so
 * the old 1931–1963 non-renewal branch and its CCG-meta-sourced
 * renewal_status field are both gone, not just unpopulated.
 *
 * Deliberately a pure function of post ID -> structured result array,
 * with no HTML or wording in it — that's ccg-frontend.php's job.
 *
 * The "+1 for the Jan-1 boundary" convention used throughout (terms
 * don't expire on the exact anniversary; a work enters the Public
 * Domain on January 1 of the year after the term completes) applies
 * uniformly to both remaining term-length constants below.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

// Term lengths, in years, before the Jan-1-boundary +1 adjustment.
const CCG_PD_TERM_ROLLING         = 95;  // rolling "already PD" threshold
const CCG_PD_TERM_DEATH_BASED     = 70;  // confirmed: Jan-1-boundary rule applies
const CCG_PD_TERM_FOREIGN_DEFAULT = 50;  // confirmed: Jan-1-boundary rule applies
const CCG_NOTICE_WINDOW_END       = 1989; // exclusive (approximated as Jan 1, not Mar 1)

/**
 * Extract a year from a value that may be a plain year ("1919", "-760")
 * or a full date string ("1919-00-00", "-0760-00-00"), per the
 * project's date-field conventions (used by both the ACF Date Ranges
 * fields and the custom-contributor-fields death_date user meta).
 * Returns null if the value is empty, "NULL", or unparseable.
 */
function ccg_parse_year_from_value( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' || strtoupper( $value ) === 'NULL' ) return null;

	if ( preg_match( '/^(-?\d{1,4})-\d{2}-\d{2}$/', $value, $m ) ) {
		return (int) $m[1];
	}
	if ( preg_match( '/^-?\d{1,4}$/', $value ) ) {
		return (int) $value;
	}
	return null;
}

/**
 * The single operative publication year for this post, from the
 * publication_year ACF field (Number type). Returns null if unset —
 * an editor hasn't yet reviewed this post case by case.
 */
function ccg_get_publication_year( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) return null;
	$value = get_field( 'publication_year', $post_id );
	if ( $value === '' || $value === null || $value === false ) return null;
	return (int) $value;
}

/**
 * The publication_country ACF field's value (a machine-readable key
 * like 'usa', matching the field's Return Format: Value setting).
 * Returns '' if unset.
 */
function ccg_get_publication_country( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) return '';
	$value = get_field( 'publication_country', $post_id );
	return is_string( $value ) ? $value : '';
}

/**
 * The death year of whichever contributor the publication_author ACF
 * field (a User field, Return Format: User Object) identifies as
 * operative for this post — read from that user's death_date meta
 * (see custom-contributor-fields.php). Returns null if
 * publication_author is unset, or if that user has no death_date set.
 */
function ccg_get_operative_death_year( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) return null;
	$author = get_field( 'publication_author', $post_id );
	if ( ! ( $author instanceof WP_User ) ) return null;

	$death_date_raw = get_user_meta( $author->ID, 'death_date', true );
	return ccg_parse_year_from_value( $death_date_raw );
}

/**
 * Compute the Public Domain date estimate for a post categorized as
 * fair_use_107 or reproduction_right_108. Callers outside this file
 * should always pass one of those two — this function no longer
 * handles any other category (see file-level comment above for why).
 *
 * Returns an associative array:
 *   status             'pd' | 'unknown' — 'unknown' only when
 *                      publication_year itself is unset (nothing to
 *                      compute yet); every other case that reaches a
 *                      branch below is at least an estimate of 'pd'.
 *   basis              'rolling_95' | 'death_based' | 'foreign_default'
 *                      | 'no_date_data'
 *   confidence         'confident' (rolling_95) | 'estimate'
 *                      (death_based, foreign_default) | null
 *   computed_year      int|null — Jan 1 of this year is the estimated
 *                      PD date. Null for rolling_95 (categorical, no
 *                      specific date claimed) and no_date_data.
 *   is_ambiguous       bool — true whenever the result should be
 *                      rendered with non-committal "estimate" wording.
 *   ambiguity_reasons  string[] — machine-readable reason codes:
 *                      'country_unset_assumed_usa', 'death_year_unknown',
 *                      'pre_1989_status_unresolved',
 *                      'notice_registration_window_unresolved'.
 */
function ccg_compute_source_pd_status( $post_id, $category_override = null ) {

	$result = array(
		'status'            => 'unknown',
		'basis'             => 'unknown',
		'confidence'        => null,
		'computed_year'     => null,
		'is_ambiguous'      => false,
		'ambiguity_reasons' => array(),
	);

	$year = ccg_get_publication_year( $post_id );

	if ( $year === null ) {
		// No publication_year entered yet for this post — an editor
		// hasn't reviewed it case by case. Nothing to compute.
		$result['basis']             = 'no_date_data';
		$result['is_ambiguous']      = true;
		$result['ambiguity_reasons'] = array( 'no_effective_date' );
		return $result;
	}

	$country    = ccg_get_publication_country( $post_id );
	$death_year = ccg_get_operative_death_year( $post_id );

	$current_year       = (int) current_time( 'Y' );
	$rolling_cutoff_year = $current_year - CCG_PD_TERM_ROLLING; // e.g. 1931 in 2026

	// --- Branch 1: pre-rolling-cutoff. Always PD; country/death irrelevant. ---
	if ( $year < $rolling_cutoff_year ) {
		$result['status']     = 'pd';
		$result['basis']      = 'rolling_95';
		$result['confidence'] = 'confident';
		return $result; // No specific date claimed — categorical, not computed.
	}

	// --- Branch 2: 1989 onward — death-based term, only if death year known. ---
	if ( $year >= CCG_NOTICE_WINDOW_END && $death_year !== null ) {
		$result['status']        = 'pd';
		$result['basis']         = 'death_based';
		$result['confidence']    = 'estimate';
		$result['computed_year'] = $death_year + CCG_PD_TERM_DEATH_BASED + 1;
		return $result;
	}

	// --- Fallback estimate: country/50-year default. ---
	// Reached for the rolling-cutoff-through-1988 range entirely (no
	// renewal-status tracking anymore — see file-level comment), and
	// for 1989-onward when no death year is known. Defaults to USA
	// when country is unset, and is ALWAYS rendered non-committally
	// regardless of whether country is known.
	$ambiguity_reasons = array();

	if ( $country === '' ) {
		$country             = 'usa';
		$ambiguity_reasons[] = 'country_unset_assumed_usa';
	}

	$candidate = $year + CCG_PD_TERM_FOREIGN_DEFAULT + 1;

	if ( $death_year !== null ) {
		$candidate = min( $candidate, $death_year + CCG_PD_TERM_FOREIGN_DEFAULT + 1 );
	} else {
		$ambiguity_reasons[] = 'death_year_unknown';
	}

	if ( $year < CCG_NOTICE_WINDOW_END ) {
		$ambiguity_reasons[] = 'pre_1989_status_unresolved';
	} else {
		$ambiguity_reasons[] = 'notice_registration_window_unresolved';
	}

	$result['status']            = 'pd';
	$result['basis']             = 'foreign_default';
	$result['confidence']        = 'estimate';
	$result['computed_year']     = $candidate;
	$result['is_ambiguous']      = true;
	$result['ambiguity_reasons'] = $ambiguity_reasons;

	return $result;
}