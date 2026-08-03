<?php
/**
 * Plugin Name: Custom Archive.org Link Wrapper
 * Description: Adds a "Read on Internet Archive" button above Advanced iFrame embeds that point to archive.org.
 * Version: 1.0.0
 * Author: Aharon Varady (for the Open Siddur Project)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load stylesheet.
 */
add_action( 'wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'os-archive-wrapper',
        plugin_dir_url( __FILE__ ) . 'css/custom-archivedotorg-link-wrapper.css',
        array(),
        '1.0.0'
    );

} );


/**
 * Wrap Advanced iFrame shortcodes pointing to archive.org.
 *
 * @param string $output Rendered shortcode HTML.
 * @param string $tag Shortcode tag.
 * @param array  $attr Shortcode attributes.
 * @param array  $m Regex matches.
 *
 * @return string
 */
function os_archive_iframe_wrapper( $output, $tag, $attr, $m ) {

    if ( 'advanced_iframe' !== $tag ) {
        return $output;
    }

    if ( empty( $attr['src'] ) ) {
        return $output;
    }

    $src = html_entity_decode( $attr['src'] );

    if ( stripos( $src, 'archive.org' ) === false ) {
        return $output;
    }

    $button = sprintf(
        '<div class="os-archive-toolbar">
            <a class="os-archive-button"
               href="%s"
               target="_blank"
               rel="noopener">
               Internet Archive ↗
            </a>
        </div>',
        esc_url( $src )
    );

    return sprintf(
        '<div class="os-archive-wrapper">%s%s</div>',
        $output,
        $button
    );
}

add_filter(
    'do_shortcode_tag',
    'os_archive_iframe_wrapper',
    10,
    4
);