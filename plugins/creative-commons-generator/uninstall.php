<?php
if ( !defined('WP_UNINSTALL_PLUGIN') )
    exit();
	
delete_option('ccg_options');

$allposts = get_posts('numberposts=-1&post_type=post&post_status=any');
foreach( $allposts as $postinfo ) {
	delete_post_meta($postinfo->ID, '_ccg_options');
}
?>