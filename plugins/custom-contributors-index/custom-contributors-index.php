<?php
/*
Plugin Name: Custom Contributors Index
Description: Display an alphabetical index of contributors and authors using data from contributors.json.
Version: 1.1
Author: Aharon Varady 
*/


function cci_enqueue_assets() {
    echo '<style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            background: #F4E1C6;
            border: 2px solid #ccc;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
        }
        .tab-button.active { background: #e0c095; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .alphabet-filter { margin: 20px 0; display: flex; flex-wrap: wrap; gap: 8px; }
        .alphabet-filter button {
            background-color: #ddd;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        .alphabet-filter button.active { background-color: #bbb; }

        .contributor-entry {
            display: none; /* Hide by default */
            align-items: flex-start;
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
        }
        .contributor-avatar {
            width: 96px;
            height: 96px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
        }
        .contributor-details {
            flex: 1;
        }
        .contributor-name {
            font-size: 18px;
            font-weight: bold;
        }
        .contributor-name a { text-decoration: none; }
        .sibling-links {
            margin-top: 5px;
        }
       /* .sibling-links a {
            margin-right: 10px;
            text-decoration: none;
        } */
        .contributor-bio {
            margin-top: 10px;
        }
    </style>
    <script>
    function showTab(tabName) {
        document.querySelectorAll(".tab-content").forEach(el => el.classList.remove("active"));
        document.querySelectorAll(".tab-button").forEach(el => el.classList.remove("active"));
        document.getElementById(tabName).classList.add("active");
        event.currentTarget.classList.add("active");
    }

    function filterByLetter(letter, tab) {
        const entries = document.querySelectorAll("#" + tab + " .contributor-entry");
        entries.forEach(entry => {
            const firstLetter = entry.getAttribute("data-letter");
            if (firstLetter === letter) {
                entry.style.display = "flex"; // Show this entry
            } else {
                entry.style.display = "none"; // Hide this entry
            }
        });

        // Update active state on alphabet filter buttons
        document.querySelectorAll("#" + tab + " .alphabet-filter button").forEach(btn => btn.classList.remove("active"));
        document.querySelector(`#${tab} .alphabet-filter button[data-letter="${letter}"]`).classList.add("active");
    }
    
    // Automatically filter "A" on Active tab on page load
    window.addEventListener("load", function() {
        filterByLetter("A", "active-tab");
    });
    </script>';
}
add_action('wp_head', 'cci_enqueue_assets');

function cci_render_contributors_index() {
    // Transient check (early return if cached)
    $cache_key = 'cci_rendered_index_html';
    $cached_html = get_transient($cache_key);
    if ($cached_html !== false) {
        return $cached_html;
    }
    
    $json_path = wp_upload_dir()['basedir'] . '/contributors_by_id.json';
    if (!file_exists($json_path)) {
        return '<p>Error: contributors_by_id.json not found.</p>';
    }

    $contributors_by_id = json_decode(file_get_contents($json_path), true);
    if (!$contributors_by_id) {
        return '<p>Error decoding contributors_by_id.json.</p>';
    }

    // Build list of main entries (one per sibling group or standalone)
    $entries = [];
    foreach ($contributors_by_id as $user_id => $user) {
        $role_label = $user['role_label'];
        $sibling_ids = $user['sibling_ids'] ?? [];
        $has_siblings = !empty($sibling_ids);

        // Only include one entry per sibling group
        if ($has_siblings && $role_label !== null) continue;

        $letter = strtoupper(substr($user['last_name'], 0, 1));
        $user['initial'] = $letter;
        $entries[] = $user;
    }
    
    usort($entries, function($a, $b) {
        return strcasecmp($a['last_name'], $b['last_name']);
    });

    // Group entries by status and by letter
    $grouped = ['active' => [], 'inactive' => []];
    foreach ($entries as $entry) {
        $status = $entry['status'];
        $letter = $entry['initial'];
        $grouped[$status][$letter][] = $entry;
    }

    // Alphabet array
    $alphabet = range('A', 'Z');

    ob_start();
    echo '<div class="contributors-index">';
    
    // Tabs
    echo '<div class="tabs">';
    echo '<button class="tab-button active" onclick="showTab(\'active-tab\')">Living</button>';
    echo '<button class="tab-button" onclick="showTab(\'inactive-tab\')">Deceased</button>';
    echo '</div>';

    // Render each tab
    foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label) {
        $tab_id = $key . '-tab';
        echo '<div id="' . esc_attr($tab_id) . '" class="tab-content' . ($key === 'active' ? ' active' : '') . '">';

        // Alphabet Filter
        echo '<div class="alphabet-filter">';
        foreach ($alphabet as $letter) {
            echo '<button data-letter="' . $letter . '" onclick="filterByLetter(\'' . $letter . '\', \'' . $tab_id . '\')">' . $letter . '</button>';
        }
        echo '</div>';

        // Contributor entries
        foreach ($alphabet as $letter) {
            if (!isset($grouped[$key][$letter])) continue;

            foreach ($grouped[$key][$letter] as $user) {
                $avatar_url = esc_url($user['avatar_url']);
                $displayname = esc_html($user['display_name']);
                $first = esc_html($user['first_name']);
                $last = esc_html($user['last_name']);
                $author_slug = esc_attr($user['author_slug']);
                $profile_link = home_url('/profile/' . $author_slug);
                $role_label = $user['role_label'];
                $sibling_ids = $user['sibling_ids'] ?? [];
                $bio = $user['description']; // Allow HTML

                echo '<div class="contributor-entry" data-letter="' . $letter . '">';
                echo '<img class="contributor-avatar" src="' . $avatar_url . '" alt="Avatar" loading="lazy">';
                echo '<div class="contributor-details">';

                // Contributor name
                echo '<div class="contributor-name">';
                if (empty($sibling_ids)) {
                    echo '<a href="' . $profile_link . '">' . $first . ' ' . $last . '</a>';
                } else {
                    echo $first . ' ' . $last;
                }

                // Role labels
                if ($role_label !== null || !empty($sibling_ids)) {
                    echo ' <span class="sibling-links">';
                    if (!empty($sibling_ids)) {
                        echo '[<a href="' . $profile_link . '">original works</a>]';
                        foreach ($sibling_ids as $sibling_id) {
                            if (isset($contributors_by_id[$sibling_id])) {
                                $sibling = $contributors_by_id[$sibling_id];
                                if (!empty($sibling['role_label'])) {
                                    $slug = esc_attr($sibling['author_slug']);
                                    $link = home_url('/profile/' . $slug);
                                    $label = esc_html($sibling['role_label']);
                                    echo ' [<a href="' . $link . '">' . $label . '</a>]';
                                }
                            }
                        }
                    } elseif ($role_label !== null) {
                        echo '(' . esc_html($role_label) . ')';
                    }
                    echo '</span>';
                }

                echo '</div>'; // .contributor-name

                if (!empty($bio)) {
                    echo '<div class="contributor-bio">' . $bio . '</div>';
                }

                echo '</div>'; // .contributor-details
                echo '</div>'; // .contributor-entry
            }
        }

        echo '</div>'; // .tab-content
    }

    echo '</div>'; // .contributors-index

    // Save to transient cache
    $output = ob_get_clean();
    set_transient($cache_key, $output, 12 * HOUR_IN_SECONDS);
    return $output;
}
add_shortcode('custom_contributors_index', 'cci_render_contributors_index');
