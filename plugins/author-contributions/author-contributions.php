<?php
/*
 * Plugin Name: Author Contributions
 * Description: Displays the categories, tags, languages, and names of users associated with the content of any contributed post, along with filter links and a date range query form.
 * Version: 1.6
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


function ac_load_contributors_by_id($force_reload = false) {
    static $cached_data = null;
    if (!$force_reload && $cached_data !== null) {
        return $cached_data;
    }

    $path = WP_CONTENT_DIR . '/uploads/contributors_by_id.json';
    if (file_exists($path)) {
        $cached_data = json_decode(file_get_contents($path), true);
        return $cached_data;
    }
    return [];
}

// Fetch posts by users with specific roles (Contributor or Author)
function ac_get_user_posts($user_id) {
    $uploads_dir = wp_upload_dir()['basedir'];
    $posts_path = $uploads_dir . '/posts.json';
    $contributors_data = ac_load_contributors_by_id();

    $posts_data = json_decode(file_get_contents($posts_path), true);
    if (!is_array($posts_data) || !is_array($contributors_data)) {
        return [];
    }

    $user_id_str = (string) $user_id;
    $user_posts = [];

    foreach ($posts_data as $post_id => $post) {
        if (empty($post['authors']) || !is_array($post['authors'])) continue;

        foreach ($post['authors'] as $author_str) {
            if (preg_match('/\((\d+)\)$/', $author_str, $matches)) {
                $author_id = $matches[1];
                if ($author_id === $user_id_str) {
                    // Extract authors with last names
                    $authors = array_map(function($author_entry) use ($contributors_data) {
                        if (preg_match('/^(.*?) \((\d+)\)$/', $author_entry, $m)) {
                            $name = trim($m[1]);
                            $id = $m[2];
                            $last_name = isset($contributors_data[$id]['last_name']) ? $contributors_data[$id]['last_name'] : '';
                            return [
                                'name' => $name,
                                'id' => (int) $id,
                                'last_name' => $last_name
                            ];
                        }
                        return null;
                    }, $post['authors']);

                    $authors = array_filter($authors); // Remove nulls

                    $user_posts[$post_id] = [
                        'categories' => $post['categories'] ?? [],
                        'tags'       => $post['tags'] ?? [],
                        'languages'  => $post['languages'] ?? [],
                        'authors'    => $authors,
                    ];
                    break;
                }
            }
        }
    }

    return $user_posts;
}

function ac_parse_labeled_items($items) {
    $parsed = [];

    foreach ($items as $item) {
        if (preg_match('/^(.*?)\s*\((\d+)\)$/', $item, $matches)) {
            $term_id = (int) $matches[2];
            $term = get_term($term_id); // This gets the term object from ID

            if ($term && !is_wp_error($term)) {
                $parsed[] = (object) [
                    'name' => $term->name,
                    'term_id' => $term_id,
                    'slug' => $term->slug,
                ];
            }
        }
    }

    return $parsed;
}


// Get all categories, tags, co-contributors, and languages for the user’s posts
function ac_get_terms_for_user($user_id) {
    $user_posts = ac_get_user_posts($user_id);

    $categories = [];
    $tags = [];
    $languages = [];
    $co_contributors = [];

    // Load contributor data once
    $contributors_by_id = ac_load_contributors_by_id();

    foreach ($user_posts as $post_data) {
        // Merge categories and tags
        $categories = array_merge($categories, ac_parse_labeled_items($post_data['categories'] ?? []));
        $tags = array_merge($tags, ac_parse_labeled_items($post_data['tags'] ?? []));

        // Parse languages
        foreach ($post_data['languages'] ?? [] as $lang_str) {
            if (preg_match('/^(.*?): ([\w\-]+) \((.+?)\)$/', $lang_str, $matches)) {
                $languages[$matches[2]] = $matches[3]; // code => name
            }
        }

        // Parse co-contributors
        foreach ($post_data['authors'] ?? [] as $author) {
            $id = (int) $author['id'];
            if ($id !== $user_id && !empty($contributors_by_id[$id])) {
                $author_data = $contributors_by_id[$id];
                $co_contributors[$id] = [
                    'id'         => $id,
                    'name'       => $author_data['display_name'],
                    'last_name'  => $author_data['last_name'],
                    'first_name' => $author_data['first_name'],
                ];
            }
        }
    }

    // Sort and clean up co-contributors
    $co_contributors = array_values($co_contributors); // Re-index
    usort($co_contributors, function ($a, $b) {
        $cmp = strcasecmp($a['last_name'], $b['last_name']);
        return $cmp !== 0 ? $cmp : strcasecmp($a['first_name'], $b['first_name']);
    });
    $co_contributors = array_map(fn($c) => ['id' => $c['id'], 'name' => $c['name']], $co_contributors);

    // Sort and deduplicate categories and tags
    $categories = ac_deduplicate_and_sort($categories);
    $tags = ac_deduplicate_and_sort($tags);

    // Sort and format languages
    asort($languages);
    $languages = array_values(array_map(
        fn($code, $name) => ['code' => $code, 'name' => $name],
        array_keys($languages),
        $languages
    ));

    return [$categories, $tags, $co_contributors, $languages];
}


// Helper function to deduplicate and sort terms by slug with era logic.
function ac_deduplicate_and_sort($items) {
    // Remove duplicates by unique property (e.g., 'ID' for authors, 'slug' for terms).
    $seen = [];
    $unique_items = [];

    foreach ($items as $item) {
      if (!in_array($item->slug, $seen)) {
        $seen[] = $item->slug;
        $unique_items[] = $item;
      }
    }
  
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
        if (isset($matches[1], $matches[2])) { // Ensure both match groups exist
            return [(int) $matches[1], strtolower($matches[2])];
        }
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
            $collab_url = esc_url(add_query_arg('collab', $contributor['id'], get_author_posts_url($user_id)));
            $output .= '<a href="' . $collab_url . '">' . esc_html($contributor['name']) . '</a> | ';

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
    if (!isset($GLOBALS['date_filter_displayed'])) { // Ensure the global variable is set
        $GLOBALS['date_filter_displayed'] = false;  // Initialize if not set
    }
    
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
