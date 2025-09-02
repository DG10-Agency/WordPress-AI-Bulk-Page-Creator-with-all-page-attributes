<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// AI generation tab content
function abpcwa_ai_generation_tab() {
    // Handle creation of suggested pages
    if (isset($_POST['action']) && $_POST['action'] == 'create_suggested_pages' && check_admin_referer('abpcwa_create_suggested_pages')) {
        if (isset($_POST['abpcwa_selected_pages']) && is_array($_POST['abpcwa_selected_pages'])) {
            $selected_pages = array_map('sanitize_text_field', $_POST['abpcwa_selected_pages']);
            $generate_images = isset($_POST['abpcwa_generate_images']) && $_POST['abpcwa_generate_images'] == '1';
            abpcwa_create_suggested_pages($selected_pages, $generate_images);
        }
        return; // Stop further processing
    }
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('abpcwa_ai_generate_pages'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Business Type</th>
                <td>
                    <input type="text" name="abpcwa_business_type" class="regular-text" placeholder="e.g., E-commerce, Blog, Corporate">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Business Details</th>
                <td>
                    <textarea name="abpcwa_business_details" rows="5" class="large-text" placeholder="Provide a brief description of the business and its services."></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">SEO Keywords</th>
                <td>
                    <input type="text" name="abpcwa_seo_keywords" class="regular-text" placeholder="e.g., digital marketing, web design, SEO services">
                    <p class="description">Comma-separated list of primary keywords for SEO optimization</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Upload Keywords CSV</th>
                <td>
                    <input type="file" name="abpcwa_keywords_csv" id="abpcwa_keywords_csv" accept=".csv">
                    <p class="description">Upload a CSV file with keywords (one keyword per line or comma-separated)</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Target Audience</th>
                <td>
                    <input type="text" name="abpcwa_target_audience" class="regular-text" placeholder="e.g., small businesses, entrepreneurs, local clients">
                    <p class="description">Describe your target audience for better content optimization</p>
                </td>
            </tr>
        </table>
        <?php submit_button('Generate Page Suggestions'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_ai_generate_pages')) {
        $business_type = sanitize_text_field($_POST['abpcwa_business_type']);
        $business_details = sanitize_textarea_field($_POST['abpcwa_business_details']);
        $seo_keywords = sanitize_text_field($_POST['abpcwa_seo_keywords']);
        $target_audience = sanitize_text_field($_POST['abpcwa_target_audience']);
        
        // Process CSV file if uploaded
        $csv_keywords = '';
        if (isset($_FILES['abpcwa_keywords_csv']) && !empty($_FILES['abpcwa_keywords_csv']['tmp_name'])) {
            $csv_keywords = abpcwa_process_keywords_csv($_FILES['abpcwa_keywords_csv']);
        }
        
        // Combine manual keywords and CSV keywords
        $all_keywords = trim($seo_keywords);
        if (!empty($csv_keywords)) {
            if (!empty($all_keywords)) {
                $all_keywords .= ', ' . $csv_keywords;
            } else {
                $all_keywords = $csv_keywords;
            }
        }
        
        abpcwa_generate_pages_with_ai($business_type, $business_details, $all_keywords, $target_audience);
    }
}

// Generate pages with AI
function abpcwa_generate_pages_with_ai($business_type, $business_details, $seo_keywords = '', $target_audience = '') {
    $provider = get_option('abpcwa_ai_provider', 'openai');
    $api_key = get_option('abpcwa_' . $provider . '_api_key');

    if (empty($api_key)) {
        echo '<div class="notice notice-error"><p>Please enter your ' . ucfirst($provider) . ' API key in the Settings tab.</p></div>';
        return;
    }

    // Call the appropriate function based on the selected provider
    $suggested_pages = [];
    switch ($provider) {
        case 'openai':
            $suggested_pages = abpcwa_get_openai_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key);
            break;
        case 'gemini':
            $suggested_pages = abpcwa_get_gemini_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key);
            break;
        case 'deepseek':
            $suggested_pages = abpcwa_get_deepseek_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key);
            break;
    }

    if (empty($suggested_pages)) {
        echo '<div class="notice notice-warning"><p>Could not generate page suggestions. Please check your API key and try again.</p></div>';
        return;
    }

    // Filter out Privacy Policy page since WordPress creates it automatically
    $suggested_pages = array_filter($suggested_pages, function($page_line) {
        return stripos($page_line, 'Privacy Policy') === false;
    });

    echo '<h3>Suggested Pages:</h3>';
    echo '<form method="post" action="">';
    wp_nonce_field('abpcwa_create_suggested_pages');
    echo '<input type="hidden" name="action" value="create_suggested_pages">';
    
    // Display in table format
    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th width="20px"><input type="checkbox" id="select-all-pages" checked></th>';
    echo '<th>Page Title</th>';
    echo '<th>Meta Description</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($suggested_pages as $page_line) {
        $excerpt = '';
        $page_title = $page_line;
        
        // Parse the page title and excerpt
        if (strpos($page_line, ':+') !== false) {
            list($page_title, $excerpt) = explode(':+', $page_line, 2);
            $excerpt = trim($excerpt);
        }
        
        // Calculate depth for indentation
        $depth = 0;
        $display_title = $page_title;
        while (substr($display_title, 0, 1) === '-') {
            $display_title = substr($display_title, 1);
            $depth++;
        }
        $display_title = trim($display_title);
        
        echo '<tr>';
        echo '<td><input type="checkbox" name="abpcwa_selected_pages[]" value="' . esc_attr($page_line) . '" checked></td>';
        echo '<td>' . str_repeat('&nbsp;&nbsp;&nbsp;', $depth) . esc_html($display_title) . '</td>';
        echo '<td>' . esc_html($excerpt) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    // Add select all JavaScript
    echo '<script>
    jQuery(document).ready(function($) {
        $("#select-all-pages").on("change", function() {
            $("input[name=\'abpcwa_selected_pages[]\']").prop("checked", $(this).prop("checked"));
        });
    });
    </script>';
    
    // Add image generation checkbox
    $provider = get_option('abpcwa_ai_provider', 'openai');
    $is_deepseek = $provider === 'deepseek';
    
    echo '<p>';
    echo '<input type="checkbox" name="abpcwa_generate_images" id="abpcwa_generate_images" value="1" ' . ($is_deepseek ? 'disabled' : '') . '>';
    echo '<label for="abpcwa_generate_images"> Generate featured images with AI</label>';
    
    if ($is_deepseek) {
        echo ' <span class="description" style="color: #d63638;">(Image generation not supported with DeepSeek)</span>';
    }
    echo '</p>';
    
    submit_button('Create Selected Pages');
    echo '</form>';
}

// Get page suggestions from OpenAI API
function abpcwa_get_openai_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    
    // Build enhanced SEO prompt
    $seo_context = '';
    if (!empty($seo_keywords)) {
        $seo_context .= "SEO Keywords: {$seo_keywords}. ";
    }
    if (!empty($target_audience)) {
        $seo_context .= "Target Audience: {$target_audience}. ";
    }
    
    $prompt = "## ROLE & CONTEXT
You are an expert SEO strategist and information architect specializing in website structure optimization for maximum search visibility and user experience.

## BUSINESS CONTEXT
- **Industry**: {$business_type}
- **Business Details**: {$business_details}
- **Target Audience**: {$target_audience}
- **Primary Keywords**: {$seo_keywords}

## TASK OBJECTIVE
Generate a comprehensive list of essential website pages that will establish topical authority and semantic relevance for this business. For each page, provide:
1. Page Title (use hyphens '-' for nesting child pages to indicate hierarchy)
2. SEO-optimized Meta Description (separated by ':+' from the title)

## STRATEGIC REQUIREMENTS

### 1. TOPICAL AUTHORITY ARCHITECTURE
- Create content clusters around core topics
- Establish pillar pages with supporting child pages
- Ensure comprehensive coverage of the business domain
- Include both commercial and informational intent pages

### 2. SEMANTIC SEO IMPLEMENTATION
- Use natural language variations of target keywords
- Incorporate related concepts and entities
- Build semantic relationships between pages
- Avoid keyword stuffing - focus on contextual relevance

### 3. EEAT OPTIMIZATION
- Demonstrate expertise through comprehensive content planning
- Show authoritativeness by covering all essential business aspects
- Build trust with transparent, valuable content
- Include experience-based content where relevant

### 4. USER INTENT MATCHING
- Commercial intent pages (services, products, pricing)
- Informational intent pages (guides, resources, FAQs)
- Navigational intent pages (contact, about, locations)
- Transactional intent pages (checkout, booking, quotes)

### 5. TECHNICAL SEO CONSIDERATIONS
- Logical URL structure with proper hierarchy
- Internal linking opportunities between related pages
- Mobile-first content approach
- Fast-loading, user-friendly page types

## SEO OPTIMIZATION REQUIREMENTS
- **Page Titles**: Must include primary keywords naturally, be compelling, and accurately describe the page content
- **Meta Descriptions**: 155-160 characters, include primary keywords naturally, be compelling and encourage click-throughs
- **Keyword Placement**: Use keywords in titles and descriptions without stuffing - make it sound natural
- **User Intent**: Match the search intent for each keyword (informational, commercial, navigational)

## OUTPUT FORMAT
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting (e.g., '-Services:-+[description]' for child pages)

## CONTEXT-AWARE PAGE SELECTION GUIDELINES:
- **Analyze the business context** and only suggest pages that make sense for this specific business type
- **Use common sense**: A portfolio website doesn't need a Pricing page, an e-commerce site does
- **Consider user intent**: Focus on pages that match what users would actually search for
- **Semantic relationships**: Create pages that build topical authority through related content clusters
- **Business model awareness**: Service businesses need different pages than product businesses or informational sites

## FLEXIBLE STRUCTURE PRINCIPLES:
- **Main Pages**: Use logical hierarchy based on business needs (not fixed templates)
- **Child Pages**: Only nest when there's a clear semantic relationship
- **Avoid unnecessary pages**: Don't include pages that don't serve a clear purpose for this business
- **User-centric**: Focus on what the target audience actually needs to find

## SMART PAGE SELECTION EXAMPLES:
- **Portfolio Website**: Home, About, Portfolio, Services, Contact, Testimonials, Blog
- **E-commerce Store**: Home, Shop, Product Categories, About, Contact, FAQ, Shipping, Returns
- **Service Business**: Home, Services, About, Contact, Testimonials, Blog, FAQ
- **Informational Site**: Home, Resources, Blog, About, Contact, Glossary, Tutorials

## OUTPUT FORMAT:
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting only when there's a clear hierarchical relationship

Focus on creating a website architecture that makes sense for THIS specific business, not a generic template. Use semantic SEO principles and common sense to determine which pages are actually needed.

Focus on creating a complete website architecture that will rank well and convert visitors.";

    $body = json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.5,
        'max_tokens' => 400,
    ]);

    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => $body,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($response_body['choices'][0]['message']['content'])) {
        $pages_str = $response_body['choices'][0]['message']['content'];
        return array_map('trim', explode("\n", $pages_str));
    }

    return [];
}

// Get page suggestions from Gemini API
function abpcwa_get_gemini_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
    
    // Build enhanced SEO prompt
    $seo_context = '';
    if (!empty($seo_keywords)) {
        $seo_context .= "SEO Keywords: {$seo_keywords}. ";
    }
    if (!empty($target_audience)) {
        $seo_context .= "Target Audience: {$target_audience}. ";
    }
    
    $prompt = "## ROLE & CONTEXT
You are an expert SEO strategist and information architect specializing in website structure optimization for maximum search visibility and user experience.

## BUSINESS CONTEXT
- **Industry**: {$business_type}
- **Business Details**: {$business_details}
- **Target Audience**: {$target_audience}
- **Primary Keywords**: {$seo_keywords}

## TASK OBJECTIVE
Generate a comprehensive list of essential website pages that will establish topical authority and semantic relevance for this business. For each page, provide:
1. Page Title (use hyphens '-' for nesting child pages to indicate hierarchy)
2. SEO-optimized Meta Description (separated by ':+' from the title)

## STRATEGIC REQUIREMENTS

### 1. TOPICAL AUTHORITY ARCHITECTURE
- Create content clusters around core topics
- Establish pillar pages with supporting child pages
- Ensure comprehensive coverage of the business domain
- Include both commercial and informational intent pages

### 2. SEMANTIC SEO IMPLEMENTATION
- Use natural language variations of target keywords
- Incorporate related concepts and entities
- Build semantic relationships between pages
- Avoid keyword stuffing - focus on contextual relevance

### 3. EEAT OPTIMIZATION
- Demonstrate expertise through comprehensive content planning
- Show authoritativeness by covering all essential business aspects
- Build trust with transparent, valuable content
- Include experience-based content where relevant

### 4. USER INTENT MATCHING
- Commercial intent pages (services, products, pricing)
- Informational intent pages (guides, resources, FAQs)
- Navigational intent pages (contact, about, locations)
- Transactional intent pages (checkout, booking, quotes)

### 5. TECHNICAL SEO CONSIDERATIONS
- Logical URL structure with proper hierarchy
- Internal linking opportunities between related pages
- Mobile-first content approach
- Fast-loading, user-friendly page types

## SEO OPTIMIZATION REQUIREMENTS
- **Page Titles**: Must include primary keywords naturally, be compelling, and accurately describe the page content
- **Meta Descriptions**: 155-160 characters, include primary keywords naturally, be compelling and encourage click-throughs
- **Keyword Placement**: Use keywords in titles and descriptions without stuffing - make it sound natural
- **User Intent**: Match the search intent for each keyword (informational, commercial, navigational)

## OUTPUT FORMAT
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting (e.g., '-Services:-+[description]' for child pages)

## CONTEXT-AWARE PAGE SELECTION GUIDELINES:
- **Analyze the business context** and only suggest pages that make sense for this specific business type
- **Use common sense**: A portfolio website doesn't need a Pricing page, an e-commerce site does
- **Consider user intent**: Focus on pages that match what users would actually search for
- **Semantic relationships**: Create pages that build topical authority through related content clusters
- **Business model awareness**: Service businesses need different pages than product businesses or informational sites

## FLEXIBLE STRUCTURE PRINCIPLES:
- **Main Pages**: Use logical hierarchy based on business needs (not fixed templates)
- **Child Pages**: Only nest when there's a clear semantic relationship
- **Avoid unnecessary pages**: Don't include pages that don't serve a clear purpose for this business
- **User-centric**: Focus on what the target audience actually needs to find

## SMART PAGE SELECTION EXAMPLES:
- **Portfolio Website**: Home, About, Portfolio, Services, Contact, Testimonials, Blog
- **E-commerce Store**: Home, Shop, Product Categories, About, Contact, FAQ, Shipping, Returns
- **Service Business**: Home, Services, About, Contact, Testimonials, Blog, FAQ
- **Informational Site**: Home, Resources, Blog, About, Contact, Glossary, Tutorials

## OUTPUT FORMAT:
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting only when there's a clear hierarchical relationship

Focus on creating a website architecture that makes sense for THIS specific business, not a generic template. Use semantic SEO principles and common sense to determine which pages are actually needed.

Focus on creating a complete website architecture that will rank well and convert visitors.";

    $body = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
    ]);

    $response = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $body,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($response_body['candidates'][0]['content']['parts'][0]['text'])) {
        $pages_str = $response_body['candidates'][0]['content']['parts'][0]['text'];
        return array_map('trim', explode("\n", $pages_str));
    }

    return [];
}

// Get page suggestions from DeepSeek API
function abpcwa_get_deepseek_suggestions($business_type, $business_details, $seo_keywords, $target_audience, $api_key) {
    $url = 'https://api.deepseek.com/v1/chat/completions';
    
    // Build enhanced SEO prompt
    $seo_context = '';
    if (!empty($seo_keywords)) {
        $seo_context .= "SEO Keywords: {$seo_keywords}. ";
    }
    if (!empty($target_audience)) {
        $seo_context .= "Target Audience: {$target_audience}. ";
    }
    
    $prompt = "## ROLE & CONTEXT
You are an expert SEO strategist and information architect specializing in website structure optimization for maximum search visibility and user experience.

## BUSINESS CONTEXT
- **Industry**: {$business_type}
- **Business Details**: {$business_details}
- **Target Audience**: {$target_audience}
- **Primary Keywords**: {$seo_keywords}

## TASK OBJECTIVE
Generate a comprehensive list of essential website pages that will establish topical authority and semantic relevance for this business. For each page, provide:
1. Page Title (use hyphens '-' for nesting child pages to indicate hierarchy)
2. SEO-optimized Meta Description (separated by ':+' from the title)

## STRATEGIC REQUIREMENTS

### 1. TOPICAL AUTHORITY ARCHITECTURE
- Create content clusters around core topics
- Establish pillar pages with supporting child pages
- Ensure comprehensive coverage of the business domain
- Include both commercial and informational intent pages

### 2. SEMANTIC SEO IMPLEMENTATION
- Use natural language variations of target keywords
- Incorporate related concepts and entities
- Build semantic relationships between pages
- Avoid keyword stuffing - focus on contextual relevance

### 3. EEAT OPTIMIZATION
- Demonstrate expertise through comprehensive content planning
- Show authoritativeness by covering all essential business aspects
- Build trust with transparent, valuable content
- Include experience-based content where relevant

### 4. USER INTENT MATCHING
- Commercial intent pages (services, products, pricing)
- Informational intent pages (guides, resources, FAQs)
- Navigational intent pages (contact, about, locations)
- Transactional intent pages (checkout, booking, quotes)

### 5. TECHNICAL SEO CONSIDERATIONS
- Logical URL structure with proper hierarchy
- Internal linking opportunities between related pages
- Mobile-first content approach
- Fast-loading, user-friendly page types

## SEO OPTIMIZATION REQUIREMENTS
- **Page Titles**: Must include primary keywords naturally, be compelling, and accurately describe the page content
- **Meta Descriptions**: 155-160 characters, include primary keywords naturally, be compelling and encourage click-throughs
- **Keyword Placement**: Use keywords in titles and descriptions without stuffing - make it sound natural
- **User Intent**: Match the search intent for each keyword (informational, commercial, navigational)

## OUTPUT FORMAT
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting (e.g., '-Services:-+[description]' for child pages)

## CONTEXT-AWARE PAGE SELECTION GUIDELINES:
- **Analyze the business context** and only suggest pages that make sense for this specific business type
- **Use common sense**: A portfolio website doesn't need a Pricing page, an e-commerce site does
- **Consider user intent**: Focus on pages that match what users would actually search for
- **Semantic relationships**: Create pages that build topical authority through related content clusters
- **Business model awareness**: Service businesses need different pages than product businesses or informational sites

## FLEXIBLE STRUCTURE PRINCIPLES:
- **Main Pages**: Use logical hierarchy based on business needs (not fixed templates)
- **Child Pages**: Only nest when there's a clear semantic relationship
- **Avoid unnecessary pages**: Don't include pages that don't serve a clear purpose for this business
- **User-centric**: Focus on what the target audience actually needs to find

## SMART PAGE SELECTION EXAMPLES:
- **Portfolio Website**: Home, About, Portfolio, Services, Contact, Testimonials, Blog
- **E-commerce Store**: Home, Shop, Product Categories, About, Contact, FAQ, Shipping, Returns
- **Service Business**: Home, Services, About, Contact, Testimonials, Blog, FAQ
- **Informational Site**: Home, Resources, Blog, About, Contact, Glossary, Tutorials

## OUTPUT FORMAT:
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting only when there's a clear hierarchical relationship

Focus on creating a website architecture that makes sense for THIS specific business, not a generic template. Use semantic SEO principles and common sense to determine which pages are actually needed.

Focus on creating a complete website architecture that will rank well and convert visitors.";

    $body = json_encode([
        'model' => 'deepseek-chat',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.5,
        'max_tokens' => 400,
    ]);

    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => $body,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($response_body['choices'][0]['message']['content'])) {
        $pages_str = $response_body['choices'][0]['message']['content'];
        return array_map('trim', explode("\n", $pages_str));
    }

    return [];
}

// Create suggested pages
function abpcwa_create_suggested_pages($pages, $generate_images = false) {
    $created_count = 0;
    $parent_id_stack = [];

    foreach ($pages as $page_line) {
        if (empty($page_line)) continue;

        $excerpt = '';
        if (strpos($page_line, ':+') !== false) {
            list($page_title, $excerpt) = explode(':+', $page_line, 2);
            $excerpt = trim($excerpt);
        } else {
            $page_title = $page_line;
        }

        $depth = 0;
        while (substr($page_title, 0, 1) === '-') {
            $page_title = substr($page_title, 1);
            $depth++;
        }
        $page_title = trim($page_title);

        $parent_id = ($depth > 0 && isset($parent_id_stack[$depth - 1])) ? $parent_id_stack[$depth - 1] : 0;

        // Generate SEO-optimized slug
        $post_name = abpcwa_generate_seo_slug($page_title);
        
        $new_page = array(
            'post_title'   => $page_title,
            'post_name'    => $post_name,
            'post_content' => '',
            'post_status'  => 'draft',
            'post_type'    => 'page',
            'post_parent'  => $parent_id,
            'post_excerpt' => $excerpt,
        );
        $page_id = wp_insert_post($new_page);

        if ($page_id) {
            $created_count++;
            
            // Generate and set featured image if enabled
            if ($generate_images) {
                abpcwa_generate_and_set_featured_image($page_id, $page_title);
            }
            
            $parent_id_stack[$depth] = $page_id;
            $parent_id_stack = array_slice($parent_id_stack, 0, $depth + 1);
        }
    }

    if ($created_count > 0) {
        $message = $created_count . ' pages created successfully as drafts.';
        if ($generate_images) {
            $message .= ' Featured images generated with AI.';
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
    }
}

// Generate and set featured image using AI
function abpcwa_generate_and_set_featured_image($post_id, $page_title) {
    $provider = get_option('abpcwa_ai_provider', 'openai');
    $api_key = get_option('abpcwa_' . $provider . '_api_key');
    $brand_color = get_option('abpcwa_brand_color', '#4A90E2');
    
    if (empty($api_key)) {
        return false;
    }
    
    // Skip if provider is DeepSeek (no image generation support)
    if ($provider === 'deepseek') {
        return false;
    }
    
    // Generate image prompt
    $prompt = "## IMAGE CREATION BRIEF
Create a professional featured image for a webpage titled: '{$page_title}'

## STYLE & AESTHETIC REQUIREMENTS
- **Style**: Modern, minimalist, abstract background
- **Color Palette**: Primary color: {$brand_color} with complementary tones
- **Mood**: Professional, clean, engaging but not distracting
- **Composition**: Balanced, with visual hierarchy that supports text overlay

## TECHNICAL SPECIFICATIONS
- **Aspect Ratio**: 16:9 (standard for featured images)
- **Resolution**: High-quality, sharp details
- **Text Readability**: Design should allow for clear text overlay
- **Brand Alignment**: Reflect the professional nature of the content

## CREATIVE DIRECTION
- Use abstract shapes, gradients, or subtle patterns
- Incorporate the primary color {$brand_color} as the dominant hue
- Create visual interest without being too busy or distracting
- Ensure the image works well as a background for white text overlay
- Maintain a professional, corporate-appropriate aesthetic

## USAGE CONTEXT
This image will be used as a featured image for a webpage, so it should:
- Be visually appealing but not overpower the content
- Work well at various sizes (thumbnail to full-width)
- Convey professionalism and relevance to the page topic
- Have adequate contrast for text readability

Avoid photorealistic images - focus on abstract, brand-aligned graphics that enhance the page's professional appearance.";
    
    // Call the appropriate image generation API
    $image_url = '';
    switch ($provider) {
        case 'openai':
            $image_url = abpcwa_generate_openai_image($prompt, $api_key);
            break;
        case 'gemini':
            $image_url = abpcwa_generate_gemini_image($prompt, $api_key);
            break;
    }
    
    if (!empty($image_url)) {
        // Use the existing featured image setting function
        abpcwa_set_featured_image($post_id, $image_url);
        return true;
    }
    
    return false;
}

// Generate image using OpenAI DALL-E
function abpcwa_generate_openai_image($prompt, $api_key) {
    $url = 'https://api.openai.com/v1/images/generations';
    
    $body = json_encode([
        'model' => 'dall-e-3',
        'prompt' => $prompt,
        'n' => 1,
        'size' => '1024x1024',
        'quality' => 'standard'
    ]);
    
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => $body,
        'timeout' => 30,
    ]);
    
    if (is_wp_error($response)) {
        return '';
    }
    
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($response_body['data'][0]['url'])) {
        return $response_body['data'][0]['url'];
    }
    
    return '';
}

// Generate image using Google Gemini
function abpcwa_generate_gemini_image($prompt, $api_key) {
    // Note: As of current implementation, Gemini doesn't have a direct image generation API like DALL-E
    // This function is a placeholder for future implementation when Gemini releases image generation
    // For now, we'll return empty string to indicate no image generation
    return '';
}

// Process keywords from CSV file
function abpcwa_process_keywords_csv($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return '';
    }
    
    $keywords = [];
    $handle = fopen($file['tmp_name'], 'r');
    
    if ($handle !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            foreach ($data as $cell) {
                $cell = trim($cell);
                if (!empty($cell)) {
                    // Handle both comma-separated values and individual keywords
                    if (strpos($cell, ',') !== false) {
                        $split_keywords = array_map('trim', explode(',', $cell));
                        $keywords = array_merge($keywords, $split_keywords);
                    } else {
                        $keywords[] = $cell;
                    }
                }
            }
        }
        fclose($handle);
    }
    
    // Remove duplicates and empty values
    $keywords = array_unique(array_filter($keywords));
    
    return implode(', ', $keywords);
}
