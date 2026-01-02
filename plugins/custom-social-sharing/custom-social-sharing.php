<?php
/**
 * Plugin Name: Custom Social Sharing
 * Description: Dynamically adds social sharing buttons for Wordpress posts, pages, categories, tags, and author pages.
 * Version: 1.2
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/custom-social-sharing
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: social sharing, categories, tags, author pages, WordPress plugin
 * Text Domain: custom-social-sharing
 *
 * Note: I've chosen to use custom URLs for sharing links rather than permalinks, because the former are shorter and stable and the latter can be long and subject to change.
 * 
 * Acknowledgment: This plugin is derived in look-and-feel from Scriptless Social Sharing by Robin Cornett <https://github.com/robincornett/scriptless-social-sharing>. 
 * Special thanks to ChatGPT by OpenAI for considerable assistance and technical guidance during this plugin's development process.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Function to determine the page context and gather sharing information
function custom_social_sharing_get_context() {
    if ( is_singular() ) {
        $post_id = get_the_ID();
        return [
            'context' => 'post',
            'id' => $post_id,
            'url' => home_url( '/?p=' . $post_id ), // Static short link format for posts
            'title' => get_the_title(),
        ];
    } elseif ( is_category() ) {
        $category = get_queried_object();
        return [
            'context' => 'category',
            'id' => $category->term_id,
            'url' => home_url( '/index.php?cat=' . $category->term_id ), // Static query string for categories
            'title' => single_cat_title( '', false ),
            'message' => 'Resources shared for '
        ];
    } elseif ( is_tag() ) {
        $tag = get_queried_object();
        return [
            'context' => 'tag',
            'id' => $tag->term_id,
            'url' => home_url( '/index.php?tag_ID=' . $tag->term_id ), // Static query string for tags
            'title' => single_tag_title( '', false ),
            'message' => 'Resources tagged with '            
        ];
    } elseif ( is_author() ) {
        $author = get_queried_object();
        return [
            'context' => 'author',
            'id' => $author->ID,
            'url' => home_url( '/index.php?user_ID=' . $author->ID ), // Static query string for authors 
            'title' => get_the_author_meta( 'display_name', $author->ID ),
            'message' => 'Resources offered by '            
        ];
    } else {
        return [
            'context' => 'home',
            'id' => null,
            'url' => home_url(),
            'title' => get_bloginfo( 'name' ),
        ];
    }
}


// Function to generate sharing buttons
function custom_social_sharing_buttons( $platforms = [] ) {
    $context_data = custom_social_sharing_get_context();
    $url = urlencode( $context_data['url'] );
    
    // Combine message and title for sharing content
    $title = isset( $context_data['message'] ) 
        ? urlencode( $context_data['message'] . $context_data['title'] ) 
        : urlencode( $context_data['title'] );

    // Default list of platforms
    $available_platforms = [
        /* 'Pocket' => "https://getpocket.com/save?url={$url}&title={$title}", */
        'Reddit' => "https://www.reddit.com/submit?url={$url}&title={$title}",
        'Facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
        'Bluesky' => "https://bsky.app/intent/compose?text={$title}%20{$url}",
        'Telegram' => "https://telegram.me/share/url?url={$url}&text={$title}",
        'WhatsApp' => "https://api.whatsapp.com/send?text={$title}%20{$url}",
        'SMS' => "sms:?&body={$title}%20{$url}",
        'Email' => "mailto:?subject={$title}&body={$url}",
    ];

    // Filter platforms if specific ones are chosen
    if ( ! empty( $platforms ) ) {
        $available_platforms = array_intersect_key( $available_platforms, array_flip( $platforms ) );
    }

    // Generate HTML for buttons
    $output = '<div class="customsocialsharing">';
    $output .= '<div class="customsocialsharing-buttons">';
    foreach ( $available_platforms as $platform => $link ) {
        $output .= "<a href='$link' target='_blank' rel='noopener noreferrer' class='button {$platform} wp-dark-mode-ignore'><span class='screen-reader-text'>Share via $platform</span>$platform</a>";
    }

    $output .= '</div></div>';

    return $output;
}



// Shortcode for sharing buttons
function custom_social_sharing_shortcode( $atts ) {
    $atts = shortcode_atts( [ 'platforms' => '' ], $atts, 'social_sharing' );
    $platforms = array_filter( explode( ',', $atts['platforms'] ) ); // Convert comma-separated list to array
    return custom_social_sharing_buttons( $platforms );
}
add_shortcode( 'social_sharing', 'custom_social_sharing_shortcode' );

// Function for programmatic use
function display_custom_social_buttons( $platforms = [] ) {
    echo custom_social_sharing_buttons( $platforms );
}

// Enqueue custom CSS for the buttons
function custom_social_sharing_enqueue_styles() {
    wp_enqueue_style(
        'custom-social-sharing-styles',
        plugin_dir_url( __FILE__ ) . 'css/custom-social-sharing.css',
        [],
        '1.0',
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'custom_social_sharing_enqueue_styles' );
