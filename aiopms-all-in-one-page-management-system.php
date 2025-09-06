<?php
/**
 * Plugin Name: AIOPMS - All In One Page Management System
 * Description: A comprehensive page management system for WordPress with bulk creation, AI generation, hierarchy management, schema markup, and menu generation.
 * Version: 3.0
 * Author: DG10
 * Author URI: https://www.dg10.agency
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('AIOPMS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AIOPMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIOPMS_GITHUB_URL', 'https://github.com/DG10-Agency/AIOPMS-All-In-One-Page-Management-System');

// Include necessary files
require_once AIOPMS_PLUGIN_PATH . 'includes/admin-menu.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/page-creation.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/csv-handler.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/settings-page.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/ai-generator.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/hierarchy-manager.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/menu-generator.php';
require_once AIOPMS_PLUGIN_PATH . 'includes/schema-generator.php';

// Enqueue scripts and styles
function aiopms_enqueue_assets() {
    wp_enqueue_style('aiopms-styles', AIOPMS_PLUGIN_URL . 'assets/css/styles.css');
    wp_enqueue_style('aiopms-admin-menu', AIOPMS_PLUGIN_URL . 'assets/css/admin-menu.css');
    wp_enqueue_style('aiopms-schema-column', AIOPMS_PLUGIN_URL . 'assets/css/schema-column.css');
    wp_enqueue_script('aiopms-scripts', AIOPMS_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), null, true);
    
    // Localize script with plugin URL
    wp_localize_script('aiopms-scripts', 'aiopms_plugin_data', array(
        'plugin_url' => AIOPMS_PLUGIN_URL
    ));
}
add_action('admin_enqueue_scripts', 'aiopms_enqueue_assets');
