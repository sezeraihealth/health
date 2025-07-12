<?php
/**
 * API Handler for External Integrations
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_APIHandler {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    public function register_rest_routes() {
        register_rest_route('sezer-ai-hospital/v1', '/patients', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_patients'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
    }
    
    public function check_permissions() {
        return current_user_can('manage_hospital');
    }
    
    public function get_patients() {
        return rest_ensure_response(array('patients' => array()));
    }
}