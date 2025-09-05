<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Menu generator functions
class ABPCWA_Menu_Generator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Detect legal pages by title patterns
     */
    public function detect_legal_pages() {
        $legal_patterns = array(
            '/privacy|policy|gdpr/i',
            '/terms|conditions|service/i', 
            '/disclaimer/i',
            '/cookie|policy/i',
            '/refund|policy/i',
            '/shipping|policy/i',
            '/return|policy/i',
            '/legal/i'
        );
        
        $legal_pages = array();
        $all_pages = get_pages(array('post_status' => 'publish'));
        
        foreach ($all_pages as $page) {
            foreach ($legal_patterns as $pattern) {
                if (preg_match($pattern, $page->post_title)) {
                    $legal_pages[] = array(
                        'ID' => $page->ID,
                        'title' => $page->post_title,
                        'url' => get_permalink($page->ID)
                    );
                    break; // Stop checking other patterns for this page
                }
            }
        }
        
        return $legal_pages;
    }
    
    /**
     * Create universal bottom menu
     */
    public function create_universal_bottom_menu() {
        if (!current_user_can('edit_theme_options')) {
            return false;
        }
        $menu_name = 'Universal Bottom Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);
        
        // Delete existing menu if it exists
        if ($menu_exists) {
            wp_delete_nav_menu($menu_exists->term_id);
        }
        
        // Create new menu
        $menu_id = wp_create_nav_menu($menu_name);
        
        if (is_wp_error($menu_id)) {
            return false;
        }
        
        // Add standard items
        $this->add_menu_item($menu_id, 'Home', home_url());
        
        // Add legal pages
        $legal_pages = $this->detect_legal_pages();
        foreach ($legal_pages as $page) {
            $this->add_menu_item($menu_id, $page['title'], $page['url']);
        }
        
        // Add sitemap if configured
        $sitemap_url = get_option('abpcwa_sitemap_url', '');
        if (!empty($sitemap_url)) {
            $this->add_menu_item($menu_id, 'Sitemap', $sitemap_url);
        }
        
        // Add contact page if exists
        $contact_page = get_page_by_title('Contact');
        if ($contact_page) {
            $this->add_menu_item($menu_id, 'Contact', get_permalink($contact_page->ID));
        }
        
        // Set menu location if footer menu location exists
        $locations = get_theme_mod('nav_menu_locations');
        $footer_location = '';
        
        // Check for common footer menu locations
        $possible_locations = array('footer', 'footer-menu', 'footer_navigation', 'menu-footer');
        foreach ($possible_locations as $location) {
            if (isset($locations[$location])) {
                $footer_location = $location;
                break;
            }
        }
        
        // If no footer location found, create one or use primary
        if (empty($footer_location)) {
            // For simplicity, we'll just return the menu ID
            // User can manually assign it to a menu location
            return $menu_id;
        }
        
        // Assign to footer location
        $locations[$footer_location] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
        
        return $menu_id;
    }
    
    /**
     * Add item to menu
     */
    private function add_menu_item($menu_id, $title, $url, $parent_id = 0) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => esc_html($title),
            'menu-item-url' => esc_url($url),
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $parent_id
        ));
    }
    
    /**
     * Generate services menu
     */
    public function create_services_menu() {
        if (!current_user_can('edit_theme_options')) {
            return false;
        }
        $services = get_pages(array(
            'post_status' => 'publish',
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-services.php'
        ));
        
        if (empty($services)) {
            // Fallback: detect services by title
            $services = get_pages(array('post_status' => 'publish'));
            $services = array_filter($services, function($page) {
                return preg_match('/service|solution|offer|package/i', $page->post_title);
            });
        }
        
        if (empty($services)) {
            return false;
        }
        
        $menu_name = 'Services Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);
        
        if ($menu_exists) {
            wp_delete_nav_menu($menu_exists->term_id);
        }
        
        $menu_id = wp_create_nav_menu($menu_name);
        
        if (is_wp_error($menu_id)) {
            return false;
        }
        
        $this->add_menu_item($menu_id, 'Services', home_url('/services/'));
        
        foreach ($services as $service) {
            $this->add_menu_item($menu_id, $service->post_title, get_permalink($service->ID));
        }
        
        return $menu_id;
    }
    
    /**
     * Generate company menu
     */
    public function create_company_menu() {
        if (!current_user_can('edit_theme_options')) {
            return false;
        }
        $company_pages = get_pages(array(
            'post_status' => 'publish',
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-about.php'
        ));
        
        if (empty($company_pages)) {
            // Fallback: detect company pages by title
            $all_pages = get_pages(array('post_status' => 'publish'));
            $company_pages = array_filter($all_pages, function($page) {
                return preg_match('/about|company|team|mission|vision|story|career|contact/i', $page->post_title);
            });
        }
        
        if (empty($company_pages)) {
            return false;
        }
        
        $menu_name = 'Company Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);
        
        if ($menu_exists) {
            wp_delete_nav_menu($menu_exists->term_id);
        }
        
        $menu_id = wp_create_nav_menu($menu_name);
        
        if (is_wp_error($menu_id)) {
            return false;
        }
        
        foreach ($company_pages as $page) {
            $this->add_menu_item($menu_id, $page->post_title, get_permalink($page->ID));
        }
        
        return $menu_id;
    }
}

// Initialize menu generator
function abpcwa_menu_generator_init() {
    return ABPCWA_Menu_Generator::get_instance();
}

// Helper function to generate universal bottom menu
function abpcwa_generate_universal_bottom_menu() {
    $generator = ABPCWA_Menu_Generator::get_instance();
    return $generator->create_universal_bottom_menu();
}

// Helper function to generate services menu
function abpcwa_generate_services_menu() {
    $generator = ABPCWA_Menu_Generator::get_instance();
    return $generator->create_services_menu();
}

// Helper function to generate company menu
function abpcwa_generate_company_menu() {
    $generator = ABPCWA_Menu_Generator::get_instance();
    return $generator->create_company_menu();
}
