<?php
/**
 * Doctor Dashboard Template
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and verify doctor role
$current_user = wp_get_current_user();
$user_roles = new SezerAIHospital_UserRoles();

if (!$user_roles->user_has_role($current_user->ID, 'hospital_doctor')) {
    wp_die(__('Access denied. You do not have permission to view this page.', 'sezer-ai-hospital'));
}

// Get database instance for data
$db = SezerAIHospital_Database::get_instance();

// Get doctor information
$doctor_info = $db->query("SELECT * FROM doctors WHERE user_id = ?", [$current_user->ID]);
$doctor = $doctor_info ? $doctor_info->fetch() : null;

if (!$doctor) {
    wp_die(__('Doctor profile not found. Please contact administrator.', 'sezer-ai-hospital'));
}

// Get doctor's statistics
$stats = array(
    'today_appointments' => $db->get_count('appointments', "doctor_id = {$doctor['id']} AND DATE(appointment_date) = CURDATE()"),
    'total_patients' => $db->get_count('appointments', "doctor_id = {$doctor['id']} AND status = 'completed'", 'DISTINCT patient_id'),
    'pending_appointments' => $db->get_count('appointments', "doctor_id = {$doctor['id']} AND status = 'scheduled'"),
    'completed_today' => $db->get_count('appointments', "doctor_id = {$doctor['id']} AND DATE(appointment_date) = CURDATE() AND status = 'completed'")
);

// Get today's appointments
$today_appointments = $db->query("
    SELECT a.*, p.first_name as patient_name, p.last_name as patient_surname, 
           p.phone, p.date_of_birth, p.medical_history
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC
", [$doctor['id']]);

// Get upcoming appointments (next 7 days)
$upcoming_appointments = $db->query("
    SELECT a.*, p.first_name as patient_name, p.last_name as patient_surname
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ? AND a.appointment_date > NOW() AND a.appointment_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    ORDER BY a.appointment_date ASC
    LIMIT 5
", [$doctor['id']]);

get_header();
?>

<div class="sezer-dashboard doctor-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">
                    <i class="fas fa-user-md"></i>
                    <?php _e('Doctor Dashboard', 'sezer-ai-hospital'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php printf(__('Welcome, Dr. %s %s', 'sezer-ai-hospital'), $doctor['first_name'], $doctor['last_name']); ?>
                </p>
                <div class="doctor-specialization">
                    <span class="specialization-badge"><?php echo esc_html($doctor['specialization']); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="startVideoConsultation()">
                    <i class="fas fa-video"></i>
                    <?php _e('Start Video Call', 'sezer-ai-hospital'); ?>
                </button>
                <button class="btn btn-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    <?php _e('Refresh', 'sezer-ai-hospital'); ?>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card today-appointments">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['today_appointments']); ?></h3>
                    <p><?php _e('Today\'s Appointments', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card total-patients">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_patients']); ?></h3>
                    <p><?php _e('Total Patients Treated', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['pending_appointments']); ?></h3>
                    <p><?php _e('Pending Appointments', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card completed-today">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['completed_today']); ?></h3>
                    <p><?php _e('Completed Today', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
            <!-- Today's Schedule -->
            <div class="dashboard-card today-schedule">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> <?php _e('Today\'s Schedule', 'sezer-ai-hospital'); ?></h3>
                    <span class="schedule-date"><?php echo date('F j, Y'); ?></span>
                </div>
                <div class="card-content">
                    <?php if ($today_appointments && $today_appointments->rowCount() > 0): ?>
                        <div class="appointments-timeline">
                            <?php while ($appointment = $today_appointments->fetch()): ?>
                                <div class="appointment-item status-<?php echo esc_attr($appointment['status']); ?>">
                                    <div class="appointment-time">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="patient-info">
                                            <h4><?php echo esc_html($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?></h4>
                                            <p class="patient-age">
                                                <?php 
                                                $age = date_diff(date_create($appointment['date_of_birth']), date_create('today'))->y;
                                                printf(__('Age: %d years', 'sezer-ai-hospital'), $age);
                                                ?>
                                            </p>
                                            <p class="appointment-type"><?php echo esc_html(ucfirst($appointment['type'])); ?></p>
                                        </div>
                                        <div class="appointment-actions">
                                            <?php if ($appointment['status'] === 'scheduled'): ?>
                                                <button class="btn btn-sm btn-primary" onclick="startConsultation(<?php echo $appointment['id']; ?>)">
                                                    <i class="fas fa-play"></i>
                                                    <?php _e('Start', 'sezer-ai-hospital'); ?>
                                                </button>
                                                <button class="btn btn-sm btn-secondary" onclick="viewPatientHistory(<?php echo $appointment['patient_id']; ?>)">
                                                    <i class="fas fa-history"></i>
                                                    <?php _e('History', 'sezer-ai-hospital'); ?>
                                                </button>
                                            <?php elseif ($appointment['status'] === 'completed'): ?>
                                                <span class="status-badge status-completed">
                                                    <i class="fas fa-check"></i>
                                                    <?php _e('Completed', 'sezer-ai-hospital'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p><?php _e('No appointments scheduled for today', 'sezer-ai-hospital'); ?></p>
                            <button class="btn btn-primary" onclick="openModal('addAppointmentModal')">
                                <?php _e('Schedule Appointment', 'sezer-ai-hospital'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-card quick-actions">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> <?php _e('Quick Actions', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <div class="action-buttons">
                        <a href="#" class="action-btn" onclick="openModal('addPrescriptionModal')">
                            <i class="fas fa-prescription-bottle-alt"></i>
                            <span><?php _e('Write Prescription', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('patientSearchModal')">
                            <i class="fas fa-search"></i>
                            <span><?php _e('Search Patient', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('medicalRecordModal')">
                            <i class="fas fa-file-medical"></i>
                            <span><?php _e('Medical Records', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="generateMedicalReport()">
                            <i class="fas fa-chart-line"></i>
                            <span><?php _e('Generate Report', 'sezer-ai-hospital'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="dashboard-card upcoming-appointments">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> <?php _e('Upcoming Appointments', 'sezer-ai-hospital'); ?></h3>
                    <a href="#" class="view-all"><?php _e('View All', 'sezer-ai-hospital'); ?></a>
                </div>
                <div class="card-content">
                    <?php if ($upcoming_appointments && $upcoming_appointments->rowCount() > 0): ?>
                        <div class="appointments-list">
                            <?php while ($appointment = $upcoming_appointments->fetch()): ?>
                                <div class="appointment-item">
                                    <div class="appointment-info">
                                        <div class="patient-name">
                                            <?php echo esc_html($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?>
                                        </div>
                                        <div class="appointment-date">
                                            <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                        </div>
                                        <div class="appointment-type">
                                            <?php echo esc_html(ucfirst($appointment['type'])); ?>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <button class="btn btn-sm btn-outline" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p><?php _e('No upcoming appointments', 'sezer-ai-hospital'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Doctor Profile -->
            <div class="dashboard-card doctor-profile">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle"></i> <?php _e('My Profile', 'sezer-ai-hospital'); ?></h3>
                    <button class="btn btn-sm btn-outline" onclick="editProfile()">
                        <i class="fas fa-edit"></i>
                        <?php _e('Edit', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
                <div class="card-content">
                    <div class="profile-info">
                        <div class="profile-item">
                            <label><?php _e('Specialization', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($doctor['specialization']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('License Number', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($doctor['license_number']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Department', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($doctor['department']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Consultation Fee', 'sezer-ai-hospital'); ?></label>
                            <span>$<?php echo number_format($doctor['consultation_fee'], 2); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Contact', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($doctor['phone']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient History Modal -->
<div id="patientHistoryModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3><?php _e('Patient Medical History', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('patientHistoryModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="patientHistoryContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Video Consultation Modal -->
<div id="videoConsultationModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3><?php _e('Video Consultation', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="endVideoCall()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="video-container">
                <video id="localVideo" autoplay muted></video>
                <video id="remoteVideo" autoplay></video>
                <div class="video-controls">
                    <button class="btn btn-danger" onclick="endVideoCall()">
                        <i class="fas fa-phone-slash"></i>
                        <?php _e('End Call', 'sezer-ai-hospital'); ?>
                    </button>
                    <button class="btn btn-secondary" onclick="toggleMute()">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <button class="btn btn-secondary" onclick="toggleVideo()">
                        <i class="fas fa-video"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    initializeNotifications();
});

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function startConsultation(appointmentId) {
    // Start consultation process
    if (confirm('<?php _e('Start consultation for this appointment?', 'sezer-ai-hospital'); ?>')) {
        // Update appointment status and open consultation interface
        updateAppointmentStatus(appointmentId, 'in_progress');
    }
}

function startVideoConsultation() {
    openModal('videoConsultationModal');
    initializeVideoCall();
}

function viewPatientHistory(patientId) {
    openModal('patientHistoryModal');
    loadPatientHistory(patientId);
}

function loadPatientHistory(patientId) {
    // Load patient history via AJAX
    document.getElementById('patientHistoryContent').innerHTML = '<div class="loading">Loading patient history...</div>';
    
    // Simulate AJAX call
    setTimeout(() => {
        document.getElementById('patientHistoryContent').innerHTML = `
            <div class="patient-history">
                <h4>Medical History</h4>
                <p>Patient history will be loaded here via AJAX call to your backend.</p>
            </div>
        `;
    }, 1000);
}

function updateAppointmentStatus(appointmentId, status) {
    // Update appointment status via AJAX
    console.log('Updating appointment', appointmentId, 'to status', status);
}

function rescheduleAppointment(appointmentId) {
    alert('<?php _e('Reschedule functionality will be implemented', 'sezer-ai-hospital'); ?>');
}

function generateMedicalReport() {
    alert('<?php _e('Medical report generation feature coming soon!', 'sezer-ai-hospital'); ?>');
}

function editProfile() {
    alert('<?php _e('Profile editing feature will be implemented', 'sezer-ai-hospital'); ?>');
}

// Video call functions
function initializeVideoCall() {
    // Initialize WebRTC video call
    console.log('Initializing video call...');
}

function endVideoCall() {
    closeModal('videoConsultationModal');
    // Clean up video call resources
}

function toggleMute() {
    // Toggle microphone
}

function toggleVideo() {
    // Toggle video
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function loadDashboardData() {
    // Load real-time data via AJAX
}

function initializeNotifications() {
    // Initialize real-time notifications
}
</script>

<?php get_footer(); ?>