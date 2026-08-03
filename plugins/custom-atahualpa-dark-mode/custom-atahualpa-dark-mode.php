<?php
/**
 * Plugin Name: Custom Atahualpa Dark Mode
 * Plugin URI: https://opensiddur.org/
 * Description: Adds a dark mode override for the Open Siddur Atahualpa theme.
 * Version: 0.3.1
 * Author: Aharon Varady (for the Open Siddur Project)
 * License: LGPL-3.0+
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Plugin constants.
 */
define( 'OSDM_VERSION', filemtime( __FILE__ ) );
define( 'OSDM_URL', plugin_dir_url( __FILE__ ) );
define( 'OSDM_PATH', plugin_dir_path( __FILE__ ) );


/**
 * Enqueue frontend assets.
 */
function osdm_enqueue_assets() {

	wp_enqueue_style(
		'osdm-dark-mode',
		OSDM_URL . 'css/dark-mode.css',
		array(),
		OSDM_VERSION
	);

	wp_enqueue_script(
		'osdm-dark-mode',
		OSDM_URL . 'js/dark-mode.js',
		array(),
		OSDM_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'osdm_enqueue_assets' );


/**
 * Restore the saved theme before the page is painted.
 */

function osdm_restore_theme() {
    ?>
    <!-- OSDM RESTORE -->
    <script>
    (function () {

        try {
            const followSystem =
                localStorage.getItem('opensiddur-system') !== 'false';

            const useSystem =
                followSystem === null ||
                followSystem === 'true';

            const theme = useSystem
                ? (
                    window.matchMedia('(prefers-color-scheme: dark)').matches
                        ? 'dark'
                        : 'light'
                  )
                : (
                    localStorage.getItem('opensiddur-theme') || 'light'
                  );

            document.documentElement.dataset.theme = theme;

        } catch (e) {}

    })();
    </script>
    <?php
}
add_action( 'wp_head', 'osdm_restore_theme', 1 );


/**
 * Output the dark mode toggle.
 */
function osdm_toggle_markup() {
	?>
	<button
    id="osdm-system"
    type="button"
    aria-label="Follow system theme"
    title="Follow system theme">
    🖥️
</button>

<button
    id="osdm-toggle"
    type="button"
    aria-label="Toggle Light/Dark theme"
    title="Toggle Light/Dark theme">
    🌙
</button>
	<?php
}
add_action( 'wp_footer', 'osdm_toggle_markup', 100 );
