<?php
/**
 * Plugin Name: Enhanced Category Description
 * Description: Parse category descriptions and inject related links and solicitations.
 * Version: 1.2
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/enhanced-category-description
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: category pages, co-authors, categories, tags, filters, WordPress plugin
 * Text Domain: enhanced-category-description
 *
 * Note: This plugin relies on helper functions located in our theme's functions.php, as well as JSON files generated from functions there.
 * Acknowledgment: Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
*/

// Hook into the category page display to modify the content
add_filter('the_content', 'enhance_category_description');

// Register shortcode to display the enhanced category description
function ecd_shortcode() {
    return enhance_category_description('');
}
add_shortcode('enhanced_category_description', 'ecd_shortcode');


// Check if a category is a terminal subcategory
function is_terminal_subcategory($category_id) {
    // Use 'category' as the taxonomy
    $term_children = get_term_children(filter_var($category_id, FILTER_VALIDATE_INT), 'category');

    // Check if the category has subcategories
    if (empty($term_children) || is_wp_error($term_children)) {
        // echo "Terminal Subcategory"; // For debugging: terminal category
        return true;
    } else {
        // Count the number of subcategories and print for debugging
        $subcategories_count = count($term_children);
        // echo "Subcategories (" . $subcategories_count . "):"; // For debugging: show count of subcategories
        return false;
    }
}


// Helper function to build filter links for categories, tags, languages, and authors
function build_filter_link($base_url, $query_param, $value) {
    return add_query_arg($query_param, $value, $base_url);
}


// Helper function to get all categories, tags, authors, and languages for posts within a terminal category
function ac_get_terms_for_category($category_id) {
    $cache_key = "category_terms_{$category_id}";
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }
    
    // Check if this is a terminal subcategory first
    if (!is_terminal_subcategory($category_id)) {
        return [[], [], []]; // Not a terminal subcategory, return empty arrays
    }

    $post_ids = get_posts([
        'category' => $category_id,
        'fields' => 'ids',
        'post_type' => 'post',
        'posts_per_page' => -1, // Retrieve all posts in this category
    ]);

    $categories = [];
    $tags = [];
    $authors = [];
    $languages = [];
    
    foreach ($post_ids as $post_id) {
        // Get associated categories and tags for each post
        $post_categories = wp_get_post_categories($post_id, ['fields' => 'all']);
        $post_tags = wp_get_post_tags($post_id, ['fields' => 'all']);
        $post_languages = get_post_meta($post_id, 'languages_meta', true);
        $post_languages = $post_languages ? json_decode($post_languages, true) : [];

        foreach ($post_languages as $lang) {
            $languages[$lang['code']] = $lang['name']; // Store by code → name
        }

        // Exclude the base category
        $post_categories = array_filter($post_categories, function($cat) use ($category_id) {
            return $cat->term_id != $category_id;
        });

        // Fetch co-authors using Co-Authors Plus, if available
        if (function_exists('get_coauthors')) {
            $post_authors = get_coauthors($post_id);
            foreach ($post_authors as $author) {
                $authors[$author->ID] = $author; // Use author ID as key to prevent duplicates
            }
        } else {
            // Fallback to single author
            $author_id = get_post_field('post_author', $post_id);
            $authors[$author_id] = get_user_by('id', $author_id);
        }

        $categories = array_merge($categories, $post_categories);
        $tags = array_merge($tags, $post_tags);
    }

    // Remove duplicates and sort terms
    $categories = ac_deduplicate_and_sort($categories);
    $tags = ac_deduplicate_and_sort($tags);
    
    asort($languages);
    $languages = array_values(array_map(fn($code, $name) => ['code' => $code, 'name' => $name], array_keys($languages), $languages));

    // Sort authors alphabetically by last name
    $authors = array_values($authors); // Convert back to indexed array after removing duplicates
    usort($authors, function($a, $b) {
        $last_name_a = get_the_author_meta('last_name', $a->ID);
        $last_name_b = get_the_author_meta('last_name', $b->ID);
        return strcasecmp($last_name_a, $last_name_b);
    });

    $result = [$categories, $tags, $authors, $languages];
    set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);
    return $result;
}


/* // Helper function to remove <div> and <blockquote> tags from the description
function strip_outer_wrappers($content) {
    return preg_replace('/^<div[^>]*>|<\/div>$|^<blockquote[^>]*>|<\/blockquote>$/i', '', $content);
} */


// Helper function to split the description into main content and related links
function split_description($description) {
    // Use <hr> as a delimiter to split into two parts
    $parts = preg_split('/<hr\s*\/?>/i', $description, 2);

    $main_content = trim($parts[0]); // First part: main content and solicitation
    $related_links = isset($parts[1]) ? trim($parts[1]) : ''; // Second part: related links (if any)

    return [$main_content, $related_links];
}

// Main function to enhance the category description output
function enhance_category_description($content) {
    if (!is_category()) return $content; // Only modify content on category pages

    $category = get_queried_object(); // Get the current category object
    $category_url = get_category_link($category->term_id); // Current category URL

    $description = term_description($category->term_id, 'category'); // Get the category description
    if (empty($description)) return $content; // If no description, return the content unchanged

    /* // Remove outer <div> and <blockquote> tags if present
    $description = strip_outer_wrappers($description); */

    // Split the description into main content and related links (if any)
    [$main_content, $related_links] = split_description($description);

    // Construct the enhanced description
    $enhanced_description = '<div class="enhanced-category-description">';
    $enhanced_description .= '<div class="category-main-content">' . $main_content . '</div>';

    // Check if this is a terminal subcategory to display filters
    if (is_terminal_subcategory($category->term_id)) {
        // Fetch categories, tags, languages, and authors associated with this category
        list($categories, $tags, $authors, $languages) = ac_get_terms_for_category($category->term_id);

        // Build filter section if any terms are found
        $filters = '<div class="category-filters">';

        // Display author filters
        if (!empty($authors)) {
            $filters .= '<div class="accordion"><strong>Filter resources by Collaborator Name</strong></div>';
            $filters .= '<div class="panel">';
            $filters .= implode(' | ', array_map(function($author) use ($category_url) {
                $link = build_filter_link($category_url, 'col', $author->ID);
                return '<a href="' . esc_url($link) . '">' . esc_html($author->display_name) . '</a>';
                // return esc_html($author->display_name);
            }, $authors));
            $filters .= '</div>';
        } 
        
        // Display tag filters
        if (!empty($tags)) {
            $filters .= '<div class="accordion"><strong>Filter resources by Tag</strong></div>';
            $filters .= '<div class="panel">';
            $filters .= implode(' | ', array_map(function($tag) use ($category_url) {
                $link = build_filter_link($category_url, 'taag', $tag->slug);
                return '<a href="' . esc_url($link) . '">' . esc_html($tag->name) . '</a>';
            }, $tags));
            $filters .= '</div>';
        }
        
        // Display category filters 
        if (!empty($categories)) {
            $filters .= '<div class="accordion"><strong>Filter resources by Category</strong></div>';
            $filters .= '<div class="panel">';
            $filters .= implode(' | ', array_map(function($cat) use ($category_url) {
                $link = build_filter_link($category_url, 'caat', $cat->slug);
                return '<a href="' . esc_url($link) . '">' . esc_html($cat->name) . '</a>';
                // return esc_html($cat->name);                
            }, $categories));
            $filters .= '</div>';
        } 
        
        // Display language filters
        if (!empty($languages)) {
            $filters .= '<div class="accordion"><strong>Filter resources by Language</strong></div>';
            $filters .= '<div class="panel"><p>';

            foreach ($languages as $lang) {
                $link = esc_url(add_query_arg(['language' => $lang['code'], 'language_name' => urlencode($lang['name'])], $category_url));
                $filters .= '<a href="' . esc_url($link) . '">' . esc_html($lang['name']) . '</a> | ';
            }

            $filters = rtrim($filters, ' | ') . '</p></div>';
        }
        
        // Date range filter
        $filters .= '<div class="accordion"><strong>Filter resources by Date Range</strong></div>';
        $filters .= '<div class="panel">';
        $filters .= '<form action="' . esc_url(remove_query_arg(['caat', 'taag', 'col', 'language', 'language_name'], $category_url)) . '" method="get" class="date-range-form">';

        // Preserve existing query parameters (except for other filters)
        $existing_params = $_GET;

        foreach ($existing_params as $key => $value) {
            if (!in_array($key, ['caat', 'taag', 'col', 'language', 'language_name', 'daterange_start', 'daterange_end'])) {
                $filters .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }

        // Pre-fill current date range if set
        $start_value = isset($_GET['daterange_start']) ? intval($_GET['daterange_start']) : '';
        $end_value = isset($_GET['daterange_end']) ? intval($_GET['daterange_end']) : '';

        $filters .= '<div class="date-range-fields">';
        $filters .= '<label for="daterange_start">From:</label>';
        $filters .= '<input type="number" id="daterange_start" name="daterange_start" value="' . esc_attr($start_value) . '" placeholder="Start year"> ';
        $filters .= '<label for="daterange_end">to:</label>';
        $filters .= '<input type="number" id="daterange_end" name="daterange_end" value="' . esc_attr($end_value) . '" placeholder="End year">';
        $filters .= '<button type="submit" class="date-range-submit">Filter</button>';
        $filters .= '</div>';

        // Instruction for BCE formatting
        $filters .= '<p style="font-size: 14px; color: #666;">Enter a start year and an end year. BCE years are preceded by a dash (e.g., -1000).</p>';

        // Add a "Clear Filters" button
        // $filters .= '<p><a href="' . esc_url(get_category_link($category->term_id)) . '" class="clear-filters">Clear Filters</a></p>';

        $filters .= '</form>';

        $filters .= '</div>';
        // end date range filter
        
        $filters .= '</div>'; // Close category-filters
        $enhanced_description .= $filters; // Append filters after main content
    }

    // If there are related links, display them after the main content and filters
    if (!empty($related_links)) {
        $enhanced_description .= '<hr class="related-links-separator">';
        $enhanced_description .= '<div class="related-links">';
        $enhanced_description .= '<div>' . $related_links . '</div>';
        $enhanced_description .= '</div>';
    }

    $enhanced_description .= '</div>'; // Close enhanced-category-description

    return $enhanced_description;
}

// Enqueue plugin styles and scripts
function ecd_enqueue_assets() {
    // Enqueue CSS
    wp_register_style('ecd-styles', plugin_dir_url(__FILE__) . 'css/ecd-styles.css');
    wp_enqueue_style('ecd-styles');

    // Enqueue JavaScript for accordion if not already loaded by another plugin
    if (!wp_script_is('ac-accordion', 'enqueued')) {
        wp_register_script('ac-accordion', plugin_dir_url(__FILE__) . 'js/ac-accordion.js', array('jquery'), '1.0', true);
        wp_enqueue_script('ac-accordion');
    }    
}
add_action('wp_enqueue_scripts', 'ecd_enqueue_assets');
