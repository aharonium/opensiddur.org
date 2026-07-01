<?php
/**
 * Plugin Name: Custom Wordpress Debug Log Widget
 * Plugin URI: https://opensiddur.org
 * Description: Displays the tail of the WordPress debug log on the Dashboard.
 * Version: 1.0.0
 * Author: Aharon Varady (for the Open Siddur Project)
 * License: LGPL-3.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OpenSiddur_Debug_Log_Widget {

	/**
	 * Absolute path to the debug log.
	 */
	protected function get_log_file(): ?string {

		if ( ! defined( 'WP_DEBUG_LOG' ) ) {
			return null;
		}

		/*
		 * WP_DEBUG_LOG can be:
		 *   false
		 *   true
		 *   '/absolute/path/to/debug.log'
		 */

		if ( WP_DEBUG_LOG === true ) {
			return WP_CONTENT_DIR . '/debug.log';
		}

		if ( is_string( WP_DEBUG_LOG ) && WP_DEBUG_LOG !== '' ) {
			return WP_DEBUG_LOG;
		}

		return null;
	}

	/**
	 * Number of lines to display.
	 */
	protected const DEFAULT_LINES = 75;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'register_widget' ] );
	}

	/**
	 * Register dashboard widget.
	 */
	public function register_widget() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'opensiddur_debug_log',
			'Debug Log',
			[ $this, 'render_widget' ]
		);
	}

	/**
	 * Render dashboard widget.
	 */
	public function render_widget() {

		$log_file = $this->get_log_file();

		if ( empty( $log_file ) ) {
			echo '<div class="notice notice-warning inline">';
			echo '<p><strong>WP_DEBUG_LOG is not configured.</strong></p>';
			echo '</div>';
			return;
		}

		if ( ! file_exists( $log_file ) ) {
			echo '<div class="notice notice-error inline">';
			echo '<p><strong>The configured debug log does not exist.</strong></p>';
			echo '<p><code>' . esc_html( $log_file ) . '</code></p>';
			echo '</div>';
			return;
		}

		if ( ! is_readable( $log_file ) ) {
			echo '<div class="notice notice-error inline">';
			echo '<p><strong>The configured debug log is not readable.</strong></p>';
			echo '<p><code>' . esc_html( $log_file ) . '</code></p>';
			echo '</div>';
			return;
		}

		$lines = (int) apply_filters(
			'opensiddur_debug_log_widget_lines',
			self::DEFAULT_LINES
		);

		echo '<div id="opensiddur-debug-log-container">';

		$this->render_body( $log_file, $lines );

		echo '</div>';
	}

	/**
	 * Render the refreshable contents of the widget.
	 */
	protected function render_body( string $log_file, int $lines ): void {

		$this->render_metadata( $log_file, $lines );

		echo '<p>';

		echo '<button type="button"
			class="button button-secondary"
			id="opensiddur-debug-log-refresh"
			data-nonce="' . esc_attr( wp_create_nonce( 'opensiddur_debug_log' ) ) . '">
			Refresh
		</button>';

		echo '</p>';

		$this->render_log( $log_file, $lines );
	}

	/**
	 * Render metadata about the debug log.
	 */
	private function render_metadata( string $log_file, int $lines ): void {

		echo '<p>';
		
		echo '<strong>Resolved log path:</strong> <code>' .
			esc_html( $log_file ) .
			'</code><br>';

		echo '<strong>Size:</strong> ' .
			size_format( filesize( $log_file ) ) .
			'<br>';

		echo '<strong>Modified:</strong> ' .
			esc_html(
				wp_date(
					'Y-m-d H:i:s T',
					filemtime( $log_file )
				)
			) .
			'<br>';

		echo '<strong>Showing last ' .
			intval( $lines ) .
			' lines.</strong>';

		echo '</p>';
	}

	/**
	 * Render the tail of the debug log.
	 */
	protected function render_log( string $log_file, int $lines ): void {

		$contents = $this->tail( $log_file, $lines );

		echo '<pre class="opensiddur-debug-log">';

		if ( $contents === '' ) {

			echo esc_html__( 'Debug log is empty.', 'custom-debug-log-widget' );

		} else {

			echo esc_html( $contents );

		}

		echo '</pre>';
	}	

	/**
	 * Efficiently return the last N lines of a file.
	 *
	 * @param string $filename
	 * @param int    $lines
	 *
	 * @return string
	 */
	protected function tail( string $filename, int $lines = 75 ) : string {

		$fp = fopen( $filename, 'rb' );

		if ( ! $fp ) {
			return 'Unable to open log.';
		}

		$buffer = '';
		$chunk_size = 4096;

		fseek( $fp, 0, SEEK_END );
		$position = ftell( $fp );
		if ( $position === 0 ) {
			fclose( $fp );
			return '';
		}

		$newlines = 0;

		while ( $position > 0 && $newlines <= $lines ) {

			$read_size = min( $chunk_size, $position );
			$position -= $read_size;

			fseek( $fp, $position );

			$chunk = fread( $fp, $read_size );

			$buffer = $chunk . $buffer;

			$newlines = substr_count( $buffer, "\n" );
		}

		fclose( $fp );

		//$buffer = trim( $buffer );
		$buffer = rtrim( $buffer, "\r\n" );
		
		$lines_array = explode( "\n", $buffer );

		return implode(
			"\n",
			array_slice( $lines_array, -$lines )
		);
	}

}

add_action( 'admin_enqueue_scripts', function ( $hook ) {

	// Only load on dashboard
	if ( $hook !== 'index.php' ) {
		return;
	}

	$handle = 'opensiddur-debug-log-dashboard';
	
	// JS
	wp_enqueue_script(
		$handle,
		plugin_dir_url( __FILE__ ) . 'assets/dashboard.js',
		[],
		filemtime( __DIR__ . '/assets/dashboard.js' ),
		true
	);

	wp_enqueue_style(
		$handle,
		plugin_dir_url( __FILE__ ) . 'assets/dashboard.css',
		[],
		filemtime( __DIR__ . '/assets/dashboard.css' )
	);

	// Pass data to JS properly (THIS replaces wp_localize_script misuse)
	wp_add_inline_script(
		$handle,
		'window.OpenSiddurDebugLog = ' . wp_json_encode([
			'nonce'   => wp_create_nonce( 'opensiddur_debug_log' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		]) . ';',
		'before'
	);
});

require_once __DIR__ . '/includes/admin-ajax.php';
new OpenSiddur_Debug_Log_Widget();