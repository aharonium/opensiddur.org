<?php
/**
 * File: ccg-post-options.php
 * Version: 1.0.0
 *
 * Admin meta box: Source PD Status + Contributor License.
 *
 * Stage 1 of the plugin rebuild described in ccg-redesign-spec.md. This
 * file replaces the original single-license meta box with two sections:
 *
 *   1. Source PD Status  — the copyright/PD status of the *exhibited
 *      source material* (often historical, independent of who's editing
 *      the post today). See spec §2a.
 *   2. Contributor License — the sharing terms under which the *post's
 *      own contributor* (translator/author of new content) releases
 *      their new work. See spec §2b.
 *
 * NOTE: this stage only changes what's collected and stored. The public
 * banner (ccg-frontend.php) still reads the old _ccg_options meta key
 * until Stage 3 lands, so front-end display is unaffected by this file.
 * New posts edited after this stage will have both the old and new meta
 * keys; migrating old data into the new structure is Stage 5.
 */

require_once( plugin_dir_path( __FILE__ ) . 'ccg-fields.php' );

add_action( 'add_meta_boxes', 'ccg_add_meta_box' );
add_action( 'save_post', 'ccg_save_post_data' );

/**
 * Register the meta box on every public, non-builtin post type, plus
 * 'post' itself — same post-type discovery logic as the original plugin.
 * Uses the add_meta_boxes hook (the correct hook for this), not
 * admin_init as the original did.
 */
function ccg_add_meta_box() {

	$args     = array(
		'public'   => true,
		'_builtin' => false,
	);
	$post_types = get_post_types( $args, 'names', 'and' );
	$post_types[] = 'post';

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'ccg_metabox',
			__( 'Open Content License & Public Domain Status', 'ccg-domain' ),
			'ccg_render_meta_box',
			$post_type
		);
	}
}

/**
 * Render both sections of the meta box.
 */
function ccg_render_meta_box( $post ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'ccg_nonce' );

	$source      = ccg_get_source_status( $post->ID );
	$contributor = ccg_get_contributor_license( $post->ID );

	echo '<style>
		.ccg-section { margin-bottom: 1.5em; }
		.ccg-section h4 { margin-bottom: 0.25em; }
		.ccg-section p.description { margin-top: 0; }
		.ccg-layer-table { width: 100%; border-collapse: collapse; }
		.ccg-layer-table th, .ccg-layer-table td { text-align: left; padding: 4px 8px 4px 0; vertical-align: top; }
		.ccg-layer-table th { font-weight: normal; color: #666; width: 33%; }
	</style>';

	ccg_render_source_status_section( $source );
	ccg_render_contributor_license_section( $contributor );
}

/**
 * Section 1: Source PD Status. Category is a single value; renewal status,
 * notice/registration status, author death year, and country of
 * publication are each split into src/trans pairs, since the source work
 * and its translation are frequently distinct copyrightable works with
 * independent authorship, dates, and countries. See spec §2a.
 */
function ccg_render_source_status_section( $source ) {

	$category_options = ccg_source_category_options();
	$renewal_options   = ccg_renewal_status_options();
	$notice_options    = ccg_notice_registration_options();
	$country_options   = ccg_country_options();
	?>
	<div class="ccg-section">
		<h4><?php esc_html_e( 'Source PD Status', 'ccg-domain' ); ?></h4>
		<p class="description">
			<?php esc_html_e( 'Describes the copyright/Public Domain status of the exhibited source material itself — independent of who is editing this post. Leave fields "Unknown" if not yet researched; the banner shown to readers omits any claim it can\'t support.', 'ccg-domain' ); ?>
		</p>

		<p>
			<label for="ccg_category"><strong><?php esc_html_e( 'Category', 'ccg-domain' ); ?></strong></label><br />
			<select name="ccg_category" id="ccg_category">
				<?php foreach ( $category_options as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['category'], $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<table class="ccg-layer-table">
			<tr>
				<th></th>
				<th><?php esc_html_e( 'Source', 'ccg-domain' ); ?></th>
				<th><?php esc_html_e( 'Translation', 'ccg-domain' ); ?></th>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Renewal status (1931–1964 works)', 'ccg-domain' ); ?></td>
				<td>
					<select name="ccg_renewal_status_src">
						<?php foreach ( $renewal_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['renewal_status_src'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<select name="ccg_renewal_status_trans">
						<?php foreach ( $renewal_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['renewal_status_trans'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Notice/registration status (1964–1989 works)', 'ccg-domain' ); ?></td>
				<td>
					<select name="ccg_notice_status_src">
						<?php foreach ( $notice_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['notice_status_src'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<select name="ccg_notice_status_trans">
						<?php foreach ( $notice_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['notice_status_trans'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( "Author/translator's death year (if known)", 'ccg-domain' ); ?></td>
				<td><input type="text" size="8" name="ccg_death_year_src" value="<?php echo esc_attr( $source['death_year_src'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1972', 'ccg-domain' ); ?>" /></td>
				<td><input type="text" size="8" name="ccg_death_year_trans" value="<?php echo esc_attr( $source['death_year_trans'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1972', 'ccg-domain' ); ?>" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Country of publication', 'ccg-domain' ); ?></td>
				<td>
					<select name="ccg_country_src">
						<?php foreach ( $country_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['country_src'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<select name="ccg_country_trans">
						<?php foreach ( $country_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source['country_trans'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Section 2: Contributor License. Single contributor/license per post
 * (known limitation — see spec §2b). Only relevant for the post's
 * actual author(s)/translator(s), not a volunteer credited for
 * scanning, digital imaging, or transcription. On this project, a
 * credited name with a role in parentheses after it (e.g. "Jane Doe
 * (transcription)") identifies that kind of volunteer, not an author —
 * the actual author/translator is a credited name with no such suffix.
 * Per your clarification, the field's name/URL are never auto-filled
 * from the currently logged-in editor (see ccg_get_contributor_license()
 * in ccg-fields.php) — that editor is very often the transcription/
 * imaging volunteer rather than the author, so defaulting from them
 * silently misattributed the license.
 */
function ccg_render_contributor_license_section( $contributor ) {

	$license_options = ccg_contributor_license_options();
	?>
	<div class="ccg-section">
		<h4><?php esc_html_e( 'Contributor License', 'ccg-domain' ); ?></h4>
		<p class="description">
			<?php esc_html_e( 'The terms under which this post\'s actual author or translator shares their new work — not a volunteer credited only for scanning, digital imaging, or transcription. On this project, a credited name followed by a role in parentheses (e.g. "Jane Doe (transcription)") identifies that kind of volunteer, not an author; enter the name of the credited contributor with no such suffix.', 'ccg-domain' ); ?>
		</p>
		<p>
			<label for="ccg_contributor_license"><strong><?php esc_html_e( 'License', 'ccg-domain' ); ?></strong></label><br />
			<select name="ccg_contributor_license" id="ccg_contributor_license">
				<?php foreach ( $license_options as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $contributor['license'], $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="ccg_contributor_name"><?php esc_html_e( 'Contributor name', 'ccg-domain' ); ?></label><br />
			<input type="text" class="widefat" name="ccg_contributor_name" id="ccg_contributor_name" value="<?php echo esc_attr( $contributor['name'] ); ?>" />
		</p>
		<p>
			<label for="ccg_contributor_url"><?php esc_html_e( 'Contributor URL', 'ccg-domain' ); ?></label><br />
			<input type="url" class="widefat" name="ccg_contributor_url" id="ccg_contributor_url" value="<?php echo esc_attr( $contributor['url'] ); ?>" />
		</p>
		<p>
			<label for="ccg_parent_resource_id"><?php esc_html_e( 'Parent/compiled work (post ID)', 'ccg-domain' ); ?></label><br />
			<input type="number" min="1" step="1" name="ccg_parent_resource_id" id="ccg_parent_resource_id" value="<?php echo esc_attr( $contributor['parent_resource_id'] ); ?>" />
			<span class="description"><?php esc_html_e( 'If this post (a microform) was extracted from a compiled macroform post — a manuscript or book — enter that post\'s ID here.', 'ccg-domain' ); ?></span>
		</p>
		<p class="description">
			<?php esc_html_e( 'If more than one contributor\'s new work is being shared on this post under different licenses, use the most restrictive of the licenses involved (CC BY-SA is more restrictive than CC BY; either is more restrictive than CC0). Works shared under Fair Use remain under Fair Use regardless. Note in the post that such combined derivative works are not necessarily endorsed by the original creator or copyright steward.', 'ccg-domain' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Save handler for both sections. Every value is whitelisted against the
 * option sets in ccg-fields.php before being stored — nothing from $_POST
 * reaches post meta unvalidated.
 */
function ccg_save_post_data( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['ccg_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['ccg_nonce'], plugin_basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$renewal_options = ccg_renewal_status_options();
	$notice_options  = ccg_notice_registration_options();
	$country_options = ccg_country_options();

	$source_status = array(
		'category'             => ccg_sanitize_choice( $_POST['ccg_category'] ?? '', ccg_source_category_options(), 'unknown' ),
		'renewal_status_src'   => ccg_sanitize_choice( $_POST['ccg_renewal_status_src'] ?? '', $renewal_options, 'unknown' ),
		'renewal_status_trans' => ccg_sanitize_choice( $_POST['ccg_renewal_status_trans'] ?? '', $renewal_options, 'unknown' ),
		'notice_status_src'    => ccg_sanitize_choice( $_POST['ccg_notice_status_src'] ?? '', $notice_options, 'unknown' ),
		'notice_status_trans'  => ccg_sanitize_choice( $_POST['ccg_notice_status_trans'] ?? '', $notice_options, 'unknown' ),
		'death_year_src'       => ccg_sanitize_year( $_POST['ccg_death_year_src'] ?? '' ),
		'death_year_trans'     => ccg_sanitize_year( $_POST['ccg_death_year_trans'] ?? '' ),
		'country_src'          => ccg_sanitize_choice( $_POST['ccg_country_src'] ?? '', $country_options, '' ),
		'country_trans'        => ccg_sanitize_choice( $_POST['ccg_country_trans'] ?? '', $country_options, '' ),
	);
	update_post_meta( $post_id, '_ccg_source_status', $source_status );

	$contributor = array(
		'license'            => ccg_sanitize_choice( $_POST['ccg_contributor_license'] ?? '', ccg_contributor_license_options(), '' ),
		'name'               => isset( $_POST['ccg_contributor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ccg_contributor_name'] ) ) : '',
		'url'                => isset( $_POST['ccg_contributor_url'] ) ? esc_url_raw( wp_unslash( $_POST['ccg_contributor_url'] ) ) : '',
		'parent_resource_id' => ccg_sanitize_parent_resource_id( $_POST['ccg_parent_resource_id'] ?? '' ),
	);
	update_post_meta( $post_id, '_ccg_contributor', $contributor );
}