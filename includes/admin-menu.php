<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add admin menu
function abpcwa_add_admin_menu() {
    add_menu_page(
        'AI Bulk Page Creator',
        'AI Bulk Pages',
        'manage_options',
        'ai-bulk-page-creator',
        'abpcwa_admin_page',
        ABPCWA_PLUGIN_URL . 'assets/images/dg10-logo.svg',
        25
    );
}
add_action('admin_menu', 'abpcwa_add_admin_menu');

// Admin page content
function abpcwa_admin_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'manual';
    ?>
    <div class="wrap">
        <h1>AI Bulk Page Creator with Attributes</h1>
        <div class="nav-tab-wrapper">
            <a href="?page=ai-bulk-page-creator&tab=manual" class="nav-tab <?php echo $active_tab == 'manual' ? 'nav-tab-active' : ''; ?>">Manual Creation</a>
            <a href="?page=ai-bulk-page-creator&tab=csv" class="nav-tab <?php echo $active_tab == 'csv' ? 'nav-tab-active' : ''; ?>">CSV Upload</a>
            <a href="?page=ai-bulk-page-creator&tab=ai" class="nav-tab <?php echo $active_tab == 'ai' ? 'nav-tab-active' : ''; ?>">Generate with AI</a>
            <a href="?page=ai-bulk-page-creator&tab=schema" class="nav-tab <?php echo $active_tab == 'schema' ? 'nav-tab-active' : ''; ?>">Schema Generator</a>
            <a href="?page=ai-bulk-page-creator&tab=menu" class="nav-tab <?php echo $active_tab == 'menu' ? 'nav-tab-active' : ''; ?>">Menu Generator</a>
            <a href="?page=ai-bulk-page-creator&tab=hierarchy" class="nav-tab <?php echo $active_tab == 'hierarchy' ? 'nav-tab-active' : ''; ?>">Page Hierarchy</a>
            <a href="?page=ai-bulk-page-creator&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        </div>
        <?php
        if ($active_tab == 'manual') {
            abpcwa_manual_creation_tab();
        } elseif ($active_tab == 'csv') {
            abpcwa_csv_upload_tab();
        } elseif ($active_tab == 'ai') {
            abpcwa_ai_generation_tab();
        } elseif ($active_tab == 'schema') {
            abpcwa_schema_generator_tab();
        } elseif ($active_tab == 'menu') {
            abpcwa_menu_generator_tab();
        } elseif ($active_tab == 'hierarchy') {
            abpcwa_hierarchy_tab();
        } elseif ($active_tab == 'settings') {
            abpcwa_settings_tab();
        }
        ?>
        <div class="dg10-footer-promo">
            <p>
                This plugin is brought to you by <a href="https://www.dg10.agency" target="_blank">DG10 Agency</a>. 
                This is an open-source project. Feel free to <a href="<?php echo esc_url(ABPCWA_GITHUB_URL); ?>" target="_blank">star us on GitHub</a>.
            </p>
        </div>
    </div>
    <?php
}

// Manual creation tab content
function abpcwa_manual_creation_tab() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('abpcwa_manual_create_pages'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Page Titles</th>
                <td>
                    <textarea name="abpcwa_titles" id="abpcwa_titles" rows="10" cols="50" class="large-text"></textarea>
                    <p class="description">
                        Enter one page title per line. Use hyphens for nesting.<br>
                        - Use <code>:+</code> for the page excerpt (meta description).<br>
                        - Use <code>:*</code> for featured image URL.<br>
                        - Use <code>::template=template-name.php</code> for page template.<br>
                        - Use <code>::status=draft</code> for post status (publish, draft, private, pending).<br>
                        - <strong>SEO slugs are automatically generated</strong> from page titles (max 72 chars).
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button('Create Pages'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_manual_create_pages')) {
        abpcwa_create_pages_manually($_POST['abpcwa_titles']);
    }
}

// CSV upload tab content
function abpcwa_csv_upload_tab() {
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('abpcwa_csv_upload'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">CSV File</th>
                <td>
                    <input type="file" name="abpcwa_csv_file" id="abpcwa_csv_file" accept=".csv">
                    <p class="description">
                        Upload a CSV file with the following columns: <code>post_title</code>, <code>slug</code> (optional), <code>post_parent</code>, <code>meta_description</code>, <code>featured_image</code>, <code>page_template</code>, <code>post_status</code>.
                        <br>The <code>post_parent</code> column should contain the title of the parent page.
                        <br><code>slug</code> is optional - if empty, SEO-optimized slugs are automatically generated.
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button('Upload and Create Pages'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_csv_upload')) {
        if (isset($_FILES['abpcwa_csv_file']) && !empty($_FILES['abpcwa_csv_file']['tmp_name'])) {
            abpcwa_create_pages_from_csv($_FILES['abpcwa_csv_file']);
        } else {
            echo '<div class="notice notice-error"><p>Please select a CSV file to upload.</p></div>';
        }
    }
}

// Menu generator tab content
function abpcwa_menu_generator_tab() {
    // Handle menu generation requests
    if (isset($_POST['generate_menu']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'abpcwa_generate_menu')) {
        $menu_type = isset($_POST['menu_type']) ? sanitize_key($_POST['menu_type']) : '';
        
        switch ($menu_type) {
            case 'universal_bottom':
                $result = abpcwa_generate_universal_bottom_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Universal Bottom Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Universal Bottom Menu.</p></div>';
                }
                break;
                
            case 'services':
                $result = abpcwa_generate_services_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Services Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible"><p>No service pages found to create Services Menu.</p></div>';
                }
                break;
                
            case 'company':
                $result = abpcwa_generate_company_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Company Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible"><p>No company pages found to create Company Menu.</p></div>';
                }
                break;
        }
    }
    ?>
    <div class="wrap">
        <h2>Menu Generator</h2>
        <p>Automatically generate WordPress menus based on your created pages.</p>
        
        <div class="menu-generator-options">
            <form method="post" action="">
                <?php wp_nonce_field('abpcwa_generate_menu'); ?>
                
                <div class="menu-option-card">
                    <h3>Universal Bottom Menu</h3>
                    <p>Creates a comprehensive footer menu with:</p>
                    <ul>
                        <li>Home link</li>
                        <li>All legal pages (Privacy Policy, Terms, etc.)</li>
                        <li>Sitemap link (from settings)</li>
                        <li>Contact page (if exists)</li>
                    </ul>
                    <input type="hidden" name="menu_type" value="universal_bottom">
                    <?php submit_button('Generate Universal Bottom Menu', 'primary', 'generate_menu'); ?>
                </div>
            </form>
            
            <form method="post" action="">
                <?php wp_nonce_field('abpcwa_generate_menu'); ?>
                
                <div class="menu-option-card">
                    <h3>Services Menu</h3>
                    <p>Creates a menu with all service-related pages:</p>
                    <ul>
                        <li>Detects pages with "service", "solution", "offer", or "package" in title</li>
                        <li>Includes a main "Services" link</li>
                        <li>Perfect for header navigation</li>
                    </ul>
                    <input type="hidden" name="menu_type" value="services">
                    <?php submit_button('Generate Services Menu', 'primary', 'generate_menu'); ?>
                </div>
            </form>
            
            <form method="post" action="">
                <?php wp_nonce_field('abpcwa_generate_menu'); ?>
                
                <div class="menu-option-card">
                    <h3>Company Menu</h3>
                    <p>Creates a menu with company information pages:</p>
                    <ul>
                        <li>Detects pages like About, Team, Mission, Contact</li>
                        <li>Ideal for footer or secondary navigation</li>
                        <li>Includes all company-related content</li>
                    </ul>
                    <input type="hidden" name="menu_type" value="company">
                    <?php submit_button('Generate Company Menu', 'primary', 'generate_menu'); ?>
                </div>
            </form>
        </div>
        
        <div class="menu-generator-info">
            <h3>How it works:</h3>
            <ul>
                <li>Menus are created in WordPress Appearance → Menus</li>
                <li>Universal Bottom Menu tries to auto-assign to footer location</li>
                <li>You can manually assign menus to locations if needed</li>
                <li>Existing menus with the same name will be replaced</li>
            </ul>
            
            <h3>Note:</h3>
            <p>Make sure you have created pages before generating menus. The generator detects pages based on their titles and content.</p>
        </div>
    </div>
    
    <style>
    .menu-generator-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    
    .menu-option-card {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    }
    
    .menu-option-card h3 {
        margin-top: 0;
        color: #2271b1;
    }
    
    .menu-option-card ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .menu-option-card li {
        margin-bottom: 5px;
    }
    
    .menu-generator-info {
        background: #f6f7f7;
        padding: 20px;
        border-left: 4px solid #2271b1;
        margin-top: 30px;
    }
    </style>
    <?php
}
