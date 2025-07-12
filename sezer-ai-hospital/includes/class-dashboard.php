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
        add_rewrite_rule('^nurse-dashboard/?$', 'index.php?sezer_dashboard=nurse', 'top');
        add_rewrite_rule('^receptionist-dashboard/?$', 'index.php?sezer_dashboard=receptionist', 'top');
        
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
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
    
    public function enqueue_dashboard_assets() {
        $dashboard = get_query_var('sezer_dashboard');
        
        if ($dashboard) {
            // Enqueue dashboard styles
            wp_enqueue_style(
                'sezer-dashboard-css',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/dashboard.css',
                array(),
                '1.0.0'
            );
            
            // Enqueue Chart.js for charts
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js',
                array(),
                '3.9.1',
                true
            );
            
            // Enqueue Font Awesome for icons
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
                array(),
                '6.0.0'
            );
            
            // Enqueue dashboard JavaScript
            wp_enqueue_script(
                'sezer-dashboard-js',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/dashboard.js',
                array('jquery', 'chart-js'),
                '1.0.0',
                true
            );
            
            // Localize script for AJAX
            wp_localize_script('sezer-dashboard-js', 'sezer_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sezer_dashboard_nonce')
            ));
        }
    }
    
    private function load_dashboard_template($dashboard) {
        // Initialize required classes
        SezerAIHospital_EnvLoader::init();
        
        // Define plugin directory constant for templates
        if (!defined('SEZER_AI_HOSPITAL_PLUGIN_DIR')) {
            define('SEZER_AI_HOSPITAL_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
        }
        
        $template_file = SEZER_AI_HOSPITAL_PLUGIN_DIR . 'templates/dashboards/dashboard-' . $dashboard . '.php';
        
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            // Fallback to basic template
            get_header();
            echo '<div class="sezer-dashboard">';
            echo '<div class="dashboard-container">';
            echo '<div class="dashboard-header">';
            echo '<h1>' . sprintf(__('%s Dashboard', 'sezer-ai-hospital'), ucfirst($dashboard)) . '</h1>';
            echo '<p>' . __('Dashboard template not found. Please check the plugin installation.', 'sezer-ai-hospital') . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            get_footer();
        }
    }
}