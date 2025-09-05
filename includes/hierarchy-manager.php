<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hierarchy tab content
function abpcwa_hierarchy_tab() {
    ?>
    <div class="abpcwa-hierarchy-container">
        <div class="abpcwa-hierarchy-header">
            <h2>Page Hierarchy Visualizer</h2>
            <p class="description">Visualize and manage your page hierarchy. This is a <strong>read-only</strong> view for visualization purposes only.</p>
            <div class="abpcwa-view-controls">
                <button class="button button-primary" data-view="tree">Tree View</button>
                <button class="button" data-view="mindmap">Mind Map</button>
                <button class="button" data-view="orgchart">Org Chart</button>
                <button class="button" data-view="grid">Grid View</button>
            </div>
            <div class="abpcwa-hierarchy-controls">
                <button id="abpcwa-expand-all" class="button">Expand All</button>
                <button id="abpcwa-collapse-all" class="button">Collapse All</button>
                <span class="spinner" id="abpcwa-hierarchy-spinner"></span>
            </div>
        </div>
        
        <div class="abpcwa-hierarchy-search">
            <input type="text" id="abpcwa-hierarchy-search" placeholder="Search pages..." class="regular-text">
        </div>
        
        <div id="abpcwa-hierarchy-view-container">
            <div id="abpcwa-hierarchy-tree" class="abpcwa-hierarchy-view active-view">
                <div class="abpcwa-loading">Loading page hierarchy...</div>
            </div>
            <div id="abpcwa-hierarchy-mindmap" class="abpcwa-hierarchy-view"></div>
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
function abpcwa_get_page_hierarchy() {
    $pages = get_pages(array(
        'sort_column' => 'menu_order, post_title',
        'sort_order' => 'ASC',
        'hierarchical' => 0, // Get flat list, we'll build hierarchy in JS
    ));

    $hierarchy_data = array();
    
    foreach ($pages as $page) {
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
                'href' => get_edit_post_link($page->ID),
                'target' => '_blank',
                'title' => 'Edit: ' . esc_attr($page->post_title)
            )
        );
    }

    return $hierarchy_data;
}

// Register REST API endpoint for read-only access
function abpcwa_register_hierarchy_rest_routes() {
    register_rest_route('abpcwa/v1', '/hierarchy', array(
        'methods' => 'GET',
        'callback' => 'abpcwa_rest_get_hierarchy',
        'permission_callback' => function () {
            return current_user_can('edit_pages');
        }
    ));
}

// Ensure REST API is loaded early enough
function abpcwa_init_hierarchy() {
    abpcwa_register_hierarchy_rest_routes();
}
add_action('init', 'abpcwa_init_hierarchy', 1);

// REST: Get hierarchy data (read-only)
function abpcwa_rest_get_hierarchy() {
    try {
        $hierarchy_data = abpcwa_get_page_hierarchy();
        return rest_ensure_response($hierarchy_data);
    } catch (Exception $e) {
        return new WP_Error('hierarchy_error', $e->getMessage(), array('status' => 500));
    }
}

// Enqueue hierarchy assets
function abpcwa_enqueue_hierarchy_assets($hook) {
    if ($hook !== 'toplevel_page_ai-bulk-page-creator') {
        return;
    }

    if (isset($_GET['tab']) && $_GET['tab'] === 'hierarchy') {
        // Enqueue jsTree
        wp_enqueue_style('jstree', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/themes/default/style.min.css');
        wp_enqueue_script('jstree', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/jstree.min.js', array('jquery'), '3.3.15', true);
        
        // Enqueue D3.js for Mind Map and Org Chart
        wp_enqueue_script('d3', 'https://d3js.org/d3.v7.min.js', array(), '7.0.0', true);

        // Enqueue our hierarchy scripts
        wp_enqueue_script('abpcwa-hierarchy', ABPCWA_PLUGIN_URL . 'assets/js/hierarchy.js', array('jquery', 'jstree', 'd3'), null, true);
        wp_enqueue_style('abpcwa-hierarchy', ABPCWA_PLUGIN_URL . 'assets/css/hierarchy.css');
        
        // Localize script with data
        wp_localize_script('abpcwa-hierarchy', 'abpcwaHierarchy', array(
            'rest_url' => rest_url('abpcwa/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'loading' => 'Loading page hierarchy...',
                'search' => 'Search pages...',
                'readonly_note' => 'Visualization only - use WordPress editor to modify hierarchy'
            )
        ));
    }
}
add_action('admin_enqueue_scripts', 'abpcwa_enqueue_hierarchy_assets');
