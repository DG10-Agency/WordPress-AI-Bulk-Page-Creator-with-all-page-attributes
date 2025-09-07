<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hierarchy tab content
function abpcwa_hierarchy_tab() {
    // Debug: Log that hierarchy tab is being loaded
    error_log('AIOPMS: Loading hierarchy tab');
    ?>
    <div class="abpcwa-hierarchy-container">
        <div class="abpcwa-hierarchy-header">
            <p class="description">Visualize and manage your page hierarchy. This is a <strong>read-only</strong> view for visualization purposes only.</p>
            <div class="abpcwa-view-controls">
                <button class="button button-primary" data-view="tree">Tree View</button>
                <button class="button" data-view="orgchart">Org Chart</button>
                <button class="button" data-view="grid">Grid View</button>
            </div>
        </div>
        <div class="abpcwa-hierarchy-controls">
            <button id="abpcwa-expand-all" class="button">Expand All</button>
            <button id="abpcwa-collapse-all" class="button">Collapse All</button>
            <span class="spinner" id="abpcwa-hierarchy-spinner"></span>
        </div>

        <div class="abpcwa-hierarchy-search">
            <input type="text" id="abpcwa-hierarchy-search" placeholder="Search pages..." class="regular-text">
        </div>

        <div class="abpcwa-hierarchy-actions">
            <div class="abpcwa-copy-options">
                <button id="abpcwa-copy-hierarchy" class="button">Copy Hierarchy</button>
            </div>
            <div class="abpcwa-export-options">
                <button id="abpcwa-export-csv" class="button button-primary">Export as CSV</button>
                <button id="abpcwa-export-markdown" class="button">Export as Markdown</button>
            </div>
        </div>

        <div id="abpcwa-hierarchy-view-container">
            <div id="abpcwa-hierarchy-tree" class="abpcwa-hierarchy-view active-view">
                <div class="abpcwa-loading">Loading page hierarchy...</div>
            </div>
            <div id="abpcwa-hierarchy-orgchart" class="abpcwa-hierarchy-view"></div>
            <div id="abpcwa-hierarchy-grid" class="abpcwa-hierarchy-view"></div>
        </div>

        <div class="abpcwa-hierarchy-note">
            <p><strong>Note:</strong> This is a visualization tool only. To change page hierarchy, please use the standard WordPress page editor where you can set parent pages using the native dropdown menu.</p>
        </div>
    </div>
    <?php
}

// Get page hierarchy data (read-only)
function aiopms_get_page_hierarchy() {
    try {
        $pages = get_pages(array(
            'sort_column' => 'menu_order, post_title',
            'sort_order' => 'ASC',
            'hierarchical' => 0, // Get flat list, we'll build hierarchy in JS
        ));

        $hierarchy_data = array();
        
        // Debug: Log page count
        error_log('AIOPMS: Found ' . count($pages) . ' pages');
    
    // Add standard pages
    foreach ($pages as $page) {
        // Get author information
        $author = get_userdata($page->post_author);
        $author_name = $author ? $author->display_name : 'Unknown';
        $author_login = $author ? $author->user_login : 'unknown';
        
        // Format dates
        $publish_date = date('M j, Y', strtotime($page->post_date));
        $modified_date = date('M j, Y', strtotime($page->post_modified));
        
        $hierarchy_data[] = array(
            'id' => $page->ID,
            'parent' => $page->post_parent ? $page->post_parent : '#',
            'text' => esc_html($page->post_title),
            'type' => 'page',
            'state' => array(
                'opened' => false,
                'disabled' => false
            ),
            'li_attr' => array(
                'data-page-id' => $page->ID,
                'data-page-status' => $page->post_status
            ),
            'a_attr' => array(
                'href' => get_permalink($page->ID),
                'target' => '_blank',
                'title' => 'View: ' . esc_attr($page->post_title)
            ),
            'meta' => array(
                'description' => esc_html($page->post_excerpt),
                'published' => $publish_date,
                'modified' => $modified_date,
                'author' => $author_name . ' (' . $author_login . ')',
                'status' => $page->post_status
            )
        );
    }

    // Add custom post types if enabled in settings
    $settings = get_option('aiopms_cpt_settings', array());
    if (isset($settings['include_in_hierarchy']) && $settings['include_in_hierarchy']) {
        $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
        
        if (is_array($dynamic_cpts)) {
            foreach ($dynamic_cpts as $post_type => $cpt_info) {
                if (!is_array($cpt_info) || !isset($cpt_info['label'])) {
                    continue; // Skip invalid CPT entries
                }
            // Add CPT archive as a parent node
            $archive_url = get_post_type_archive_link($post_type);
            if ($archive_url) {
                $hierarchy_data[] = array(
                    'id' => 'cpt_archive_' . $post_type,
                    'parent' => '#',
                    'text' => $cpt_info['label'] . ' (Archive)',
                    'type' => 'cpt_archive',
                    'state' => array(
                        'opened' => false,
                        'disabled' => false
                    ),
                    'li_attr' => array(
                        'data-cpt-type' => $post_type,
                        'data-cpt-archive' => 'true'
                    ),
                    'a_attr' => array(
                        'href' => $archive_url,
                        'target' => '_blank',
                        'title' => 'View Archive: ' . esc_attr($cpt_info['label'])
                    ),
                    'meta' => array(
                        'description' => 'Archive page for ' . $cpt_info['label'],
                        'type' => 'Custom Post Type Archive'
                    )
                );
            }
            
            // Add individual CPT posts as children
            $cpt_posts = get_posts(array(
                'post_type' => $post_type,
                'numberposts' => -1,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            foreach ($cpt_posts as $post) {
                $author = get_userdata($post->post_author);
                $author_name = $author ? $author->display_name : 'Unknown';
                $author_login = $author ? $author->user_login : 'unknown';
                
                $publish_date = date('M j, Y', strtotime($post->post_date));
                $modified_date = date('M j, Y', strtotime($post->post_modified));
                
                $hierarchy_data[] = array(
                    'id' => 'cpt_' . $post->ID,
                    'parent' => 'cpt_archive_' . $post_type,
                    'text' => esc_html($post->post_title),
                    'type' => 'cpt_post',
                    'state' => array(
                        'opened' => false,
                        'disabled' => false
                    ),
                    'li_attr' => array(
                        'data-post-id' => $post->ID,
                        'data-post-type' => $post_type,
                        'data-post-status' => $post->post_status
                    ),
                    'a_attr' => array(
                        'href' => get_permalink($post->ID),
                        'target' => '_blank',
                        'title' => 'View: ' . esc_attr($post->post_title)
                    ),
                    'meta' => array(
                        'description' => esc_html($post->post_excerpt),
                        'published' => $publish_date,
                        'modified' => $modified_date,
                        'author' => $author_name . ' (' . $author_login . ')',
                        'status' => $post->post_status,
                        'type' => $cpt_info['label']
                    )
                );
            }
        }
        }
    }

        return $hierarchy_data;
    } catch (Exception $e) {
        error_log('AIOPMS: Error in get_page_hierarchy: ' . $e->getMessage());
        return array(); // Return empty array on error
    }
}

// Register REST API endpoint for read-only access
function aiopms_register_hierarchy_rest_routes() {
    register_rest_route('aiopms/v1', '/hierarchy', array(
        'methods' => 'GET',
        'callback' => 'aiopms_rest_get_hierarchy',
        'permission_callback' => function () {
            return current_user_can('edit_pages');
        }
    ));
    
    // Debug: Log that REST API route is being registered
    error_log('AIOPMS: Registering REST API route: aiopms/v1/hierarchy');
}

// Register AJAX handlers for file exports
function aiopms_register_export_ajax_handlers() {
    add_action('wp_ajax_aiopms_export_csv', 'aiopms_ajax_export_hierarchy_csv');
    add_action('wp_ajax_aiopms_export_markdown', 'aiopms_ajax_export_hierarchy_markdown');
    add_action('wp_ajax_aiopms_export_json', 'aiopms_ajax_export_hierarchy_json');
}

// Ensure REST API and AJAX handlers are loaded early enough
function aiopms_init_hierarchy() {
    // Debug: Log that hierarchy initialization is starting
    error_log('AIOPMS: Initializing hierarchy system');
    
    aiopms_register_hierarchy_rest_routes();
    aiopms_register_export_ajax_handlers();
    
    // Debug: Log that hierarchy initialization is complete
    error_log('AIOPMS: Hierarchy system initialized');
}
add_action('init', 'aiopms_init_hierarchy', 1);

// REST: Get hierarchy data (read-only)
function aiopms_rest_get_hierarchy($request) {
    try {
        // Debug: Log that REST API callback is being called
        error_log('AIOPMS: REST API callback called');
        
        $hierarchy_data = aiopms_get_page_hierarchy();
        
        // Debug: Log the data to help troubleshoot
        error_log('AIOPMS Hierarchy Data: ' . print_r($hierarchy_data, true));
        
        return rest_ensure_response($hierarchy_data);
    } catch (Exception $e) {
        error_log('AIOPMS Hierarchy Error: ' . $e->getMessage());
        return new WP_Error('hierarchy_error', $e->getMessage(), array('status' => 500));
    }
}

// AJAX: Export hierarchy data as CSV
function aiopms_ajax_export_hierarchy_csv() {
    // Verify nonce for security
    if (!wp_verify_nonce($_GET['nonce'], 'aiopms_export_nonce')) {
        wp_die('Security check failed');
    }

    // Check user permissions
    if (!current_user_can('edit_pages')) {
        wp_die('Insufficient permissions');
    }

    try {
        $pages = get_pages(array(
            'sort_column' => 'menu_order, post_title',
            'sort_order' => 'ASC',
            'hierarchical' => 0,
        ));

        $hierarchy_data = aiopms_build_hierarchy_for_export($pages);
        $site_title = sanitize_file_name(get_bloginfo('name'));
        $filename = $site_title . '.csv';
        $csv_content = aiopms_generate_hierarchy_csv($hierarchy_data);

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set proper headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv_content));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        // Output the CSV content
        echo $csv_content;
        exit;

    } catch (Exception $e) {
        wp_die('Export failed: ' . $e->getMessage());
    }
}

// AJAX: Export hierarchy data as Markdown
function aiopms_ajax_export_hierarchy_markdown() {
    // Verify nonce for security
    if (!wp_verify_nonce($_GET['nonce'], 'aiopms_export_nonce')) {
        wp_die('Security check failed');
    }

    // Check user permissions
    if (!current_user_can('edit_pages')) {
        wp_die('Insufficient permissions');
    }

    try {
        $pages = get_pages(array(
            'sort_column' => 'menu_order, post_title',
            'sort_order' => 'ASC',
            'hierarchical' => 0,
        ));

        $hierarchy_data = aiopms_build_hierarchy_for_export($pages);
        $site_title = sanitize_file_name(get_bloginfo('name'));
        $filename = $site_title . '.md';
        $markdown_content = aiopms_generate_hierarchy_markdown($hierarchy_data);

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set proper headers for Markdown download
        header('Content-Type: text/markdown; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($markdown_content));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        // Output the Markdown content
        echo $markdown_content;
        exit;

    } catch (Exception $e) {
        wp_die('Export failed: ' . $e->getMessage());
    }
}

// AJAX: Export hierarchy data as JSON
function aiopms_ajax_export_hierarchy_json() {
    // Verify nonce for security
    if (!wp_verify_nonce($_GET['nonce'], 'aiopms_export_nonce')) {
        wp_die('Security check failed');
    }

    // Check user permissions
    if (!current_user_can('edit_pages')) {
        wp_die('Insufficient permissions');
    }

    try {
        $pages = get_pages(array(
            'sort_column' => 'menu_order, post_title',
            'sort_order' => 'ASC',
            'hierarchical' => 0,
        ));

        $hierarchy_data = aiopms_build_hierarchy_for_export($pages);
        $site_title = sanitize_file_name(get_bloginfo('name'));
        $filename = $site_title . '.json';
        
        // Convert to JSON format with proper structure
        $json_data = array(
            'site_title' => get_bloginfo('name'),
            'export_date' => current_time('Y-m-d H:i:s'),
            'total_pages' => count($hierarchy_data['pages_by_id']),
            'hierarchy' => $hierarchy_data['pages_by_id']
        );
        
        $json_content = wp_json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set proper headers for JSON download
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json_content));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        // Output the JSON content
        echo $json_content;
        exit;

    } catch (Exception $e) {
        wp_die('Export failed: ' . $e->getMessage());
    }
}

// Enqueue hierarchy assets
function aiopms_enqueue_hierarchy_assets($hook) {
    if ($hook !== 'toplevel_page_aiopms-page-management') {
        return;
    }

    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'manual';

    if ($active_tab === 'hierarchy') {
        // Debug: Log that hierarchy assets are being enqueued
        error_log('AIOPMS: Enqueuing hierarchy assets for tab: ' . $active_tab);
        
        // Enqueue jsTree
        wp_enqueue_style('jstree', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/themes/default/style.min.css');
        wp_enqueue_script('jstree', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/jstree.min.js', array('jquery'), '3.3.15', true);
        
        // Enqueue D3.js for Mind Map and Org Chart
        wp_enqueue_script('d3', 'https://d3js.org/d3.v7.min.js', array(), '7.0.0', true);

        // Enqueue our hierarchy scripts
        wp_enqueue_script('aiopms-hierarchy', AIOPMS_PLUGIN_URL . 'assets/js/hierarchy.js', array('jquery', 'jstree', 'd3'), null, true);
        wp_enqueue_style('aiopms-hierarchy', AIOPMS_PLUGIN_URL . 'assets/css/hierarchy.css');
        
        // Localize script with data
        $localize_data = array(
            'rest_url' => rest_url('aiopms/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'loading' => 'Loading page hierarchy...',
                'search' => 'Search pages...',
                'readonly_note' => 'Visualization only - use WordPress editor to modify hierarchy'
            )
        );
        
        // Debug: Log localization data
        error_log('AIOPMS: Localizing script with data: ' . print_r($localize_data, true));
        
        wp_localize_script('aiopms-hierarchy', 'aiopmsHierarchy', $localize_data);
    }
}
add_action('admin_enqueue_scripts', 'aiopms_enqueue_hierarchy_assets');

// Build hierarchical structure for export (including all levels)
function aiopms_build_hierarchy_for_export($pages) {
    $pages_by_id = array();
    $pages_by_parent = array();

    foreach ($pages as $page) {
        $pages_by_id[$page->ID] = array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'parent' => $page->post_parent,
            'url' => get_permalink($page->ID),
            'excerpt' => $page->post_excerpt,
            'published' => $page->post_date,
            'modified' => $page->post_modified,
            'author_id' => $page->post_author,
            'status' => $page->post_status,
            'level' => 0
        );

        if (!isset($pages_by_parent[$page->post_parent])) {
            $pages_by_parent[$page->post_parent] = array();
        }
        $pages_by_parent[$page->post_parent][] = $page->ID;
    }

    // Calculate hierarchy levels
    $roots = isset($pages_by_parent[0]) ? $pages_by_parent[0] : array();

    function calculate_levels($page_id, &$pages_by_id, $pages_by_parent, $level = 0) {
        if (!isset($pages_by_id[$page_id])) return;

        $pages_by_id[$page_id]['level'] = $level;

        if (isset($pages_by_parent[$page_id])) {
            foreach ($pages_by_parent[$page_id] as $child_id) {
                calculate_levels($child_id, $pages_by_id, $pages_by_parent, $level + 1);
            }
        }
    }

    foreach ($roots as $root_id) {
        calculate_levels($root_id, $pages_by_id, $pages_by_parent, 0);
    }

    return array('pages_by_id' => $pages_by_id, 'roots' => $roots);
}

// Generate CSV from hierarchy data
function aiopms_generate_hierarchy_csv($hierarchy_data) {
    $pages_by_id = $hierarchy_data['pages_by_id'];

    $headers = array(
        'Title',
        'URL',
        'Excerpt',
        'Published Date',
        'Modified Date',
        'Author',
        'Status',
        'Hierarchy Level',
        'Parent Page ID'
    );

    $csv_lines = array();
    $csv_lines[] = implode(',', array_map('aiopms_escape_csv', $headers));

    // Sort by hierarchy and title
    $sorted_pages = array();
    function add_page_to_sorted($page_ids, $pages_by_id, &$sorted_pages, $level = 0) {
        foreach ($page_ids as $id) {
            if (isset($pages_by_id[$id])) {
                $page = $pages_by_id[$id];
                $sorted_pages[] = $page;

                // Add children recursively
                $children = array_filter($pages_by_id, function($p) use ($id) {
                    return $p['parent'] == $id;
                });
                if (!empty($children)) {
                    $child_ids = array_map(function($c) { return $c['id']; }, $children);
                    add_page_to_sorted($child_ids, $pages_by_id, $sorted_pages, $level + 1);
                }
            }
        }
    }

    $root_ids = array_keys(array_filter($pages_by_id, function($p) { return $p['level'] == 0; }));
    add_page_to_sorted($root_ids, $pages_by_id, $sorted_pages);

    foreach ($sorted_pages as $page) {
        $author = get_userdata($page['author_id']);
        $author_name = $author ? $author->display_name : 'Unknown';

        $row = array(
            $page['title'],
            $page['url'],
            $page['excerpt'],
            date('Y-m-d', strtotime($page['published'])),
            date('Y-m-d', strtotime($page['modified'])),
            $author_name,
            $page['status'],
            $page['level'],
            $page['parent'] ?: ''
        );

        $csv_lines[] = implode(',', array_map('aiopms_escape_csv', $row));
    }

    return implode("\n", $csv_lines);
}

// Generate Markdown from hierarchy data
function aiopms_generate_hierarchy_markdown($hierarchy_data) {
    $pages_by_id = $hierarchy_data['pages_by_id'];
    $roots = $hierarchy_data['roots'];

    $markdown_lines = array();
    $markdown_lines[] = '# Page Hierarchy';

    function build_markdown_level($page_ids, $pages_by_id, &$markdown_lines, $level = 0) {
        foreach ($page_ids as $id) {
            if (isset($pages_by_id[$id])) {
                $page = $pages_by_id[$id];
                $indent = str_repeat('  ', $level); // Two spaces for indentation
                $markdown_lines[] = $indent . '- [' . $page['title'] . '](' . $page['url'] . ')';

                // Find and process children
                $children = array_filter($pages_by_id, function($p) use ($id) {
                    return $p['parent'] == $id;
                });

                if (!empty($children)) {
                    $child_ids = array_map(function($c) { return $c['id']; }, $children);
                    build_markdown_level($child_ids, $pages_by_id, $markdown_lines, $level + 1);
                }
            }
        }
    }

    build_markdown_level($roots, $pages_by_id, $markdown_lines);

    return implode("\n", $markdown_lines);
}

// Helper function to escape CSV values
function aiopms_escape_csv($string) {
    // Escape quotes and wrap in quotes if contains comma, quote, or newline
    if (strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
        $string = str_replace('"', '""', $string);
        $string = '"' . $string . '"';
    }
    return $string;
}


