<?php
/**
 * File: ccg-acf-editor.php
 * Version: 1.0.0
 *
 * Editor-side helpers for the "Copyright Determination" ACF field
 * group (publication_year, publication_country, publication_author).
 *
 * ACF's User field type has no built-in awareness of Co-Authors Plus
 * (a separate plugin, with its own author-assignment system) — by
 * default a User field would let an editor pick literally any site
 * user, and would show up blank on every post regardless of who's
 * already credited via Co-Authors Plus. The two filters below connect
 * the two systems for the publication_author field specifically:
 *
 *   - Restricts the field's selectable users to only this post's
 *     actual Co-Authors Plus authors, per the requirement that there
 *     should never be a publication_author who isn't already a known
 *     co-author of the post.
 *   - Pre-fills the field (display only — not a database write) with
 *     the post's first Co-Authors Plus author when nothing has been
 *     explicitly saved yet, so the field isn't blank on first view of
 *     a post that already has known co-authors.
 *
 * Both are scoped to this one field via ACF's key= filter-name
 * convention, so they can't accidentally affect any other ACF field
 * on the site.
 *
 * Co-Authors Plus "guest authors" (pseudo-authors with no real login,
 * a feature of that plugin) aren't real WP_User accounts and can't be
 * represented by an ACF User field at all. This project's contributor/
 * author roles are built from real registered users (per the
 * contributors_by_id.json generator's own get_users() call), so this
 * shouldn't come up in practice — but if a post's only co-author turns
 * out to be a guest author, both functions below simply find no
 * eligible real-user co-author and behave as if the post had none.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

define( 'CCG_PUBLICATION_AUTHOR_FIELD_KEY', 'field_6a8265f5439c6' );

add_filter( 'acf/fields/user/query/key=' . CCG_PUBLICATION_AUTHOR_FIELD_KEY, 'ccg_restrict_publication_author_choices', 10, 3 );
add_filter( 'acf/load_value/key=' . CCG_PUBLICATION_AUTHOR_FIELD_KEY, 'ccg_default_publication_author', 10, 3 );

/**
 * Get this post's Co-Authors Plus authors as real WP_User objects
 * only — filtering out any guest authors, which can't be represented
 * by an ACF User field. Shared by both filters below.
 */
function ccg_get_real_coauthors( $post_id ) {

	if ( ! function_exists( 'get_coauthors' ) ) return array();

	$coauthors = get_coauthors( $post_id );
	return array_values( array_filter( $coauthors, function( $coauthor ) {
		return $coauthor instanceof WP_User;
	} ) );
}

/**
 * Restrict the publication_author field's selectable users (the
 * AJAX-driven search/select ACF's User field uses) to only this
 * post's actual Co-Authors Plus authors.
 */
function ccg_restrict_publication_author_choices( $args, $field, $post_id ) {

	if ( ! is_numeric( $post_id ) ) return $args;

	$coauthors = ccg_get_real_coauthors( (int) $post_id );

	// Fail closed to an impossible ID rather than silently falling
	// back to ACF's default "all users" behavior — an empty co-author
	// list should mean an empty choice list, not an unrestricted one.
	$args['include'] = empty( $coauthors ) ? array( 0 ) : wp_list_pluck( $coauthors, 'ID' );

	return $args;
}

/**
 * Pre-fill the field's displayed/returned value with the post's first
 * Co-Authors Plus author when nothing has been explicitly saved yet.
 * Only affects what's shown/returned when no stored value exists —
 * doesn't write anything to the database; the editor still needs to
 * save the post (confirming or changing the pre-filled choice) for a
 * real value to be stored.
 */
function ccg_default_publication_author( $value, $post_id, $field ) {

	if ( ! empty( $value ) ) return $value;
	if ( ! is_numeric( $post_id ) ) return $value;

	$coauthors = ccg_get_real_coauthors( (int) $post_id );
	return empty( $coauthors ) ? $value : $coauthors[0]->ID;
}