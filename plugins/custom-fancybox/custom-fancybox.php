<?php
/**
 * Plugin Name: Custom Fancybox
 * Description: Adds Fancybox iframe modals for specific links, without interfering with existing Lightbox2 image handling.
 * Version: 1.3
 * Author: Aharon Varady
 */

add_action('wp_enqueue_scripts', 'iframe_lightbox_enqueue_assets');

function iframe_lightbox_enqueue_assets() {
    if (!is_admin()) {
        // Load Fancybox 5 (latest via jsDelivr)
        wp_enqueue_style(
            'iframe-fancybox',
            'https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css',
            array(),
            null
        );

        wp_enqueue_script(
            'iframe-fancybox',
            'https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.min.js',
            array(),
            null,
            true
        );

        // Initialize only for links with data-lightbox-type="iframe"
        wp_add_inline_script('iframe-fancybox', <<<JS
        document.addEventListener('DOMContentLoaded', function () {
            Fancybox.bind('a[data-lightbox-type="iframe"]', {
                type: 'iframe'
            });
        });
        JS
        );
    }
}
