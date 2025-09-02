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
            abpcwa_create_suggested_pages($selected_pages);
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
        </table>
        <?php submit_button('Generate Page Suggestions'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_ai_generate_pages')) {
        $business_type = sanitize_text_field($_POST['abpcwa_business_type']);
        $business_details = sanitize_textarea_field($_POST['abpcwa_business_details']);
        abpcwa_generate_pages_with_ai($business_type, $business_details);
    }
}

// Generate pages with AI
function abpcwa_generate_pages_with_ai($business_type, $business_details) {
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
            $suggested_pages = abpcwa_get_openai_suggestions($business_type, $business_details, $api_key);
            break;
        case 'gemini':
            $suggested_pages = abpcwa_get_gemini_suggestions($business_type, $business_details, $api_key);
            break;
        case 'deepseek':
            $suggested_pages = abpcwa_get_deepseek_suggestions($business_type, $business_details, $api_key);
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
    echo '<ul style="list-style-type: none;">';
    foreach ($suggested_pages as $page_title) {
        echo '<li><input type="checkbox" name="abpcwa_selected_pages[]" value="' . esc_attr($page_title) . '" checked> ' . esc_html($page_title) . '</li>';
    }
    echo '</ul>';
    submit_button('Create Selected Pages');
    echo '</form>';
}

// Get page suggestions from OpenAI API
function abpcwa_get_openai_suggestions($business_type, $business_details, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-friendly meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. Business Type: {$business_type}. Details: {$business_details}. Return only the list, with each page on a new line.";

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
function abpcwa_get_gemini_suggestions($business_type, $business_details, $api_key) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-friendly meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. Business Type: {$business_type}. Details: {$business_details}. Return only the list, with each page on a new line.";

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
function abpcwa_get_deepseek_suggestions($business_type, $business_details, $api_key) {
    $url = 'https://api.deepseek.com/v1/chat/completions';
    $prompt = "Generate a list of essential website pages for a business. For each page, provide a title and a brief, SEO-friendly meta description (excerpt), separated by ':+'. Use hyphens (-) for nesting child pages. Include standard business and legal pages. Business Type: {$business_type}. Details: {$business_details}. Return only the list, with each page on a new line.";

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
function abpcwa_create_suggested_pages($pages) {
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
            $parent_id_stack[$depth] = $page_id;
            $parent_id_stack = array_slice($parent_id_stack, 0, $depth + 1);
        }
    }

    if ($created_count > 0) {
        echo '<div class="notice notice-success is-dismissible"><p>' . $created_count . ' pages created successfully as drafts.</p></div>';
    }
}
