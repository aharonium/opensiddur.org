<?php
/**
 * CF5 RPS – Related Posts Pullers
 * Stage 2.5 refactor (PHP 7.4–8.x safe)
 */

/**
 * Helper: limit post IDs safely
 */
function cf5_rps_limit_post_ids( $posts, $limit ) {
	$ids = array();
	if ( ! empty( $posts ) && is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			if ( count( $ids ) >= $limit ) {
				break;
			}
			if ( isset( $post->ID ) ) {
				$ids[] = (int) $post->ID;
			}
		}
	}
	return $ids;
}

/**
 * YARPP
 */
function get_cf5_yarpp_related_posts() {

	if ( ! function_exists( 'yarpp_get_related' ) ) {
		return array();
	}

	global $cf5_rps;

	$related_posts = yarpp_get_related();

	return cf5_rps_limit_post_ids( $related_posts, $cf5_rps['num'] );
}

/**
 * Inbuilt fallback (tags → categories)
 */
function get_cf5_inbuilt_related_posts() {

	global $post, $cf5_rps;

	if ( ! isset( $post->ID ) ) {
		return array();
	}

	$args = array();

	$tags = wp_get_post_tags( $post->ID );

	if ( ! empty( $tags ) ) {

		$tag_ids = wp_list_pluck( $tags, 'term_id' );

		$args = array(
			'tag__in'        => $tag_ids,
			'post__not_in'   => array( $post->ID ),
			'numberposts'    => (int) $cf5_rps['num'],
			'orderby'        => 'rand',
		);

	} else {

		$categories = get_the_category( $post->ID );

		if ( ! empty( $categories ) ) {

			$category_ids = wp_list_pluck( $categories, 'term_id' );

			$args = array(
				'category__in'   => $category_ids,
				'post__not_in'   => array( $post->ID ),
				'numberposts'    => (int) $cf5_rps['num'],
				'orderby'        => 'rand',
			);
		}
	}

	if ( empty( $args ) ) {
		return array();
	}

	$related_posts = get_posts( $args );

	return cf5_rps_limit_post_ids( $related_posts, $cf5_rps['num'] );
}
