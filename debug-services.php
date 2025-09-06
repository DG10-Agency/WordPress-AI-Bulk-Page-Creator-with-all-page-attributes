<?php
/**
 * Debug Service Detection Test
 * 
 * Run this file to see which pages are being detected as service pages
 * Place this file in your WordPress root directory and access via browser
 */

// Load WordPress
require_once('../../../wp-config.php');

// Include the menu generator
require_once('includes/menu-generator.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this debug script.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>AIOPMS Service Detection Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .detected { color: green; font-weight: bold; }
        .not-detected { color: red; }
        .main-services { color: blue; font-weight: bold; }
        h3 { color: #333; }
        h4 { color: #666; }
        h5 { color: #888; margin-top: 20px; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>

<h1>AIOPMS Service Page Detection Debug</h1>

<div style="background: #ffffcc; padding: 10px; border: 1px solid #ffcc00; margin-bottom: 20px;">
    <strong>Note:</strong> This debug page shows you which pages are being detected as service pages and how they're being categorized.
</div>

<?php

// Run the debug function
if (function_exists('aiopms_debug_service_detection')) {
    aiopms_debug_service_detection();
} else {
    echo "<p><strong>Error:</strong> Debug function not found. Make sure the menu generator is properly loaded.</p>";
}

?>

<h3>Next Steps:</h3>
<ul>
    <li>Review the "All Pages Analysis" table above to see which pages are being detected</li>
    <li>If some service pages are not being detected, their page titles might need to be adjusted</li>
    <li>Alternatively, you can modify the detection patterns in the menu generator</li>
    <li>Once satisfied with the detection, regenerate your service menu from the WordPress admin</li>
</ul>

<h3>Manual Service Menu Generation:</h3>
<p>After reviewing the debug results, you can manually generate the service menu by going to:</p>
<p><strong>WordPress Admin → AIOPMS → Generate Service Menu</strong></p>

</body>
</html>
