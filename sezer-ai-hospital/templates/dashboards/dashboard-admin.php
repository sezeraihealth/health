<?php
/**
 * Admin Dashboard Template
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and verify admin role
$current_user = wp_get_current_user();
$user_roles = new SezerAIHospital_UserRoles();

if (!$user_roles->user_has_role($current_user->ID, 'hospital_admin')) {
    wp_die(__('Access denied. You do not have permission to view this page.', 'sezer-ai-hospital'));
}

// Get database instance for data
$db = SezerAIHospital_Database::get_instance();

// Get dashboard statistics
$stats = array(
    'total_patients' => $db->get_count('patients'),
    'total_doctors' => $db->get_count('doctors'),
    'total_appointments' => $db->get_count('appointments'),
    'pending_appointments' => $db->get_count('appointments', "status = 'scheduled'"),
    'completed_appointments' => $db->get_count('appointments', "status = 'completed'"),
    'cancelled_appointments' => $db->get_count('appointments', "status = 'cancelled'")
);

// Get recent appointments
$recent_appointments = $db->query("
    SELECT a.*, p.first_name as patient_name, p.last_name as patient_surname, 
           d.first_name as doctor_name, d.last_name as doctor_surname
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    LEFT JOIN doctors d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");

get_header();
?>

<div class="sezer-dashboard admin-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">
                    <i class="fas fa-user-shield"></i>
                    <?php _e('Admin Dashboard', 'sezer-ai-hospital'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php printf(__('Welcome back, %s', 'sezer-ai-hospital'), $current_user->display_name); ?>
                </p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    <?php _e('Refresh', 'sezer-ai-hospital'); ?>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card patients">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_patients']); ?></h3>
                    <p><?php _e('Total Patients', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card doctors">
                <div class="stat-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_doctors']); ?></h3>
                    <p><?php _e('Total Doctors', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card appointments">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_appointments']); ?></h3>
                    <p><?php _e('Total Appointments', 'sezer-ai-hospital'); ?></p>
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
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
            <!-- Quick Actions -->
            <div class="dashboard-card quick-actions">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> <?php _e('Quick Actions', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <div class="action-buttons">
                        <a href="#" class="action-btn" onclick="openModal('addPatientModal')">
                            <i class="fas fa-user-plus"></i>
                            <span><?php _e('Add Patient', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('addDoctorModal')">
                            <i class="fas fa-user-md"></i>
                            <span><?php _e('Add Doctor', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('scheduleAppointmentModal')">
                            <i class="fas fa-calendar-plus"></i>
                            <span><?php _e('Schedule Appointment', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="generateReports()">
                            <i class="fas fa-chart-bar"></i>
                            <span><?php _e('Generate Reports', 'sezer-ai-hospital'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="dashboard-card recent-appointments">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> <?php _e('Recent Appointments', 'sezer-ai-hospital'); ?></h3>
                    <a href="#" class="view-all"><?php _e('View All', 'sezer-ai-hospital'); ?></a>
                </div>
                <div class="card-content">
                    <?php if ($recent_appointments && $recent_appointments->rowCount() > 0): ?>
                        <div class="appointments-list">
                            <?php while ($appointment = $recent_appointments->fetch()): ?>
                                <div class="appointment-item">
                                    <div class="appointment-info">
                                        <div class="patient-name">
                                            <?php echo esc_html($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?>
                                        </div>
                                        <div class="doctor-name">
                                            <?php _e('Dr.', 'sezer-ai-hospital'); ?> <?php echo esc_html($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?>
                                        </div>
                                        <div class="appointment-date">
                                            <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="appointment-status">
                                        <span class="status-badge status-<?php echo esc_attr($appointment['status']); ?>">
                                            <?php echo esc_html(ucfirst($appointment['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p><?php _e('No appointments found', 'sezer-ai-hospital'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Status -->
            <div class="dashboard-card system-status">
                <div class="card-header">
                    <h3><i class="fas fa-server"></i> <?php _e('System Status', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <div class="status-items">
                        <div class="status-item">
                            <div class="status-indicator online"></div>
                            <span><?php _e('Database Connection', 'sezer-ai-hospital'); ?></span>
                            <span class="status-value"><?php _e('Online', 'sezer-ai-hospital'); ?></span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator online"></div>
                            <span><?php _e('API Services', 'sezer-ai-hospital'); ?></span>
                            <span class="status-value"><?php _e('Active', 'sezer-ai-hospital'); ?></span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator online"></div>
                            <span><?php _e('Video Consultation', 'sezer-ai-hospital'); ?></span>
                            <span class="status-value"><?php _e('Ready', 'sezer-ai-hospital'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointment Statistics Chart -->
            <div class="dashboard-card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> <?php _e('Appointment Statistics', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <canvas id="appointmentChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="addPatientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Add New Patient', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('addPatientModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addPatientForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('First Name', 'sezer-ai-hospital'); ?></label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Last Name', 'sezer-ai-hospital'); ?></label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Date of Birth', 'sezer-ai-hospital'); ?></label>
                        <input type="date" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Gender', 'sezer-ai-hospital'); ?></label>
                        <select name="gender" required>
                            <option value=""><?php _e('Select Gender', 'sezer-ai-hospital'); ?></option>
                            <option value="male"><?php _e('Male', 'sezer-ai-hospital'); ?></option>
                            <option value="female"><?php _e('Female', 'sezer-ai-hospital'); ?></option>
                            <option value="other"><?php _e('Other', 'sezer-ai-hospital'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Phone Number', 'sezer-ai-hospital'); ?></label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label><?php _e('Address', 'sezer-ai-hospital'); ?></label>
                    <textarea name="address" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addPatientModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php _e('Add Patient', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadDashboardData();
});

// Chart initialization
function initializeChart() {
    const ctx = document.getElementById('appointmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['<?php _e('Scheduled', 'sezer-ai-hospital'); ?>', '<?php _e('Completed', 'sezer-ai-hospital'); ?>', '<?php _e('Cancelled', 'sezer-ai-hospital'); ?>'],
            datasets: [{
                data: [<?php echo $stats['pending_appointments']; ?>, <?php echo $stats['completed_appointments']; ?>, <?php echo $stats['cancelled_appointments']; ?>],
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });
}

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function loadDashboardData() {
    // Load real-time data via AJAX
    // Implementation depends on your AJAX endpoints
}

function generateReports() {
    // Generate and download reports
    alert('<?php _e('Report generation feature coming soon!', 'sezer-ai-hospital'); ?>');
}

// Form submissions
document.getElementById('addPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle patient addition via AJAX
    alert('<?php _e('Patient addition feature will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('addPatientModal');
});
</script>

<?php get_footer(); ?>