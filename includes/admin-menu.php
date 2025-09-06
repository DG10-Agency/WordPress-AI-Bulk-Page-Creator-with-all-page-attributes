<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add admin menu
function aiopms_add_admin_menu() {
    add_menu_page(
        'AIOPMS - Page Management',
        'AIOPMS',
        'manage_options',
        'aiopms-page-management',
        'aiopms_admin_page',
        AIOPMS_PLUGIN_URL . 'assets/images/dg10-logo.svg',
        25
    );
}
add_action('admin_menu', 'aiopms_add_admin_menu');

// Admin page content
function aiopms_admin_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'manual';
    ?>
    <div class="wrap">
        <h1>AIOPMS - All In One Page Management System</h1>
        <div class="nav-tab-wrapper">
            <a href="?page=aiopms-page-management&tab=manual" class="nav-tab <?php echo $active_tab == 'manual' ? 'nav-tab-active' : ''; ?>">Manual Creation</a>
            <a href="?page=aiopms-page-management&tab=csv" class="nav-tab <?php echo $active_tab == 'csv' ? 'nav-tab-active' : ''; ?>">CSV Upload</a>
            <a href="?page=aiopms-page-management&tab=ai" class="nav-tab <?php echo $active_tab == 'ai' ? 'nav-tab-active' : ''; ?>">Generate with AI</a>
            <a href="?page=aiopms-page-management&tab=schema" class="nav-tab <?php echo $active_tab == 'schema' ? 'nav-tab-active' : ''; ?>">Schema Generator</a>
            <a href="?page=aiopms-page-management&tab=menu" class="nav-tab <?php echo $active_tab == 'menu' ? 'nav-tab-active' : ''; ?>">Menu Generator</a>
            <a href="?page=aiopms-page-management&tab=hierarchy" class="nav-tab <?php echo $active_tab == 'hierarchy' ? 'nav-tab-active' : ''; ?>">Page Hierarchy</a>
            <a href="?page=aiopms-page-management&tab=export" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>">Hierarchy Export</a>
            <a href="?page=aiopms-page-management&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        </div>
        <?php
        if ($active_tab == 'manual') {
            aiopms_manual_creation_tab();
        } elseif ($active_tab == 'csv') {
            aiopms_csv_upload_tab();
        } elseif ($active_tab == 'ai') {
            aiopms_ai_generation_tab();
        } elseif ($active_tab == 'schema') {
            aiopms_schema_generator_tab();
        } elseif ($active_tab == 'menu') {
            aiopms_menu_generator_tab();
        } elseif ($active_tab == 'hierarchy') {
            abpcwa_hierarchy_tab();
        } elseif ($active_tab == 'export') {
            aiopms_hierarchy_export_tab();
        } elseif ($active_tab == 'settings') {
            aiopms_settings_tab();
        }
        ?>
        <div class="dg10-footer-promo">
            <p>
                This plugin is brought to you by <a href="https://www.dg10.agency" target="_blank">DG10 Agency</a>. 
                This is an open-source project. Feel free to <a href="<?php echo esc_url(AIOPMS_GITHUB_URL); ?>" target="_blank">star us on GitHub</a>.
            </p>
        </div>
    </div>
    <?php
}

// Manual creation tab content
function aiopms_manual_creation_tab() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('aiopms_manual_create_pages'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Page Titles</th>
                <td>
                    <textarea name="aiopms_titles" id="aiopms_titles" rows="10" cols="50" class="large-text"></textarea>
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
    if (isset($_POST['submit']) && check_admin_referer('aiopms_manual_create_pages')) {
        aiopms_create_pages_manually($_POST['aiopms_titles']);
    }
}

// CSV upload tab content
function aiopms_csv_upload_tab() {
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('aiopms_csv_upload'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">CSV File</th>
                <td>
                    <input type="file" name="aiopms_csv_file" id="aiopms_csv_file" accept=".csv">
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
    if (isset($_POST['submit']) && check_admin_referer('aiopms_csv_upload')) {
        if (isset($_FILES['aiopms_csv_file']) && !empty($_FILES['aiopms_csv_file']['tmp_name'])) {
            aiopms_create_pages_from_csv($_FILES['aiopms_csv_file']);
        } else {
            echo '<div class="notice notice-error"><p>Please select a CSV file to upload.</p></div>';
        }
    }
}

// Menu generator tab content
function aiopms_menu_generator_tab() {
    // Handle menu generation requests
    if (isset($_POST['generate_menu']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'aiopms_generate_menu')) {
        $menu_type = isset($_POST['menu_type']) ? sanitize_key($_POST['menu_type']) : '';

        switch ($menu_type) {
            case 'universal_bottom':
                $result = aiopms_generate_universal_bottom_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Universal Bottom Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Universal Bottom Menu.</p></div>';
                }
                break;

            case 'services':
                $result = aiopms_generate_services_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Services Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible"><p>No service pages found to create Services Menu.</p></div>';
                }
                break;

            case 'company':
                $result = aiopms_generate_company_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Company Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible"><p>No company pages found to create Company Menu.</p></div>';
                }
                break;

            case 'main_navigation':
                $result = aiopms_generate_main_navigation_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Main Navigation Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Main Navigation Menu.</p></div>';
                }
                break;

            case 'resources':
                $result = aiopms_generate_resources_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Resources Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Resources Menu.</p></div>';
                }
                break;

            case 'footer_quick_links':
                $result = aiopms_generate_footer_quick_links_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Footer Quick Links Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Footer Quick Links Menu.</p></div>';
                }
                break;

            case 'social_media':
                $result = aiopms_generate_social_media_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Social Media Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Social Media Menu.</p></div>';
                }
                break;

            case 'support':
                $result = aiopms_generate_support_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Support Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create Support Menu.</p></div>';
                }
                break;

            case 'products':
                $result = aiopms_generate_products_menu();
                if ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>Products Menu created successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible"><p>No product pages found to create Products Menu.</p></div>';
                }
                break;
        }
    }
    ?>
    <div class="wrap">
        <h2>Menu Generator</h2>
        <p>Automatically generate WordPress menus based on your created pages.</p>
        
        <div class="menu-generator-options">
            <!-- Primary Navigation Menus -->
            <div class="menu-category">
                <h3>🧭 Primary Navigation</h3>
                <div class="menu-cards-grid">
                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Main Navigation Menu</h3>
                            <p>Complete primary header navigation with:</p>
                            <ul>
                                <li>Home + About/Company dropdowns</li>
                                <li>Services/Solutions with sub-items</li>
                                <li>Industry/Solution categories</li>
                                <li>Resources/Blog with categories</li>
                                <li>Contact page integration</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="main_navigation">
                            <?php submit_button('Generate Main Navigation', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>

                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

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
                </div>
            </div>

            <!-- Content & Product Menus -->
            <div class="menu-category">
                <h3>📄 Content & Products</h3>
                <div class="menu-cards-grid">
                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Products Menu</h3>
                            <p>Catalog menu for product-based businesses:</p>
                            <ul>
                                <li>Detects product, catalog, shop pages</li>
                                <li>Links to pricing and packages</li>
                                <li>Perfect for e-commerce sites</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="products">
                            <?php submit_button('Generate Products Menu', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>

                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Resources Menu</h3>
                            <p>Knowledge base and content navigation:</p>
                            <ul>
                                <li>Blog posts and categories</li>
                                <li>Guides, tutorials, documentation</li>
                                <li>FAQ and help resources</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="resources">
                            <?php submit_button('Generate Resources Menu', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>

                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Support Menu</h3>
                            <p>Customer support and help navigation:</p>
                            <ul>
                                <li>Help, FAQ, troubleshooting pages</li>
                                <li>Contact support integration</li>
                                <li>Documentation and guides</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="support">
                            <?php submit_button('Generate Support Menu', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer & Social Menus -->
            <div class="menu-category">
                <h3>🔗 Footer & Links</h3>
                <div class="menu-cards-grid">
                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Universal Bottom Menu</h3>
                            <p>Comprehensive footer menu with:</p>
                            <ul>
                                <li>Home link</li>
                                <li>All legal pages (Privacy, Terms, etc.)</li>
                                <li>Sitemap link (from settings)</li>
                                <li>Contact page integration</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="universal_bottom">
                            <?php submit_button('Generate Footer Menu', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>

                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Footer Quick Links</h3>
                            <p>Minimal footer navigation:</p>
                            <ul>
                                <li>Home and essential pages</li>
                                <li>Popular content links</li>
                                <li>Sitemap integration</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="footer_quick_links">
                            <?php submit_button('Generate Quick Links', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Company & Social -->
            <div class="menu-category">
                <h3>🏢 Company & Social</h3>
                <div class="menu-cards-grid">
                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

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

                    <form method="post" action="">
                        <?php wp_nonce_field('aiopms_generate_menu'); ?>

                        <div class="menu-option-card">
                            <h3>Social Media Menu</h3>
                            <p>Social media links menu:</p>
                            <ul>
                                <li>Facebook, Twitter, LinkedIn</li>
                                <li>Instagram, YouTube, Pinterest</li>
                                <li>Ready for customization</li>
                            </ul>
                            <input type="hidden" name="menu_type" value="social_media">
                            <?php submit_button('Generate Social Menu', 'primary', 'generate_menu'); ?>
                        </div>
                    </form>
                </div>
            </div>
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
        display: flex;
        flex-direction: column;
        gap: 40px;
        margin: 30px 0;
    }

    .menu-category {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 25px;
        border: 1px solid #e0e0e0;
    }

    .menu-category h3 {
        margin: 0 0 20px 0;
        color: #1d2327;
        font-size: 20px;
        font-weight: 600;
        border-bottom: 2px solid #2271b1;
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .menu-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }

    .menu-option-card {
        background: #fff;
        padding: 25px;
        border: 1px solid #dcdcde;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .menu-option-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-color: #2271b1;
    }

    .menu-option-card h3 {
        margin: 0 0 15px 0;
        color: #2271b1;
        font-size: 16px;
        font-weight: 600;
    }

    .menu-option-card p {
        margin: 0 0 15px 0;
        color: #646970;
        font-size: 14px;
        line-height: 1.5;
    }

    .menu-option-card ul {
        margin: 15px 0 20px 0;
        padding-left: 20px;
    }

    .menu-option-card li {
        margin-bottom: 6px;
        color: #50575e;
        font-size: 13px;
        line-height: 1.4;
    }

    .menu-option-card .button {
        width: 100%;
        text-align: center;
        padding: 10px 16px;
        height: auto;
        font-size: 14px;
        font-weight: 500;
    }

    .menu-generator-info {
        background: #f0f6fc;
        padding: 25px;
        border-left: 4px solid #2271b1;
        margin-top: 40px;
        border-radius: 0 8px 8px 0;
    }

    .menu-generator-info h3 {
        margin: 0 0 15px 0;
        color: #1d2327;
        font-size: 16px;
    }

    .menu-generator-info ul {
        margin: 15px 0;
        padding-left: 20px;
    }

    .menu-generator-info li {
        margin-bottom: 8px;
        color: #50575e;
    }

    /* Responsive Design */
    @media screen and (max-width: 920px) {
        .menu-cards-grid {
            grid-template-columns: 1fr;
        }
    }

    @media screen and (max-width: 600px) {
        .menu-category {
            padding: 20px 15px;
        }

        .menu-option-card {
            padding: 20px;
        }

        .menu-cards-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
}

// Hierarchy Export tab content
function aiopms_hierarchy_export_tab() {
    ?>
    <div class="wrap">
        <h2>Hierarchy Export Tools</h2>
        <p>Export and copy your page hierarchy for documentation, planning, or analysis purposes.</p>

        <div class="aiopms-export-section">
            <div class="aiopms-export-info">
                <h3>📋 Copy Hierarchy</h3>
                <p>Copy complete page hierarchy as plain, indented text to clipboard.</p>
                <ul>
                    <li>All pages from the website</li>
                    <li>Complete subpage structure</li>
                    <li>Accurate nesting levels</li>
                    <li>Current hierarchy format</li>
                </ul>
                <div class="aiopms-export-copy-controls">
                    <button id="aiopms-export-copy-hierarchy" class="button button-primary">📋 Copy Hierarchy</button>
                </div>
            </div>

            <div class="aiopms-export-info">
                <h3>📊 Export Files</h3>
                <p>Download complete page hierarchy data in different formats.</p>
                <ul>
                    <li>CSV format with all metadata</li>
                    <li>Markdown format for documentation</li>
                    <li>JSON format for data processing</li>
                    <li>Proper file naming with site title</li>
                    <li>Complete hierarchical structure</li>
                </ul>
                <div class="aiopms-export-file-controls">
                    <button id="aiopms-export-csv" class="button button-secondary">📊 Export as CSV</button>
                    <button id="aiopms-export-markdown" class="button button-secondary">📄 Export as Markdown</button>
                    <button id="aiopms-export-json" class="button button-secondary">📋 Export as JSON</button>
                </div>
            </div>
        </div>

        <div class="aiopms-export-preview">
            <h3>File Format Preview</h3>
            <div class="aiopms-export-format-examples">
                <div class="format-example">
                    <h4>Copy to Clipboard Output:</h4>
                    <pre class="example-code">Home
  About Us
    Team
    History
  Services
    Web Development
    SEO</pre>
                </div>

                <div class="format-example">
                    <h4>CSV Export Sample:</h4>
                    <pre class="example-code">"Title","URL","Excerpt","Published Date","Status"
"Home Page","https://example.com/home","Welcome to homepage","2024-01-15","publish"
"About Us","https://example.com/about","About our company","2024-01-16","publish"</pre>
                </div>

                <div class="format-example">
                    <h4>Markdown Export Sample:</h4>
                    <pre class="example-code"># Page Hierarchy

- [Home Page](https://example.com/home)
  - [About Us](https://example.com/about)
    - [Team](https://example.com/team)
    - [History](https://example.com/history)
  - [Services](https://example.com/services)</pre>
                </div>

                <div class="format-example">
                    <h4>JSON Export Sample:</h4>
                    <pre class="example-code">{
  "site_title": "My Website",
  "export_date": "2024-01-15 10:30:00",
  "total_pages": 5,
  "hierarchy": {
    "1": {
      "id": 1,
      "title": "Home Page",
      "parent": 0,
      "url": "https://example.com/home"
    }
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- Toast notifications container -->
        <div id="aiopms-export-toast-container"></div>
    </div>

    <style>
    .aiopms-export-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin: 30px 0;
    }

    .aiopms-export-info {
        background: #fff;
        padding: 25px;
        border: 1px solid #dcdcde;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .aiopms-export-info h3 {
        margin: 0 0 15px 0;
        color: #2271b1;
        font-size: 18px;
    }

    .aiopms-export-info ul {
        margin: 15px 0 20px 0;
        padding-left: 20px;
    }

    .aiopms-export-info li {
        margin-bottom: 8px;
        color: #646970;
    }

    .aiopms-export-copy-controls,
    .aiopms-export-file-controls {
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .aiopms-export-preview {
        background: #f6f7f7;
        padding: 25px;
        border-radius: 6px;
        border-left: 4px solid #2271b1;
        margin-top: 30px;
    }

    .aiopms-export-preview h3 {
        margin: 0 0 20px 0;
        color: #1d2327;
    }

    .aiopms-export-format-examples {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }

    .format-example {
        background: #fff;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .format-example h4 {
        margin: 0 0 10px 0;
        color: #2271b1;
        font-size: 14px;
        font-weight: 600;
    }

    .format-example pre {
        margin: 0;
        padding: 10px;
        background: #f8f8f8;
        border: 1px solid #e0e0e0;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.4;
        max-height: 150px;
        overflow-y: auto;
    }

    /* Toast Styles */
    .aiopms-toast {
        position: fixed;
        top: 40px;
        right: 20px;
        padding: 12px 16px;
        border-radius: 4px;
        color: #fff;
        font-size: 14px;
        font-weight: 500;
        z-index: 99999;
        opacity: 0;
        transform: translateY(-10px);
        animation: aiopmsToastSlideIn 0.3s ease forwards;
        max-width: 400px;
        word-wrap: break-word;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .aiopms-toast.success {
        background: #28a745;
        border: 1px solid #218838;
    }

    .aiopms-toast.error {
        background: #dc3545;
        border: 1px solid #c82333;
    }

    @keyframes aiopmsToastSlideIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media screen and (max-width: 782px) {
        .aiopms-export-section {
            grid-template-columns: 1fr;
        }

        .aiopms-export-copy-controls,
        .aiopms-export-file-controls {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .aiopms-export-format-examples {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Get hierarchy data
        let exportData = null;

        // Load hierarchy data
        function loadExportData() {
            $.ajax({
                url: '<?php echo esc_url(rest_url('aiopms/v1/hierarchy')); ?>',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function(data) {
                    exportData = data;
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load export data:', error);
                    showToast('Error loading page data. Please try again.', 'error');
                }
            });
        }

        // Copy hierarchy to clipboard
        $('#aiopms-export-copy-hierarchy').on('click', function() {
            if (!exportData) {
                loadExportData();
                setTimeout(() => copyHierarchyToClipboard(), 1000);
                return;
            }
            copyHierarchyToClipboard();
        });

        function copyHierarchyToClipboard() {
            if (!exportData) {
                showToast('No page data available. Please wait for data to load.', 'error');
                return;
            }

            const result = generateHierarchicalText(exportData);

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(result).then(() => {
                    showToast('Page hierarchy copied to clipboard!');
                }).catch((err) => {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(result);
                });
            } else {
                fallbackCopyTextToClipboard(result);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showToast('Page hierarchy copied to clipboard!');
                } else {
                    showToast('Failed to copy to clipboard', 'error');
                }
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                showToast('Failed to copy to clipboard', 'error');
            }

            document.body.removeChild(textArea);
        }

        function generateHierarchicalText(data) {
            let output = '';

            function buildText(nodeId, prefix = '', seen = new Set()) {
                if (seen.has(nodeId)) return;
                seen.add(nodeId);

                const node = data.find(item => item.id === nodeId);
                if (!node) return;

                output += prefix + node.text + '\n';

                const children = data.filter(item => item.parent === nodeId).sort((a, b) => a.text.localeCompare(b.text));
                children.forEach(child => {
                    const childPrefix = prefix + '  ';
                    buildText(child.id, childPrefix, new Set(seen));
                });
            }

            const roots = data.filter(item => item.parent === '#').sort((a, b) => a.text.localeCompare(b.text));
            roots.forEach(root => {
                buildText(root.id);
            });

            return output.trim();
        }

        // Export to CSV
        $('#aiopms-export-csv').on('click', function() {
            const csvUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=aiopms_export_csv&nonce=<?php echo wp_create_nonce('aiopms_export_nonce'); ?>';
            const link = document.createElement('a');
            link.href = csvUrl;
            link.download = '<?php echo sanitize_file_name(get_bloginfo('name')); ?>.csv';
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('CSV export started! Check your downloads.');
        });

        // Export to Markdown
        $('#aiopms-export-markdown').on('click', function() {
            const markdownUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=aiopms_export_markdown&nonce=<?php echo wp_create_nonce('aiopms_export_nonce'); ?>';
            const link = document.createElement('a');
            link.href = markdownUrl;
            link.download = '<?php echo sanitize_file_name(get_bloginfo('name')); ?>.md';
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('Markdown export started! Check your downloads.');
        });

        // Export to JSON
        $('#aiopms-export-json').on('click', function() {
            const jsonUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=aiopms_export_json&nonce=<?php echo wp_create_nonce('aiopms_export_nonce'); ?>';
            const link = document.createElement('a');
            link.href = jsonUrl;
            link.download = '<?php echo sanitize_file_name(get_bloginfo('name')); ?>.json';
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('JSON export started! Check your downloads.');
        });

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = $(`<div class="aiopms-toast ${type}">${message}</div>`);
            $('#aiopms-export-toast-container').append(toast);
            setTimeout(() => toast.remove(), 5000);
        }

        // Load data on page load
        loadExportData();
    });
    </script>
    <?php
}
