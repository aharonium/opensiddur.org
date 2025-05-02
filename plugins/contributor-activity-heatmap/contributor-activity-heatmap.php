<?php
/*
 * Plugin Name: Contributor Activity Heatmap
 * Description: Displays a GitHub-style SVG heatmap of post publishing activity.
 * Version: 1.2
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/contributor-activity-heatmap
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: front page, contributors, authors, user avatars, shortcode, WordPress plugin
 * Text Domain: featured-contributors
 *
 * Note: This plugin relies on entries in a JSON file generated within our theme's functions.php. All the heavy lifting is done in vanilla javascript.
 * Acknowledgment: Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
*/


add_shortcode('contributor_activity_heatmap', 'contributor_activity_heatmap_shortcode');
add_action('wp_enqueue_scripts', 'contributor_heatmap_enqueue_scripts');

function contributor_heatmap_enqueue_scripts() {
    if (!is_admin()) {
        wp_register_script(
            'contributor-heatmap',
            plugin_dir_url(__FILE__) . 'js/heatmap-loader.js',
            [],
            '1.1', // Updated version number for cache busting
            true // Load in footer
        );

        wp_register_style(
            'contributor-heatmap-style',
            plugin_dir_url(__FILE__) . 'css/heatmap.css',
            [],
            '1.1' // Updated version number for cache busting
        );
    }
}

function contributor_activity_heatmap_shortcode() {
    wp_enqueue_script('contributor-heatmap');
    wp_enqueue_style('contributor-heatmap-style');

    $upload_dir = wp_upload_dir();
    $json_url = trailingslashit($upload_dir['baseurl']) . 'posts.json';

    ob_start();
    ?>
    <div id="contributor-activity-heatmap" class="contributor-activity-heatmap" data-json-url="<?php echo esc_url($json_url); ?>">
        <div class="heatmap-container">
            <div class="heatmap-loading"></div>
        </div>
        
        <!-- Footer with Year Link and Version Number -->
        <div class="heatmap-footer">
            <span id="heatmap-year-link">
                <!-- Year link will be dynamically added by JS -->
            </span>
            <span id="heatmap-version"></span> <!-- Version number displayed -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}
