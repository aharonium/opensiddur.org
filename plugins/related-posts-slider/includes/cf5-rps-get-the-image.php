<?php
/**
 * CF5 RPS – Get the Image (Stage 2)
 * Internals aligned with Get the Image v1.1
 * Public API preserved for RPS compatibility
 */

add_theme_support( 'post-thumbnails' );

/* Cache invalidation */
add_action( 'save_post',         'cf5_rps_get_the_image_delete_cache_by_post' );
add_action( 'deleted_post_meta', 'cf5_rps_get_the_image_delete_cache_by_meta', 10, 2 );
add_action( 'updated_post_meta', 'cf5_rps_get_the_image_delete_cache_by_meta', 10, 2 );
add_action( 'added_post_meta',   'cf5_rps_get_the_image_delete_cache_by_meta', 10, 2 );

function cf5_rps_get_the_image_delete_cache_by_post( $post_id ) {
	if ( is_numeric( $post_id ) ) {
		delete_post_meta( (int) $post_id, '_cf5_rps_get_the_image_cache' );
	}
}

function cf5_rps_get_the_image_delete_cache_by_meta( $meta_id, $post_id ) {
	if ( is_numeric( $post_id ) ) {
		delete_post_meta( (int) $post_id, '_cf5_rps_get_the_image_cache' );
	}
}

/**
 * Main API function (signature unchanged)
 */
function cf5_rps_get_the_image( $args = array() ) {

	$defaults = array(
		'post_id'            => get_the_ID(),
		'custom_key'         => '',
		'attachment'         => false,
		'order_of_image'     => 1,
		'size'               => 'thumbnail',
		'the_post_thumbnail' => false,
		'image_scan'         => false,
		'image_class'        => '',
		'link_to_post'       => false,
		'default_image'      => false,
		'width'              => false,
		'height'             => false,
		'permalink'          => '',
		'echo'               => true,
	);

	$args = wp_parse_args( $args, $defaults );
	$post_id = (int) $args['post_id'];

	if ( ! $post_id ) {
		return '';
	}

	/* ---------- CACHE ---------- */

	$cache = get_post_meta( $post_id, '_cf5_rps_get_the_image_cache', true );
	if ( is_array( $cache ) && isset( $cache[ md5( serialize( $args ) ) ] ) ) {
		$image = $cache[ md5( serialize( $args ) ) ];
		if ( $args['echo'] ) {
			echo $image;
			return;
		}
		return $image;
	}

	$image = '';

	/* ---------- 1. FEATURED IMAGE ---------- */

	if ( $args['the_post_thumbnail'] && has_post_thumbnail( $post_id ) ) {
		$image = get_the_post_thumbnail(
			$post_id,
			$args['size'],
			array( 'class' => $args['image_class'] )
		);
	}

	/* ---------- 2. CUSTOM FIELD ---------- */

	if ( ! $image && ! empty( $args['custom_key'] ) ) {
		foreach ( (array) $args['custom_key'] as $key ) {
			$meta = get_post_meta( $post_id, $key, true );
			if ( $meta ) {
				$image = sprintf(
					'<img src="%s" class="%s" />',
					esc_url( $meta ),
					esc_attr( $args['image_class'] )
				);
				break;
			}
		}
	}

	/* ---------- 3. ATTACHMENTS ---------- */

	if ( ! $image && $args['attachment'] ) {
		$attachments = get_attached_media( 'image', $post_id );
		if ( $attachments ) {
			$attachments = array_values( $attachments );
			$index = max( 0, (int) $args['order_of_image'] - 1 );

			if ( isset( $attachments[ $index ] ) ) {
				$image = wp_get_attachment_image(
					$attachments[ $index ]->ID,
					$args['size'],
					false,
					array( 'class' => $args['image_class'] )
				);
			}
		}
	}

	/* ---------- 4. CONTENT SCAN ---------- */

	if ( ! $image && $args['image_scan'] ) {
		$post = get_post( $post_id );
		if ( $post && preg_match(
			'/<img[^>]+src=["\']([^"\'>\s]+)["\']/i',
			$post->post_content,
			$matches
		) ) {
			$image = sprintf(
				'<img src="%s" class="%s" />',
				esc_url( $matches[1] ),
				esc_attr( $args['image_class'] )
			);
		}
	}

	/* ---------- LINK WRAP ---------- */

	if ( $image && $args['link_to_post'] ) {
		$permalink = $args['permalink'] ?: get_permalink( $post_id );
		$image = '<a href="' . esc_url( $permalink ) . '">' . $image . '</a>';
	}

	/* ---------- SAVE CACHE ---------- */

	$key = md5( serialize( $args ) );
	$cache = is_array( $cache ) ? $cache : array();
	$cache[ $key ] = $image;
	update_post_meta( $post_id, '_cf5_rps_get_the_image_cache', $cache );

	if ( $args['echo'] ) {
		echo $image;
		return;
	}

	return $image;
}
