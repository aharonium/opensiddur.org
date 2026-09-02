<?php
/*
Plugin Name: Custom License Index
Description: Generates a two-tier index of posts by license/copyright-exception category (CC BY-SA, CC BY, CC0, Public Domain, Fair Use, Reproduction Right), then by the site's four root categories. Shortcode: [custom_license_index]. Links out to the separate /license/ results page (theme template).
Version: 2.0.0
Author: Aharon Varady (for the Open Siddur Project)
*/

/**
 * File: custom-license-index.php
 * Version: 2.0.0
 *
 * Two-tier index, matching the languages-scripts-index / languages-scripts
 * two-page pattern:
 *   Tier 1 (tabs)  -- one of the 7 open_content_license choices
 *   Tier 2 (links) -- the site's 4 root categories, with a post count
 *                     for that license+root combination, linking out
 *                     to the separate /license/ results page
 *
 * The actual post-list results (Tier 3) are NOT rendered by this
 * plugin -- that lives in a theme page template (license.php, matching
 * languages-scripts.php's role), which calls lix_get_posts_for_slug_and_category()
 * below to get its post ID list, then runs its own WP_Query for full
 * pagination/excerpt support. This plugin only builds and exposes the
 * underlying index data plus the two-tier browsing UI.
 *
 * Data sources -- both regenerated daily via cron, read from cached
 * copies rather than live per-post queries (same reasoning as
 * ccg-json-data.php: the expensive work should happen once a day, not
 * once per page view):
 *
 *   posts.json       -- gives each post's open_content_license raw value
 *                        and its assigned categories (leaf-level, "Name
 *                        (term_id)" strings)
 *   categories.json   -- gives every category's hierarchical_id, whose
 *                        first dot-segment (1/2/3/4) directly identifies
 *                        which of the 4 root categories it descends
 *                        from, at any depth -- no parent-chain walking
 *                        needed
 *
 * License classification reuses ccg_acf_license_url_map() and
 * ccg_extract_license_url_from_acf() from CCG's ccg-acf-mapping.php,
 * rather than re-deriving the same URL -> category logic a second
 * time (drift risk if the choices list ever changes and only one
 * copy gets updated). This is a soft dependency: if CCG isn't active,
 * the shortcode renders a clear notice instead of guessing.
 *
 * Public URL slugs (e.g. "cc-by-sa") are deliberately distinct from
 * CCG's internal 'value' keys (e.g. "by-sa") -- see
 * lix_get_license_tabs() below, which is the single source of truth
 * mapping between the two.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

// The site's four root categories -- confirmed stable IDs from
// categories.json. Order here is display order for Tier 2 links.
function lix_get_root_categories() {
	return array(
		1132 => '🖖︎ Prayers & Praxes',
		230  => '👂︎ Liturgical Readings, Sources, and Cantillation',
		2099 => '📚︎ Compiled Prayer Books (Siddurim, Haggadot, &c.)',
		91   => '⋯ Miscellanea (Ketubot, Art, Essays on Prayer, &c.)',
	);
}

// Single source of truth for the 7 open_content_license choices:
// CCG's internal 'value' (array key, matches ccg_acf_license_url_map())
// => ['slug' => public URL-facing slug, 'label' => tab display text].
// Order here is display order for Tier 1 tabs.
function lix_get_license_tabs() {
	return array(
		'by-sa'                             => array( 'slug' => 'cc-by-sa',              'label' => 'CC BY-SA' ),
		'by'                                => array( 'slug' => 'cc-by',                 'label' => 'CC BY' ),
		'zero'                              => array( 'slug' => 'cc0',                   'label' => 'CC0 (PD)' ),
		'public_domain_govt_work'           => array( 'slug' => 'pd-govt-work',          'label' => 'PD (Government Work)' ),
		'public_domain_foreign_govt_edict'  => array( 'slug' => 'pd-foreign-edict',      'label' => 'PD (Foreign Edict)' ),
		'reproduction_right_108'            => array( 'slug' => 'fu-reproduction-right', 'label' => 'Reproduction Right' ),
		'fair_use_107'                      => array( 'slug' => 'fu',                    'label' => 'Fair Use Right' ),
	);
}

/**
 * Reverse lookup: public slug (as it'll appear in ?license=...) back
 * to CCG's internal 'value' key. Returns null if the slug isn't
 * recognized.
 */
function lix_internal_value_from_slug( $slug ) {
	foreach ( lix_get_license_tabs() as $internal_value => $def ) {
		if ( $def['slug'] === $slug ) return $internal_value;
	}
	return null;
}

/**
 * The badge image filename for a given internal license value,
 * derived from CCG's own ccg_acf_license_url_map() rather than a
 * second hardcoded copy (avoids the two ever drifting apart). Returns
 * null if CCG isn't active or the value has no badge.
 */
function lix_get_badge_filename_for_value( $internal_value ) {
	if ( ! function_exists( 'ccg_acf_license_url_map' ) ) return null;
	foreach ( ccg_acf_license_url_map() as $entry ) {
		if ( ( $entry['value'] ?? null ) === $internal_value ) {
			return $entry['badge'] ?? null;
		}
	}
	return null;
}

/**
 * The badge image URL for a given internal license value — or null if
 * there's no badge for it, OR (per your request to prepare for
 * missing images) if the file doesn't actually exist on disk. This is
 * a real filesystem check, not just trusting the map's filename, so a
 * missing/renamed image file quietly omits the <img> tag rather than
 * rendering a broken-image icon.
 */
function lix_get_badge_image_url( $internal_value ) {

	$filename = lix_get_badge_filename_for_value( $internal_value );
	if ( ! $filename ) return null;

	$path = WP_PLUGIN_DIR . '/creative-commons-generator/images/' . $filename;
	if ( ! file_exists( $path ) ) return null;

	return plugins_url( 'creative-commons-generator/images/' . $filename );
}

/**
 * Root-category thumbnail image URLs, sourced from categories.json's
 * own featured_image.url field for each of the 4 root category
 * entries — per your instruction, the images live in that daily-
 * generated file rather than being hardcoded here. A root category
 * with no featured image set simply has no entry in the returned
 * array (categories.json's own data already indicates "missing" —
 * no need for an additional existence check on a remote URL).
 */
function lix_get_root_category_images() {

	$categories = lix_load_categories_json();
	$root_ids   = array_keys( lix_get_root_categories() );
	$images     = array();

	foreach ( $categories as $cat ) {
		if ( empty( $cat['id'] ) || ! in_array( (int) $cat['id'], $root_ids, true ) ) continue;
		if ( ! empty( $cat['featured_image']['url'] ) ) {
			$images[ (int) $cat['id'] ] = $cat['featured_image']['url'];
		}
	}

	return $images;
}

add_shortcode( 'custom_license_index', 'lix_render_shortcode' );

/**
 * Cached loader for one of the two daily-generated JSON files. Mirrors
 * ccg-json-data.php's pattern: parse the file at most once per actual
 * change (detected via filemtime), cache the decoded array via the
 * Transients API.
 */
function lix_load_json_cached( $filename, $cache_key ) {

	$path = trailingslashit( wp_upload_dir()['basedir'] ) . $filename;
	if ( ! file_exists( $path ) ) return array();

	$file_mtime = filemtime( $path );
	if ( $file_mtime === false ) return array();

	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) && isset( $cached['mtime'] ) && $cached['mtime'] === $file_mtime ) {
		return $cached['data'];
	}

	$raw  = file_get_contents( $path );
	$data = ( $raw !== false ) ? json_decode( $raw, true ) : null;
	if ( ! is_array( $data ) ) $data = array();

	set_transient( $cache_key, array( 'mtime' => $file_mtime, 'data' => $data ), DAY_IN_SECONDS );

	return $data;
}

function lix_load_posts_json() {
	return lix_load_json_cached( 'posts.json', 'lix_posts_json_cache' );
}

function lix_load_categories_json() {
	return lix_load_json_cached( 'categories.json', 'lix_categories_json_cache' );
}

/**
 * Extract the trailing numeric ID from a "Name (id)" string -- the
 * convention posts.json uses for authors, categories, and tags alike.
 * Returns null if the string doesn't end in "(digits)".
 */
function lix_extract_trailing_id( $entry ) {
	if ( preg_match( '/\((\d+)\)\s*$/', trim( (string) $entry ), $m ) ) {
		return (int) $m[1];
	}
	return null;
}

/**
 * Build a category_id => root_category_id lookup from categories.json,
 * using each entry's hierarchical_id -- its first dot-segment (1/2/3/4)
 * directly identifies the root, at any depth, with no tree-walking.
 */
function lix_build_category_root_lookup() {

	$categories   = lix_load_categories_json();
	$root_by_hpos = array( '1' => 1132, '2' => 230, '3' => 2099, '4' => 91 );
	$lookup       = array();

	foreach ( $categories as $cat ) {
		if ( empty( $cat['id'] ) || empty( $cat['hierarchical_id'] ) ) continue;
		$first_segment = strtok( (string) $cat['hierarchical_id'], '.' );
		if ( isset( $root_by_hpos[ $first_segment ] ) ) {
			$lookup[ (int) $cat['id'] ] = $root_by_hpos[ $first_segment ];
		}
	}

	return $lookup;
}

/**
 * Classify a post's raw open_content_license string (from posts.json)
 * into one of the 7 license 'value' keys, using CCG's own URL map --
 * not a re-derived copy. Returns null if CCG isn't active, or if the
 * value is empty/unrecognized.
 */
function lix_classify_license( $raw_license_html ) {

	if ( ! function_exists( 'ccg_extract_license_url_from_acf' ) || ! function_exists( 'ccg_acf_license_url_map' ) ) {
		return null;
	}
	if ( empty( $raw_license_html ) ) return null;

	$url = ccg_extract_license_url_from_acf( $raw_license_html );
	if ( ! $url ) return null;

	$map = ccg_acf_license_url_map();
	return isset( $map[ $url ] ) ? $map[ $url ]['value'] : null;
}

/**
 * Build the full drill-down index: license_value => root_category_id
 * => array of post records (id, title, permalink, post_date). Cached
 * via transient, keyed off both source files' combined mtime so it
 * rebuilds whenever either one actually changes.
 */
function lix_build_index() {

	$posts_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'posts.json';
	$cats_path  = trailingslashit( wp_upload_dir()['basedir'] ) . 'categories.json';
	$posts_mtime = file_exists( $posts_path ) ? filemtime( $posts_path ) : 0;
	$cats_mtime  = file_exists( $cats_path ) ? filemtime( $cats_path ) : 0;
	$combined_mtime = $posts_mtime . ':' . $cats_mtime;

	$cached = get_transient( 'lix_full_index_cache' );
	if ( is_array( $cached ) && ( $cached['mtime'] ?? null ) === $combined_mtime ) {
		return $cached['index'];
	}

	$posts        = lix_load_posts_json();
	$category_map = lix_build_category_root_lookup();
	$root_ids     = array_keys( lix_get_root_categories() );

	$index = array();
	foreach ( array_keys( lix_get_license_tabs() ) as $license_value ) {
		$index[ $license_value ] = array_fill_keys( $root_ids, array() );
	}

	foreach ( $posts as $post_id => $post_data ) {

		$license_value = lix_classify_license( $post_data['open_content_license'] ?? '' );
		if ( $license_value === null || ! isset( $index[ $license_value ] ) ) continue;

		$post_roots = array();
		foreach ( (array) ( $post_data['categories'] ?? array() ) as $cat_entry ) {
			$cat_id = lix_extract_trailing_id( $cat_entry );
			if ( $cat_id !== null && isset( $category_map[ $cat_id ] ) ) {
				$post_roots[ $category_map[ $cat_id ] ] = true; // dedupe multi-branch same-root
			}
		}
		if ( empty( $post_roots ) ) continue;

		$record = array(
			'id'        => (int) $post_id,
			'title'     => $post_data['post_title'] ?? '',
			'permalink' => $post_data['permalink'] ?? '',
			'post_date' => $post_data['post_date'] ?? '',
		);

		foreach ( array_keys( $post_roots ) as $root_id ) {
			$index[ $license_value ][ $root_id ][] = $record;
		}
	}

	set_transient( 'lix_full_index_cache', array( 'mtime' => $combined_mtime, 'index' => $index ), DAY_IN_SECONDS );

	return $index;
}

/**
 * Public accessor for the /license/ results page (theme template) --
 * the one thing this plugin exposes for use outside itself. Takes the
 * PUBLIC slug (as read from $_GET['license'] on that page) and a root
 * category ID, and returns the array of post records
 * (id, title, permalink, post_date) for that combination. The
 * template only needs 'id' from each (to build a WP_Query post__in
 * list) -- title/permalink/post_date are included for convenience or
 * a lighter-weight fallback display.
 */
function lix_get_posts_for_slug_and_category( $license_slug, $root_id ) {

	$internal_value = lix_internal_value_from_slug( $license_slug );
	if ( $internal_value === null ) return array();

	$index = lix_build_index();
	return $index[ $internal_value ][ (int) $root_id ] ?? array();
}

/**
 * Shortcode entry point -- Tier 1 (tabs) + Tier 2 (root-category links
 * with counts) only. Tier 2 links point to the separate /license/
 * results page; this shortcode never renders results itself.
 */
function lix_render_shortcode() {

	if ( ! function_exists( 'ccg_acf_license_url_map' ) ) {
		return '<p><em>' . esc_html__( 'The License Index requires the CCG plugin to be active.', 'ccg-domain' ) . '</em></p>';
	}

	$index = lix_build_index();
	$tabs  = lix_get_license_tabs();
	$roots = lix_get_root_categories();
	$root_images = lix_get_root_category_images();

	// If arriving with ?license=<slug> (e.g. the "Back to index" link
	// from the results page — see license.php), open on that tab
	// instead of always defaulting to the first one. Cheap: just a
	// GET-param read and a lookup against the same small tabs array
	// already being iterated below — no extra queries, no effect on
	// the cached index build.
	$requested_slug = isset( $_GET['license'] ) ? sanitize_text_field( wp_unslash( $_GET['license'] ) ) : '';
	$requested_value = $requested_slug ? lix_internal_value_from_slug( $requested_slug ) : null;
	$active_tab = ( $requested_value && isset( $tabs[ $requested_value ] ) ) ? $requested_value : array_key_first( $tabs );

	ob_start();
	echo '<div class="lix-index">';

	echo '<div class="tabs">';
	foreach ( $tabs as $license_value => $def ) {
		$badge_url = lix_get_badge_image_url( $license_value );
		$badge_html = $badge_url
			? '<img src="' . esc_url( $badge_url ) . '" alt="" class="lix-tab-badge" />'
			: '';
		printf(
			'<button class="tab-button%1$s" onclick="lixShowTab(\'%2$s\')">%3$s%4$s</button>',
			( $license_value === $active_tab ) ? ' active' : '',
			esc_js( $license_value ),
			$badge_html,
			esc_html( $def['label'] )
		);
	}
	echo '</div>';

	foreach ( $tabs as $license_value => $def ) {
		lix_render_category_links_panel( $index, $license_value, $def['slug'], $roots, $root_images, $license_value === $active_tab );
	}

	echo '</div>';
	return ob_get_clean();
}

/**
 * Tier 2: the 4 root-category links (with counts and, when available,
 * a thumbnail from categories.json's featured_image) for one license
 * tab. Links point to the /license/ results page with the license's
 * PUBLIC slug and the root category's numeric ID.
 */
function lix_render_category_links_panel( $index, $license_value, $license_slug, $roots, $root_images, $is_active ) {

	printf(
		'<div id="lix-%1$s" class="tab-content%2$s">',
		esc_attr( $license_value ),
		$is_active ? ' active' : ''
	);

	foreach ( $roots as $root_id => $root_name ) {
		$count = count( $index[ $license_value ][ $root_id ] ?? array() );
		$link  = esc_url( add_query_arg( array(
			'license'  => $license_slug,
			'category' => $root_id,
		), home_url( '/license/' ) ) );

		$thumb_html = '';
		if ( ! empty( $root_images[ $root_id ] ) ) {
			$thumb_html = '<img src="' . esc_url( $root_images[ $root_id ] ) . '" alt="" class="lix-category-thumb" />';
		}

		printf(
			'<div class="lix-category-listing">%1$s<div class="lix-category-text"><a href="%2$s"><b>%3$s</b></a><div>(%4$s: %5$d)</div></div></div>',
			$thumb_html,
			$link,
			html_entity_decode( wp_strip_all_tags( $root_name ) ),
			esc_html__( 'Resources available', 'ccg-domain' ),
			$count
		);
	}

	echo '</div>';
}

/**
 * Styles/script for the tab UI -- same visual/JS pattern as
 * languages-scripts-index.php's tab mechanic.
 */
function lix_enqueue_assets() {
	echo '<style>
	.lix-index .tabs { display: flex; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid #ccc; }
	.lix-index .tab-button {
		padding: 10px 20px;
		cursor: pointer;
		background: #F4E1C6;
		border: 2px solid #ccc;
		border-bottom: none;
		border-radius: 8px 8px 0 0;
		font-weight: bold;
	}
	.lix-index .tab-button.active { background: #e0c095; border-bottom: 2px solid #e0c095; }
	.lix-index .tab-content { display: none; padding: 10px; }
	.lix-index .tab-content.active { display: block; }
	.lix-tab-badge { height: 20px; width: auto; vertical-align: middle; margin-right: 6px; }
	.lix-category-listing { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #ccc; }
	.lix-category-thumb { height: 60px; width: 60px; object-fit: cover; border-radius: 6px; margin-right: 12px; flex-shrink: 0; }
	</style>';
	echo '<script>
	function lixShowTab(tab) {
		document.querySelectorAll(".lix-index .tab-content").forEach(el => el.classList.remove("active"));
		document.querySelectorAll(".lix-index .tab-button").forEach(el => el.classList.remove("active"));
		document.getElementById("lix-" + tab).classList.add("active");
		event.currentTarget.classList.add("active");
	}
	</script>';
}
add_action( 'wp_head', 'lix_enqueue_assets' );