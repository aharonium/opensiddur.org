<?php
function ccg_display($content) {
	if( is_single() ) {
		global $post;
		if ( get_post_meta( $post->ID, '_ccg_options' ) )	$content .= ccg_get_banner( get_post_meta( $post->ID, '_ccg_options', true ), 'meta_exist' );
		else	$content .= ccg_get_banner( get_option( 'ccg_options' ), 'no_meta' );
	}
	return $content;
}
add_filter( 'the_content', 'ccg_display', 99999999999);
?>