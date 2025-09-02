<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create pages from CSV file
function abpcwa_create_pages_from_csv($file) {
    if (!is_uploaded_file($file['tmp_name'])) {
        return;
    }

    $csv_data = array_map('str_getcsv', file($file['tmp_name']));
    $header = array_shift($csv_data);
    $created_pages = 0;
    $page_map = []; // To map titles to page IDs for parent lookup

    foreach ($csv_data as $row) {
        $row_data = array_combine($header, $row);

        $post_title = sanitize_text_field($row_data['post_title']);
        $post_parent_title = isset($row_data['post_parent']) ? sanitize_text_field($row_data['post_parent']) : '';
        $meta_description = isset($row_data['meta_description']) ? sanitize_text_field($row_data['meta_description']) : '';
        $featured_image_url = isset($row_data['featured_image']) ? esc_url_raw($row_data['featured_image']) : '';
        $page_template = isset($row_data['page_template']) ? sanitize_text_field($row_data['page_template']) : '';
        $post_status = isset($row_data['post_status']) ? sanitize_text_field($row_data['post_status']) : 'publish';

        // Determine parent ID
        $parent_id = 0;
        if (!empty($post_parent_title) && isset($page_map[$post_parent_title])) {
            $parent_id = $page_map[$post_parent_title];
        }

        // Create page
        $new_page = array(
            'post_title'   => $post_title,
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
            $page_map[$post_title] = $page_id; // Map title to ID for future parent lookups

            // Set featured image
            if (!empty($featured_image_url)) {
                abpcwa_set_featured_image($page_id, $featured_image_url);
            }
        }
    }

    if ($created_pages > 0) {
        echo '<div class="notice notice-success is-dismissible"><p>' . $created_pages . ' pages created successfully from the CSV file!</p></div>';
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>No pages were created from the CSV file. Please check the file format.</p></div>';
    }
}
