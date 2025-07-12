<?php
/**
 * Plugin Name: SEZER AI Hospital
 * Plugin URI: https://sezeraihealth.com
 * Description: Advanced healthcare system with role-based dashboards, PostgreSQL integration, multi-language support, video consultations, and health data tracking.
 * Version: 1.0.0
 * Author: SEZER AI Health
 * Author URI: https://sezeraihealth.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sezer-ai-hospital
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SEZER_AI_HOSPITAL_VERSION', '1.0.0');
define('SEZER_AI_HOSPITAL_PLUGIN_FILE', __FILE__);
define('SEZER_AI_HOSPITAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SEZER_AI_HOSPITAL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class SezerAIHospital {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('sezer-ai-hospital', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Load core classes
        $this->load_dependencies();
        
        // Initialize components
        $this->init_components();
    }
    
    private function load_dependencies() {
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-env-loader.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-database.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-user-roles.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-dashboard.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-video-consultation.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-health-tracker.php';
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'includes/class-api-handler.php';
        
        if (is_admin()) {
            require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'admin/class-admin.php';
        }
        
        require_once SEZER_AI_HOSPITAL_PLUGIN_DIR . 'public/class-public.php';
    }
    
    private function init_components() {
        // Initialize environment loader
        SezerAIHospital_EnvLoader::init();
        
        // Initialize database
        SezerAIHospital_Database::get_instance();
        
        // Initialize user roles
        new SezerAIHospital_UserRoles();
        
        // Initialize dashboard
        new SezerAIHospital_Dashboard();
        
        // Initialize video consultation
        new SezerAIHospital_VideoConsultation();
        
        // Initialize health tracker
        new SezerAIHospital_HealthTracker();
        
        // Initialize API handler
        new SezerAIHospital_APIHandler();
        
        // Initialize admin interface
        if (is_admin()) {
            new SezerAIHospital_Admin();
        }
        
        // Initialize public interface
        new SezerAIHospital_Public();
    }
    
    public function activate() {
        // Create user roles
        $user_roles = new SezerAIHospital_UserRoles();
        $user_roles->create_roles();
        
        // Create database tables
        $database = SezerAIHospital_Database::get_instance();
        $database->create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new SezerAIHospital();