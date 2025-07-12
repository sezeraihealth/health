<?php
/**
 * Dashboard Management
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_Dashboard {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize dashboard functionality
        add_rewrite_rule('^doctor-dashboard/?$', 'index.php?sezer_dashboard=doctor', 'top');
        add_rewrite_rule('^patient-dashboard/?$', 'index.php?sezer_dashboard=patient', 'top');
        add_rewrite_rule('^admin-dashboard/?$', 'index.php?sezer_dashboard=admin', 'top');
        
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'sezer_dashboard';
        return $vars;
    }
    
    public function template_redirect() {
        $dashboard = get_query_var('sezer_dashboard');
        
        if (!$dashboard) {
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }
        
        // Load appropriate dashboard template
        $this->load_dashboard_template($dashboard);
        exit;
    }
    
    private function load_dashboard_template($dashboard) {
        get_header();
        echo '<div class="sezer-dashboard">';
        echo '<h1>' . sprintf(__('%s Dashboard', 'sezer-ai-hospital'), ucfirst($dashboard)) . '</h1>';
        echo '<p>' . __('Welcome to your dashboard!', 'sezer-ai-hospital') . '</p>';
        echo '</div>';
        get_footer();
    }
}