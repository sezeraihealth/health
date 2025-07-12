<?php
/**
 * Public Interface
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_Public {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('sezer_appointment_form', array($this, 'appointment_form_shortcode'));
        add_shortcode('sezer_doctor_list', array($this, 'doctor_list_shortcode'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('sezer-public', SEZER_AI_HOSPITAL_PLUGIN_URL . 'assets/css/public.css', array(), SEZER_AI_HOSPITAL_VERSION);
        wp_enqueue_script('sezer-public', SEZER_AI_HOSPITAL_PLUGIN_URL . 'assets/js/public.js', array('jquery'), SEZER_AI_HOSPITAL_VERSION, true);
    }
    
    public function appointment_form_shortcode($atts) {
        ob_start();
        ?>
        <div class="sezer-appointment-form">
            <h3><?php _e('Book an Appointment', 'sezer-ai-hospital'); ?></h3>
            <form>
                <p>
                    <label><?php _e('Select Doctor', 'sezer-ai-hospital'); ?></label>
                    <select name="doctor_id">
                        <option value=""><?php _e('Choose a doctor...', 'sezer-ai-hospital'); ?></option>
                    </select>
                </p>
                <p>
                    <label><?php _e('Preferred Date', 'sezer-ai-hospital'); ?></label>
                    <input type="date" name="appointment_date" required>
                </p>
                <p>
                    <label><?php _e('Notes', 'sezer-ai-hospital'); ?></label>
                    <textarea name="notes" rows="3"></textarea>
                </p>
                <p>
                    <button type="submit"><?php _e('Book Appointment', 'sezer-ai-hospital'); ?></button>
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function doctor_list_shortcode($atts) {
        ob_start();
        ?>
        <div class="sezer-doctor-list">
            <h3><?php _e('Our Doctors', 'sezer-ai-hospital'); ?></h3>
            <p><?php _e('No doctors found. Please add doctors through the admin panel.', 'sezer-ai-hospital'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}