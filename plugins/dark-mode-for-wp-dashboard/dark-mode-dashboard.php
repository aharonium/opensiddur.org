<?php
/**
 * Plugin Name: Dark Mode for WP Dashboard
 * Plugin URI: https://wordpress.org/plugins/dark-mode-for-wp-dashboard/
 * Description: Enable dark mode for the WordPress dashboard
 * Author: Naiche
 * Author URI: https://profiles.wordpress.org/naiches/
 * Text Domain: dark-mode-for-wp-dashboard
 * Version: 1.3.11
 * Tested up to: 7.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

define( 'DARK_MODE_DASHBOARD_VERSION', '1.3.11' );
define( 'DARK_MODE_DASHBOARD_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
define( 'DARK_MODE_DASHBOARD_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Design tokens that PHP needs to emit inline.
 *
 * The SCSS token file (assets/scss/tokens/_dark.scss) is the source of truth
 * for the palette; this is the short list the anti-flash and editor-canvas
 * inline CSS reference, kept in one place so no hex ever gets typed twice.
 *
 * @return array<string,string>
 */
function dark_mode_dashboard_tokens() {
    return array(
        'bg-base'        => '#1a1e26',
        'bg-surface'     => '#252a34',
        'bg-input'       => '#1e232c',
        'text-primary'   => '#eceff4',
        'text-secondary' => '#a0aab6',
        'border'         => '#3b4252',
        'accent'         => '#6b9cff',
    );
}

/**
 * Get the current user's dark mode preference.
 *
 * The single source of truth. Everything else — the body class, asset
 * loading, the editor integrations and the profile form — derives from this
 * one function, so the dark_mode_dashboard_default_preference filter is
 * honoured everywhere rather than in one place out of three.
 *
 * @param int|null $user_id User to resolve for. Defaults to the current user.
 * @return string 'enabled', 'disabled' or 'auto'.
 */
function dark_mode_dashboard_get_preference( $user_id = null ) {
    $allowed = array( 'enabled', 'disabled', 'auto' );
    $user_id = null === $user_id ? get_current_user_id() : (int) $user_id;

    // No user means a visitor (or a request nobody is logged in to). Dark mode
    // is a dashboard preference; it must never decide how the public site
    // looks. Resolving user 0 through the default below handed every logged-out
    // visitor 'enabled', and with it a dark front-end TinyMCE.
    if ( ! $user_id ) {
        return 'disabled';
    }

    $pref = get_user_meta( $user_id, 'dark_mode_preference', true );

    if ( in_array( $pref, $allowed, true ) ) {
        return $pref;
    }

    // A user outside the current migration batch still needs the appearance
    // they chose under the old flag on their first admin request.
    if ( ! metadata_exists( 'user', $user_id, 'dark_mode_preference' )
        && metadata_exists( 'user', $user_id, 'dark_mode_dashboard' ) ) {
        return '1' === (string) get_user_meta( $user_id, 'dark_mode_dashboard', true ) ? 'disabled' : 'enabled';
    }

    /**
     * Filters the preference applied to users who never chose one.
     *
     * @param string $preference One of 'enabled', 'disabled' or 'auto'.
     */
    $default = apply_filters( 'dark_mode_dashboard_default_preference', 'enabled' );

    return in_array( $default, $allowed, true ) ? $default : 'enabled';
}

/**
 * Whether dark styling may apply for the current user.
 *
 * True for 'enabled' and for 'auto'. Under 'auto' the operating system has the
 * final say — the media query in assets/css/dark-mode.css and the inline
 * resolver script decide whether the page actually renders dark — but the
 * assets and editor integrations still have to be in place for it to be able
 * to, which is what this answers.
 *
 * @return bool
 */
function dark_mode_dashboard_is_active() {
    return 'disabled' !== dark_mode_dashboard_get_preference();
}

/**
 * Optional per-plugin and per-theme stylesheets.
 *
 * Each entry compiles from assets/scss/plugins-entry/<slug>.scss to
 * assets/css/plugins/<slug>.css and is enqueued only when the software it
 * targets is actually running. Detection is by constant, class or function
 * first (survives a renamed plugin folder) with the plugin file as a fallback.
 *
 * @return array<string,array<string,mixed>>
 */
function dark_mode_dashboard_style_modules() {
    $modules = array(
        'acf'                   => array( 'constants' => array( 'ACF_VERSION' ), 'classes' => array( 'ACF' ) ),
        'aioseo'                => array( 'constants' => array( 'AIOSEO_VERSION' ), 'functions' => array( 'aioseo' ) ),
        'better-search-replace' => array( 'classes' => array( 'Better_Search_Replace' ), 'plugin' => 'better-search-replace/better-search-replace.php' ),
        'code-snippets'         => array( 'constants' => array( 'CODE_SNIPPETS_VERSION' ), 'plugin' => 'code-snippets/code-snippets.php' ),
        'jetpack'               => array( 'constants' => array( 'JETPACK__VERSION' ) ),
        'kadence-blocks'        => array( 'constants' => array( 'KADENCE_BLOCKS_VERSION' ) ),
        'smartcrawl'            => array( 'constants' => array( 'SMARTCRAWL_VERSION' ), 'plugin' => 'smartcrawl-seo/wpmu-dev-seo.php' ),
        'smush'                 => array( 'constants' => array( 'WP_SMUSH_VERSION' ), 'plugin' => 'wp-smushit/wp-smush.php' ),
        'the-seo-framework'     => array( 'constants' => array( 'THE_SEO_FRAMEWORK_VERSION' ), 'functions' => array( 'tsf' ) ),
        'woocommerce'           => array( 'classes' => array( 'WooCommerce' ) ),
        'wp-nested-pages'       => array( 'constants' => array( 'NESTEDPAGES_VERSION' ), 'plugin' => 'wp-nested-pages/nestedpages.php' ),
        'wpcode'                => array( 'constants' => array( 'WPCODE_VERSION' ), 'plugin' => 'insert-headers-and-footers/ihaf.php' ),
        'yoast-seo'             => array( 'constants' => array( 'WPSEO_VERSION' ) ),
        'zamok'                 => array( 'constants' => array( 'ZAMOK_VERSION' ) ),
        'cocoon'                => array( 'themes' => array( 'cocoon-master', 'cocoon-child-master' ) ),
    );

    /**
     * Filters the optional stylesheet map.
     *
     * @param array $modules Slug => detection rules.
     */
    return apply_filters( 'dark_mode_dashboard_style_modules', $modules );
}

/**
 * Decide whether one optional stylesheet's target is present.
 *
 * @param array $rules Detection rules for a single module.
 * @return bool
 */
function dark_mode_dashboard_module_is_active( $rules ) {
    foreach ( (array) ( isset( $rules['constants'] ) ? $rules['constants'] : array() ) as $constant ) {
        if ( defined( $constant ) ) {
            return true;
        }
    }

    foreach ( (array) ( isset( $rules['classes'] ) ? $rules['classes'] : array() ) as $class_name ) {
        if ( class_exists( $class_name ) ) {
            return true;
        }
    }

    foreach ( (array) ( isset( $rules['functions'] ) ? $rules['functions'] : array() ) as $function_name ) {
        if ( function_exists( $function_name ) ) {
            return true;
        }
    }

    foreach ( (array) ( isset( $rules['themes'] ) ? $rules['themes'] : array() ) as $theme_slug ) {
        if ( get_template() === $theme_slug || get_stylesheet() === $theme_slug ) {
            return true;
        }
    }

    if ( ! empty( $rules['plugin'] ) && function_exists( 'is_plugin_active' ) && is_plugin_active( $rules['plugin'] ) ) {
        return true;
    }

    return false;
}

/**
 * Register one of our stylesheets, with its RTL sibling when one exists.
 *
 * @param string $handle   Style handle.
 * @param string $rel_path Path relative to the plugin root.
 * @return bool True when the file exists and was registered.
 */
function dark_mode_dashboard_register_style( $handle, $rel_path ) {
    if ( ! file_exists( DARK_MODE_DASHBOARD_DIR . $rel_path ) ) {
        return false;
    }

    wp_register_style(
        $handle,
        DARK_MODE_DASHBOARD_PLUGIN_PATH . $rel_path,
        array(),
        DARK_MODE_DASHBOARD_VERSION
    );

    // npm run build emits a flipped sibling next to every stylesheet. Only
    // advertise it when it is really on disk: core does not check, and a
    // missing RTL file is served as WordPress's themed 404 page.
    if ( file_exists( DARK_MODE_DASHBOARD_DIR . str_replace( '.css', '-rtl.css', $rel_path ) ) ) {
        wp_style_add_data( $handle, 'rtl', 'replace' );
    }

    return true;
}

/**
 * Migrate legacy dark_mode_dashboard user meta to dark_mode_preference.
 *
 * The old flag was inverted: '1' meant disabled, empty meant enabled.
 *
 * Runs in batches of 200 IDs per admin request so a site with tens of
 * thousands of legacy rows cannot exhaust memory or time out, and only clears
 * the old key once the new one has been read back.
 *
 * The done-flag is per site, not network-wide: get_users() only ever searches
 * the current site, so a network flag would let one site with no legacy rows
 * mark every other site complete and strand their users on the old key.
 */
function dark_mode_dashboard_migrate() {
    if ( get_option( 'dark_mode_migration_done' ) ) {
        return;
    }

    $user_ids = get_users(
        array(
            'meta_key' => 'dark_mode_dashboard', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-time migration, batched, flags itself done.
            'fields'   => 'ID',
            'number'   => 200,
        )
    );

    if ( empty( $user_ids ) ) {
        update_option( 'dark_mode_migration_done', true );
        return;
    }

    foreach ( $user_ids as $user_id ) {
        // Never overwrite a choice already made in the current format. Across
        // more than one batch a user we have not reached yet can save a new
        // preference; their stale legacy row must be dropped, not replayed
        // over the choice they just made.
        if ( '' !== (string) get_user_meta( $user_id, 'dark_mode_preference', true ) ) {
            delete_user_meta( $user_id, 'dark_mode_dashboard' );
            continue;
        }

        $old_value  = get_user_meta( $user_id, 'dark_mode_dashboard', true );
        $preference = ( '1' === (string) $old_value ) ? 'disabled' : 'enabled';

        // Only create a preference when the key is still absent. A choice
        // saved after the check above must not be overwritten by migration.
        $added = add_user_meta( $user_id, 'dark_mode_preference', $preference, true );

        // Only drop the legacy key once the new one is readable. A failed
        // write must leave the row to be retried on the next request, not
        // silently reset the user to the default.
        if ( $added && $preference === get_user_meta( $user_id, 'dark_mode_preference', true ) ) {
            delete_user_meta( $user_id, 'dark_mode_dashboard' );
        }
    }
}
add_action( 'admin_init', 'dark_mode_dashboard_migrate' );

/**
 * Whether the current admin screen hosts a block or classic editor canvas.
 *
 * Used to keep the canvas-sync script and the editor stylesheet off the 95
 * percent of admin screens that can never contain one.
 *
 * @return bool
 */
function dark_mode_dashboard_is_editor_screen() {
    global $pagenow;

    if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'site-editor.php', 'widgets.php' ), true ) ) {
        return true;
    }

    if ( function_exists( 'get_current_screen' ) ) {
        $screen = get_current_screen();

        if ( $screen && ( $screen->is_block_editor() || 'post' === $screen->base ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Whether this screen can ever receive the dark mode body class.
 *
 * wp-admin/customize.php builds its own $body_class string and never applies
 * the admin_body_class filter (nor does it add wp-admin), so none of our
 * body.wp-admin.dark-mode rules can match there. Enqueuing the stylesheets on
 * that screen shipped ~160 KB of CSS that was inert by construction.
 *
 * @return bool
 */
function dark_mode_dashboard_screen_supports_body_class() {
    global $pagenow;

    /**
     * Filters the screens the dark mode stylesheets are delivered to.
     *
     * @param bool   $supported Whether the current screen gets the body class.
     * @param string $pagenow   Current admin page file.
     */
    return (bool) apply_filters(
        'dark_mode_dashboard_screen_supported',
        'customize.php' !== $pagenow,
        (string) $pagenow
    );
}

/**
 * Register and enqueue the admin stylesheets.
 *
 * Three tiers:
 *
 *   toggle.css   always — 600 bytes of admin-bar switch styling.
 *   dark-mode.css + the optional per-plugin sheets — enqueued for 'enabled'
 *                  and 'auto', merely REGISTERED for 'disabled' so the toggle
 *                  can fetch them on first click instead of every user paying
 *                  for them on every page.
 *
 * Optional sheets are gated on the plugin or theme they target actually being
 * active, so a WooCommerce-only site no longer downloads rules for eleven
 * other plugins.
 */
function dark_mode_dashboard_enqueue_styles() {
    if ( ! dark_mode_dashboard_screen_supports_body_class() ) {
        return;
    }

    $enqueue = dark_mode_dashboard_is_active();

    if ( dark_mode_dashboard_register_style( 'dark-mode-dashboard-toggle', 'assets/css/toggle.css' ) ) {
        wp_enqueue_style( 'dark-mode-dashboard-toggle' );
    }

    /**
     * Filters the URL of the main dark mode stylesheet.
     *
     * @param string $url Stylesheet URL.
     */
    $dark_mode_css = apply_filters(
        'dark_mode_dashboard_css',
        DARK_MODE_DASHBOARD_PLUGIN_PATH . 'assets/css/dark-mode.css'
    );

    wp_register_style( 'dark-mode-dashboard', $dark_mode_css, array(), DARK_MODE_DASHBOARD_VERSION );

    if ( file_exists( DARK_MODE_DASHBOARD_DIR . 'assets/css/dark-mode-rtl.css' )
        && $dark_mode_css === DARK_MODE_DASHBOARD_PLUGIN_PATH . 'assets/css/dark-mode.css' ) {
        wp_style_add_data( 'dark-mode-dashboard', 'rtl', 'replace' );
    }

    if ( $enqueue ) {
        wp_enqueue_style( 'dark-mode-dashboard' );
    }

    foreach ( dark_mode_dashboard_style_modules() as $slug => $rules ) {
        if ( ! dark_mode_dashboard_module_is_active( $rules ) ) {
            continue;
        }

        $handle = 'dark-mode-dashboard-' . $slug;

        if ( ! dark_mode_dashboard_register_style( $handle, 'assets/css/plugins/' . $slug . '.css' ) ) {
            continue;
        }

        if ( $enqueue ) {
            wp_enqueue_style( $handle );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'dark_mode_dashboard_enqueue_styles' );

/**
 * Critical CSS attached to the main stylesheet.
 *
 * Paints the shell before the rest of the cascade resolves, and covers the
 * 'auto' preference with its own media query so an auto user on a dark OS
 * never sees a light frame while the resolver script is still parsing.
 */
function dark_mode_dashboard_inline_critical_css() {
    if ( ! dark_mode_dashboard_is_active() || ! wp_style_is( 'dark-mode-dashboard', 'registered' ) ) {
        return;
    }

    $t = dark_mode_dashboard_tokens();

    $block = static function ( $scope ) use ( $t ) {
        return $scope . '{color-scheme:dark;background-color:' . $t['bg-base'] . ';color:' . $t['text-primary'] . '}'
            . sprintf(
                '%1$s #wpwrap,%1$s #wpcontent,%1$s #wpbody,%1$s #adminmenuback,%1$s #adminmenuwrap,%1$s #wpadminbar',
                $scope
            )
            . '{background-color:' . $t['bg-base'] . '}';
    };

    $css = $block( 'html body.wp-admin.dark-mode' );

    if ( 'auto' === dark_mode_dashboard_get_preference() ) {
        $css .= '@media (prefers-color-scheme:dark){' . $block( 'html body.wp-admin.dark-mode-auto' ) . '}';
    }

    wp_add_inline_style( 'dark-mode-dashboard', $css );
}
add_action( 'admin_enqueue_scripts', 'dark_mode_dashboard_inline_critical_css', 11 );

/**
 * Enqueue the block editor canvas stylesheet.
 *
 * Registered on enqueue_block_assets rather than enqueue_block_editor_assets
 * because the post content renders in an iframe and only styles from this hook
 * are carried into it. The hook also fires on the front end, hence the admin
 * guard.
 *
 * It is enqueued for EVERY preference, including 'disabled'. The stylesheet is
 * scoped to body.dark-mode, so it does nothing until the class is there — but
 * it has to be there for a light-mode user to be able to switch the canvas to
 * dark without reloading. That was half of the broken in-editor toggle.
 */
function dark_mode_dashboard_enqueue_editor_styles() {
    if ( ! is_admin() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return;
    }

    if ( dark_mode_dashboard_register_style( 'dark-mode-dashboard-editor', 'assets/css/dark-mode-editor.css' ) ) {
        wp_enqueue_style( 'dark-mode-dashboard-editor' );
    }
}
add_action( 'enqueue_block_assets', 'dark_mode_dashboard_enqueue_editor_styles' );

/**
 * Whether the editor integrations below may touch this request at all.
 *
 * Both block_editor_settings_all and tiny_mce_before_init also fire on the
 * public site — wp_editor() in acf_form(), bbPress, LMS and front-end
 * submission plugins — and on the Customizer, none of which carries the dark
 * dashboard around them. Without this guard those editors were painted dark
 * inside a light page.
 *
 * @return bool
 */
function dark_mode_dashboard_editor_integration_applies() {
    return is_admin()
        && dark_mode_dashboard_screen_supports_body_class()
        && apply_filters( 'dark_mode_dashboard_editor_canvas', true );
}

/**
 * Dark styles for the TinyMCE (classic editor) document.
 *
 * Used twice: inlined as content_style before first paint, and handed to the
 * toolbar toggle so it can darken an editor that was rendered light. Text
 * colours are deliberately not !important, so colours the author picked in the
 * editor (inline styles) still show.
 *
 * @return string
 */
function dark_mode_dashboard_tinymce_css() {
    $t = dark_mode_dashboard_tokens();

    return 'html,body,body#tinymce,body.mce-content-body{background:' . $t['bg-input'] . '!important;color:' . $t['text-primary'] . '!important}'
        . 'body a{color:' . $t['accent'] . '}'
        . 'body p,body li,body td,body th,body div,body span{color:' . $t['text-primary'] . '}';
}

/**
 * Inline CSS handed to the block editor as part of its settings.
 *
 * WordPress injects these before the canvas iframe paints its first frame, so
 * this is what stops the white flash. It goes into the IFRAME ONLY — never the
 * parent document — which is why it can afford the inverted scope: with no
 * class present the canvas is already dark, so a canvas that mounts (or
 * remounts) faster than the sync script still renders correctly, and the
 * toggle turns it off by adding dark-mode-off.
 *
 * @param array $settings Block editor settings.
 * @return array
 */
function dark_mode_dashboard_editor_settings( $settings ) {
    if ( ! dark_mode_dashboard_editor_integration_applies() ) {
        return $settings;
    }

    $preference = dark_mode_dashboard_get_preference();

    if ( 'disabled' === $preference ) {
        return $settings;
    }

    $t   = dark_mode_dashboard_tokens();
    $css = 'body:not(.dark-mode-off){color-scheme:dark;'
        . '--dm-bg-base:' . $t['bg-base'] . ';'
        . '--dm-bg-surface:' . $t['bg-surface'] . ';'
        . '--dm-bg-input:' . $t['bg-input'] . ';'
        . '--dm-text-primary:' . $t['text-primary'] . ';'
        . '--dm-text-secondary:' . $t['text-secondary'] . ';'
        . '--dm-border:' . $t['border'] . ';'
        . '--dm-accent:' . $t['accent'] . ';'
        . 'background-color:' . $t['bg-base'] . ';color:' . $t['text-primary'] . '}'
        . 'body:not(.dark-mode-off) .editor-styles-wrapper{background-color:' . $t['bg-base'] . ';color:' . $t['text-primary'] . '}';

    if ( 'auto' === $preference ) {
        $css = '@media (prefers-color-scheme:dark){' . $css . '}';
    }

    $settings['styles'][] = array( 'css' => $css );

    return $settings;
}
add_filter( 'block_editor_settings_all', 'dark_mode_dashboard_editor_settings' );

/**
 * Inject dark body styles into the TinyMCE (classic editor) iframe.
 *
 * content_style is inlined into the iframe document, so like the block editor
 * settings CSS above it lands before first paint. The toolbar toggle reverses
 * it by injecting a light override into the same document.
 *
 * @param array $mce_init TinyMCE configuration.
 * @return array
 */
function dark_mode_dashboard_tinymce_init( $mce_init ) {
    if ( ! dark_mode_dashboard_editor_integration_applies() ) {
        return $mce_init;
    }

    $preference = dark_mode_dashboard_get_preference();

    if ( 'disabled' === $preference ) {
        return $mce_init;
    }

    $styles = dark_mode_dashboard_tinymce_css();

    if ( 'auto' === $preference ) {
        $styles = '@media (prefers-color-scheme:dark){' . $styles . '}';
    }

    if ( isset( $mce_init['content_style'] ) ) {
        $mce_init['content_style'] .= ' ' . $styles;
    } else {
        $mce_init['content_style'] = $styles;
    }

    return $mce_init;
}
add_filter( 'tiny_mce_before_init', 'dark_mode_dashboard_tinymce_init' );

/*
 * There is deliberately no add_editor_style() and no mce_css filter here.
 *
 * add_editor_style() treats any absolute URL as remote: core fetches it with
 * wp_remote_get() while assembling editor settings, on every block editor load,
 * with no caching and no HTTP status check, and inlines whatever comes back.
 * That is a synchronous loopback request per page load on block themes, and it
 * is how a missing file once ended up rendering the themed 404 page inside the
 * Custom HTML block (1.3.3) and how an RTL site got a 404 inlined as CSS
 * (1.3.4). enqueue_block_assets already delivers the same file into the iframe,
 * and block_editor_settings_all covers the pre-paint window, so the whole path
 * was pure cost. mce_css went with it: it loaded the canvas sheet into TinyMCE a
 * second time on top of get_editor_stylesheets(), and content_style above is
 * what actually darkens the classic editor.
 */

/**
 * Add the dark mode body class.
 *
 * 'dark-mode' for enabled, 'dark-mode-auto' for auto, nothing for disabled.
 *
 * Auto deliberately does NOT get 'dark-mode'. That is what made auto behave as
 * permanent dark regardless of the operating system: every compiled rule is
 * scoped to .dark-mode, so emitting both classes meant auto could only ever be
 * on. The resolver script promotes the body to .dark-mode when, and only when,
 * the OS reports a dark colour scheme.
 *
 * @param string $classes Space-separated admin body classes.
 * @return string
 */
function dark_mode_dashboard_body_class( $classes ) {
    $preference = dark_mode_dashboard_get_preference();

    if ( 'enabled' === $preference ) {
        $classes .= ' dark-mode';
    } elseif ( 'auto' === $preference ) {
        $classes .= ' dark-mode-auto';
    }

    return $classes;
}
add_filter( 'admin_body_class', 'dark_mode_dashboard_body_class' );

/**
 * Resolve 'auto' against the operating system, before anything paints.
 *
 * Printed immediately after the opening <body> tag, which is the earliest
 * point at which document.body exists. It has to be inline and synchronous:
 * an enqueued script runs too late and the admin would flash light.
 *
 * Shipping the whole stylesheet a second time inside a prefers-color-scheme
 * block would cost roughly 120 KB; this costs one line and also lets the admin
 * follow the OS live, without a reload, via the change listener.
 *
 * The listener only speaks for a user who has not spoken for themselves. An
 * explicit click on the toolbar toggle removes the 'dark-mode-auto' marker
 * class (see applyState() in toggle.js), and the listener stands down from
 * that moment until the page is reloaded. Without that check the toggle
 * appeared to work and was then quietly undone the next time the operating
 * system changed theme — the user's own choice losing to their OS.
 */
function dark_mode_dashboard_auto_resolver() {
    if ( 'auto' !== dark_mode_dashboard_get_preference() ) {
        return;
    }

    // Note the guard is inside the handler rather than a "first run" argument:
    // addEventListener passes the event object as the first argument, which
    // would make any such flag truthy for exactly the calls it must not skip.
    $js = '(function(){var m=window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)");'
        . 'if(!m){return;}'
        . 'var a=function(){'
        . 'if(!document.body.classList.contains("dark-mode-auto")){return;}'
        . 'document.body.classList.toggle("dark-mode",m.matches);'
        . 'document.dispatchEvent(new CustomEvent("dark-mode-dashboard-change",{detail:{dark:m.matches}}));};'
        . 'a();'
        . 'if(m.addEventListener){m.addEventListener("change",a);}else if(m.addListener){m.addListener(a);}'
        . '})();';

    wp_print_inline_script_tag( $js, array( 'id' => 'dark-mode-dashboard-auto' ) );
}
add_action( 'in_admin_header', 'dark_mode_dashboard_auto_resolver', 0 );

/**
 * Add the dark mode toggle to the admin toolbar.
 *
 * Rendered as a role="switch" with aria-checked so the state is exposed to
 * assistive technology; the icon swap alone conveyed nothing.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function dark_mode_dashboard_toolbar_link( $wp_admin_bar ) {
    if ( ! is_admin() ) {
        return;
    }

    $moon = '<span class="dm-icon dm-icon-moon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9.37 5.51A7.35 7.35 0 0 0 9.1 7.5c0 4.08 3.32 7.4 7.4 7.4.68 0 1.35-.09 1.99-.27A7.014 7.014 0 0 1 12 19c-3.86 0-7-3.14-7-7 0-2.93 1.81-5.45 4.37-6.49zM12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg></span>';
    $sun  = '<span class="dm-icon dm-icon-sun" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM11 1h2v3h-2zm0 19h2v3h-2zM3.52 4.93l1.41-1.41 2.12 2.12-1.41 1.42zM16.95 18.36l1.41-1.41 2.12 2.12-1.41 1.41zM1 11h3v2H1zm19 0h3v2h-3zM4.93 20.48l2.12-2.12 1.42 1.41-2.13 2.12zM18.36 7.05l2.12-2.12 1.41 1.41-2.12 2.12z"/></svg></span>';

    $wp_admin_bar->add_node(
        array(
            'id'    => 'dark-mode-dashboard',
            'title' => $moon . $sun . '<span class="screen-reader-text dm-label">' . esc_html__( 'Dark Mode', 'dark-mode-for-wp-dashboard' ) . '</span>',
            'href'  => '#',
            'meta'  => array(
                'class' => 'dark-mode-dashboard',
                'title' => esc_attr__( 'Dark Mode', 'dark-mode-for-wp-dashboard' ),
                // Polite live region for the result of a toggle, including a
                // failed save. The toggle is JavaScript-only, so the script is
                // also what applies role="switch" / aria-checked to the anchor:
                // add_node() has no API for arbitrary anchor attributes, and
                // announcing a switch that cannot work without JS would be
                // worse than announcing nothing.
                'html'  => '<div class="dm-live screen-reader-text" aria-live="polite"></div>',
            ),
        )
    );
}
add_action( 'admin_bar_menu', 'dark_mode_dashboard_toolbar_link', 999 );

/**
 * Enqueue the toolbar toggle script.
 *
 * Previously this was a raw <script> block printed in the footer, which a
 * nonce-based Content Security Policy on /wp-admin silently killed. As an
 * enqueued file it also gets wp_script_add_data() CSP handling for free.
 */
function dark_mode_dashboard_enqueue_toggle_script() {
    $file = 'assets/js/toggle.js';

    // The Customizer renders no admin bar, so there is no switch for this to
    // drive — and none of the stylesheets it lazy-loads are registered there.
    if ( ! dark_mode_dashboard_screen_supports_body_class() ) {
        return;
    }

    if ( ! file_exists( DARK_MODE_DASHBOARD_DIR . $file ) ) {
        return;
    }

    wp_enqueue_script(
        'dark-mode-dashboard-toggle',
        DARK_MODE_DASHBOARD_PLUGIN_PATH . $file,
        array(),
        DARK_MODE_DASHBOARD_VERSION,
        true
    );

    $styles = array();

    // Handed to the script so a light-mode user, who downloads none of the
    // theme CSS, can still switch the lights on without a reload.
    foreach ( wp_styles()->registered as $handle => $style ) {
        if ( 0 !== strpos( $handle, 'dark-mode-dashboard' ) || 'dark-mode-dashboard-toggle' === $handle ) {
            continue;
        }

        if ( ! wp_style_is( $handle, 'enqueued' ) && ! empty( $style->src ) ) {
            $src = $style->src;

            // toggle.js injects these as raw <link> elements, which never passes
            // through the RTL substitution WordPress applies when IT prints a
            // style — that is driven by the wp_style_add_data( ..., 'rtl',
            // 'replace' ) calls above, and only runs for styles it enqueues.
            // These are deliberately not enqueued, so without this an admin on
            // an RTL site who switched the lights on was handed the LTR build
            // and kept it until the next page load.
            //
            // The style's own 'rtl' data decides, so the swap happens exactly
            // where core would have done it. Guarded on the file being present:
            // a stylesheet that is not there comes back as the themed 404 page
            // and gets injected as CSS, which is the 1.3.4 failure this plugin
            // has already shipped once.
            if ( is_rtl() && 'replace' === ( $style->extra['rtl'] ?? '' ) ) {
                $rtl_src  = preg_replace( '/\.css$/', '-rtl.css', $src );
                $rtl_path = str_replace( DARK_MODE_DASHBOARD_PLUGIN_PATH, DARK_MODE_DASHBOARD_DIR, (string) $rtl_src );

                if ( $rtl_src !== $src && file_exists( $rtl_path ) ) {
                    $src = $rtl_src;
                }
            }

            $styles[ $handle ] = $src;
        }
    }

    wp_localize_script(
        'dark-mode-dashboard-toggle',
        'darkModeDashboard',
        array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'dark_mode_dashboard_nonce' ),
            'preference'   => dark_mode_dashboard_get_preference(),
            'editorCanvas' => apply_filters( 'dark_mode_dashboard_editor_canvas', true ) && dark_mode_dashboard_is_editor_screen(),
            // Classic editors live on far more screens than the editor ones
            // (WooCommerce's short description, ACF WYSIWYG fields, options
            // pages), so they are synced on any screen, gated only by the filter.
            'tinymce'      => (bool) apply_filters( 'dark_mode_dashboard_editor_canvas', true ),
            'lazyStyles'   => $styles,
            // The override is appended after content_style, so it wins on
            // source order; only the body paint needs !important to beat the
            // dark content_style's own. Text colours stay overridable by the
            // author's inline colours, the same as in the dark version.
            'tinymceLight' => 'html,body,body#tinymce,body.mce-content-body{background:#fff!important;color:#444!important}body p,body li,body td,body th,body div,body span{color:#444}body a{color:#0073aa}',
            'tinymceDark'  => dark_mode_dashboard_tinymce_css(),
            'canvasDark'   => 'body{background-color:' . dark_mode_dashboard_tokens()['bg-base'] . ';color:' . dark_mode_dashboard_tokens()['text-primary'] . '}',
            'i18n'         => array(
                'switchToLight' => __( 'Switch to light mode', 'dark-mode-for-wp-dashboard' ),
                'switchToDark'  => __( 'Switch to dark mode', 'dark-mode-for-wp-dashboard' ),
                'nowDark'       => __( 'Dark mode on.', 'dark-mode-for-wp-dashboard' ),
                'nowLight'      => __( 'Dark mode off.', 'dark-mode-for-wp-dashboard' ),
                'saveFailed'    => __( 'Dark mode preference could not be saved. Your previous setting was restored.', 'dark-mode-for-wp-dashboard' ),
            ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'dark_mode_dashboard_enqueue_toggle_script', 12 );

/**
 * AJAX handler for the toolbar toggle.
 *
 * Reports failure rather than swallowing it, so the script can put the UI back
 * where it was instead of showing a state the server never stored.
 */
function dark_mode_dashboard_ajax_toggle() {
    if ( ! check_ajax_referer( 'dark_mode_dashboard_nonce', 'security', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Your session expired. Reload the page and try again.', 'dark-mode-for-wp-dashboard' ) ), 403 );
    }

    if ( ! current_user_can( 'read' ) ) {
        wp_send_json_error( array( 'message' => __( 'You are not allowed to do that.', 'dark-mode-for-wp-dashboard' ) ), 403 );
    }

    $allowed    = array( 'enabled', 'disabled', 'auto' );
    $preference = isset( $_POST['preference'] ) ? sanitize_text_field( wp_unslash( $_POST['preference'] ) ) : '';

    if ( ! in_array( $preference, $allowed, true ) ) {
        wp_send_json_error( array( 'message' => __( 'Unrecognised preference.', 'dark-mode-for-wp-dashboard' ) ), 400 );
    }

    $user_id = get_current_user_id();

    // update_user_meta() returns false both on failure and when the value is
    // unchanged, so an unchanged value has to be excluded before trusting it.
    if ( $preference !== get_user_meta( $user_id, 'dark_mode_preference', true )
        && ! update_user_meta( $user_id, 'dark_mode_preference', $preference ) ) {
        wp_send_json_error( array( 'message' => __( 'The preference could not be saved.', 'dark-mode-for-wp-dashboard' ) ), 500 );
    }

    wp_send_json_success( array( 'preference' => $preference ) );
}
add_action( 'wp_ajax_dark_mode_dashboard_toggle', 'dark_mode_dashboard_ajax_toggle' );

/**
 * Display the dark mode preference field on the user profile screen.
 *
 * @param WP_User $user The user being edited.
 */
function dark_mode_dashboard_user_profile_fields( $user ) {
    // Same resolver the rest of the plugin uses, for whichever user is being
    // edited, so the radio that appears selected is the one actually in effect.
    $preference = dark_mode_dashboard_get_preference( $user->ID );

    $choices = array(
        'enabled'  => __( 'Dark (always)', 'dark-mode-for-wp-dashboard' ),
        'disabled' => __( 'Light (always)', 'dark-mode-for-wp-dashboard' ),
        'auto'     => __( 'Auto (system)', 'dark-mode-for-wp-dashboard' ),
    );
    ?>
    <h3><?php esc_html_e( 'Dark Mode', 'dark-mode-for-wp-dashboard' ); ?></h3>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <?php esc_html_e( 'Dashboard appearance', 'dark-mode-for-wp-dashboard' ); ?>
            </th>
            <td>
                <fieldset>
                    <?php foreach ( $choices as $value => $label ) : ?>
                        <label>
                            <input type="radio" name="dark_mode_preference" value="<?php echo esc_attr( $value ); ?>" <?php checked( $preference, $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                        <br>
                    <?php endforeach; ?>
                </fieldset>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'dark_mode_dashboard_user_profile_fields' );
add_action( 'edit_user_profile', 'dark_mode_dashboard_user_profile_fields' );

/**
 * Save the dark mode preference from the user profile screen.
 *
 * @param int $user_id The user being saved.
 */
function dark_mode_dashboard_save_user_profile_fields( $user_id ) {
    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( ! isset( $_POST['dark_mode_preference'] ) ) {
        return;
    }

    $allowed    = array( 'enabled', 'disabled', 'auto' );
    $preference = sanitize_text_field( wp_unslash( $_POST['dark_mode_preference'] ) );

    if ( ! in_array( $preference, $allowed, true ) ) {
        return;
    }

    // The radio group always submits something, so saving a profile for any
    // other reason — an admin changing someone's email — used to write the
    // default in as that user's explicit choice, after which a later change to
    // dark_mode_dashboard_default_preference no longer reached them. A user
    // with no stored choice only gets one when the submitted value differs
    // from what they already resolve to.
    $stored = get_user_meta( $user_id, 'dark_mode_preference', true );

    if ( ! in_array( $stored, $allowed, true ) && dark_mode_dashboard_get_preference( $user_id ) === $preference ) {
        return;
    }

    update_user_meta( $user_id, 'dark_mode_preference', $preference );
}
add_action( 'personal_options_update', 'dark_mode_dashboard_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'dark_mode_dashboard_save_user_profile_fields' );
