<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create pages from CSV file
function abpcwa_create_pages_from_csv($file) {
    // 1. Validate file upload
    if (!is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('File upload failed. Please try again.', 'abpcwa') . '</p></div>';
        return;
    }

    // 2. Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime_type !== 'text/csv' && $mime_type !== 'application/csv') {
         echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid file type. Please upload a valid CSV file.', 'abpcwa') . '</p></div>';
        return;
    }

    $csv_data = array_map('str_getcsv', file($file['tmp_name']));
    if (empty($csv_data)) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('The CSV file is empty.', 'abpcwa') . '</p></div>';
        return;
    }

    $header = array_shift($csv_data);
    $header = array_map('trim', $header); // Trim whitespace from headers
    $created_pages = 0;
    $page_map = []; // To map titles to page IDs for parent lookup

    foreach ($csv_data as $row_index => $row) {
        if (count($header) !== count($row)) {
            // Skip malformed rows
            continue;
        }
        $row_data = array_combine($header, $row);

        $post_title = isset($row_data['post_title']) ? sanitize_text_field(wp_unslash($row_data['post_title'])) : '';
        if (empty($post_title)) {
            continue; // A title is required
        }

        $post_parent_title = isset($row_data['post_parent']) ? sanitize_text_field(wp_unslash($row_data['post_parent'])) : '';
        $meta_description = isset($row_data['meta_description']) ? sanitize_textarea_field(wp_unslash($row_data['meta_description'])) : '';
        $featured_image_url = isset($row_data['featured_image']) ? esc_url_raw(wp_unslash($row_data['featured_image'])) : '';
        $page_template = isset($row_data['page_template']) ? sanitize_text_field(wp_unslash($row_data['page_template'])) : '';
        $post_status = isset($row_data['post_status']) ? sanitize_key(wp_unslash($row_data['post_status'])) : 'publish';
        
        // Use custom slug if provided, otherwise generate SEO-optimized slug
        $post_name = isset($row_data['slug']) && !empty($row_data['slug']) 
            ? sanitize_title(wp_unslash($row_data['slug']))
            : abpcwa_generate_seo_slug($post_title);

        // Determine parent ID
        $parent_id = 0;
        if (!empty($post_parent_title) && isset($page_map[$post_parent_title])) {
            $parent_id = $page_map[$post_parent_title];
        }

        
        // Create page
        $new_page = array(
            'post_title'   => $post_title,
            'post_name'    => $post_name,
            'post_content' => '',
            'post_status'  => $post_status,
            'post_type'    => 'page',
            'post_parent'  => $parent_id,
            'page_template' => $page_template,
            'post_excerpt'  => $meta_description
        );
        $page_id = wp_insert_post($new_page);

        if ($page_id && !is_wp_error($page_id)) {
            $created_pages++;
            $page_map[$post_title] = $page_id; // Map title to ID for future parent lookups

            // Set featured image with SEO metadata
            if (!empty($featured_image_url)) {
                $image_title = "Featured Image for " . sanitize_text_field($post_title);
                $keywords = abpcwa_extract_primary_keywords($post_title);
                $image_alt = "Visual representation of " . $keywords . " concept";
                $image_description = "Featured image for " . sanitize_text_field($post_title) . " page";
                
                abpcwa_set_featured_image($page_id, $featured_image_url, $image_title, $image_alt, $image_description);
            }
        }
    }

    if ($created_pages > 0) {
        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('%d pages created successfully from the CSV file!', 'abpcwa'), absint($created_pages)) . '</p></div>';
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('No pages were created from the CSV file. Please check the file format and content.', 'abpcwa') . '</p></div>';
    }
}
