<?php
/*
Plugin Name: Open Content License and Public Domain Status
Plugin URI:  https://github.com/aharonium/open-content-license-generator-plugin
Description: Display the Open Content compatible license or Fair Use exception under which post content is shared.
Version:     2.0
Author:      Aharon Varady (for the Open Siddur Project)
Author URI:  http://aharon.varady.net/omphalos
License:     LGPL3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html
*/

/**
 * File: ccg-init.php
 * Version: 1.2.0 (file-level tracking, distinct from the plugin-wide
 * Version: field above)
 *
 * Provenance note.
 *
 * This plugin originated as "Creative Commons Generator" by OptimalDevs,
 * Alejandro Galvez, and Andy Peter Hernandez Salazar
 * (https://wordpress.org/plugins/creative-commons-generator/), last
 * updated by its original developers in 2015. It was closed by
 * WordPress.org on October 23, 2019 ("Guideline Violation") and is no
 * longer available for download from the plugin directory.
 *
 * The Open Siddur Project forked and substantially modified the plugin
 * beginning some years prior to that closure — restricting the offered
 * licenses to a mutually-compatible set (CC BY-SA, CC BY, CC0) plus a
 * Fair Use exception. As of the redesign, it reads ACF's own
 * open_content_license field directly at display time as the permanent
 * source of truth (see ccg-acf-mapping.php); the CCG-native
 * Source PD Status / Contributor License postmeta model from an
 * earlier iteration of the redesign, and the one-time migration
 * originally planned to populate it from ACF, are both deprecated —
 * migration was never pursued, and ACF remains authoritative
 * indefinitely. No code from the original 2015-era release is knowingly
 * still present verbatim; this note exists for attribution and history,
 * not to imply any relationship with or endorsement by the original
 * developers.
 */

load_plugin_textdomain( 'ccg-domain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
require_once( 'ccg-json-data.php' );
require_once( 'ccg-admin.php' );
require_once( 'ccg-post-options.php' );
require_once( 'ccg-acf-editor.php' );
require_once( 'ccg-frontend.php' );
?>