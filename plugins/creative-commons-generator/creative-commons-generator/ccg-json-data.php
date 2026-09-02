<?php
/**
 * File: ccg-json-data.php
 * Version: 1.0.0
 *
 * Loader/cache for posts.json's `authors` data.
 *
 * posts.json (and its siblings tags.json, categories.json,
 * contributors_by_id.json) live in wp-content/uploads/, regenerated
 * daily via system cron from the theme's functions.php. Only the
 * `authors` array is used here — posts.json's `open_content_license`
 * and `date_src_*`/`date_trans_*` fields are just daily-stale exports
 * of the same ACF fields this plugin already reads live (confirmed by
 * matching HTML/field-name shape), so there's no reason to source those
 * from JSON instead of ACF. `authors` is the one genuinely new piece:
 * a structured, per-post list of every credited contributor with their
 * role, which neither ACF nor CCG's own (now-deprecated) postmeta has
 * an equivalent for.
 *
 * Since the file only changes once a day, the expensive part (reading
 * + json_decode-ing the whole file) only needs to happen once per
 * actual change, not once per page view. A post_id => authors[] index
 * is cached via the Transients API (so it rides on the site's object
 * cache when one's configured) and invalidated by comparing the
 * cached copy's filemtime to the live file's — no fixed TTL guessing,
 * a stale cache is only possible for the brief window between cron
 * writing a new file and the next request rebuilding the index.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

define( 'CCG_POSTS_JSON_PATH', WP_CONTENT_DIR . '/uploads/posts.json' );
define( 'CCG_POSTS_JSON_CACHE_KEY', 'ccg_posts_json_authors_index' );

/**
 * Return the post_id => authors[] index, rebuilding it from posts.json
 * if there's no cached copy or the cached copy predates the file's
 * current mtime. Each authors[] entry is:
 *   'name' — display name, with any role/ID parentheticals stripped
 *   'id'   — the trailing (ID) from the raw entry, as an int
 *   'role' — the role parenthetical immediately before the ID (e.g.
 *            'translation'), or null for an unsuffixed (author) entry
 * Returns an empty array if the file is missing, unreadable, or not
 * valid JSON — fails closed, never fatals a page render.
 */
function ccg_get_posts_json_authors_index() {

	if ( ! file_exists( CCG_POSTS_JSON_PATH ) ) return array();

	$file_mtime = filemtime( CCG_POSTS_JSON_PATH );
	if ( $file_mtime === false ) return array();

	$cached = get_transient( CCG_POSTS_JSON_CACHE_KEY );
	if ( is_array( $cached ) && isset( $cached['mtime'] ) && $cached['mtime'] === $file_mtime ) {
		return $cached['index'];
	}

	$index = ccg_build_posts_json_authors_index();

	// DAY_IN_SECONDS is belt-and-suspenders only — the mtime check above
	// is the real invalidation mechanism; this just bounds how long a
	// cache entry can survive if the file were ever deleted outright.
	set_transient( CCG_POSTS_JSON_CACHE_KEY, array(
		'mtime' => $file_mtime,
		'index' => $index,
	), DAY_IN_SECONDS );

	return $index;
}

/**
 * Parse posts.json fresh and build the post_id => authors[] index.
 * Every field in the file other than `authors` is deliberately ignored
 * — see the file-level comment above.
 */
function ccg_build_posts_json_authors_index() {

	$raw = file_get_contents( CCG_POSTS_JSON_PATH );
	if ( $raw === false ) return array();

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) return array();

	$index = array();

	foreach ( $data as $post_id => $post_data ) {
		if ( empty( $post_data['authors'] ) || ! is_array( $post_data['authors'] ) ) continue;

		$parsed = array();
		foreach ( $post_data['authors'] as $entry ) {
			$parsed_entry = ccg_parse_json_author_entry( $entry );
			if ( $parsed_entry !== null ) $parsed[] = $parsed_entry;
		}

		if ( ! empty( $parsed ) ) {
			$index[ (int) $post_id ] = $parsed;
		}
	}

	return $index;
}

/**
 * Parse one raw authors[] string into name/id/role, e.g.:
 *   "Wolf Heidenheim (2410)"
 *     -> name "Wolf Heidenheim", id 2410, role null
 *   "Jenny Marmorstein (translation) (2409)"
 *     -> name "Jenny Marmorstein", id 2409, role "translation"
 *   "Aharon N. Varady (digital imaging and document preparation) (2241)"
 *     -> name "Aharon N. Varady", id 2241,
 *        role "digital imaging and document preparation"
 * The trailing "(digits)" is always the user ID, per the project's
 * export convention; an optional role parenthetical immediately
 * precedes it. Returns null if the string doesn't match this shape at
 * all — fails closed rather than guessing at a malformed entry.
 */
function ccg_parse_json_author_entry( $entry ) {

	$entry = trim( (string) $entry );

	if ( ! preg_match( '/^(.*)\s*\((\d+)\)\s*$/', $entry, $m ) ) return null;

	$remainder = trim( $m[1] );
	$id        = (int) $m[2];

	if ( preg_match( '/^(.*)\s*\(([^()]+)\)\s*$/', $remainder, $rm ) ) {
		return array(
			'name' => trim( $rm[1] ),
			'id'   => $id,
			'role' => trim( $rm[2] ),
		);
	}

	return array(
		'name' => $remainder,
		'id'   => $id,
		'role' => null,
	);
}

/**
 * The authors[] list for one post, or an empty array if posts.json has
 * no entry (or no authors data) for it.
 */
function ccg_get_json_authors_for_post( $post_id ) {
	$index = ccg_get_posts_json_authors_index();
	return $index[ (int) $post_id ] ?? array();
}