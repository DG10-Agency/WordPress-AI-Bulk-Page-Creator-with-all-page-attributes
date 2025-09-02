<?php
/**
 * Schema Generator Test File
 * This file demonstrates the schema generation functionality
 */

// Include WordPress functionality
require_once('../../../wp-load.php');

// Test schema detection and generation
function test_schema_generation() {
    echo "<h2>Schema Generator Test</h2>";
    
    // Create a test page
    $test_page = array(
        'post_title'   => 'Frequently Asked Questions',
        'post_content' => '<h2>What is your return policy?</h2><p>We offer a 30-day return policy on all products.</p><h2>Do you offer international shipping?</h2><p>Yes, we ship to over 50 countries worldwide.</p>',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    
    $page_id = wp_insert_post($test_page);
    
    if ($page_id) {
        echo "<p>Created test page with ID: $page_id</p>";
        
        // Generate schema
        $schema_data = abpcwa_generate_schema_markup($page_id);
        
        echo "<h3>Detected Schema Type:</h3>";
        $schema_type = get_post_meta($page_id, '_abpcwa_schema_type', true);
        echo "<p>" . ucfirst($schema_type) . "</p>";
        
        echo "<h3>Generated Schema Markup:</h3>";
        echo "<pre>" . json_encode($schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
        
        // Clean up
        wp_delete_post($page_id, true);
        echo "<p>Test page deleted.</p>";
    } else {
        echo "<p>Failed to create test page.</p>";
    }
}

// Run the test
test_schema_generation();
?>
