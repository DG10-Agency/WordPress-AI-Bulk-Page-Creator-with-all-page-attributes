<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Schema types constants
define('ABPCWA_SCHEMA_FAQ', 'faq');
define('ABPCWA_SCHEMA_BLOG', 'blog');
define('ABPCWA_SCHEMA_ARTICLE', 'article');
define('ABPCWA_SCHEMA_SERVICE', 'service');
define('ABPCWA_SCHEMA_PRODUCT', 'product');
define('ABPCWA_SCHEMA_ORGANIZATION', 'organization');
define('ABPCWA_SCHEMA_LOCAL_BUSINESS', 'local_business');
define('ABPCWA_SCHEMA_WEBPAGE', 'webpage');

// Detect schema type for a page
function abpcwa_detect_schema_type($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return ABPCWA_SCHEMA_WEBPAGE;
    }

    $content = $post->post_content;
    $title = $post->post_title;
    $excerpt = $post->post_excerpt;

    // Check for FAQ content patterns
    if (abpcwa_is_faq_page($content, $title)) {
        return ABPCWA_SCHEMA_FAQ;
    }

    // Check for blog post
    if (abpcwa_is_blog_post($post)) {
        return ABPCWA_SCHEMA_BLOG;
    }

    // Check for article
    if (abpcwa_is_article($content, $title)) {
        return ABPCWA_SCHEMA_ARTICLE;
    }

    // Check for service page
    if (abpcwa_is_service_page($title, $content)) {
        return ABPCWA_SCHEMA_SERVICE;
    }

    // Check for product page
    if (abpcwa_is_product_page($title, $content)) {
        return ABPCWA_SCHEMA_PRODUCT;
    }

    // Check for organization page
    if (abpcwa_is_organization_page($title)) {
        return ABPCWA_SCHEMA_ORGANIZATION;
    }

    // Check for local business page
    if (abpcwa_is_local_business_page($title)) {
        return ABPCWA_SCHEMA_LOCAL_BUSINESS;
    }

    // Default to webpage
    return ABPCWA_SCHEMA_WEBPAGE;
}

// Check if content contains FAQ patterns
function abpcwa_is_faq_page($content, $title) {
    // Check title for FAQ indicators
    $faq_keywords = ['faq', 'frequently asked', 'questions', 'q&a', 'help center'];
    foreach ($faq_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }

    // Check content for question-answer patterns
    $question_patterns = [
        '/<h[1-6][^>]*>.*\?.*<\/h[1-6]>/i',
        '/<strong>.*\?.*<\/strong>/i',
        '/<b>.*\?.*<\/b>/i',
        '/<p><strong>.*\?.*<\/strong><\/p>/i'
    ];

    foreach ($question_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true;
        }
    }

    return false;
}

// Check if post is a blog post
function abpcwa_is_blog_post($post) {
    if ($post->post_type === 'post') {
        return true;
    }

    // Check if page has blog-like characteristics
    $blog_categories = ['blog', 'news', 'article', 'post'];
    $categories = wp_get_post_categories($post->ID, ['fields' => 'names']);
    
    foreach ($categories as $category) {
        if (in_array(strtolower($category), $blog_categories)) {
            return true;
        }
    }

    return false;
}

// Check if content is an article
function abpcwa_is_article($content, $title) {
    // Articles typically have longer content
    if (str_word_count(strip_tags($content)) > 500) {
        return true;
    }

    // Check for article indicators in title
    $article_keywords = ['guide', 'tutorial', 'how to', 'tips', 'review', 'analysis'];
    foreach ($article_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

// Check if page is a service page
function abpcwa_is_service_page($title, $content) {
    $service_keywords = ['service', 'solution', 'package', 'offer', 'consulting', 'support'];
    foreach ($service_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }

    // Check content for service-related terms
    $service_content_keywords = ['pricing', 'features', 'benefits', 'what we offer'];
    foreach ($service_content_keywords as $keyword) {
        if (stripos($content, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

// Check if page is a product page
function abpcwa_is_product_page($title, $content) {
    $product_keywords = ['product', 'item', 'buy', 'purchase', 'order', 'shop'];
    foreach ($product_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }

    // Check for price information
    if (preg_match('/\$\d+\.?\d*/', $content) || preg_match('/\d+\.?\d*\s*(USD|EUR|GBP|INR)/i', $content)) {
        return true;
    }

    return false;
}

// Check if page is an organization page
function abpcwa_is_organization_page($title) {
    $org_keywords = ['about', 'company', 'team', 'mission', 'vision', 'values', 'history'];
    foreach ($org_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Check if page is a local business page
function abpcwa_is_local_business_page($title) {
    $business_keywords = ['location', 'store', 'office', 'contact', 'address', 'hours', 'map'];
    foreach ($business_keywords as $keyword) {
        if (stripos($title, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Generate schema markup for a page
function abpcwa_generate_schema_markup($post_id) {
    $schema_type = abpcwa_detect_schema_type($post_id);
    $schema_data = [];

    switch ($schema_type) {
        case ABPCWA_SCHEMA_FAQ:
            $schema_data = abpcwa_generate_faq_schema($post_id);
            break;
        case ABPCWA_SCHEMA_BLOG:
            $schema_data = abpcwa_generate_blog_schema($post_id);
            break;
        case ABPCWA_SCHEMA_ARTICLE:
            $schema_data = abpcwa_generate_article_schema($post_id);
            break;
        case ABPCWA_SCHEMA_SERVICE:
            $schema_data = abpcwa_generate_service_schema($post_id);
            break;
        case ABPCWA_SCHEMA_PRODUCT:
            $schema_data = abpcwa_generate_product_schema($post_id);
            break;
        case ABPCWA_SCHEMA_ORGANIZATION:
            $schema_data = abpcwa_generate_organization_schema($post_id);
            break;
        case ABPCWA_SCHEMA_LOCAL_BUSINESS:
            $schema_data = abpcwa_generate_local_business_schema($post_id);
            break;
        default:
            $schema_data = abpcwa_generate_webpage_schema($post_id);
            break;
    }

    // Store schema data as post meta
    update_post_meta($post_id, '_abpcwa_schema_type', $schema_type);
    update_post_meta($post_id, '_abpcwa_schema_data', $schema_data);

    return $schema_data;
}

// Generate FAQ schema
function abpcwa_generate_faq_schema($post_id) {
    $post = get_post($post_id);
    $content = $post->post_content;
    
    // Extract questions and answers from content
    $faq_items = abpcwa_extract_faq_items($content);
    
    if (empty($faq_items)) {
        // Fallback to webpage schema if no FAQ items found
        return abpcwa_generate_webpage_schema($post_id);
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faq_items
    ];

    return $schema;
}

// Extract FAQ items from content
function abpcwa_extract_faq_items($content) {
    $faq_items = [];
    
    // Pattern to match question-answer pairs
    $patterns = [
        // Match headings with questions followed by paragraphs
        '/(<h[1-6][^>]*>.*\?.*<\/h[1-6]>)(.*?)(?=<h[1-6]|$)/is',
        // Match bold questions followed by text
        '/(<strong>.*\?.*<\/strong>)(.*?)(?=<strong>|$)/is',
        // Match paragraph with strong question
        '/(<p><strong>.*\?.*<\/strong><\/p>)(.*?)(?=<p><strong>|$)/is'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $question = strip_tags($match[1]);
                $answer = strip_tags($match[2]);
                
                if (!empty($question) && !empty($answer)) {
                    $faq_items[] = [
                        '@type' => 'Question',
                        'name' => trim($question),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => trim($answer)
                        ]
                    ];
                }
            }
        }
    }

    return $faq_items;
}

// Generate Blog schema
function abpcwa_generate_blog_schema($post_id) {
    $post = get_post($post_id);
    $author_id = $post->post_author;
    $author = get_userdata($author_id);
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => get_the_title($post_id),
        'description' => get_the_excerpt($post_id),
        'datePublished' => get_the_date('c', $post_id),
        'dateModified' => get_the_modified_date('c', $post_id),
        'author' => [
            '@type' => 'Person',
            'name' => $author->display_name
        ],
        'publisher' => abpcwa_get_organization_schema(),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink($post_id)
        ]
    ];

    // Add featured image if available
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
    if ($thumbnail_url) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $thumbnail_url
        ];
    }

    return $schema;
}

// Generate Article schema
function abpcwa_generate_article_schema($post_id) {
    $post = get_post($post_id);
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => get_the_title($post_id),
        'description' => get_the_excerpt($post_id),
        'datePublished' => get_the_date('c', $post_id),
        'dateModified' => get_the_modified_date('c', $post_id),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink($post_id)
        ],
        'publisher' => abpcwa_get_organization_schema()
    ];

    // Add author if available
    $author_id = $post->post_author;
    if ($author_id) {
        $author = get_userdata($author_id);
        $schema['author'] = [
            '@type' => 'Person',
            'name' => $author->display_name
        ];
    }

    // Add featured image if available
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
    if ($thumbnail_url) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $thumbnail_url
        ];
    }

    return $schema;
}

// Generate Service schema
function abpcwa_generate_service_schema($post_id) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => get_the_title($post_id),
        'description' => get_the_excerpt($post_id),
        'provider' => abpcwa_get_organization_schema(),
        'areaServed' => 'Worldwide',
        'serviceType' => get_the_title($post_id)
    ];

    return $schema;
}

// Generate Product schema
function abpcwa_generate_product_schema($post_id) {
    // This is a basic implementation - would need e-commerce integration for full product schema
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => get_the_title($post_id),
        'description' => get_the_excerpt($post_id),
        'sku' => 'PROD-' . $post_id,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'USD',
            'price' => '0.00',
            'availability' => 'https://schema.org/InStock'
        ]
    ];

    // Add featured image if available
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
    if ($thumbnail_url) {
        $schema['image'] = $thumbnail_url;
    }

    return $schema;
}

// Generate Organization schema
function abpcwa_generate_organization_schema($post_id) {
    return abpcwa_get_organization_schema();
}

// Get organization schema (reusable)
function abpcwa_get_organization_schema() {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    return [
        '@type' => 'Organization',
        'name' => $site_name,
        'url' => $site_url,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => get_site_icon_url()
        ]
    ];
}

// Generate Local Business schema
function abpcwa_generate_local_business_schema($post_id) {
    // Basic implementation - would need address data integration
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => get_bloginfo('name'),
        'description' => get_the_excerpt($post_id),
        'url' => home_url()
    ];

    return $schema;
}

// Generate WebPage schema (fallback)
function abpcwa_generate_webpage_schema($post_id) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => get_the_title($post_id),
        'description' => get_the_excerpt($post_id),
        'url' => get_permalink($post_id)
    ];

    // Add publisher information
    $schema['publisher'] = abpcwa_get_organization_schema();

    return $schema;
}

// Output schema markup in head
function abpcwa_output_schema_markup() {
    if (is_singular()) {
        global $post;
        $schema_data = get_post_meta($post->ID, '_abpcwa_schema_data', true);
        
        if (!empty($schema_data) && is_array($schema_data)) {
            echo '<script type="application/ld+json">' . json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
}
add_action('wp_head', 'abpcwa_output_schema_markup');

// Generate schema when page is saved
function abpcwa_generate_schema_on_save($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if schema generation is enabled
    $auto_generate = get_option('abpcwa_auto_schema_generation', true);
    if (!$auto_generate) {
        return;
    }

    // Generate schema markup
    abpcwa_generate_schema_markup($post_id);
}
add_action('save_post', 'abpcwa_generate_schema_on_save');
add_action('save_post_page', 'abpcwa_generate_schema_on_save');

// Add schema column to pages list
function abpcwa_add_schema_column($columns) {
    $columns['schema'] = 'Schema';
    return $columns;
}
add_filter('manage_page_posts_columns', 'abpcwa_add_schema_column');

// Display schema type in the schema column
function abpcwa_display_schema_column($column, $post_id) {
    if ($column === 'schema') {
        $schema_type = get_post_meta($post_id, '_abpcwa_schema_type', true);
        if (!empty($schema_type)) {
            echo '<span class="abpcwa-schema-badge abpcwa-schema-' . esc_attr($schema_type) . '">' . esc_html(ucfirst($schema_type)) . '</span>';
        } else {
            echo '<span class="abpcwa-schema-badge abpcwa-schema-none">Not Generated</span>';
        }
    }
}
add_action('manage_page_posts_custom_column', 'abpcwa_display_schema_column', 10, 2);

// Make schema column sortable
function abpcwa_make_schema_column_sortable($columns) {
    $columns['schema'] = 'schema';
    return $columns;
}
add_filter('manage_edit-page_sortable_columns', 'abpcwa_make_schema_column_sortable');

// Handle schema column sorting
function abpcwa_handle_schema_column_sorting($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'schema') {
        $query->set('meta_key', '_abpcwa_schema_type');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'abpcwa_handle_schema_column_sorting');

// Add quick actions for schema generation
function abpcwa_add_schema_quick_actions($actions, $post) {
    $schema_type = get_post_meta($post->ID, '_abpcwa_schema_type', true);
    
    if (empty($schema_type)) {
        $actions['generate_schema'] = '<a href="' . wp_nonce_url(admin_url('admin.php?page=ai-bulk-page-creator&action=generate_schema&post=' . $post->ID), 'generate_schema_' . $post->ID) . '">Generate Schema</a>';
    } else {
        $actions['regenerate_schema'] = '<a href="' . wp_nonce_url(admin_url('admin.php?page=ai-bulk-page-creator&action=regenerate_schema&post=' . $post->ID), 'regenerate_schema_' . $post->ID) . '">Regenerate Schema</a>';
    }
    
    return $actions;
}
add_filter('page_row_actions', 'abpcwa_add_schema_quick_actions', 10, 2);

// Handle schema generation actions
function abpcwa_handle_schema_generation_actions() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ai-bulk-page-creator') {
        return;
    }

    if (isset($_GET['action']) && isset($_GET['post']) && isset($_GET['_wpnonce'])) {
        $action = sanitize_text_field($_GET['action']);
        $post_id = intval($_GET['post']);
        
        if ($action === 'generate_schema') {
            if (wp_verify_nonce($_GET['_wpnonce'], 'generate_schema_' . $post_id)) {
                abpcwa_generate_schema_markup($post_id);
                wp_redirect(admin_url('edit.php?post_type=page&schema_generated=1'));
                exit;
            }
        } elseif ($action === 'regenerate_schema') {
            if (wp_verify_nonce($_GET['_wpnonce'], 'regenerate_schema_' . $post_id)) {
                abpcwa_generate_schema_markup($post_id);
                wp_redirect(admin_url('edit.php?post_type=page&schema_regenerated=1'));
                exit;
            }
        }
    }
}
add_action('admin_init', 'abpcwa_handle_schema_generation_actions');

// Add admin notices for schema generation
function abpcwa_schema_generation_notices() {
    if (isset($_GET['schema_generated']) && $_GET['schema_generated'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Schema generated successfully!</p></div>';
    }
    if (isset($_GET['schema_regenerated']) && $_GET['schema_regenerated'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Schema regenerated successfully!</p></div>';
    }
}
add_action('admin_notices', 'abpcwa_schema_generation_notices');

// Schema generator tab content
function abpcwa_schema_generator_tab() {
    // Handle bulk schema generation
    if (isset($_POST['generate_all_schemas']) && check_admin_referer('abpcwa_generate_all_schemas')) {
        $pages = get_posts([
            'post_type' => 'page',
            'numberposts' => -1,
            'post_status' => 'any'
        ]);
        
        $generated_count = 0;
        foreach ($pages as $page) {
            abpcwa_generate_schema_markup($page->ID);
            $generated_count++;
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Generated schema for ' . $generated_count . ' pages!</p></div>';
    }
    
    // Handle individual page schema generation
    if (isset($_POST['generate_schema_for_page']) && check_admin_referer('abpcwa_generate_schema_for_page')) {
        $page_id = intval($_POST['page_id']);
        if ($page_id) {
            abpcwa_generate_schema_markup($page_id);
            echo '<div class="notice notice-success is-dismissible"><p>Schema generated for selected page!</p></div>';
        }
    }
    ?>
    <div class="wrap abpcwa-schema-tab">
        <h2>Schema Markup Generator</h2>
        <p>Automatically generate structured data (schema.org) markup for your pages to improve SEO and search visibility.</p>
        
        <div class="abpcwa-schema-stats">
            <h4>Schema Statistics</h4>
            <?php
            $pages = get_posts([
                'post_type' => 'page',
                'numberposts' => -1,
                'post_status' => 'any'
            ]);
            
            $schema_stats = [
                'total' => 0,
                'with_schema' => 0,
                'types' => []
            ];
            
            foreach ($pages as $page) {
                $schema_stats['total']++;
                $schema_type = get_post_meta($page->ID, '_abpcwa_schema_type', true);
                if (!empty($schema_type)) {
                    $schema_stats['with_schema']++;
                    if (!isset($schema_stats['types'][$schema_type])) {
                        $schema_stats['types'][$schema_type] = 0;
                    }
                    $schema_stats['types'][$schema_type]++;
                }
            }
            ?>
            <ul>
                <li><strong>Total Pages:</strong> <?php echo $schema_stats['total']; ?></li>
                <li><strong>Pages with Schema:</strong> <?php echo $schema_stats['with_schema']; ?> (<?php echo round(($schema_stats['with_schema'] / $schema_stats['total']) * 100, 1); ?>%)</li>
                <li><strong>Pages without Schema:</strong> <?php echo $schema_stats['total'] - $schema_stats['with_schema']; ?></li>
            </ul>
            
            <?php if (!empty($schema_stats['types'])): ?>
            <h4>Schema Type Distribution</h4>
            <ul>
                <?php foreach ($schema_stats['types'] as $type => $count): ?>
                <li><strong><?php echo ucfirst($type); ?>:</strong> <?php echo $count; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        
        <div class="abpcwa-schema-actions">
            <h3>Bulk Schema Generation</h3>
            <form method="post" action="">
                <?php wp_nonce_field('abpcwa_generate_all_schemas'); ?>
                <p>Generate schema markup for all pages. This will automatically detect the appropriate schema type for each page.</p>
                <?php submit_button('Generate Schema for All Pages', 'primary', 'generate_all_schemas'); ?>
            </form>
        </div>
        
        <div class="abpcwa-schema-type-selector">
            <h3>Generate Schema for Specific Page</h3>
            <form method="post" action="">
                <?php wp_nonce_field('abpcwa_generate_schema_for_page'); ?>
                <select name="page_id" required>
                    <option value="">Select a page...</option>
                    <?php
                    $pages = get_posts([
                        'post_type' => 'page',
                        'numberposts' => -1,
                        'post_status' => 'publish',
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);
                    
                    foreach ($pages as $page) {
                        $schema_type = get_post_meta($page->ID, '_abpcwa_schema_type', true);
                        $schema_status = $schema_type ? '(Has: ' . ucfirst($schema_type) . ')' : '(No Schema)';
                        echo '<option value="' . $page->ID . '">' . esc_html($page->post_title) . ' ' . $schema_status . '</option>';
                    }
                    ?>
                </select>
                <?php submit_button('Generate Schema', 'secondary', 'generate_schema_for_page'); ?>
            </form>
        </div>
        
        <div class="abpcwa-schema-preview">
            <h3>Schema Preview</h3>
            <p>Select a page to preview its generated schema markup:</p>
            <select id="abpcwa-schema-preview-select">
                <option value="">Select a page to preview...</option>
                <?php
                foreach ($pages as $page) {
                    $schema_type = get_post_meta($page->ID, '_abpcwa_schema_type', true);
                    if ($schema_type) {
                        echo '<option value="' . $page->ID . '">' . esc_html($page->post_title) . ' (' . ucfirst($schema_type) . ')</option>';
                    }
                }
                ?>
            </select>
            
            <div id="abpcwa-schema-preview-content" style="display: none; margin-top: 15px;">
                <h4>Schema Markup Preview:</h4>
                <pre id="abpcwa-schema-preview-json"></pre>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#abpcwa-schema-preview-select').on('change', function() {
                var pageId = $(this).val();
                if (pageId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'abpcwa_get_schema_preview',
                            page_id: pageId,
                            nonce: '<?php echo wp_create_nonce('abpcwa_schema_preview'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#abpcwa-schema-preview-json').text(JSON.stringify(response.data, null, 2));
                                $('#abpcwa-schema-preview-content').show();
                            }
                        }
                    });
                } else {
                    $('#abpcwa-schema-preview-content').hide();
                }
            });
        });
        </script>
        
        <div class="abpcwa-schema-info">
            <h3>About Schema Markup</h3>
            <p>Schema.org markup helps search engines understand your content better, which can lead to:</p>
            <ul>
                <li>Rich snippets in search results</li>
                <li>Improved click-through rates</li>
                <li>Better understanding of page content</li>
                <li>Enhanced visibility for specific content types (FAQs, products, services, etc.)</li>
            </ul>
            <p>The plugin automatically detects the most appropriate schema type based on your page content and generates the proper JSON-LD markup.</p>
        </div>
    </div>
    <?php
}

// AJAX handler for schema preview
function abpcwa_ajax_get_schema_preview() {
    check_ajax_referer('abpcwa_schema_preview', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $page_id = intval($_POST['page_id']);
    if (!$page_id) {
        wp_send_json_error('Invalid page ID');
    }
    
    $schema_data = get_post_meta($page_id, '_abpcwa_schema_data', true);
    if (empty($schema_data)) {
        wp_send_json_error('No schema data found');
    }
    
    wp_send_json_success($schema_data);
}
add_action('wp_ajax_abpcwa_get_schema_preview', 'abpcwa_ajax_get_schema_preview');
