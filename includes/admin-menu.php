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
        AIOPMS_PLUGIN_URL . 'assets/images/logo.svg',
        25
    );
}
add_action('admin_menu', 'aiopms_add_admin_menu');

// Admin page content
function aiopms_admin_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'manual';
    
    // Define menu items with their details
    $menu_items = array(
        'manual' => array(
            'title' => 'Manual Page Creation',
            'icon' => '📝',
            'description' => 'Create pages manually with custom hierarchy and attributes'
        ),
        'csv' => array(
            'title' => 'CSV Upload',
            'icon' => '📊',
            'description' => 'Bulk import pages from CSV files'
        ),
        'ai' => array(
            'title' => 'AI Generation',
            'icon' => '🚀',
            'description' => 'Generate pages with AI assistance'
        ),
        'schema' => array(
            'title' => 'Schema Generator',
            'icon' => '🏷️',
            'description' => 'Create structured data markup'
        ),
        'menu' => array(
            'title' => 'Menu Generator',
            'icon' => '🍔',
            'description' => 'Automatically generate WordPress menus'
        ),
        'hierarchy' => array(
            'title' => 'Page Hierarchy',
            'icon' => '🌳',
            'description' => 'Visualize and manage page structure'
        ),
        'export' => array(
            'title' => 'Hierarchy Export',
            'icon' => '📤',
            'description' => 'Export page hierarchy data'
        ),
        'keyword-analysis' => array(
            'title' => 'Keyword Analysis',
            'icon' => '🔍',
            'description' => 'Analyze keyword density and SEO'
        ),
        'settings' => array(
            'title' => 'Settings',
            'icon' => '⚙️',
            'description' => 'Configure plugin options'
        )
    );
    ?>
    <div class="wrap dg10-brand">
        <div class="dg10-main-layout">
            <!-- Admin Sidebar -->
            <div class="dg10-admin-sidebar">
                <div class="dg10-sidebar-header">
                    <div class="dg10-sidebar-title">
                        <img src="<?php echo AIOPMS_PLUGIN_URL; ?>assets/images/logo.svg" alt="AIOPMS Plugin" style="width: 24px; height: 24px;">
                        AIOPMS
                    </div>
                    <p class="dg10-sidebar-subtitle">All In One Page Management System</p>
                </div>
                
                <nav class="dg10-sidebar-nav">
                    <?php foreach ($menu_items as $tab_key => $item): ?>
                        <a href="?page=aiopms-page-management&tab=<?php echo esc_attr($tab_key); ?>" 
                           class="dg10-sidebar-nav-item <?php echo $active_tab == $tab_key ? 'active' : ''; ?>"
                           title="<?php echo esc_attr($item['description']); ?>">
                            <span class="nav-icon"><?php echo $item['icon']; ?></span>
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            
            <!-- Main Content Area -->
            <div class="dg10-main-content">
                <div class="dg10-card">
                    <div class="dg10-card-header">
                        <div class="dg10-hero-content">
                            <div class="dg10-hero-text">
                                <h1><?php echo esc_html($menu_items[$active_tab]['title']); ?></h1>
                                <p class="dg10-hero-description">
                                    <?php echo esc_html($menu_items[$active_tab]['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="dg10-card-body">
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
        } elseif ($active_tab == 'keyword-analysis') {
            aiopms_keyword_analysis_tab();
        } elseif ($active_tab == 'settings') {
            aiopms_settings_tab();
        }
        ?>
                    </div>
                    <div class="dg10-card-footer">
                        <div class="dg10-promotion-section">
                            <div class="dg10-promotion-header">
                                <img src="<?php echo AIOPMS_PLUGIN_URL; ?>assets/images/dg10-brand-logo.svg" alt="DG10 Agency" class="dg10-promotion-logo">
                                <h3>About us</h3>
                            </div>
                            <div class="dg10-promotion-content">
                                <p>DG10 Agency specializes in creating powerful WordPress and Elementor solutions. We help businesses build custom websites, optimize performance, and implement complex integrations that drive results.</p>
                                <div class="dg10-promotion-buttons">
                                    <a href="https://www.dg10.agency" target="_blank" class="dg10-btn dg10-btn-primary">
                                        Visit Website
                                        <span class="dg10-btn-icon">→</span>
                                    </a>
                                    <a href="https://calendly.com/dg10-agency/30min" target="_blank" class="dg10-btn dg10-btn-outline">
                                        <span class="dg10-btn-icon">📅</span>
                                        Book a Free Consultation
                                    </a>
                                </div>
                                <p class="dg10-promotion-footer">
                                    This is an open-source project - please <a href="<?php echo esc_url(AIOPMS_GITHUB_URL); ?>" target="_blank">star the repo on GitHub</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar JavaScript -->
    <script>
    jQuery(document).ready(function($) {
        // Handle sidebar navigation
        $('.dg10-sidebar-nav-item').on('click', function(e) {
            // Remove active class from all items
            $('.dg10-sidebar-nav-item').removeClass('active');
            // Add active class to clicked item
            $(this).addClass('active');
        });
        
        // Handle responsive sidebar behavior
        function handleSidebarResponsive() {
            if ($(window).width() <= 960) {
                // Mobile/tablet view - horizontal scroll
                $('.dg10-sidebar-nav').addClass('mobile-nav');
            } else {
                // Desktop view - vertical sidebar
                $('.dg10-sidebar-nav').removeClass('mobile-nav');
            }
        }
        
        // Run on load and resize
        handleSidebarResponsive();
        $(window).on('resize', handleSidebarResponsive);
        
        // Smooth scroll for mobile navigation
        $('.dg10-sidebar-nav').on('scroll', function() {
            // Optional: Add scroll indicators or other mobile nav enhancements
        });
        
        // Add keyboard navigation support
        $('.dg10-sidebar-nav-item').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Focus management for accessibility
        $('.dg10-sidebar-nav-item').on('focus', function() {
            $(this).addClass('focused');
        }).on('blur', function() {
            $(this).removeClass('focused');
        });
    });
    </script>
    
    <style>
    /* Additional JavaScript-triggered styles */
    .dg10-sidebar-nav-item.focused {
        outline: 2px solid #2271b1;
        outline-offset: 2px;
    }
    
    .dg10-sidebar-nav.mobile-nav {
        scrollbar-width: thin;
        scrollbar-color: #2271b1 #f1f1f1;
    }
    
    .dg10-sidebar-nav.mobile-nav::-webkit-scrollbar {
        height: 6px;
    }
    
    .dg10-sidebar-nav.mobile-nav::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .dg10-sidebar-nav.mobile-nav::-webkit-scrollbar-thumb {
        background: #2271b1;
        border-radius: 3px;
    }
    
    .dg10-sidebar-nav.mobile-nav::-webkit-scrollbar-thumb:hover {
        background: #1e5a96;
    }
    </style>
    <?php
}

// Manual creation tab content
function aiopms_manual_creation_tab() {
    ?>
    <div class="dg10-card">
        <div class="dg10-card-body">
            <form method="post" action="">
                <?php wp_nonce_field('aiopms_manual_create_pages'); ?>
                <div class="dg10-form-group">
                    <label for="aiopms_titles" class="dg10-form-label">Page Titles</label>
                    <textarea name="aiopms_titles" id="aiopms_titles" rows="10" class="dg10-form-textarea" placeholder="Enter one page title per line. Use hyphens for nesting..."></textarea>
                    <div class="dg10-form-help">
                        <strong>Syntax Guide:</strong><br>
                        • Use <code>-</code> for child pages (one hyphen per level)<br>
                        • Use <code>:+</code> for meta description<br>
                        • Use <code>:*</code> for featured image URL<br>
                        • Use <code>::template=template-name.php</code> for page template<br>
                        • Use <code>::status=draft</code> for post status<br>
                        • <strong>SEO slugs are automatically generated</strong> (max 72 chars)
                    </div>
                </div>
                <button type="submit" name="submit" class="dg10-btn dg10-btn-primary">
                    🚀 Create Pages
                </button>
            </form>
        </div>
    </div>
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

// Keyword Analysis tab content
function aiopms_keyword_analysis_tab() {
    ?>
    <div class="dg10-card">
        <div class="dg10-card-body">
            <form id="aiopms-keyword-analysis-form">
                <?php wp_nonce_field('aiopms_keyword_analysis', 'aiopms_keyword_nonce'); ?>
                
                <div class="dg10-form-group">
                    <label for="aiopms_page_select" class="dg10-form-label">Select Page to Analyze</label>
                    <select name="page_id" id="aiopms_page_select" class="dg10-form-select" required>
                        <option value="">Loading pages...</option>
                    </select>
                    <div class="dg10-form-help">
                        Choose a published page or post to analyze for keyword density
                    </div>
                </div>
                
                <div class="dg10-form-group">
                    <label for="aiopms_keywords_input" class="dg10-form-label">Keywords to Analyze</label>
                    <textarea name="keywords" id="aiopms_keywords_input" rows="8" class="dg10-form-textarea" 
                              placeholder="Enter keywords to analyze, one per line or separated by commas:&#10;&#10;web design&#10;SEO services&#10;digital marketing&#10;responsive design, mobile optimization" required></textarea>
                    <div class="dg10-form-help">
                        <strong>Format:</strong> One keyword per line, or comma-separated keywords. The analyzer will count exact matches (case-insensitive) and calculate density percentages.
                    </div>
                    <div id="keyword-count" class="dg10-form-help" style="margin-top: 8px;"></div>
                </div>
                
                <div class="dg10-form-group">
                    <button type="submit" id="aiopms-analyze-btn" class="dg10-btn dg10-btn-primary">
                        <span class="btn-text">🔍 Analyze Keywords</span>
                        <span class="dg10-spinner dg10-hidden"></span>
                    </button>
                </div>
            </form>
            
            <!-- Results Section -->
            <div id="aiopms-analysis-results" class="dg10-hidden">
                <div class="dg10-card">
                    <div class="dg10-card-header">
                        <h3>📊 Analysis Results</h3>
                        <div class="analysis-actions">
                            <button id="export-csv-btn" class="dg10-btn dg10-btn-outline dg10-btn-sm">
                                📊 Export CSV
                            </button>
                            <button id="export-json-btn" class="dg10-btn dg10-btn-outline dg10-btn-sm">
                                📋 Export JSON
                            </button>
                        </div>
                    </div>
                    <div class="dg10-card-body">
                        <!-- Page Info -->
                        <div id="page-info-section" class="analysis-section">
                            <h4>📄 Page Information</h4>
                            <div id="page-info-content"></div>
                        </div>
                        
                        <!-- Summary -->
                        <div id="summary-section" class="analysis-section">
                            <h4>📈 Analysis Summary</h4>
                            <div id="summary-content"></div>
                        </div>
                        
                        <!-- Keywords Table -->
                        <div id="keywords-section" class="analysis-section">
                            <h4>🔍 Keyword Analysis</h4>
                            <div class="table-responsive">
                                <table id="keywords-table" class="dg10-table">
                                    <thead>
                                        <tr>
                                            <th>Keyword</th>
                                            <th>Count</th>
                                            <th>Density</th>
                                            <th>Status</th>
                                            <th>Areas Found</th>
                                            <th>Context</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Recommendations -->
                        <div id="recommendations-section" class="analysis-section">
                            <h4>💡 SEO Recommendations</h4>
                            <div id="recommendations-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .analysis-actions {
        display: flex;
        gap: var(--dg10-spacing-sm);
        margin-left: auto;
    }
    
    .analysis-section {
        margin-bottom: var(--dg10-spacing-xl);
        padding-bottom: var(--dg10-spacing-lg);
        border-bottom: 1px solid #E5E7EB;
    }
    
    .analysis-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .analysis-section h4 {
        margin: 0 0 var(--dg10-spacing-md) 0;
        color: var(--dg10-primary);
        font-size: var(--dg10-font-size-lg);
    }
    
    .page-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--dg10-spacing-md);
        margin-bottom: var(--dg10-spacing-lg);
    }
    
    .page-info-item {
        background: var(--dg10-light-gray);
        padding: var(--dg10-spacing-md);
        border-radius: var(--dg10-radius-md);
        border-left: 4px solid var(--dg10-primary);
    }
    
    .page-info-label {
        font-size: var(--dg10-font-size-xs);
        font-weight: 600;
        color: var(--dg10-neutral);
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-bottom: var(--dg10-spacing-xs);
    }
    
    .page-info-value {
        font-size: var(--dg10-font-size-sm);
        color: var(--dg10-dark-blue);
        font-weight: 500;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: var(--dg10-spacing-md);
        margin-bottom: var(--dg10-spacing-lg);
    }
    
    .summary-item {
        text-align: center;
        padding: var(--dg10-spacing-lg);
        background: var(--dg10-white);
        border: 1px solid #E5E7EB;
        border-radius: var(--dg10-radius-lg);
        box-shadow: var(--dg10-shadow-sm);
    }
    
    .summary-number {
        font-size: var(--dg10-font-size-2xl);
        font-weight: 700;
        color: var(--dg10-primary);
        margin-bottom: var(--dg10-spacing-xs);
    }
    
    .summary-label {
        font-size: var(--dg10-font-size-sm);
        color: var(--dg10-neutral);
        font-weight: 500;
    }
    
    .dg10-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--dg10-white);
        border-radius: var(--dg10-radius-lg);
        overflow: hidden;
        box-shadow: var(--dg10-shadow-sm);
    }
    
    .dg10-table th {
        background: var(--dg10-gradient-dark);
        color: var(--dg10-white);
        padding: var(--dg10-spacing-md);
        text-align: left;
        font-weight: 600;
        font-size: var(--dg10-font-size-sm);
    }
    
    .dg10-table td {
        padding: var(--dg10-spacing-md);
        border-bottom: 1px solid #E5E7EB;
        font-size: var(--dg10-font-size-sm);
        vertical-align: top;
    }
    
    .dg10-table tr:hover {
        background: var(--dg10-light-gray);
    }
    
    .keyword-cell {
        font-weight: 600;
        color: var(--dg10-dark-blue);
    }
    
    .count-cell {
        text-align: center;
        font-weight: 600;
    }
    
    .density-cell {
        text-align: center;
        font-weight: 600;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: var(--dg10-spacing-xs) var(--dg10-spacing-sm);
        border-radius: var(--dg10-radius-sm);
        font-size: var(--dg10-font-size-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .status-high {
        background: var(--dg10-gradient-error);
        color: var(--dg10-white);
    }
    
    .status-good {
        background: var(--dg10-gradient-success);
        color: var(--dg10-white);
    }
    
    .status-moderate {
        background: var(--dg10-gradient-warning);
        color: var(--dg10-white);
    }
    
    .status-low {
        background: var(--dg10-gradient-info);
        color: var(--dg10-white);
    }
    
    .areas-found {
        font-size: var(--dg10-font-size-xs);
        color: var(--dg10-neutral);
    }
    
    .context-preview {
        max-width: 200px;
        font-size: var(--dg10-font-size-xs);
        color: var(--dg10-neutral);
        line-height: 1.4;
    }
    
    .context-preview strong {
        color: var(--dg10-primary);
        font-weight: 600;
    }
    
    .recommendations-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .recommendations-list li {
        padding: var(--dg10-spacing-md);
        margin-bottom: var(--dg10-spacing-sm);
        background: var(--dg10-light-gray);
        border-left: 4px solid var(--dg10-primary);
        border-radius: 0 var(--dg10-radius-md) var(--dg10-radius-md) 0;
        font-size: var(--dg10-font-size-sm);
        line-height: 1.5;
    }
    
    .table-responsive {
        overflow-x: auto;
        border-radius: var(--dg10-radius-lg);
    }
    
    @media (max-width: 768px) {
        .analysis-actions {
            flex-direction: column;
            margin-left: 0;
            margin-top: var(--dg10-spacing-md);
        }
        
        .page-info-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }
        
        .dg10-table {
            font-size: var(--dg10-font-size-xs);
        }
        
        .dg10-table th,
        .dg10-table td {
            padding: var(--dg10-spacing-sm);
        }
        
        .context-preview {
            max-width: 150px;
        }
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        let analysisData = null;
        
        // Load pages on page load
        loadPages();
        
        // Keyword count display
        $('#aiopms_keywords_input').on('input', function() {
            const text = $(this).val();
            const keywords = text.split(/[\r\n,]+/).filter(k => k.trim().length > 0);
            $('#keyword-count').text('Keywords: ' + keywords.length);
        });
        
        // Form submission
        $('#aiopms-keyword-analysis-form').on('submit', function(e) {
            e.preventDefault();
            
            const pageId = $('#aiopms_page_select').val();
            const keywords = $('#aiopms_keywords_input').val();
            
            if (!pageId || !keywords.trim()) {
                alert('Please select a page and enter keywords to analyze.');
                return;
            }
            
            analyzeKeywords(pageId, keywords);
        });
        
        // Export functions
        $('#export-csv-btn').on('click', function() {
            if (analysisData) {
                exportAnalysis('csv');
            }
        });
        
        $('#export-json-btn').on('click', function() {
            if (analysisData) {
                exportAnalysis('json');
            }
        });
        
        function loadPages() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiopms_get_pages',
                    nonce: $('#aiopms_keyword_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        const select = $('#aiopms_page_select');
                        select.empty();
                        select.append('<option value="">Select a page...</option>');
                        
                        response.data.forEach(function(page) {
                            select.append(`<option value="${page.id}">${page.title} (${page.type})</option>`);
                        });
                    } else {
                        console.error('Failed to load pages:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading pages:', error);
                }
            });
        }
        
        function analyzeKeywords(pageId, keywords) {
            const btn = $('#aiopms-analyze-btn');
            const btnText = btn.find('.btn-text');
            const spinner = btn.find('.dg10-spinner');
            
            // Show loading state
            btn.prop('disabled', true);
            btnText.addClass('dg10-hidden');
            spinner.removeClass('dg10-hidden');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiopms_analyze_keywords',
                    page_id: pageId,
                    keywords: keywords,
                    nonce: $('#aiopms_keyword_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        analysisData = response.data;
                        displayResults(response.data);
                        $('#aiopms-analysis-results').removeClass('dg10-hidden');
                    } else {
                        alert('Analysis failed: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Analysis error:', error);
                    alert('Analysis failed. Please try again.');
                },
                complete: function() {
                    // Hide loading state
                    btn.prop('disabled', false);
                    btnText.removeClass('dg10-hidden');
                    spinner.addClass('dg10-hidden');
                }
            });
        }
        
        function displayResults(data) {
            // Display page info
            displayPageInfo(data.page_info);
            
            // Display summary
            displaySummary(data.summary);
            
            // Display keywords table
            displayKeywordsTable(data.keywords);
            
            // Display recommendations
            displayRecommendations(data.summary.recommendations);
        }
        
        function displayPageInfo(pageInfo) {
            const content = `
                <div class="page-info-grid">
                    <div class="page-info-item">
                        <div class="page-info-label">Page Title</div>
                        <div class="page-info-value">${pageInfo.title}</div>
                    </div>
                    <div class="page-info-item">
                        <div class="page-info-label">URL</div>
                        <div class="page-info-value"><a href="${pageInfo.url}" target="_blank">View Page</a></div>
                    </div>
                    <div class="page-info-item">
                        <div class="page-info-label">Word Count</div>
                        <div class="page-info-value">${pageInfo.word_count.toLocaleString()}</div>
                    </div>
                    <div class="page-info-item">
                        <div class="page-info-label">Analysis Date</div>
                        <div class="page-info-value">${pageInfo.analysis_date}</div>
                    </div>
                </div>
            `;
            $('#page-info-content').html(content);
        }
        
        function displaySummary(summary) {
            const content = `
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-number">${summary.total_keywords}</div>
                        <div class="summary-label">Total Keywords</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">${summary.keywords_found}</div>
                        <div class="summary-label">Keywords Found</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">${summary.average_density}%</div>
                        <div class="summary-label">Avg Density</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">${summary.total_words.toLocaleString()}</div>
                        <div class="summary-label">Total Words</div>
                    </div>
                </div>
            `;
            $('#summary-content').html(content);
        }
        
        function displayKeywordsTable(keywords) {
            const tbody = $('#keywords-table tbody');
            tbody.empty();
            
            keywords.forEach(function(keyword) {
                const areas = [];
                if (keyword.area_counts.title > 0) areas.push(`Title (${keyword.area_counts.title})`);
                if (keyword.area_counts.content > 0) areas.push(`Content (${keyword.area_counts.content})`);
                if (keyword.area_counts.meta_description > 0) areas.push(`Meta (${keyword.area_counts.meta_description})`);
                if (keyword.area_counts.excerpt > 0) areas.push(`Excerpt (${keyword.area_counts.excerpt})`);
                if (keyword.area_counts.headings > 0) areas.push(`Headings (${keyword.area_counts.headings})`);
                
                const context = keyword.context.length > 0 ? 
                    keyword.context[0].substring(0, 100) + '...' : 'No context found';
                
                const row = `
                    <tr>
                        <td class="keyword-cell">${keyword.keyword}</td>
                        <td class="count-cell">${keyword.count}</td>
                        <td class="density-cell">${keyword.density}%</td>
                        <td><span class="status-badge status-${keyword.status}">${keyword.status}</span></td>
                        <td class="areas-found">${areas.join(', ') || 'Not found'}</td>
                        <td class="context-preview">${context}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
        
        function displayRecommendations(recommendations) {
            const content = `
                <ul class="recommendations-list">
                    ${recommendations.map(rec => `<li>${rec}</li>`).join('')}
                </ul>
            `;
            $('#recommendations-content').html(content);
        }
        
        function exportAnalysis(format) {
            if (!analysisData) return;
            
            const form = $('<form>', {
                method: 'POST',
                action: ajaxurl,
                target: '_blank'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'aiopms_export_keyword_analysis'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: $('#aiopms_keyword_nonce').val()
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'format',
                value: format
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'analysis_data',
                value: JSON.stringify(analysisData)
            }));
            
            $('body').append(form);
            form.submit();
            form.remove();
        }
    });
    </script>
    <?php
}
