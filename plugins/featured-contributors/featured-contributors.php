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
function get_featured_contributors($status, $min_posts, $weeks = 0, $limit = 10, $omit = '', $shuffle = false) {
    $json_file = wp_upload_dir()['basedir'] . '/contributors_by_id.json'; 
    if (!file_exists($json_file)) {
        return [];
    }

    $contributors_data = json_decode(file_get_contents($json_file), true);
    if (!$contributors_data || !is_array($contributors_data)) {
        return [];
    }

    $omit_ids = !empty($omit) ? array_map('intval', explode(',', $omit)) : [];
    $cutoff_date = ($weeks > 0) ? strtotime("-{$weeks} weeks") : null;

    // Filter contributors
    $filtered_contributors = array_filter($contributors_data, function($contributor) use ($status, $omit_ids, $min_posts, $cutoff_date) {
        if ($contributor['status'] !== $status) {
            return false;
        }
        if ($contributor['post_count'] < $min_posts) {
            return false;
        }
        if (in_array($contributor['author_id'], $omit_ids)) {
            return false;
        }
        if ($cutoff_date && isset($contributor['last_active'])) {
            $last_active_time = strtotime($contributor['last_active']);
            if ($last_active_time < $cutoff_date) {
                return false;
            }
        }
        return true;
    });

    $filtered_contributors = array_values($filtered_contributors);

    // If limit is less than available, pick a random subset
    if ($limit < count($filtered_contributors)) {
        $keys = array_rand($filtered_contributors, $limit);
        $selected = is_array($keys)
            ? array_map(fn($k) => $filtered_contributors[$k], $keys)
            : [$filtered_contributors[$keys]];
    } else {
        $selected = $filtered_contributors;
    }

    // Shuffle or sort display
    if ($shuffle) {
        shuffle($selected);
    } else {
        usort($selected, fn($a, $b) => strcasecmp($a['last_name'], $b['last_name']));
    }

    return $selected;
}


// Shortcode function to display contributors
function fc_display_contributors($atts) {
    // Default shortcode attributes
    $atts = shortcode_atts(array(
        'status' => 'active',          // 'active' or 'inactive'
        'min_posts' => 1,              // Minimum post count
        'weeks' => 0,                  // Optional activity window
        'limit' => 40,                 // Max contributors to show
        'omit' => '',                  // Comma-separated user IDs to omit
        'shuffle' => 'false'           // Whether to shuffle display order
    ), $atts);

    $shuffle = filter_var($atts['shuffle'], FILTER_VALIDATE_BOOLEAN);

    // Get contributors (selection logic happens inside based on limit)
    $contributors = get_featured_contributors(
        $atts['status'],
        $atts['min_posts'],
        $atts['weeks'],
        $atts['limit'],
        $atts['omit'],
        $shuffle // pass to internal shuffle logic
    );

    if (empty($contributors)) {
        return '<p>No contributors found.</p>';
    }

    // Build output HTML
    $output = '<div class="featured-contributors-container">';
    foreach ($contributors as $contributor) {
        $avatar = '<img src="' . esc_url($contributor['avatar_url']) . '" width="100" height="100" alt="' . esc_attr($contributor['display_name']) . '" loading="lazy" />';
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
