<?php
/**
 * Plugin Name: Custom Lightbox2 Integration
 * Description: Adds Lightbox2 support for linked images in posts, including optional captions from alt attributes.
 * Version: 1.0
 * Author: Aharon Varady
 */

add_action('wp_enqueue_scripts', 'custom_lightbox2_enqueue_scripts');

function custom_lightbox2_enqueue_scripts() {
    // Load Lightbox2 CSS and JS from CDN
    wp_enqueue_style(
        'lightbox2',
        'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css'
    );

    wp_enqueue_script(
        'lightbox2',
        'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js',
        array('jquery'),
        null,
        true
    );

    // Add inline script to automatically add data-lightbox and optional captions
    wp_add_inline_script('lightbox2', <<<JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a').forEach(function(a) {
            if (!a.href.match(/\\.(jpe?g|png|gif)$/i)) return;
            if (!a.querySelector('img')) return;

            a.setAttribute('data-lightbox', 'post-images');
            var caption = null;

            var figure = a.closest('figure');
            if (figure) {
                var figcaption = figure.querySelector('figcaption');
                if (figcaption) caption = figcaption.innerText.trim();
            }

            if (!caption) {
                var captionNode = a.parentElement.querySelector('.wp-caption-text');
                if (captionNode) caption = captionNode.innerText.trim();
            }

            if (!caption) {
                var alt = a.querySelector('img').getAttribute('alt');
                if (alt) caption = alt.trim();
            }

            if (caption) a.setAttribute('data-title', caption);
        });
    });
    JS
    );
}