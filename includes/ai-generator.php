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
    <form method="post" action="">
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
        abpcwa_generate_pages_with_ai($business_type, $business_details, $seo_keywords, $target_audience);
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
    
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-optimized meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. 
    
    Business Type: {$business_type}. 
    Details: {$business_details}.
    {$seo_context}
    
    Optimize the meta descriptions for search engines and ensure they include relevant keywords naturally. Return only the list, with each page on a new line.";

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
    
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-optimized meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. 
    
    Business Type: {$business_type}. 
    Details: {$business_details}.
    {$seo_context}
    
    Optimize the meta descriptions for search engines and ensure they include relevant keywords naturally. Return only the list, with each page on a new line.";

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
    
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-optimized meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. 
    
    Business Type: {$business_type}. 
    Details: {$business_details}.
    {$seo_context}
    
    Optimize the meta descriptions for search engines and ensure they include relevant keywords naturally. Return only the list, with each page on a new line.";

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

        $new_page = array(
            'post_title'   => $page_title,
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
    $prompt = "Create a simple, minimalist, abstract background image suitable for a webpage titled '{$page_title}'. The primary color should be '{$brand_color}'.";
    
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
