<?php
/**
 * Uninstall handler.
 *
 * Removes the per-user preference and the migration flag. Deactivating the
 * plugin leaves both alone — only deleting it clears them.
 *
 * @package Dark_Mode_For_WP_Dashboard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die();
}

delete_metadata( 'user', 0, 'dark_mode_preference', '', true );
delete_metadata( 'user', 0, 'dark_mode_dashboard', '', true );

// The migration flag is per site (see dark_mode_dashboard_migrate()), so on a
// network every site has its own copy, not just the one uninstalling.
if ( is_multisite() ) {
    foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $dark_mode_dashboard_site_id ) {
        switch_to_blog( $dark_mode_dashboard_site_id );
        delete_option( 'dark_mode_migration_done' );
        restore_current_blog();
    }
} else {
    delete_option( 'dark_mode_migration_done' );
}

delete_site_option( 'dark_mode_migration_done' );
