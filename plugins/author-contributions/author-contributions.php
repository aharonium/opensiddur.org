<?php
/*
 * Plugin Name: Author Contributions
 * Description: Displays the categories, tags, languages, and names of users associated with the content of any contributed post, along with filter links and a date range query form.
 * Version: 1.6
 * Author: Aharon Varady
 * Author URI: https://github.com/aharonium/
 * Plugin URI: https://github.com/aharonium/opensiddur.org/plugins/author-contributions
 * License: LGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.html
 * Requires PHP: 7.4
 * Requires at least: 5.2
 * Tested up to: 6.4
 * Tags: author pages, co-authors, categories, tags, filters, WordPress plugin
 * Text Domain: author-contributions
 *
 * Note: This plugin relies on JSON files generated from functions in our theme's functions.php file.
*/

define('AC_LAZYLOAD_THRESHOLD', 800);

function ac_should_lazyload_filters($user_id) {
    $user_posts = ac_get_user_posts($user_id);
    $post_count = count($user_posts);
    return $post_count >= AC_LAZYLOAD_THRESHOLD;
}

add_action('wp_ajax_ac_ajax_load_filters', 'ac_ajax_load_filters');
add_action('wp_ajax_nopriv_ac_ajax_load_filters', 'ac_ajax_load_filters');

function ac_ajax_load_filters() {
    $user_id = intval($_POST['user_id']);
    if (!$user_id) {
        wp_send_json_error('Invalid user ID.');
    }

    list($categories, $tags, $co_contributors, $languages) = ac_get_terms_for_user($user_id);
    $html = ac_render_filters_html($categories, $tags, $co_contributors, $languages, $user_id);

    wp_send_json_success($html);
}

function ac_enqueue_assets() {
    wp_register_style('ac-styles', plugin_dir_url(__FILE__) . 'css/ac-styles.css');
    wp_enqueue_style('ac-styles');

    wp_register_script('ac-accordion', plugin_dir_url(__FILE__) . 'js/ac-accordion.js', array('jquery'), '1.0', true);
    wp_enqueue_script('ac-accordion');
    
    wp_register_script('ac-lazyload-filters', plugin_dir_url(__FILE__) . 'js/ac-lazyload-filters.js', array('jquery'), '1.0', true);
    wp_enqueue_script('ac-lazyload-filters'); // 
}
add_action('wp_enqueue_scripts', 'ac_enqueue_assets');

function ac_load_contributors_by_id($force_reload = false) {
    static $cached_data = null;
    if (!$force_reload && $cached_data !== null) {
        return $cached_data;
    }

    $path = WP_CONTENT_DIR . '/uploads/contributors_by_id.json';
    if (file_exists($path)) {
        $cached_data = json_decode(file_get_contents($path), true);
        return $cached_data;
    }
    return [];
}

function ac_get_user_posts($user_id) {
    $uploads_dir = wp_upload_dir()['basedir'];
    $posts_path = $uploads_dir . '/posts.json';
    $contributors_data = ac_load_contributors_by_id();

    $posts_data = json_decode(file_get_contents($posts_path), true);
    if (!is_array($posts_data) || !is_array($contributors_data)) {
        return [];
    }

    $user_id_str = (string) $user_id;
    $user_posts = [];

    foreach ($posts_data as $post_id => $post) {
        if (empty($post['authors']) || !is_array($post['authors'])) continue;

        foreach ($post['authors'] as $author_str) {
            if (preg_match('/\((\d+)\)$/', $author_str, $matches)) {
                $author_id = $matches[1];
                if ($author_id === $user_id_str) {
                    $authors = array_map(function($author_entry) use ($contributors_data) {
                        if (preg_match('/^(.*?) \((\d+)\)$/', $author_entry, $m)) {
                            $name = trim($m[1]);
                            $id = $m[2];
                            $last_name = $contributors_data[$id]['last_name'] ?? '';
                            return [
                                'name' => $name,
                                'id' => (int) $id,
                                'last_name' => $last_name
                            ];
                        }
                        return null;
                    }, $post['authors']);
                    $authors = array_filter($authors);

                    $user_posts[$post_id] = [
                        'categories' => $post['categories'] ?? [],
                        'tags'       => $post['tags'] ?? [],
                        'languages'  => $post['languages'] ?? [],
                        'authors'    => $authors,
                    ];
                    break;
                }
            }
        }
    }

    return $user_posts;
}

function ac_parse_labeled_items($items) {
    $parsed = [];

    foreach ($items as $item) {
        if (preg_match('/^(.*?)\s*\((\d+)\)$/', $item, $matches)) {
            $term_id = (int) $matches[2];
            $term = get_term($term_id);

            if ($term && !is_wp_error($term)) {
                $parsed[] = (object) [
                    'name' => $term->name,
                    'term_id' => $term_id,
                    'slug' => $term->slug,
                ];
            }
        }
    }

    return $parsed;
}

function ac_get_terms_for_user($user_id) {
    $user_posts = ac_get_user_posts($user_id);

    $categories = [];
    $tags = [];
    $languages = [];
    $co_contributors = [];

    $contributors_by_id = ac_load_contributors_by_id();

    foreach ($user_posts as $post_data) {
        $categories = array_merge($categories, ac_parse_labeled_items($post_data['categories'] ?? []));
        $tags = array_merge($tags, ac_parse_labeled_items($post_data['tags'] ?? []));

        foreach ($post_data['languages'] ?? [] as $lang_str) {
            if (preg_match('/^(.*?): ([\w\-]+) \((.+?)\)$/', $lang_str, $matches)) {
                $languages[$matches[2]] = $matches[3];
            }
        }

        foreach ($post_data['authors'] ?? [] as $author) {
            $id = (int) $author['id'];
            if ($id !== $user_id && !empty($contributors_by_id[$id])) {
                $author_data = $contributors_by_id[$id];
                $co_contributors[$id] = [
                    'id'         => $id,
                    'name'       => $author_data['display_name'],
                    'last_name'  => $author_data['last_name'],
                    'first_name' => $author_data['first_name'],
                ];
            }
        }
    }

    $co_contributors = array_values($co_contributors);
    usort($co_contributors, function ($a, $b) {
        $cmp = strcasecmp($a['last_name'], $b['last_name']);
        return $cmp !== 0 ? $cmp : strcasecmp($a['first_name'], $b['first_name']);
    });
    $co_contributors = array_map(fn($c) => ['id' => $c['id'], 'name' => $c['name']], $co_contributors);

    $categories = ac_deduplicate_and_sort($categories);
    $tags = ac_deduplicate_and_sort($tags);

    asort($languages);
    $languages = array_values(array_map(
        fn($code, $name) => ['code' => $code, 'name' => $name],
        array_keys($languages),
        $languages
    ));

    return [$categories, $tags, $co_contributors, $languages];
}

function ac_deduplicate_and_sort($items) {
    $seen = [];
    $unique_items = [];

    foreach ($items as $item) {
        if (!in_array($item->slug, $seen)) {
            $seen[] = $item->slug;
            $unique_items[] = $item;
        }
    }

    usort($unique_items, function($a, $b) {
        [$numA, $eraA] = extract_numeric_and_era($a->slug);
        [$numB, $eraB] = extract_numeric_and_era($b->slug);

        $era_order = ['before-common-era' => 1, 'common-era' => 2, 'anno-mundi' => 3];
        $era_diff = ($era_order[$eraA] ?? 0) <=> ($era_order[$eraB] ?? 0);

        if ($era_diff !== 0) return $era_diff;
        $num_diff = $numA <=> $numB;
        if ($eraA === 'before-common-era') $num_diff *= -1;

        return $num_diff !== 0 ? $num_diff : strcasecmp($a->slug, $b->slug);
    });

    return $unique_items;
}

function extract_numeric_and_era($slug) {
    if (preg_match('/(\d+)(?:st|nd|rd|th)?(?:-century)?(?:-(before-common-era|common-era|anno-mundi))?/i', $slug, $matches)) {
        if (isset($matches[1], $matches[2])) {
            return [(int) $matches[1], strtolower($matches[2])];
        }
    }
    return [null, ''];
}

function ac_format_line($terms, $type, $user_id) {
    $formatted = array_map(function ($term) use ($type, $user_id) {
        $link = esc_url(add_query_arg($type, $term->slug, get_author_posts_url($user_id)));
        return '<a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a>';
    }, $terms);

    return implode(' | ', $formatted);
}

function ac_render_filters_html($categories, $tags, $co_contributors, $languages, $user_id) {
    $output = '';

    if (!empty($categories)) {
        $output .= '<div class="accordion">Filter resources by Category</div>';
        $output .= '<div class="panel"><p>' . ac_format_line($categories, 'cat', $user_id) . '</p></div>';
    }

    if (!empty($tags)) {
        $output .= '<div class="accordion">Filter resources by Tag</div>';
        $output .= '<div class="panel"><p>' . ac_format_line($tags, 'tag', $user_id) . '</p></div>';
    }

    if (!empty($co_contributors)) {
        $output .= '<div class="accordion">Filter resources by Collaborator Name</div>';
        $output .= '<div class="panel"><p>';
        foreach ($co_contributors as $contributor) {
            $link = esc_url(add_query_arg('collab', $contributor['id'], get_author_posts_url($user_id)));
            $output .= '<a href="' . $link . '">' . esc_html($contributor['name']) . '</a> | ';
        }
        $output = rtrim($output, ' | ');
        $output .= '</p></div>';
    }

    if (!empty($languages)) {
        $output .= '<div class="accordion">Filter resources by Language</div>';
        $output .= '<div class="panel"><p>';
        foreach ($languages as $lang) {
            $link = esc_url(add_query_arg([
                'language' => $lang['code'],
                'language_name' => urlencode($lang['name'])
            ], get_author_posts_url($user_id)));
            $output .= '<a href="' . $link . '">' . esc_html($lang['name']) . '</a> | ';
        }
        $output = rtrim($output, ' | ') . '</p></div>';
    }

    if (!isset($GLOBALS['date_filter_displayed'])) {
        $GLOBALS['date_filter_displayed'] = false;
    }

    if (!$GLOBALS['date_filter_displayed']) {
        $GLOBALS['date_filter_displayed'] = true;
        $output .= '<div class="accordion">Filter resources by Date Range</div>';
        $output .= '<div class="panel"><form method="get" action="' . esc_url(get_author_posts_url($user_id)) . '">';

        foreach ($_GET as $key => $value) {
            if (in_array($key, ['daterange_start', 'daterange_end'])) {
                $output .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }

        $output .= '<div class="date-range-fields">';
        $output .= '<label for="daterange_start">From:</label>';
        $output .= '<input type="number" id="daterange_start" name="daterange_start" placeholder="Start year" min="-5000" max="5000">';
        $output .= ' <label for="daterange_end">to:</label>';
        $output .= '<input type="number" id="daterange_end" name="daterange_end" placeholder="End year" min="-5000" max="5000">';
        $output .= '<button type="submit" class="date-range-submit">Filter</button>';
        $output .= '</div>';
        $output .= '<p style="font-size: 14px; color: #666;">Enter a start year and an end year. BCE years are preceded by a hyphen (e.g., -1000).</p>';

        $output .= '</form></div>';
    }

    return $output;
}

function ac_display_author_contributions($atts) {
    $user_id = get_query_var('author');
    if (!$user_id) return '<p>No author found.</p>';

    $lazyload_filters = ac_should_lazyload_filters($user_id);
    list($categories, $tags, $co_contributors, $languages) = ac_get_terms_for_user($user_id);

    $output = '<div class="author-contributions">';
    
    if ($lazyload_filters) {
        $output .= '
            <div id="filters-container"></div>
            <button id="load-filters-button">Show Filters</button>
            <script>
                var ac_ajax_object = {
                    ajax_url: "' . esc_url(admin_url('admin-ajax.php')) . '",
                    user_id: "' . esc_js($user_id) . '"
                };
            </script>';
    } else {
        $output .= ac_render_filters_html($categories, $tags, $co_contributors, $languages, $user_id);
    }

    $output .= '</div>';
    return $output;
}

add_shortcode('author_contributions', 'ac_display_author_contributions');
