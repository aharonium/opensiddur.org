<?php
/**
 * File: ccg-admin.php
 * Version: 1.2.0
 *
 * Site-wide settings page.
 *
 * Controls display toggles consumed by ccg-frontend.php's banner
 * rendering. All per-post license/category data now lives in ACF's
 * own open_content_license field, read directly at display time — see
 * ccg-acf-mapping.php — so this page has no per-post data of its own
 * to manage, only site-wide display behavior. The original settings
 * page had no CSRF nonce at all on its save handler — fixed here.
 */

require_once( plugin_dir_path( __FILE__ ) . 'ccg-fields.php' );

add_action( 'admin_menu', 'ccg_menu', 0 );

function ccg_menu() {
	add_options_page(
		__( 'Open Content License & PD Status Settings', 'ccg-domain' ),
		'CCG Settings',
		'manage_options',
		'ccg-settings',
		'ccg_settings_page'
	);
}

function ccg_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['ccg_settings_submit'] ) ) {

		check_admin_referer( 'ccg_settings_save', 'ccg_settings_nonce' );

		$settings = array(
			'show_title'                => ! empty( $_POST['ccg_show_title'] ),
			'show_pd_estimate_sentence' => ! empty( $_POST['ccg_show_pd_estimate_sentence'] ),
			'show_estimated_pd_date'    => ! empty( $_POST['ccg_show_estimated_pd_date'] ),
			'show_contributor_pd_note'  => ! empty( $_POST['ccg_show_contributor_pd_note'] ),
		);
		update_option( 'ccg_settings', $settings );

		echo '<div id="message" class="updated"><p>' . esc_html__( 'Settings saved.', 'ccg-domain' ) . '</p></div>';
	}

	$settings = ccg_get_settings();
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Open Content License & Public Domain Status — Settings', 'ccg-domain' ); ?></h2>
		<p><?php esc_html_e( 'Per-post copyright/Public Domain status and contributor licensing are set on each post\'s edit screen. The settings below control site-wide display behavior only.', 'ccg-domain' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'ccg_settings_save', 'ccg_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Show post title', 'ccg-domain' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ccg_show_title" value="1" <?php checked( $settings['show_title'] ); ?> />
							<?php esc_html_e( 'Use the post\'s title to lead the banner, instead of the generic "This work is shared through the Open Siddur Project".', 'ccg-domain' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show source content PD-estimate line', 'ccg-domain' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ccg_show_pd_estimate_sentence" value="1" <?php checked( $settings['show_pd_estimate_sentence'] ); ?> />
							<?php esc_html_e( 'For posts shared under a Fair Use or Reproduction Right exception, include the "The source content displayed is estimated to have entered the Public Domain..." sentence when a date estimate is available.', 'ccg-domain' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show estimated PD/expiration dates', 'ccg-domain' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ccg_show_estimated_pd_date" value="1" <?php checked( $settings['show_estimated_pd_date'] ); ?> />
							<?php esc_html_e( 'Include the computed year(s) within that sentence. Leave unchecked while the underlying dates aren\'t yet reliable — the rest of the sentence still displays either way (when the setting above is on).', 'ccg-domain' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show contributor-work PD-by-age note', 'ccg-domain' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ccg_show_contributor_pd_note" value="1" <?php checked( $settings['show_contributor_pd_note'] ); ?> />
							<?php esc_html_e( 'Include the third banner sentence, estimating when the post\'s own contributed text (not the exhibited source material) enters the Public Domain, 95 years after its publish date.', 'ccg-domain' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<p><input type="submit" name="ccg_settings_submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'ccg-domain' ); ?>" /></p>
		</form>
	</div>
	<?php
}