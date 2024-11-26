<?php
/**
 * Plugin Name: Custom Category Grid
 * Description: A plugin to display category images with custom styles and layout.
 * Version: 1.3
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/custom-category-grid
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: category pages, subcategories, co-authors, categories, tags, filters, WordPress plugin
 * Text Domain: custom-category-grid
 *
 * Note: This plugin relies on helper functions located in our theme's functions.php, as well as JSON files generated from functions there.
 * This plugin relies on the "Post Category Image Grid and Slider Pro" plugin by WPOnlineSupport/EssentialPlugin <https://www.wponlinesupport.com/downloads/post-category-image-grid-and-slider-pro/>. 
 * The plugin for providing a simple means of designating a category image. A non-commercial free version is available here <https://wordpress.org/plugins/post-category-image-with-grid-and-slider/> (although this code refers to the pro version).
 * 
 * Acknowledgment: Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
*/

// Register shortcode to display category images grid
function custom_category_grid_shortcode($atts) {
    // Shortcode attributes with default values
    $atts = shortcode_atts([
        'term_ids' => '', // Comma-separated term IDs
        'sub_categories' => false, // Whether to show subcategories only
        'svg_width' => 420, // Default width of the SVG frame
        'image_percentage' => 80, // Category image size as a percentage of the SVG width
        'image_size' => 'thumbnail', // Registered image size: thumbnail, medium, large, full
        'gap' => 10, // Gap between category images
        'sort' => 'default', // Sorting option: 'default', 'name', 'slug', 'term_order'
        'show_title' => true, // Whether to display the category title
    ], $atts, 'custom_category_grid');

    // Convert term IDs into an array
    $term_ids = array_map('intval', explode(',', $atts['term_ids']));

    // Fetch categories based on the shortcode attributes
    $query_args = [
        'taxonomy' => 'category',
        'hide_empty' => false,
    ];

    if ($atts['sub_categories'] && !empty($term_ids)) {
        $query_args['parent'] = $term_ids[0]; // Get sub-categories of the first parent
    } else {
        $query_args['include'] = $term_ids; // Include specific term IDs
    }

    $categories = get_terms($query_args);

    // If no categories are found, return an empty string
    if (empty($categories)) {
        return '';
    }

    // Handle sorting
    switch ($atts['sort']) {
        case 'name':
            usort($categories, fn($a, $b) => strcmp($a->name, $b->name));
            break;
        case 'slug':
            usort($categories, fn($a, $b) => strcmp($a->slug, $b->slug));
            break;
        case 'term_order':
            usort($categories, fn($a, $b) => $a->term_order <=> $b->term_order);
            break;
        default: // Default to the order of term_ids provided
            usort($categories, function ($a, $b) use ($term_ids) {
                $pos_a = array_search($a->term_id, $term_ids);
                $pos_b = array_search($b->term_id, $term_ids);
                return $pos_a - $pos_b;
            });
            break;
    }

    // Calculate SVG height based on the fixed aspect ratio (420x325)
    $svg_height = ($atts['svg_width'] * 325) / 420;

    // Start generating the HTML output
    $output = '<div class="ccg-custom-category-grid" style="display: flex; flex-wrap: wrap; gap: ' . esc_attr($atts['gap']) . 'px;">';

    foreach ($categories as $category) {
        $category_link = "/index.php?cat=$category->term_id"; // Get the category link
        $image_url = function_exists('pciwgas_pro_term_image') 
            ? pciwgas_pro_term_image($category->term_id, $atts['image_size']) 
            : ''; // Get category image with selected size

        // Calculate category image dimensions based on SVG width and image percentage
        $svg_width = esc_attr($atts['svg_width']); 
        $image_width = $svg_width * ($atts['image_percentage'] / 100); 

        // Build each category box
        $output .= '<div class="ccg-category-box" style="display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">';
        $output .= '<a href="' . esc_url($category_link) . '" style="display: block; position: relative; width: ' . $svg_width . 'px; height: ' . $svg_height . 'px; background: url(\'https://opensiddur.org/wp-content/uploads/2023/08/folder_icon_gray-1.svg\') no-repeat center center; background-size: cover;">';
        
        // If the category image exists, display it inside the SVG frame
        if ($image_url) {
            $output .= '<img class="ccg-custom-category-image" src="' . esc_url($image_url) . '" ';
            $output .= 'alt="' . esc_attr($category->name) . '" ';
            $output .= 'style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); ';
            $output .= 'width: ' . $image_width . 'px; height: auto; object-fit: contain;">';
        }

        $output .= '</a>'; // Close the anchor tag

        // Display the category title BELOW the SVG image, if enabled
        if ($atts['show_title'] !== 'false') {
            $output .= '<div class="ccg-category-title" style="margin-top: 10px; font-size: 1rem; text-align: center; width: ' . $svg_width . 'px;">';
            $output .= esc_html($category->name);
            $output .= '</div>'; // Close the title div
        }        

        $output .= '</div>'; // Close category-box
    }

    $output .= '</div>'; // End custom-category-grid

    return $output;
}
add_shortcode('custom_category_grid', 'custom_category_grid_shortcode');

// Enqueue CSS for the grid (if needed)
function custom_category_grid_styles() {
    wp_enqueue_style('custom-category-grid-css', plugin_dir_url(__FILE__) . 'css/custom-category-grid.css');
}
add_action('wp_enqueue_scripts', 'custom_category_grid_styles');
