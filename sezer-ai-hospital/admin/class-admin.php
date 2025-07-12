<?php
/**
 * Admin Interface
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('SEZER AI Hospital', 'sezer-ai-hospital'),
            __('AI Hospital', 'sezer-ai-hospital'),
            'manage_options',
            'sezer-ai-hospital',
            array($this, 'admin_page'),
            'dashicons-heart',
            30
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('SEZER AI Hospital', 'sezer-ai-hospital'); ?></h1>
            <p><?php _e('Advanced healthcare management system for WordPress.', 'sezer-ai-hospital'); ?></p>
            
            <div class="sezer-admin-dashboard">
                <h2><?php _e('System Status', 'sezer-ai-hospital'); ?></h2>
                
                <?php
                $database = SezerAIHospital_Database::get_instance();
                if ($database->is_connected()) {
                    echo '<p style="color: green;">✓ ' . __('Database connected successfully', 'sezer-ai-hospital') . '</p>';
                } else {
                    echo '<p style="color: red;">✗ ' . __('Database connection failed', 'sezer-ai-hospital') . '</p>';
                }
                ?>
                
                <h2><?php _e('Quick Actions', 'sezer-ai-hospital'); ?></h2>
                <p>
                    <a href="<?php echo home_url('/doctor-dashboard/'); ?>" class="button">
                        <?php _e('Doctor Dashboard', 'sezer-ai-hospital'); ?>
                    </a>
                    <a href="<?php echo home_url('/patient-dashboard/'); ?>" class="button">
                        <?php _e('Patient Dashboard', 'sezer-ai-hospital'); ?>
                    </a>
                    <a href="<?php echo home_url('/admin-dashboard/'); ?>" class="button">
                        <?php _e('Admin Dashboard', 'sezer-ai-hospital'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
}