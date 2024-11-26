<?php
/*
 * Plugin Name: Author Contributions
 * Description: Displays the categories, tags, and names of users associated with any contributed post, along with filter links.
 * Version: 1.1
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/author-contributions
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: author pages, co-authors, categories, tags, filters, WordPress plugin
 * Text Domain: author-contributions
 *
 * Note: This plugin relies on helper functions located in our theme's functions.php, as well as JSON files generated from functions there.
 * Acknowledgment: Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
*/

// Enqueue plugin styles and scripts
function ac_enqueue_assets() {
    // Enqueue CSS
    wp_register_style('ac-styles', plugin_dir_url(__FILE__) . 'css/ac-styles.css');
    wp_enqueue_style('ac-styles');
    
// Enqueue JavaScript for accordion
    wp_register_script('ac-accordion', plugin_dir_url(__FILE__) . 'js/ac-accordion.js', array('jquery'), '1.0', true);
    wp_enqueue_script('ac-accordion');
}
add_action('wp_enqueue_scripts', 'ac_enqueue_assets');

// Fetch posts by users with specific roles (Contributor or Author)
function ac_get_user_posts($user_id) {
    // Use transient caching to avoid repeated queries
    $cache_key = "author_contributions_{$user_id}";
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    // Query all posts where the user has either "Contributor" or "Author" role
    $args = array(
        'post_type'      => 'any',
        'posts_per_page' => -1, // Fetch all relevant posts
        'author'         => $user_id,
        'fields'         => 'ids', // Only retrieve post IDs to optimize performance
    );
    $query = new WP_Query($args);

    // Store post IDs in cache for 12 hours
    set_transient($cache_key, $query->posts, 12 * HOUR_IN_SECONDS);

    return $query->posts;
}

// Get all categories, tags, and co-contributors for the user’s posts
function ac_get_terms_for_user($user_id) {
    $post_ids = ac_get_user_posts($user_id); // Get all posts for the user
    $categories = [];
    $tags = [];
    $co_contributors = [];

    foreach ($post_ids as $post_id) {
        $post_categories = wp_get_post_categories($post_id, ['fields' => 'all']);
        $post_tags = wp_get_post_tags($post_id, ['fields' => 'all']);

        // Get co-contributors for each post
        if (function_exists('get_coauthors')) {
            $post_coauthors = get_coauthors($post_id);
            foreach ($post_coauthors as $coauthor) {
                if ($coauthor->ID !== $user_id) { // Exclude the current author
                    $co_contributors[] = $coauthor;
                }
            }
        }

        $categories = array_merge($categories, $post_categories);
        $tags = array_merge($tags, $post_tags);
    }

    // Remove duplicates from co-contributors
    $co_contributors = array_unique($co_contributors, SORT_REGULAR);
    // $co_contributors = ac_deduplicate_and_sort($co_contributors, 'last_name');
    
    // Sort co-contributors by last name
    usort($co_contributors, function($a, $b) {
        $last_name_a = get_the_author_meta('last_name', $a->ID);
        $last_name_b = get_the_author_meta('last_name', $b->ID);

        return strcasecmp($last_name_a, $last_name_b);
    });

    // Sort categories and tags alphabetically
    $categories = ac_deduplicate_and_sort($categories);
    $tags = ac_deduplicate_and_sort($tags);

    return [$categories, $tags, $co_contributors];
}

// Helper function to deduplicate and sort terms by slug with era logic.
function ac_deduplicate_and_sort($items) {
    // Remove duplicates by unique property (e.g., 'ID' for authors, 'slug' for terms).
    $unique_items = array_unique($items, SORT_REGULAR);

    // Sort items by slug alpha-numerically with custom era logic.
    usort($unique_items, function($a, $b) {
        $slugA = $a->slug;
        $slugB = $b->slug;

        // Extract numeric and era components from slugs.
        [$numA, $eraA] = extract_numeric_and_era($slugA);
        [$numB, $eraB] = extract_numeric_and_era($slugB);

        // Sort by era precedence: before-common-era < common-era < anno-mundi.
        $era_order = [
            'before-common-era' => 1, 
            'common-era' => 2, 
            'anno-mundi' => 3
        ];
        $era_diff = ($era_order[$eraA] ?? 0) <=> ($era_order[$eraB] ?? 0);

        if ($era_diff !== 0) {
            return $era_diff; // Sort by era if different.
        }

        // If in the same era, sort numerically (descending for 'before-common-era').
        $num_diff = $numA <=> $numB;
        if ($eraA === 'before-common-era') {
            $num_diff *= -1; // Reverse order for 'before-common-era'.
        }

        if ($num_diff !== 0) {
            return $num_diff; // Return numeric difference.
        }

        // Fall back to standard alphabetical slug sorting.
        return strcasecmp($slugA, $slugB);
    });

    return $unique_items;
}

// Extract numeric and era components from slug (e.g., "10th-century-common-era" => [10, 'common-era']).
function extract_numeric_and_era($slug) {
    // Match numbers followed by an optional era (before-common-era, common-era, or anno-mundi).
    if (preg_match('/(\d+)(?:st|nd|rd|th)?-century-(before-common-era|common-era|anno-mundi)/i', $slug, $matches)) {
        return [(int) $matches[1], strtolower($matches[2])];
    }
    return [null, '']; // No match found.
}


// Format terms as a single line separated by ' | '
function ac_format_line($terms, $type, $user_id) {
    $formatted = array_map(function ($term) use ($type, $user_id) {
        $link = add_query_arg($type, $term->slug, get_author_posts_url($user_id));
        return '<a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a>';
    }, $terms);

    return implode(' | ', $formatted); // Join with ' | '
}

// Main shortcode function to display the accordion sections
function ac_display_author_contributions($atts) {
    $user_id = get_query_var('author'); // Get the current author ID
    if (!$user_id) return '<p>No author found.</p>';

    list($categories, $tags, $co_contributors) = ac_get_terms_for_user($user_id);

    $output = '<div class="author-contributions">';

    // Display the categories
    if (!empty($categories)) {
        // $output .= '<div class="accordion-section">';
        $output .= '<div class="accordion">Filter resources by Category</div>';
        $output .= '<div class="panel"><p>';
        $output .= ac_format_line($categories, 'cat', $user_id);
        $output .= '</p></div>';
    }

    // Display the tags
    if (!empty($tags)) {
        // $output .= '<div class="accordion-section">';
        $output .= '<div class="accordion">Filter resources by Tag</div>';
        $output .= '<div class="panel"><p>';
        $output .= ac_format_line($tags, 'tag', $user_id);
        $output .= '</p></div>';
    }

    // Display the co-contributors
     if (!empty($co_contributors)) {
        // $output .= '<div class="accordion-section">';
        $output .= '<div class="accordion">Filter resources by Name</div>';
        $output .= '<div class="panel"><p>';
        
        foreach ($co_contributors as $contributor) {
            // Generate the correct URL to the author page with the collaborator filter
            $collab_url = esc_url(add_query_arg('collab', $contributor->ID, get_author_posts_url($curauth->ID)));
            $output .= '<a href="' . $collab_url . '">' . esc_html($contributor->display_name) . '</a> | ';
        }

        $output = rtrim($output, ' | '); // Remove the last separator
        $output .= '</p></div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('author_contributions', 'ac_display_author_contributions');
