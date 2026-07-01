<?php
/**
 * AJAX handler for the Debug Log widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OpenSiddur_Debug_Log_Ajax extends OpenSiddur_Debug_Log_Widget {

	public function __construct() {

		add_action(
			'wp_ajax_opensiddur_debug_log_refresh',
			[ $this, 'refresh_log' ]
		);

	}

	/**
	 * Return the latest log contents.
	 */
	public function refresh_log(): void {

		check_ajax_referer(
			'opensiddur_debug_log',
			'nonce'
		);

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error(
				[
					'message' => 'Permission denied.'
				],
				403
			);

		}

		$log_file = $this->get_log_file();

		if (
			empty( $log_file ) ||
			! file_exists( $log_file ) ||
			! is_readable( $log_file )
		) {

			wp_send_json_error(
				[
					'message' => 'Debug log unavailable.'
				]
			);

		}

		$lines = (int) apply_filters(
			'opensiddur_debug_log_widget_lines',
			self::DEFAULT_LINES
		);

		ob_start();

        $this->render_body(
            $log_file,
            $lines
        );
 
		wp_send_json_success(
			[
				'html' => ob_get_clean(),
			]
		);

	}

}

new OpenSiddur_Debug_Log_Ajax();