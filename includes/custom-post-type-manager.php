<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Custom Post Type Manager for AIOPMS
 * Handles dynamic custom post type registration, management, and integration
 */

// Initialize custom post type manager
function aiopms_init_custom_post_type_manager() {
    // Register existing dynamic CPTs on init
    add_action('init', 'aiopms_register_existing_dynamic_cpts', 20);
    
    // Add admin menu for CPT management
    add_action('admin_menu', 'aiopms_add_cpt_management_menu');
    
    // Add REST API endpoints for CPT data
    add_action('rest_api_init', 'aiopms_register_cpt_rest_endpoints');
    
    // Add CPT data to hierarchy export
    add_filter('aiopms_hierarchy_export_data', 'aiopms_add_cpt_to_hierarchy_export');
    
    // Add CPT archives to menu generation
    add_filter('aiopms_menu_generation_pages', 'aiopms_add_cpt_archives_to_menus');
    
    // Add schema generation for CPTs
    add_action('aiopms_generate_schema_for_post', 'aiopms_generate_cpt_schema', 10, 2);
}
add_action('plugins_loaded', 'aiopms_init_custom_post_type_manager');

// Register existing dynamic CPTs
function aiopms_register_existing_dynamic_cpts() {
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    
    foreach ($dynamic_cpts as $post_type => $cpt_data) {
        aiopms_register_dynamic_custom_post_type($cpt_data);
    }
}

// Add CPT management menu
function aiopms_add_cpt_management_menu() {
    add_submenu_page(
        'aiopms-page-management',
        'Custom Post Types',
        'Custom Post Types',
        'manage_options',
        'aiopms-cpt-management',
        'aiopms_cpt_management_page'
    );
}

// CPT management page
function aiopms_cpt_management_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'list';
    ?>
    <div class="wrap">
        <h1>Custom Post Types Management</h1>
        
        <nav class="nav-tab-wrapper">
            <a href="?page=aiopms-cpt-management&tab=list" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>">
                📋 Manage CPTs
            </a>
            <a href="?page=aiopms-cpt-management&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>">
                ➕ Create New CPT
            </a>
            <a href="?page=aiopms-cpt-management&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                ⚙️ Settings
            </a>
        </nav>
        
        <div class="tab-content">
            <?php
            if ($active_tab == 'list') {
                aiopms_cpt_list_tab();
            } elseif ($active_tab == 'create') {
                aiopms_cpt_create_tab();
            } elseif ($active_tab == 'settings') {
                aiopms_cpt_settings_tab();
            }
            ?>
        </div>
    </div>
    <?php
}

// CPT list tab
function aiopms_cpt_list_tab() {
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    
    // Handle CPT deletion
    if (isset($_POST['delete_cpt']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'aiopms_delete_cpt')) {
        $cpt_to_delete = sanitize_key($_POST['cpt_name']);
        if (isset($dynamic_cpts[$cpt_to_delete])) {
            unset($dynamic_cpts[$cpt_to_delete]);
            update_option('aiopms_dynamic_cpts', $dynamic_cpts);
            echo '<div class="notice notice-success"><p>Custom post type deleted successfully.</p></div>';
        }
    }
    
    ?>
    <div class="aiopms-cpt-list">
        <h2>Dynamic Custom Post Types</h2>
        <p>Manage custom post types created by the AIOPMS Advanced Mode.</p>
        
        <?php if (empty($dynamic_cpts)): ?>
            <div class="no-cpts">
                <p>No dynamic custom post types have been created yet.</p>
                <p>Use the <a href="?page=aiopms-page-management&tab=ai">AI Generation</a> tab with Advanced Mode enabled to create custom post types.</p>
            </div>
        <?php else: ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Post Type</th>
                        <th>Label</th>
                        <th>Description</th>
                        <th>Posts Count</th>
                        <th>Custom Fields</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dynamic_cpts as $post_type => $cpt_data): ?>
                        <?php
                        $posts_count = wp_count_posts($post_type);
                        $total_posts = $posts_count->publish + $posts_count->draft + $posts_count->private;
                        ?>
                        <tr>
                            <td><code><?php echo esc_html($post_type); ?></code></td>
                            <td><strong><?php echo esc_html($cpt_data['label']); ?></strong></td>
                            <td><?php echo esc_html($cpt_data['description']); ?></td>
                            <td><?php echo $total_posts; ?> posts</td>
                            <td>
                                <?php if (!empty($cpt_data['custom_fields'])): ?>
                                    <?php echo count($cpt_data['custom_fields']); ?> fields
                                <?php else: ?>
                                    No custom fields
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('edit.php?post_type=' . $post_type); ?>" class="button button-small">View Posts</a>
                                <a href="<?php echo admin_url('post-new.php?post_type=' . $post_type); ?>" class="button button-small">Add New</a>
                                <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this custom post type? This action cannot be undone.');">
                                    <?php wp_nonce_field('aiopms_delete_cpt'); ?>
                                    <input type="hidden" name="cpt_name" value="<?php echo esc_attr($post_type); ?>">
                                    <input type="submit" name="delete_cpt" value="Delete" class="button button-small button-link-delete">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

// CPT create tab
function aiopms_cpt_create_tab() {
    // Handle manual CPT creation
    if (isset($_POST['create_manual_cpt']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'aiopms_create_manual_cpt')) {
        $cpt_data = array(
            'name' => sanitize_key($_POST['cpt_name']),
            'label' => sanitize_text_field($_POST['cpt_label']),
            'description' => sanitize_textarea_field($_POST['cpt_description']),
            'custom_fields' => array()
        );
        
        // Process custom fields
        if (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
            foreach ($_POST['custom_fields'] as $field) {
                if (!empty($field['name']) && !empty($field['label'])) {
                    $cpt_data['custom_fields'][] = array(
                        'name' => sanitize_key($field['name']),
                        'label' => sanitize_text_field($field['label']),
                        'type' => sanitize_key($field['type']),
                        'description' => sanitize_text_field($field['description']),
                        'required' => isset($field['required'])
                    );
                }
            }
        }
        
        if (aiopms_register_dynamic_custom_post_type($cpt_data)) {
            echo '<div class="notice notice-success"><p>Custom post type created successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Failed to create custom post type. Please check your input.</p></div>';
        }
    }
    
    ?>
    <div class="aiopms-cpt-create">
        <h2>Create Custom Post Type</h2>
        <p>Manually create a custom post type with custom fields.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('aiopms_create_manual_cpt'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Post Type Slug</th>
                    <td>
                        <input type="text" name="cpt_name" class="regular-text" required placeholder="e.g., portfolio, case_study">
                        <p class="description">Lowercase, no spaces, use underscores for multiple words</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Display Label</th>
                    <td>
                        <input type="text" name="cpt_label" class="regular-text" required placeholder="e.g., Portfolio, Case Study">
                        <p class="description">Human-readable name for the post type</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Description</th>
                    <td>
                        <textarea name="cpt_description" rows="3" class="large-text" placeholder="Brief description of what this post type is for"></textarea>
                    </td>
                </tr>
            </table>
            
            <h3>Custom Fields</h3>
            <div id="custom-fields-container">
                <div class="custom-field-row">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Field Name</th>
                            <th scope="row">Field Label</th>
                            <th scope="row">Field Type</th>
                            <th scope="row">Description</th>
                            <th scope="row">Required</th>
                            <th scope="row">Actions</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="custom_fields[0][name]" class="regular-text" placeholder="field_slug"></td>
                            <td><input type="text" name="custom_fields[0][label]" class="regular-text" placeholder="Field Label"></td>
                            <td>
                                <select name="custom_fields[0][type]">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="url">URL</option>
                                    <option value="image">Image URL</option>
                                    <option value="select">Select</option>
                                </select>
                            </td>
                            <td><input type="text" name="custom_fields[0][description]" class="regular-text" placeholder="Field description"></td>
                            <td><input type="checkbox" name="custom_fields[0][required]"></td>
                            <td><button type="button" class="button remove-field">Remove</button></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <p>
                <button type="button" id="add-custom-field" class="button">Add Custom Field</button>
            </p>
            
            <?php submit_button('Create Custom Post Type', 'primary', 'create_manual_cpt'); ?>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        let fieldIndex = 1;
        
        $('#add-custom-field').on('click', function() {
            const fieldRow = `
                <div class="custom-field-row">
                    <table class="form-table">
                        <tr>
                            <td><input type="text" name="custom_fields[${fieldIndex}][name]" class="regular-text" placeholder="field_slug"></td>
                            <td><input type="text" name="custom_fields[${fieldIndex}][label]" class="regular-text" placeholder="Field Label"></td>
                            <td>
                                <select name="custom_fields[${fieldIndex}][type]">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="url">URL</option>
                                    <option value="image">Image URL</option>
                                    <option value="select">Select</option>
                                </select>
                            </td>
                            <td><input type="text" name="custom_fields[${fieldIndex}][description]" class="regular-text" placeholder="Field description"></td>
                            <td><input type="checkbox" name="custom_fields[${fieldIndex}][required]"></td>
                            <td><button type="button" class="button remove-field">Remove</button></td>
                        </tr>
                    </table>
                </div>
            `;
            
            $('#custom-fields-container').append(fieldRow);
            fieldIndex++;
        });
        
        $(document).on('click', '.remove-field', function() {
            $(this).closest('.custom-field-row').remove();
        });
    });
    </script>
    
    <style>
    .custom-field-row {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
    }
    
    .custom-field-row table {
        margin: 0;
    }
    
    .custom-field-row th,
    .custom-field-row td {
        padding: 8px;
        vertical-align: middle;
    }
    </style>
    <?php
}

// CPT settings tab
function aiopms_cpt_settings_tab() {
    // Handle settings save
    if (isset($_POST['save_cpt_settings']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'aiopms_save_cpt_settings')) {
        $settings = array(
            'auto_schema_generation' => isset($_POST['auto_schema_generation']),
            'include_in_menus' => isset($_POST['include_in_menus']),
            'include_in_hierarchy' => isset($_POST['include_in_hierarchy']),
            'auto_generate_sample_content' => isset($_POST['auto_generate_sample_content'])
        );
        
        update_option('aiopms_cpt_settings', $settings);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $settings = get_option('aiopms_cpt_settings', array(
        'auto_schema_generation' => true,
        'include_in_menus' => true,
        'include_in_hierarchy' => true,
        'auto_generate_sample_content' => true
    ));
    
    ?>
    <div class="aiopms-cpt-settings">
        <h2>Custom Post Type Settings</h2>
        <p>Configure how custom post types integrate with other AIOPMS features.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('aiopms_save_cpt_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Auto Schema Generation</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_schema_generation" value="1" <?php checked($settings['auto_schema_generation']); ?>>
                            Automatically generate schema markup for custom post types
                        </label>
                        <p class="description">When enabled, schema markup will be generated for all custom post type entries</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Include in Menu Generation</th>
                    <td>
                        <label>
                            <input type="checkbox" name="include_in_menus" value="1" <?php checked($settings['include_in_menus']); ?>>
                            Include custom post type archives in menu generation
                        </label>
                        <p class="description">When enabled, custom post type archive pages will be included in generated menus</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Include in Hierarchy</th>
                    <td>
                        <label>
                            <input type="checkbox" name="include_in_hierarchy" value="1" <?php checked($settings['include_in_hierarchy']); ?>>
                            Include custom post types in hierarchy view and export
                        </label>
                        <p class="description">When enabled, custom post types will appear in the page hierarchy</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Auto Generate Sample Content</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_generate_sample_content" value="1" <?php checked($settings['auto_generate_sample_content']); ?>>
                            Automatically create sample entries when creating custom post types
                        </label>
                        <p class="description">When enabled, sample entries will be created automatically for new custom post types</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Settings', 'primary', 'save_cpt_settings'); ?>
        </form>
    </div>
    <?php
}

// Register REST API endpoints for CPT data
function aiopms_register_cpt_rest_endpoints() {
    register_rest_route('aiopms/v1', '/cpts', array(
        'methods' => 'GET',
        'callback' => 'aiopms_get_cpts_rest_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
}

// Get CPTs data for REST API
function aiopms_get_cpts_rest_data($request) {
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    $cpt_data = array();
    
    foreach ($dynamic_cpts as $post_type => $cpt_info) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        
        $cpt_data[] = array(
            'post_type' => $post_type,
            'label' => $cpt_info['label'],
            'description' => $cpt_info['description'],
            'posts_count' => count($posts),
            'custom_fields' => $cpt_info['custom_fields'] ?? array(),
            'posts' => array_map(function($post) {
                return array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'status' => $post->post_status,
                    'url' => get_permalink($post->ID)
                );
            }, $posts)
        );
    }
    
    return rest_ensure_response($cpt_data);
}

// Add CPT data to hierarchy export
function aiopms_add_cpt_to_hierarchy_export($data) {
    $settings = get_option('aiopms_cpt_settings', array());
    if (!isset($settings['include_in_hierarchy']) || !$settings['include_in_hierarchy']) {
        return $data;
    }
    
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    
    foreach ($dynamic_cpts as $post_type => $cpt_info) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'numberposts' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($posts as $post) {
            $data[] = array(
                'id' => 'cpt_' . $post->ID,
                'text' => $cpt_info['label'] . ': ' . $post->post_title,
                'parent' => '#',
                'type' => 'cpt',
                'post_type' => $post_type,
                'url' => get_permalink($post->ID)
            );
        }
    }
    
    return $data;
}

// Add CPT archives to menu generation
function aiopms_add_cpt_archives_to_menus($pages) {
    $settings = get_option('aiopms_cpt_settings', array());
    if (!isset($settings['include_in_menus']) || !$settings['include_in_menus']) {
        return $pages;
    }
    
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    
    foreach ($dynamic_cpts as $post_type => $cpt_info) {
        $archive_url = get_post_type_archive_link($post_type);
        if ($archive_url) {
            $pages[] = array(
                'title' => $cpt_info['label'],
                'url' => $archive_url,
                'type' => 'cpt_archive',
                'post_type' => $post_type
            );
        }
    }
    
    return $pages;
}

// Generate schema markup for custom post types
function aiopms_generate_cpt_schema($post_id, $post) {
    $settings = get_option('aiopms_cpt_settings', array());
    if (!isset($settings['auto_schema_generation']) || !$settings['auto_schema_generation']) {
        return;
    }
    
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    $post_type = $post->post_type;
    
    if (!isset($dynamic_cpts[$post_type])) {
        return;
    }
    
    $cpt_info = $dynamic_cpts[$post_type];
    
    // Generate appropriate schema based on post type
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $post->post_title,
        'description' => wp_trim_words($post->post_content, 20),
        'url' => get_permalink($post_id),
        'datePublished' => get_the_date('c', $post_id),
        'dateModified' => get_the_modified_date('c', $post_id)
    );
    
    // Add custom fields to schema
    if (!empty($cpt_info['custom_fields'])) {
        foreach ($cpt_info['custom_fields'] as $field) {
            $value = get_post_meta($post_id, $field['name'], true);
            if (!empty($value)) {
                $schema[$field['name']] = $value;
            }
        }
    }
    
    // Add schema to post meta
    update_post_meta($post_id, '_aiopms_schema_markup', $schema);
}

// Helper function to get all dynamic CPTs
function aiopms_get_dynamic_cpts() {
    return get_option('aiopms_dynamic_cpts', []);
}

// Helper function to check if a post type is dynamic
function aiopms_is_dynamic_cpt($post_type) {
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    return isset($dynamic_cpts[$post_type]);
}

// Helper function to get CPT info
function aiopms_get_cpt_info($post_type) {
    $dynamic_cpts = get_option('aiopms_dynamic_cpts', []);
    return isset($dynamic_cpts[$post_type]) ? $dynamic_cpts[$post_type] : null;
}
