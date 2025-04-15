<?php
/*
Plugin Name: Contributor Activity Heatmap
Description: Displays a GitHub-style SVG heatmap of post publishing activity.
Version: 1.1
Author: Aharon Varady
*/

add_shortcode('contributor_activity_heatmap', 'contributor_activity_heatmap_shortcode');
add_action('wp_enqueue_scripts', 'contributor_heatmap_enqueue_scripts');

function contributor_heatmap_enqueue_scripts() {
    if (!is_admin()) {
        wp_register_script(
            'contributor-heatmap',
            plugin_dir_url(__FILE__) . 'js/heatmap.js',
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
            <div class="heatmap-loading">Loading post activity…</div>
        </div>
        
        <!-- Footer with Year Link and Version Number -->
        <div class="heatmap-footer">
            <span id="heatmap-year-link">
                <!-- Year link will be dynamically added by JS -->
            </span>
            <span id="heatmap-version">Version 1.1</span> <!-- Version number displayed -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}
