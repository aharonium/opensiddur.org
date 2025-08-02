<?php
/*
Plugin Name: Languages & Scripts Index
Description: Generates an index of all languages and scripts used on the site with filter links.
Version: 1.0
Author: Aharon Varady
*/

// Add shortcode to display the Languages & Scripts index
add_shortcode('languages_scripts_index', 'lsi_render_index_page');

// Function to get all unique languages and scripts from post metadata
function lsi_get_languages_scripts() {
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
            $languages[$code]['count'] = isset($languages[$code]['count']) ? $languages[$code]['count'] + 1 : 1;
        }
        
        $scrs = get_post_meta($post->ID, 'scripts_meta', true);
        $scrs = $scrs ? json_decode($scrs, true) : [];
        foreach ($scrs as $scr) {
            $code = $scr['code'];
            $scripts[$code]['name'] = $scr['name'];
            $scripts[$code]['count'] = isset($scripts[$code]['count']) ? $scripts[$code]['count'] + 1 : 1;
        }
    }
    
    asort($languages);
    asort($scripts);
    
    return ['languages' => $languages, 'scripts' => $scripts];
}

// Function to render the index page
function lsi_render_index_page() {
    $data = lsi_get_languages_scripts();
    ob_start();
    echo '<div class="languages-scripts-index">';
    echo '<div class="tabs">';
    echo '<button class="tab-button active" onclick="showTab(\'languages\')">Languages</button>';
    echo '<button class="tab-button" onclick="showTab(\'scripts\')">Scripts</button>';
    echo '</div>';
    
    // Languages Tab
    echo '<div id="languages" class="tab-content active">';
    foreach ($data['languages'] as $code => $lang) {
        $link = esc_url(add_query_arg([
            'language' => $code,
            'language_name' => urlencode($lang['name']) // Add the name
        ], home_url('/languages-scripts/')));
        echo "<div class='langscript-listing' style='min-height:96px;'>
                <div id='langscriptimg-size' style='position: absolute; width: 96px;'>
                    <img alt='Languages' src='https://opensiddur.org/wp-content/uploads/2025/03/speech-bubbles.png' class='langscriptimg' height='96' width='96' style='height:96px;width:100%;'>
                </div>
                <div style='font-size:15px;padding-left: 120px;'>
                    <div><a href='{$link}'><b>{$lang['name']}</b></a></div><p />
                    <div>(Resources available: {$lang['count']})</div>
                </div>
              </div>";
    }
    echo '</div>';
    
    // Scripts Tab
    echo '<div id="scripts" class="tab-content">';
    foreach ($data['scripts'] as $code => $scr) {
        $link = esc_url(add_query_arg([
            'script' => $code,
            'script_name' => urlencode($scr['name'])
        ], home_url('/languages-scripts/')));
        // $link = esc_url(add_query_arg('script', $code, home_url('/')));
        echo "<div class='langscript-listing' style='min-height:96px;'>
                <div id='langscriptimg-size' style='position: absolute; width: 96px;'>
                    <img alt='Scripts' src='https://opensiddur.org/wp-content/uploads/2025/07/gratis-pluma.png' class='langscriptimg' height='96' width='96' style='height:96px;width:100%;'>
                </div>
                <div style='font-size:15px;padding-left: 120px;'>
                    <div><a href='{$link}'><b>{$scr['name']}</b></a></div><p />
                    <div>(Resources available: {$scr['count']})</div>
                </div>
              </div>";
    }
    echo '</div>';
    echo '</div>';
    
    return ob_get_clean();
}

// Enqueue styles and scripts for tabs
function lsi_enqueue_assets() {
    echo '<style>
    .tabs { display: flex; gap: 10px; border-bottom: 2px solid #ccc; }
    .tab-button {
        padding: 10px 20px;
        cursor: pointer;
        background: #F4E1C6; /* Manila folder color */
        border: 2px solid #ccc;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
    }
    .tab-button.active { background: #e0c095; border-bottom: 2px solid #e0c095; }
    .tab-content { display: none; padding: 10px; }
    .tab-content.active { display: block; }
    .langscript-listing { display: flex; align-items: center; border-bottom: 1px solid #ccc; padding: 10px; }
    .langscriptimg-container { width: 96px; }
    .langscriptimg { height: 96px; width: 96px; border-radius: 8px; }
    .listing-text { font-size: 15px; padding-left: 20px; }
    </style>';
    echo '<script>
    function showTab(tab) {
        document.querySelectorAll(".tab-content").forEach(el => el.classList.remove("active"));
        document.querySelectorAll(".tab-button").forEach(el => el.classList.remove("active"));
        document.getElementById(tab).classList.add("active");
        event.currentTarget.classList.add("active");
    }
    </script>';
}
add_action('wp_head', 'lsi_enqueue_assets');
