<?php
/*
Plugin Name: Languages & Scripts Manager
Description: A custom admin page for managing language and script metadata.
Version: 1.1
Author: Aharon N. Varady
*/

function ls_add_admin_menu() {
    // Add "Languages & Scripts" as a sub-menu item under "Posts"
    add_submenu_page(
        'edit.php', // Parent slug: "Posts"
        'Languages & Scripts', // Page title
        'Languages & Scripts', // Menu title
        'manage_options', // Capability
        'languages-scripts', // Menu slug
        'ls_render_admin_page' // Callback function
    );

    // Keep hidden submenus for editing language/script entries
    add_submenu_page(null, 'Edit Language', 'Edit Language', 'manage_options', 'edit-language', 'ls_render_edit_language_page');
    add_submenu_page(null, 'Edit Script', 'Edit Script', 'manage_options', 'edit-script', 'ls_render_edit_script_page');
}
add_action('admin_menu', 'ls_add_admin_menu');


// Retrieve all languages and scripts from post metadata and count associated posts
function ls_get_languages_scripts() {
    global $wpdb;
    $posts = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish'");
    $languages = [];
    $scripts = [];
    
    foreach ($posts as $post) {
        $langs = get_post_meta($post->ID, 'languages_meta', true);
        $langs = $langs ? json_decode($langs, true) : [];
        foreach ($langs as $lang) {
            $code = $lang['code'];
            $languages[$code]['name'] = $lang['name'];
            $languages[$code]['standard'] = $lang['standard'] ?? 'Unknown';
            $languages[$code]['posts'][] = $post->ID;
        }
        
        $scrs = get_post_meta($post->ID, 'scripts_meta', true);
        $scrs = $scrs ? json_decode($scrs, true) : [];
        foreach ($scrs as $scr) {
            $code = $scr['code'];
            $scripts[$code]['name'] = $scr['name'];
            $scripts[$code]['standard'] = $scr['standard'] ?? 'Unknown';
            $scripts[$code]['posts'][] = $post->ID;
        }
    }
    
    asort($languages);
    asort($scripts);
    
    return ['languages' => $languages, 'scripts' => $scripts];
}

// Render the main admin page
function ls_render_admin_page() {
    $data = ls_get_languages_scripts();
    echo '<div class="wrap"><h1>Languages & Scripts</h1>';
    
    echo '<h2>Languages</h2><table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Name</th><th>Code</th><th>Standard</th><th>Posts</th><th>Actions</th></tr></thead><tbody>';
    foreach ($data['languages'] as $code => $lang) {
        $edit_url = admin_url("admin.php?page=edit-language&code={$code}");
        echo "<tr>
                <td>{$lang['name']}</td>
                <td>{$code}</td>
                <td>{$lang['standard']}</td>
                <td>" . count($lang['posts']) . "</td>
                <td>
                    <a href='{$edit_url}'>Edit</a>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
    
    echo '<h2>Scripts</h2><table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Name</th><th>Code</th><th>Standard</th><th>Posts</th><th>Actions</th></tr></thead><tbody>';
    foreach ($data['scripts'] as $code => $scr) {
        $edit_url = admin_url("admin.php?page=edit-script&code={$code}");
        echo "<tr>
                <td>{$scr['name']}</td>
                <td>{$code}</td>
                <td>{$scr['standard']}</td>
                <td>" . count($scr['posts']) . "</td>
                <td>
                    <a href='{$edit_url}'>Edit</a>
                </td>
              </tr>";
    }
    echo '</tbody></table></div>';
}

// Render the edit page for languages
function ls_render_edit_language_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Sorry, you are not allowed to access this page.'));
    }
    
    $code = $_GET['code'] ?? '';
    $languages = ls_get_languages_scripts()['languages'];
    if (!$code || !isset($languages[$code])) {
        echo '<div class="wrap"><h1>Error</h1><p>Invalid language code.</p></div>';
        return;
    }
    
    $language = $languages[$code];
    echo '<div class="wrap"><h1>Edit Language</h1>';
    echo '<form method="post">';
    echo '<label>Name: <input type="text" name="lang_name" value="' . esc_attr($language['name']) . '"></label><br>';
    echo '<label>Code: <input type="text" name="lang_code" value="' . esc_attr($code) . '" readonly></label><br>';
    echo '<label>Standard: <input type="text" name="lang_standard" value="' . esc_attr($language['standard']) . '" readonly></label><br>';
    echo '<button type="submit" name="save_language">Save Changes</button> ';
    echo '<a href="' . admin_url('admin.php?page=languages-scripts') . '">Back</a>';
    echo '</form></div>';
}

// Ensure the edit page renders correctly
add_action('admin_menu', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'edit-language') {
        add_submenu_page(null, 'Edit Language', 'Edit Language', 'manage_options', 'edit-language', 'ls_render_edit_language_page');
    }
    if (isset($_GET['page']) && $_GET['page'] === 'edit-script') {
        add_submenu_page(null, 'Edit Script', 'Edit Script', 'manage_options', 'edit-script', 'ls_render_edit_script_page');
    }
});

// Render the edit page for scripts
function ls_render_edit_script_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Sorry, you are not allowed to access this page.'));
    }
    
    $code = $_GET['code'] ?? '';
    $scripts = ls_get_languages_scripts()['scripts'];
    if (!$code || !isset($scripts[$code])) {
        echo '<div class="wrap"><h1>Error</h1><p>Invalid script code.</p></div>';
        return;
    }
    
    $script = $scripts[$code];
    echo '<div class="wrap"><h1>Edit Script</h1>';
    echo '<form method="post">';
    echo '<label>Name: <input type="text" name="script_name" value="' . esc_attr($script['name']) . '"></label><br>';
    echo '<label>Code: <input type="text" name="script_code" value="' . esc_attr($code) . '" readonly></label><br>';
    echo '<label>Standard: <input type="text" name="script_standard" value="' . esc_attr($script['standard']) . '" readonly></label><br>';
    echo '<button type="submit" name="save_script">Save Changes</button> ';
    echo '<a href="' . admin_url('admin.php?page=languages-scripts') . '">Back</a>';
    echo '</form></div>';
}

// Handle saving language edits
function ls_save_language_edit() {
    if (isset($_POST['save_language']) && isset($_GET['code'])) {
        $code = sanitize_text_field($_GET['code']);
        $new_name = sanitize_text_field($_POST['lang_name']);
        $new_standard = sanitize_text_field($_POST['lang_standard']);

        // Retrieve posts with this language
        $posts = get_posts([
            'post_type'      => 'any',
            'posts_per_page' => -1,
            'meta_query'     => [[
                'key'     => 'languages_meta',
                'value'   => $code,
                'compare' => 'LIKE'
            ]]
        ]);

        foreach ($posts as $post) {
            $languages = get_post_meta($post->ID, 'languages_meta', true);
            $languages = $languages ? json_decode($languages, true) : [];

            foreach ($languages as &$lang) {
                if ($lang['code'] === $code) {
                    $lang['name'] = $new_name;
                    $lang['standard'] = $new_standard;
                }
            }

            // Ensure Unicode characters are stored properly
            update_post_meta($post->ID, 'languages_meta', json_encode($languages, JSON_UNESCAPED_UNICODE));
        }

        wp_redirect(admin_url('admin.php?page=languages-scripts'));
        exit;
    }
}
add_action('admin_init', 'ls_save_language_edit');


// Handle saving scripts edits
function ls_save_script_edit() {
    if (isset($_POST['save_script']) && isset($_GET['code'])) {
        $code = sanitize_text_field($_GET['code']);
        $new_name = sanitize_text_field($_POST['script_name']);
        $new_standard = sanitize_text_field($_POST['script_standard']);
        
        // Retrieve posts with this language
        $posts = get_posts([
            'post_type' => 'any',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => 'scripts_meta',
                'value' => $code,
                'compare' => 'LIKE'
            ]]
        ]);
        
        foreach ($posts as $post) {
            $scripts = get_post_meta($post->ID, 'scripts_meta', true);
            $scripts = $scripts ? json_decode($scripts, true) : [];
            foreach ($scripts as &$scr) {
                if ($scr['code'] === $code) {
                    $scr['name'] = $new_name;
                    $scr['standard'] = $new_standard;
                }
            }
            update_post_meta($post->ID, 'scripts_meta', json_encode($scripts, JSON_UNESCAPED_UNICODE));
        }
        
        wp_redirect(admin_url('admin.php?page=languages-scripts'));
        exit;
    }
}
add_action('admin_init', 'ls_save_script_edit');
