<?php
/**
 * Plugin Name: AI Bulk Page Creator with Attributes
 * Description: A plugin to create multiple pages in bulk manually, via CSV upload, or with AI-powered suggestions.
 * Version: 3.0
 * Author: DG10
 * Author URI: https://www.dg10.agency
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('ABPCWA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ABPCWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ABPCWA_GITHUB_URL', 'https://github.com/your-repo-here'); // <--- UPDATE THIS URL

// Include necessary files
require_once ABPCWA_PLUGIN_PATH . 'includes/admin-menu.php';
require_once ABPCWA_PLUGIN_PATH . 'includes/page-creation.php';
require_once ABPCWA_PLUGIN_PATH . 'includes/csv-handler.php';
require_once ABPCWA_PLUGIN_PATH . 'includes/settings-page.php';
require_once ABPCWA_PLUGIN_PATH . 'includes/ai-generator.php';

// Enqueue scripts and styles
function abpcwa_enqueue_assets() {
    wp_enqueue_style('abpcwa-styles', ABPCWA_PLUGIN_URL . 'assets/css/styles.css');
    wp_enqueue_script('abpcwa-scripts', ABPCWA_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'abpcwa_enqueue_assets');
