<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create pages from manual input
function abpcwa_create_pages_manually($titles_str) {
    $titles = explode("\n", sanitize_textarea_field($titles_str));
    $titles = array_map('trim', $titles);

    $parent_id_stack = [];
    $created_pages = 0;

    foreach ($titles as $title) {
        if (!empty($title)) {
            $depth = 0;
            $meta_description = '';
            $featured_image_url = '';
            $page_template = '';
            $post_status = 'publish';

            // Extract meta description
            if (strpos($title, ':+') !== false) {
                list($title, $meta_description) = explode(':+', $title, 2);
                $meta_description = trim($meta_description);
            }

            // Extract featured image URL
            if (strpos($title, ':*') !== false) {
                list($title, $featured_image_url) = explode(':*', $title, 2);
                $featured_image_url = trim($featured_image_url);
            }

            // Extract page template
            if (strpos($title, '::template=') !== false) {
                list($title, $template_part) = explode('::template=', $title, 2);
                $page_template = trim($template_part);
            }

            // Extract post status
            if (strpos($title, '::status=') !== false) {
                list($title, $status_part) = explode('::status=', $title, 2);
                $post_status = trim($status_part);
            }

            // Calculate depth
            while (substr($title, 0, 1) === '-') {
                $title = substr($title, 1);
                $depth++;
            }
            $title = trim($title);

            // Determine parent ID
            $parent_id = ($depth > 0 && isset($parent_id_stack[$depth - 1])) ? $parent_id_stack[$depth - 1] : 0;

            // Generate SEO-optimized slug
            $post_name = abpcwa_generate_seo_slug($title);
            
            // Create page
            $new_page = array(
                'post_title'   => wp_strip_all_tags($title),
                'post_name'    => $post_name,
                'post_content' => '',
                'post_status'  => $post_status,
                'post_type'    => 'page',
                'post_parent'  => $parent_id,
                'page_template' => $page_template,
                'post_excerpt'  => $meta_description
            );
            $page_id = wp_insert_post($new_page);

            if ($page_id) {
                $created_pages++;
                // Set featured image
                if (!empty($featured_image_url)) {
                    abpcwa_set_featured_image($page_id, $featured_image_url);
                }

                // Update parent stack
                $parent_id_stack[$depth] = $page_id;
                $parent_id_stack = array_slice($parent_id_stack, 0, $depth + 1);
            }
        }
    }

    if ($created_pages > 0) {
        echo '<div class="notice notice-success is-dismissible"><p>' . $created_pages . ' pages created successfully!</p></div>';
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>No pages were created. Please check your input.</p></div>';
    }
}

// Generate SEO-optimized slug
function abpcwa_generate_seo_slug($title, $max_length = 72) {
    // Convert to lowercase
    $slug = strtolower($title);
    
    // Replace spaces with hyphens
    $slug = str_replace(' ', '-', $slug);
    
    // Remove special characters, keep only alphanumeric and hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    
    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Trim hyphens from beginning and end
    $slug = trim($slug, '-');
    
    // Limit to max length while preserving word boundaries
    if (strlen($slug) > $max_length) {
        $slug = substr($slug, 0, $max_length);
        // Don't end with a hyphen
        $slug = rtrim($slug, '-');
    }
    
    return $slug;
}

// Set featured image
function abpcwa_set_featured_image($post_id, $image_url) {
    // Check if the image URL is valid
    if (filter_var($image_url, FILTER_VALIDATE_URL) === FALSE) {
        return;
    }

    // Use WordPress HTTP API to fetch the image
    $response = wp_remote_get($image_url, ['timeout' => 30]);
    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        return; // Failed to download image
    }

    $image_data = wp_remote_retrieve_body($response);
    $filename = basename($image_url);
    $upload_dir = wp_upload_dir();

    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    set_post_thumbnail($post_id, $attach_id);
}
