<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Keyword Density Analyzer for AIOPMS Plugin
 * Provides comprehensive keyword analysis functionality
 */

class AIOPMS_Keyword_Analyzer {
    
    private $plugin_url;
    private $plugin_path;
    
    public function __construct() {
        $this->plugin_url = AIOPMS_PLUGIN_URL;
        $this->plugin_path = AIOPMS_PLUGIN_PATH;
        
        // Initialize hooks
        add_action('wp_ajax_aiopms_analyze_keywords', array($this, 'analyze_keywords_ajax'));
        add_action('wp_ajax_aiopms_get_pages', array($this, 'get_pages_ajax'));
        add_action('wp_ajax_aiopms_export_keyword_analysis', array($this, 'export_analysis_ajax'));
    }
    
    /**
     * Get all published pages for dropdown
     */
    public function get_pages_ajax() {
        check_ajax_referer('aiopms_keyword_analysis', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $pages = get_posts(array(
            'post_type' => array('page', 'post'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        $formatted_pages = array();
        foreach ($pages as $page) {
            $formatted_pages[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'url' => get_permalink($page->ID),
                'type' => $page->post_type
            );
        }
        
        wp_send_json_success($formatted_pages);
    }
    
    /**
     * Analyze keywords for a specific page
     */
    public function analyze_keywords_ajax() {
        check_ajax_referer('aiopms_keyword_analysis', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $page_id = intval($_POST['page_id']);
        $keywords_input = sanitize_textarea_field($_POST['keywords']);
        
        if (empty($page_id) || empty($keywords_input)) {
            wp_send_json_error('Page ID and keywords are required');
        }
        
        $analysis = $this->analyze_page_keywords($page_id, $keywords_input);
        
        if ($analysis) {
            wp_send_json_success($analysis);
        } else {
            wp_send_json_error('Failed to analyze keywords');
        }
    }
    
    /**
     * Export keyword analysis results
     */
    public function export_analysis_ajax() {
        check_ajax_referer('aiopms_keyword_analysis', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $format = sanitize_text_field($_POST['format']);
        $analysis_data = $_POST['analysis_data'];
        
        if (empty($format) || empty($analysis_data)) {
            wp_die('Invalid export parameters');
        }
        
        $this->export_analysis($format, $analysis_data);
    }
    
    /**
     * Main keyword analysis function
     */
    public function analyze_page_keywords($page_id, $keywords_input) {
        // Get page content
        $page = get_post($page_id);
        if (!$page) {
            return false;
        }
        
        // Extract and clean keywords
        $keywords = $this->extract_keywords($keywords_input);
        if (empty($keywords)) {
            return false;
        }
        
        // Get page content for analysis
        $content_data = $this->extract_page_content($page);
        
        // Analyze each keyword
        $results = array();
        $total_words = $this->count_total_words($content_data['full_content']);
        
        foreach ($keywords as $keyword) {
            $keyword_analysis = $this->analyze_single_keyword($keyword, $content_data, $total_words);
            if ($keyword_analysis) {
                $results[] = $keyword_analysis;
            }
        }
        
        // Sort by density (highest first)
        usort($results, function($a, $b) {
            return $b['density'] <=> $a['density'];
        });
        
        return array(
            'page_info' => array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'url' => get_permalink($page->ID),
                'type' => $page->post_type,
                'word_count' => $total_words,
                'analysis_date' => current_time('Y-m-d H:i:s')
            ),
            'keywords' => $results,
            'summary' => $this->generate_summary($results, $total_words)
        );
    }
    
    /**
     * Extract keywords from input text
     */
    private function extract_keywords($keywords_input) {
        // Split by newlines and commas
        $lines = preg_split('/[\r\n]+/', $keywords_input);
        $keywords = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Split by commas if present
            $comma_separated = explode(',', $line);
            foreach ($comma_separated as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword) && strlen($keyword) > 1) {
                    $keywords[] = $keyword;
                }
            }
        }
        
        // Remove duplicates and return
        return array_unique($keywords);
    }
    
    /**
     * Extract all relevant content from a page
     */
    private function extract_page_content($page) {
        // Get meta description
        $meta_description = get_post_meta($page->ID, '_yoast_wpseo_metadesc', true);
        if (empty($meta_description)) {
            $meta_description = get_post_meta($page->ID, '_aioseo_description', true);
        }
        
        // Get excerpt
        $excerpt = $page->post_excerpt;
        if (empty($excerpt)) {
            $excerpt = wp_trim_words($page->post_content, 55);
        }
        
        // Extract headings
        $headings = $this->extract_headings($page->post_content);
        
        // Clean main content
        $clean_content = $this->clean_content($page->post_content);
        
        // Combine all content for analysis
        $full_content = implode(' ', array(
            $page->post_title,
            $meta_description,
            $excerpt,
            $clean_content,
            implode(' ', $headings)
        ));
        
        return array(
            'title' => $page->post_title,
            'content' => $clean_content,
            'meta_description' => $meta_description,
            'excerpt' => $excerpt,
            'headings' => $headings,
            'full_content' => $full_content
        );
    }
    
    /**
     * Extract headings from content
     */
    private function extract_headings($content) {
        $headings = array();
        
        // Match h1-h6 tags
        preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $heading) {
                $clean_heading = wp_strip_all_tags($heading);
                if (!empty($clean_heading)) {
                    $headings[] = $clean_heading;
                }
            }
        }
        
        return $headings;
    }
    
    /**
     * Clean content by removing HTML and normalizing text
     */
    private function clean_content($content) {
        // Remove HTML tags
        $content = wp_strip_all_tags($content);
        
        // Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        
        // Normalize whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }
    
    /**
     * Count total words in content
     */
    private function count_total_words($content) {
        $words = str_word_count($content, 0, '0123456789');
        return $words;
    }
    
    /**
     * Analyze a single keyword
     */
    private function analyze_single_keyword($keyword, $content_data, $total_words) {
        $keyword_lower = strtolower($keyword);
        $keyword_count = 0;
        $positions = array();
        
        // Count occurrences in different content areas
        $areas = array(
            'title' => $content_data['title'],
            'content' => $content_data['content'],
            'meta_description' => $content_data['meta_description'],
            'excerpt' => $content_data['excerpt'],
            'headings' => implode(' ', $content_data['headings'])
        );
        
        $area_counts = array();
        foreach ($areas as $area_name => $area_content) {
            if (empty($area_content)) continue;
            
            $area_content_lower = strtolower($area_content);
            $area_count = substr_count($area_content_lower, $keyword_lower);
            $area_counts[$area_name] = $area_count;
            $keyword_count += $area_count;
        }
        
        // Find positions in full content
        $full_content_lower = strtolower($content_data['full_content']);
        $offset = 0;
        while (($pos = strpos($full_content_lower, $keyword_lower, $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }
        
        // Calculate density
        $density = $total_words > 0 ? ($keyword_count / $total_words) * 100 : 0;
        
        // Determine status
        $status = $this->get_keyword_status($density);
        
        return array(
            'keyword' => $keyword,
            'count' => $keyword_count,
            'density' => round($density, 2),
            'status' => $status,
            'positions' => $positions,
            'area_counts' => $area_counts,
            'context' => $this->get_keyword_context($keyword_lower, $content_data['content'], 3)
        );
    }
    
    /**
     * Get keyword status based on density
     */
    private function get_keyword_status($density) {
        if ($density >= 3.0) {
            return 'high';
        } elseif ($density >= 1.0) {
            return 'good';
        } elseif ($density >= 0.5) {
            return 'moderate';
        } else {
            return 'low';
        }
    }
    
    /**
     * Get context around keyword occurrences
     */
    private function get_keyword_context($keyword, $content, $context_words = 3) {
        $contexts = array();
        $content_lower = strtolower($content);
        $words = explode(' ', $content);
        $words_lower = explode(' ', $content_lower);
        
        foreach ($words_lower as $index => $word) {
            if (strpos($word, $keyword) !== false) {
                $start = max(0, $index - $context_words);
                $end = min(count($words) - 1, $index + $context_words);
                
                $context = array_slice($words, $start, $end - $start + 1);
                $contexts[] = implode(' ', $context);
            }
        }
        
        return array_slice($contexts, 0, 5); // Limit to 5 contexts
    }
    
    /**
     * Generate analysis summary
     */
    private function generate_summary($results, $total_words) {
        $total_keywords = count($results);
        $keywords_found = count(array_filter($results, function($r) { return $r['count'] > 0; }));
        $avg_density = $total_keywords > 0 ? array_sum(array_column($results, 'density')) / $total_keywords : 0;
        
        $status_counts = array(
            'high' => 0,
            'good' => 0,
            'moderate' => 0,
            'low' => 0
        );
        
        foreach ($results as $result) {
            $status_counts[$result['status']]++;
        }
        
        return array(
            'total_keywords' => $total_keywords,
            'keywords_found' => $keywords_found,
            'total_words' => $total_words,
            'average_density' => round($avg_density, 2),
            'status_distribution' => $status_counts,
            'recommendations' => $this->generate_recommendations($results, $status_counts)
        );
    }
    
    /**
     * Generate SEO recommendations
     */
    private function generate_recommendations($results, $status_counts) {
        $recommendations = array();
        
        if ($status_counts['high'] > 0) {
            $recommendations[] = 'Consider reducing keyword density for over-optimized terms (3%+ density)';
        }
        
        if ($status_counts['low'] > 0) {
            $recommendations[] = 'Add more content or include missing keywords naturally';
        }
        
        if ($status_counts['good'] > 0) {
            $recommendations[] = 'Good keyword distribution found for some terms';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Keyword analysis complete. Review individual results for optimization opportunities.';
        }
        
        return $recommendations;
    }
    
    /**
     * Export analysis results
     */
    private function export_analysis($format, $analysis_data) {
        $page_info = $analysis_data['page_info'];
        $keywords = $analysis_data['keywords'];
        $summary = $analysis_data['summary'];
        
        $filename = sanitize_file_name($page_info['title']) . '_keyword_analysis_' . date('Y-m-d');
        
        switch ($format) {
            case 'csv':
                $this->export_csv($filename, $page_info, $keywords, $summary);
                break;
            case 'json':
                $this->export_json($filename, $analysis_data);
                break;
            default:
                wp_die('Invalid export format');
        }
    }
    
    /**
     * Export as CSV
     */
    private function export_csv($filename, $page_info, $keywords, $summary) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Page info header
        fputcsv($output, array('Page Analysis Report'));
        fputcsv($output, array('Page Title', $page_info['title']));
        fputcsv($output, array('URL', $page_info['url']));
        fputcsv($output, array('Word Count', $page_info['word_count']));
        fputcsv($output, array('Analysis Date', $page_info['analysis_date']));
        fputcsv($output, array(''));
        
        // Summary
        fputcsv($output, array('Summary'));
        fputcsv($output, array('Total Keywords', $summary['total_keywords']));
        fputcsv($output, array('Keywords Found', $summary['keywords_found']));
        fputcsv($output, array('Average Density', $summary['average_density'] . '%'));
        fputcsv($output, array(''));
        
        // Keywords data
        fputcsv($output, array('Keyword', 'Count', 'Density (%)', 'Status', 'Title', 'Content', 'Meta Description', 'Excerpt', 'Headings'));
        
        foreach ($keywords as $keyword) {
            fputcsv($output, array(
                $keyword['keyword'],
                $keyword['count'],
                $keyword['density'],
                ucfirst($keyword['status']),
                $keyword['area_counts']['title'] ?? 0,
                $keyword['area_counts']['content'] ?? 0,
                $keyword['area_counts']['meta_description'] ?? 0,
                $keyword['area_counts']['excerpt'] ?? 0,
                $keyword['area_counts']['headings'] ?? 0
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export as JSON
     */
    private function export_json($filename, $analysis_data) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($analysis_data, JSON_PRETTY_PRINT);
        exit;
    }
}

// Initialize the keyword analyzer
new AIOPMS_Keyword_Analyzer();
