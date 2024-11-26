<?php
/*
 * Plugin Name: Featured Contributors
 * Description: A plugin to showcase project contributors with contributor or author user roles with a minimum number of posts.
 * Version: 1.1
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/featured-contributors
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: front page, contributors, authors, user avatars, shortcode, WordPress plugin
 * Text Domain: featured-contributors
 *
 * Note: This plugin relies on helper functions located in our theme's functions.php, as well as JSON files generated from functions there.
 * Acknowledgment: Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
*/

// Enqueue plugin styles for avatars layout
function fc_enqueue_styles() {
    wp_register_style( 'fc-styles',  plugin_dir_url( __FILE__ ) . 'css/fc-styles.css' );
    wp_enqueue_style( 'fc-styles' );
}
add_action('wp_enqueue_scripts', 'fc_enqueue_styles');


// Function to read and filter contributors from the JSON file
function get_featured_contributors($status, $min_posts, $weeks = 0, $limit = 10, $omit = '', $random = false) {
    // Read the JSON file
    $json_file = wp_upload_dir()['basedir'] . '/contributors.json'; 
    if (!file_exists($json_file)) {
        return [];
    }

    $authors_data = json_decode(file_get_contents($json_file), true);
    if (!$authors_data) {
        return [];
    }

    // Prepare omit filter
    $omit_ids = !empty($omit) ? array_map('intval', explode(',', $omit)) : [];

    // Calculate cutoff date if 'weeks' is specified
    $cutoff_date = ($weeks > 0) ? strtotime("-{$weeks} weeks") : null;

    // Filter authors based on status, last_active, and omit filter
    $filtered_authors = array_filter($authors_data, function($author) use ($status, $omit_ids, $min_posts, $cutoff_date) {
        if ($author['status'] !== $status) {
            return false;
        }
        if ($author['post_count'] < $min_posts) {
            return false;
        }
        if (in_array($author['author_id'], $omit_ids)) {
            return false;
        }
        if ($cutoff_date && isset($author['last_active'])) {
            $last_active_time = strtotime($author['last_active']);
            if ($last_active_time < $cutoff_date) {
                return false;
            }
        }
        return true;
    });

    // If random=true, shuffle and select a subset based on the limit
    if ($random) {
        shuffle($filtered_authors);
        $filtered_authors = array_slice($filtered_authors, 0, $limit);
    }

    // Always sort alphabetically by last name
    usort($filtered_authors, function ($a, $b) {
        return strcasecmp($a['last_name'], $b['last_name']);
    });

    // If not random, limit the number of results
    if (!$random) {
        $filtered_authors = array_slice($filtered_authors, 0, $limit);
    }

    return $filtered_authors;
}

// Shortcode function to display contributors
function fc_display_contributors($atts) {
    // Default attributes for the shortcode
    $atts = shortcode_atts(array(
        'status' => 'active',    // Status: 'active' or 'inactive'
        'min_posts' => 1,        // Minimum number of posts
        'weeks' => 0,            // Time frame in weeks (last_active filter)
        'limit' => 40,           // Limit number of contributors to display
        'omit' => '',            // Omit specific user IDs
        'random' => 'false'      // Show contributors in random order (true/false)
    ), $atts);

    $random = filter_var($atts['random'], FILTER_VALIDATE_BOOLEAN);

    // Get the contributors from JSON
    $contributors = get_featured_contributors(
        $atts['status'], 
        $atts['min_posts'], 
        $atts['weeks'], 
        $atts['limit'], 
        $atts['omit'], 
        $random
    );

    if (empty($contributors)) {
        return '<p>No contributors found.</p>';
    }

    $output = '<div class="featured-contributors-container">';
    foreach ($contributors as $contributor) {
        $avatar = '<img src="' . esc_url($contributor['avatar_url']) . '" width="100" height="100" />';
        $name = esc_html($contributor['display_name']);
        $profile_url = esc_url($contributor['profile_page_link']);

        $output .= '<div class="featured-contributor">';
        $output .= '<a href="' . $profile_url . '">' . $avatar . '<br>';
        $output .= '<p>' . $name . '</p>';
        $output .= '</a></div>';
    }
    $output .= '</div>';

    return $output;
}

add_shortcode('featured_contributors', 'fc_display_contributors');