<?php
/*
Plugin Name: Custom Contributor Fields
Plugin URI:  https://github.com/aharonium/open-content-license-generator-plugin
Description: Adds custom fields to user profile edit screens for data relevant to contributor/author record-keeping (e.g. death date, for Public Domain calculations). Built as a generic field registry so new fields can be added without duplicating render/save logic. Stores as standard WordPress user meta — no ACF dependency, works with ACF Free or no ACF at all.
Version:     1.1.0
Author:      Aharon Varady (for the Open Siddur Project)
Author URI:  http://aharon.varady.net/omphalos
License:     LGPL3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html
*/

/**
 * File: custom-contributor-fields.php
 * Version: 1.1.0
 *
 * Renders and saves a set of fields on both the "edit your own
 * profile" screen (profile.php) and the "edit another user" screen
 * (user-edit.php) — WordPress fires different hooks for each, so both
 * are wired to the same render/save functions. Driven entirely by the
 * registry in ccf_get_field_definitions() below — adding a new field
 * means adding one entry there; the render/save functions loop over
 * the registry generically and don't need to change.
 *
 * All fields are plain text inputs stored as standard WordPress user
 * meta (get_user_meta() / update_user_meta()), under the meta key
 * given as each entry's array key — no ACF dependency, so this works
 * identically whether or not ACF is active, and needs no ACF Pro
 * features (ACF Free doesn't support User-location field groups,
 * which is why this exists as its own small plugin instead).
 *
 * Any code (CCG or otherwise) can read a field's value directly with:
 *   get_user_meta( $user_id, '<field_key>', true )
 * — no dependency on this plugin's own functions required, since
 * every field is just standard WordPress user meta under a known key.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access.

add_action( 'show_user_profile', 'ccf_render_fields' );
add_action( 'edit_user_profile', 'ccf_render_fields' );
add_action( 'personal_options_update', 'ccf_save_fields' );
add_action( 'edit_user_profile_update', 'ccf_save_fields' );

/**
 * Registry of fields shown on both user-edit screens. Add a
 * new field by adding an entry here — nothing else needs to change.
 *
 *   'pattern'     — regex the submitted value must match to be saved
 *                   (fail-closed: a non-matching value is silently
 *                   dropped rather than stored malformed); omit (null)
 *                   to accept any non-empty string as-is.
 *   'label'       — field label shown on the edit screen
 *   'placeholder' — example text shown in the empty input
 *   'description' — help text shown below the input
 */
function ccf_get_field_definitions() {
	return array(
		'death_date' => array(
			'label'       => __( 'Death Date', 'ccg-domain' ),
			'placeholder' => 'e.g. 1972-00-00',
			'description' => __( 'Format: YYYY-MM-DD, using 00 for an unknown month or day (e.g. "1972-00-00" for a known year only). Leading "-" for BCE years. Leave blank if unknown. Used for the 70-years-after-death Public Domain calculation.', 'ccg-domain' ),
			// Same approximate-date shape as the project's other date
			// fields (date_src_start, etc.): YYYY-MM-DD, optional
			// leading '-' for BCE, '00' allowed for unknown month/day.
			'pattern'     => '/^-?\d{1,4}-\d{2}-\d{2}$/',
		),
		// Add future fields here, e.g.:
		// 'birth_date' => array(
		// 	'label'       => __( 'Birth Date', 'ccg-domain' ),
		// 	'placeholder' => 'e.g. 1900-00-00',
		// 	'description' => __( 'Same YYYY-MM-DD format as Death Date.', 'ccg-domain' ),
		// 	'pattern'     => '/^-?\d{1,4}-\d{2}-\d{2}$/',
		// ),
	);
}

/**
 * Render every registered field as a row in its own form-table section
 * — on both the "edit your own profile" and "edit another user"
 * screens (see the two add_action() calls above). Only rendered for
 * users who can actually save it (current_user_can('edit_users')) —
 * showing an editable-looking field to someone who can't save it would
 * just silently fail on submit, which is worse than not showing it.
 */
function ccf_render_fields( $user ) {

	if ( ! current_user_can( 'edit_users' ) ) return;

	$fields = ccf_get_field_definitions();
	if ( empty( $fields ) ) return;
	?>
	<h2><?php esc_html_e( 'Contributor Record', 'ccg-domain' ); ?></h2>
	<table class="form-table">
		<?php foreach ( $fields as $key => $def ) :
			$value = get_user_meta( $user->ID, $key, true );
			?>
			<tr>
				<th><label for="ccf_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def['label'] ); ?></label></th>
				<td>
					<input type="text" name="ccf_<?php echo esc_attr( $key ); ?>" id="ccf_<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						class="regular-text" placeholder="<?php echo esc_attr( $def['placeholder'] ); ?>" />
					<?php if ( ! empty( $def['description'] ) ) : ?>
						<p class="description"><?php echo esc_html( $def['description'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php wp_nonce_field( 'ccf_save_fields', 'ccf_fields_nonce' ); ?>
	<?php
}

/**
 * Save every registered field, each validated against its own
 * 'pattern' (if set). A blank submission deletes the meta key
 * entirely; a non-matching non-blank value is silently ignored (the
 * field just shows its previous value again on reload) rather than
 * stored malformed — same fail-closed convention as the rest of the
 * project's sanitizers (see ccg_sanitize_year() in ccg-fields.php).
 */
function ccf_save_fields( $user_id ) {

	if ( ! current_user_can( 'edit_users' ) ) return;
	if ( ! isset( $_POST['ccf_fields_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['ccf_fields_nonce'], 'ccf_save_fields' ) ) return;

	foreach ( ccf_get_field_definitions() as $key => $def ) {

		$post_key = 'ccf_' . $key;
		if ( ! isset( $_POST[ $post_key ] ) ) continue;

		$raw = trim( wp_unslash( $_POST[ $post_key ] ) );

		if ( $raw === '' ) {
			delete_user_meta( $user_id, $key );
			continue;
		}

		if ( empty( $def['pattern'] ) || preg_match( $def['pattern'], $raw ) ) {
			update_user_meta( $user_id, $key, $raw );
		}
	}
}