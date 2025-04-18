<?php
/*
 * Plugin Name: Author Contributions
 * Description: Displays the categories, tags, languages, and names of users associated with the content of any contributed post, along with filter links and a date range query form.
 * Version: 1.5
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
    $cache_key = "author_contributions_{$user_id}";
    
    // Use caching only if no daterange is applied
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $args = [
        'post_type'      => 'any',
        'posts_per_page' => -1,
        'author'         => $user_id,
        'fields'         => 'ids',
    ];

    // Run the query
    $query = new WP_Query($args);

    // Cache results only when not filtered
    set_transient($cache_key, $query->posts, 12 * HOUR_IN_SECONDS);

    return $query->posts;
}


// Get all categories, tags, co-contributors, and languages for the user’s posts
function ac_get_terms_for_user($user_id) {
    $post_ids = ac_get_user_posts($user_id);
    $categories = [];
    $tags = [];
    $co_contributors = [];
    $languages = [];

    foreach ($post_ids as $post_id) {
        $post_categories = wp_get_post_categories($post_id, ['fields' => 'all']);
        $post_tags = wp_get_post_tags($post_id, ['fields' => 'all']);
        $post_languages = get_post_meta($post_id, 'languages_meta', true);
        $post_languages = $post_languages ? json_decode($post_languages, true) : [];

        foreach ($post_languages as $lang) {
            $languages[$lang['code']] = $lang['name']; // Store by code → name
        }

        // Get co-contributors
        if (function_exists('get_coauthors')) {
            $post_coauthors = get_coauthors($post_id);
            foreach ($post_coauthors as $coauthor) {
                if ($coauthor->ID !== $user_id) {
                    $co_contributors[] = $coauthor;
                }
            }
        }

        $categories = array_merge($categories, $post_categories);
        $tags = array_merge($tags, $post_tags);
    }

    // Remove duplicates
    $co_contributors = array_unique($co_contributors, SORT_REGULAR);
    $categories = ac_deduplicate_and_sort($categories);
    $tags = ac_deduplicate_and_sort($tags);

    // Sort languages alphabetically & reset array keys
    asort($languages);
    $languages = array_values(array_map(fn($code, $name) => ['code' => $code, 'name' => $name], array_keys($languages), $languages));

    return [$categories, $tags, $co_contributors, $languages];
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


// For tags, extract numeric and era components from slug (e.g., "10th-century-common-era" => [10, 'common-era']).
function extract_numeric_and_era($slug) {
    // Match numbers followed by an optional era (before-common-era, common-era, or anno-mundi).
    if (preg_match('/(\d+)(?:st|nd|rd|th)?(?:-century)?(?:-(before-common-era|common-era|anno-mundi))?/i', $slug, $matches)) {
        return [(int) $matches[1], strtolower($matches[2])];
    }
    return [null, '']; // No match found.
}


// Format terms as a single line separated by ' | '
function ac_format_line($terms, $type, $user_id) {
    $formatted = array_map(function ($term) use ($type, $user_id) {
        $link = esc_url(add_query_arg($type, $term->slug, get_author_posts_url($user_id)));
        return '<a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a>';
    }, $terms);

    return implode(' | ', $formatted); // Join with ' | '
}


// Main shortcode function to display the accordion sections
function ac_display_author_contributions($atts) {
    $user_id = get_query_var('author'); // Get the current author ID
    if (!$user_id) return '<p>No author found.</p>';

    list($categories, $tags, $co_contributors, $languages) = ac_get_terms_for_user($user_id);

    $output = '<div class="author-contributions">';
    
    // Display the categories
    if (!empty($categories)) {
        $output .= '<div class="accordion">Filter resources by Category</div>';
        $output .= '<div class="panel"><p>';
        $output .= ac_format_line($categories, 'cat', $user_id);
        $output .= '</p></div>';
    }

    // Display the tags
    if (!empty($tags)) {
        $output .= '<div class="accordion">Filter resources by Tag</div>';
        $output .= '<div class="panel"><p>';
        $output .= ac_format_line($tags, 'tag', $user_id);
        $output .= '</p></div>';
    }

    // Display the co-contributors
    if (!empty($co_contributors)) {
        $output .= '<div class="accordion">Filter resources by Collaborator Name</div>';
        $output .= '<div class="panel"><p>';
        
        foreach ($co_contributors as $contributor) {
            $collab_url = esc_url(add_query_arg('collab', $contributor->ID, get_author_posts_url($user_id)));
            $output .= '<a href="' . $collab_url . '">' . esc_html($contributor->display_name) . '</a> | ';
        }

        $output = rtrim($output, ' | '); // Remove last separator
        $output .= '</p></div>';
    }

    // Display the languages
    if (!empty($languages)) {
        $output .= '<div class="accordion">Filter resources by Language</div>';
        $output .= '<div class="panel"><p>';

        foreach ($languages as $lang) { // Correct way to loop through languages
            $link = esc_url(add_query_arg([
                'language' => $lang['code'],
                'language_name' => urlencode($lang['name'])
            ], get_author_posts_url($user_id)));
            $output .= '<a href="' . $link . '">' . esc_html($lang['name']) . '</a> | ';
        }

        $output = rtrim($output, ' | ') . '</p></div>';
    }
    
    // Display date filter form inside the accordion
    if (!$GLOBALS['date_filter_displayed']) {
        $GLOBALS['date_filter_displayed'] = true;

        $output .= '<div class="accordion">Filter resources by Date Range</div>';
        $output .= '<div class="panel"><form method="get" action="' . esc_url(get_author_posts_url($user_id)) . '">';

        // Preserve only date range parameters
        $existing_params = $_GET;
        foreach ($existing_params as $key => $value) {
            if ($key !== 'daterange_start' && $key !== 'daterange_end') {
                continue;
            }
            $output .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
        }

        // Date input fields
        $output .= '<div class="date-range-fields">';
        $output .= '<label for="daterange_start">From:</label>';
        $output .= '<input type="number" id="daterange_start" name="daterange_start" placeholder="Start year" min="-5000" max="5000">';
        $output .= ' <label for="daterange_end">to:</label>';
        $output .= '<input type="number" id="daterange_end" name="daterange_end" placeholder="End year" min="-5000" max="5000">';
        $output .= '<button type="submit" class="date-range-submit">Filter</button>';
        $output .= '</div>';

        // Instruction for BCE formatting
        $output .= '<p style="font-size: 14px; color: #666;">Enter a start year and an end year. BCE years are preceded by a hyphen (e.g., -1000).</p>';

        $output .= '</form></div>';
    }

    $output .= '</div>';
    return $output;
}

add_shortcode('author_contributions', 'ac_display_author_contributions');
