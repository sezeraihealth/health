<?php
/**
 * User Roles Management
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_UserRoles {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize user role management
    }
    
    public function create_roles() {
        // Create hospital-specific user roles
        add_role('hospital_doctor', __('Doctor', 'sezer-ai-hospital'), array(
            'read' => true,
            'manage_patients' => true,
            'view_medical_records' => true,
            'edit_medical_records' => true,
            'conduct_video_consultations' => true,
        ));
        
        add_role('hospital_patient', __('Patient', 'sezer-ai-hospital'), array(
            'read' => true,
            'view_own_medical_records' => true,
            'book_appointments' => true,
            'track_health_data' => true,
        ));
        
        add_role('hospital_admin', __('Hospital Administrator', 'sezer-ai-hospital'), array(
            'read' => true,
            'manage_options' => true,
            'manage_hospital' => true,
            'view_all_records' => true,
            'manage_users' => true,
        ));
    }
}