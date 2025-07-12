<?php
/**
 * Video Consultation Management
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_VideoConsultation {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize video consultation functionality
        add_rewrite_rule('^video-consultation/([^/]+)/?$', 'index.php?sezer_video_room=$matches[1]', 'top');
        add_filter('query_vars', array($this, 'add_query_vars'));
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'sezer_video_room';
        return $vars;
    }
}