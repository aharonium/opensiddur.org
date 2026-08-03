<?php
/*
Plugin Name: Open Content License Generator
Plugin URI:  https://github.com/aharonium/open-content-license-generator-plugin
Description: Indicate the Open Content compatible license under which your post content is shared.
Version:     1.4.1
Author:      Aharon Varady, the Jewish Free-Culture Society
Author URI:  http://aharon.varady.net/omphalos
License:     LGPL3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html
*/
load_plugin_textdomain( 'ccg-domain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
require_once( 'ccg-admin.php' );
require_once( 'ccg-post-options.php' );
require_once( 'ccg-frontend.php' );
?>