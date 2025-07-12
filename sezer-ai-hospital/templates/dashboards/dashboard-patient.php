<?php
/**
 * Patient Dashboard Template
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and verify patient role
$current_user = wp_get_current_user();
$user_roles = new SezerAIHospital_UserRoles();

if (!$user_roles->user_has_role($current_user->ID, 'hospital_patient')) {
    wp_die(__('Access denied. You do not have permission to view this page.', 'sezer-ai-hospital'));
}

// Get database instance for data
$db = SezerAIHospital_Database::get_instance();

// Get patient information
$patient_info = $db->query("SELECT * FROM patients WHERE user_id = ?", [$current_user->ID]);
$patient = $patient_info ? $patient_info->fetch() : null;

if (!$patient) {
    wp_die(__('Patient profile not found. Please contact administrator.', 'sezer-ai-hospital'));
}

// Get patient's statistics
$stats = array(
    'total_appointments' => $db->get_count('appointments', "patient_id = {$patient['id']}"),
    'upcoming_appointments' => $db->get_count('appointments', "patient_id = {$patient['id']} AND appointment_date > NOW() AND status = 'scheduled'"),
    'completed_appointments' => $db->get_count('appointments', "patient_id = {$patient['id']} AND status = 'completed'"),
    'next_appointment' => $db->query("
        SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_surname, d.specialization
        FROM appointments a 
        LEFT JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.patient_id = ? AND a.appointment_date > NOW() AND a.status = 'scheduled'
        ORDER BY a.appointment_date ASC 
        LIMIT 1
    ", [$patient['id']])
);

$next_appointment = $stats['next_appointment'] ? $stats['next_appointment']->fetch() : null;

// Get recent appointments
$recent_appointments = $db->query("
    SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_surname, d.specialization
    FROM appointments a 
    LEFT JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC 
    LIMIT 5
", [$patient['id']]);

// Get available doctors for booking
$available_doctors = $db->query("
    SELECT * FROM doctors 
    ORDER BY specialization, first_name
");

get_header();
?>

<div class="sezer-dashboard patient-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">
                    <i class="fas fa-user"></i>
                    <?php _e('Patient Dashboard', 'sezer-ai-hospital'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php printf(__('Welcome, %s %s', 'sezer-ai-hospital'), $patient['first_name'], $patient['last_name']); ?>
                </p>
                <div class="patient-id">
                    <span class="id-badge"><?php _e('Patient ID:', 'sezer-ai-hospital'); ?> <?php echo esc_html($patient['patient_id']); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openModal('bookAppointmentModal')">
                    <i class="fas fa-calendar-plus"></i>
                    <?php _e('Book Appointment', 'sezer-ai-hospital'); ?>
                </button>
                <button class="btn btn-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    <?php _e('Refresh', 'sezer-ai-hospital'); ?>
                </button>
            </div>
        </div>

        <!-- Next Appointment Alert -->
        <?php if ($next_appointment): ?>
        <div class="next-appointment-alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="alert-info">
                    <h3><?php _e('Next Appointment', 'sezer-ai-hospital'); ?></h3>
                    <p>
                        <?php printf(
                            __('Dr. %s %s - %s', 'sezer-ai-hospital'),
                            $next_appointment['doctor_name'],
                            $next_appointment['doctor_surname'],
                            $next_appointment['specialization']
                        ); ?>
                    </p>
                    <p class="appointment-time">
                        <?php echo date('F j, Y \a\t g:i A', strtotime($next_appointment['appointment_date'])); ?>
                    </p>
                </div>
                <div class="alert-actions">
                    <button class="btn btn-sm btn-primary" onclick="joinVideoCall(<?php echo $next_appointment['id']; ?>)">
                        <i class="fas fa-video"></i>
                        <?php _e('Join Video Call', 'sezer-ai-hospital'); ?>
                    </button>
                    <button class="btn btn-sm btn-outline" onclick="rescheduleAppointment(<?php echo $next_appointment['id']; ?>)">
                        <i class="fas fa-calendar-alt"></i>
                        <?php _e('Reschedule', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total-appointments">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_appointments']); ?></h3>
                    <p><?php _e('Total Appointments', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card upcoming">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['upcoming_appointments']); ?></h3>
                    <p><?php _e('Upcoming Appointments', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card completed">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['completed_appointments']); ?></h3>
                    <p><?php _e('Completed Visits', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card health-score">
                <div class="stat-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-content">
                    <h3>85<span class="unit">%</span></h3>
                    <p><?php _e('Health Score', 'sezer-ai-hospital'); ?></p>
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
                        <a href="#" class="action-btn" onclick="openModal('bookAppointmentModal')">
                            <i class="fas fa-calendar-plus"></i>
                            <span><?php _e('Book Appointment', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('healthDataModal')">
                            <i class="fas fa-chart-line"></i>
                            <span><?php _e('Health Data', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('prescriptionsModal')">
                            <i class="fas fa-prescription-bottle-alt"></i>
                            <span><?php _e('My Prescriptions', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('medicalRecordsModal')">
                            <i class="fas fa-file-medical"></i>
                            <span><?php _e('Medical Records', 'sezer-ai-hospital'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="dashboard-card recent-appointments">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> <?php _e('Recent Appointments', 'sezer-ai-hospital'); ?></h3>
                    <a href="#" class="view-all"><?php _e('View All', 'sezer-ai-hospital'); ?></a>
                </div>
                <div class="card-content">
                    <?php if ($recent_appointments && $recent_appointments->rowCount() > 0): ?>
                        <div class="appointments-list">
                            <?php while ($appointment = $recent_appointments->fetch()): ?>
                                <div class="appointment-item">
                                    <div class="appointment-info">
                                        <div class="doctor-name">
                                            <?php _e('Dr.', 'sezer-ai-hospital'); ?> <?php echo esc_html($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?>
                                        </div>
                                        <div class="specialization">
                                            <?php echo esc_html($appointment['specialization']); ?>
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
                            <button class="btn btn-primary" onclick="openModal('bookAppointmentModal')">
                                <?php _e('Book Your First Appointment', 'sezer-ai-hospital'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Health Tracking -->
            <div class="dashboard-card health-tracking">
                <div class="card-header">
                    <h3><i class="fas fa-heartbeat"></i> <?php _e('Health Tracking', 'sezer-ai-hospital'); ?></h3>
                    <button class="btn btn-sm btn-outline" onclick="addHealthData()">
                        <i class="fas fa-plus"></i>
                        <?php _e('Add Data', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
                <div class="card-content">
                    <div class="health-metrics">
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div class="metric-info">
                                <label><?php _e('Blood Pressure', 'sezer-ai-hospital'); ?></label>
                                <span class="metric-value">120/80 <small>mmHg</small></span>
                                <span class="metric-date"><?php _e('Last updated: Today', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fas fa-weight"></i>
                            </div>
                            <div class="metric-info">
                                <label><?php _e('Weight', 'sezer-ai-hospital'); ?></label>
                                <span class="metric-value">70 <small>kg</small></span>
                                <span class="metric-date"><?php _e('Last updated: 2 days ago', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fas fa-thermometer-half"></i>
                            </div>
                            <div class="metric-info">
                                <label><?php _e('Temperature', 'sezer-ai-hospital'); ?></label>
                                <span class="metric-value">36.5 <small>°C</small></span>
                                <span class="metric-date"><?php _e('Last updated: Yesterday', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="health-chart">
                        <canvas id="healthChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Patient Profile -->
            <div class="dashboard-card patient-profile">
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
                            <label><?php _e('Date of Birth', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo date('F j, Y', strtotime($patient['date_of_birth'])); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Gender', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html(ucfirst($patient['gender'])); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Phone', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($patient['phone']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Emergency Contact', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($patient['emergency_contact_name']); ?></span>
                            <small><?php echo esc_html($patient['emergency_contact_phone']); ?></small>
                        </div>
                        <div class="profile-item">
                            <label><?php _e('Allergies', 'sezer-ai-hospital'); ?></label>
                            <span><?php echo esc_html($patient['allergies'] ?: __('None reported', 'sezer-ai-hospital')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Book Appointment Modal -->
<div id="bookAppointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Book New Appointment', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('bookAppointmentModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="bookAppointmentForm">
                <div class="form-group">
                    <label><?php _e('Select Doctor', 'sezer-ai-hospital'); ?></label>
                    <select name="doctor_id" required>
                        <option value=""><?php _e('Choose a doctor', 'sezer-ai-hospital'); ?></option>
                        <?php if ($available_doctors && $available_doctors->rowCount() > 0): ?>
                            <?php while ($doctor = $available_doctors->fetch()): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php printf('Dr. %s %s - %s', $doctor['first_name'], $doctor['last_name'], $doctor['specialization']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Preferred Date', 'sezer-ai-hospital'); ?></label>
                        <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Preferred Time', 'sezer-ai-hospital'); ?></label>
                        <select name="appointment_time" required>
                            <option value=""><?php _e('Select time', 'sezer-ai-hospital'); ?></option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Appointment Type', 'sezer-ai-hospital'); ?></label>
                    <select name="appointment_type" required>
                        <option value=""><?php _e('Select type', 'sezer-ai-hospital'); ?></option>
                        <option value="consultation"><?php _e('Consultation', 'sezer-ai-hospital'); ?></option>
                        <option value="follow_up"><?php _e('Follow-up', 'sezer-ai-hospital'); ?></option>
                        <option value="emergency"><?php _e('Emergency', 'sezer-ai-hospital'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('Reason for Visit', 'sezer-ai-hospital'); ?></label>
                    <textarea name="notes" rows="3" placeholder="<?php _e('Please describe your symptoms or reason for the visit', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('bookAppointmentModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php _e('Book Appointment', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Health Data Modal -->
<div id="healthDataModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Add Health Data', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('healthDataModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="healthDataForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Blood Pressure (Systolic)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="bp_systolic" placeholder="120">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Blood Pressure (Diastolic)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="bp_diastolic" placeholder="80">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Weight (kg)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" step="0.1" name="weight" placeholder="70.0">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Temperature (°C)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" step="0.1" name="temperature" placeholder="36.5">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Notes', 'sezer-ai-hospital'); ?></label>
                    <textarea name="notes" rows="3" placeholder="<?php _e('Any additional notes about your health', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('healthDataModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php _e('Save Data', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeHealthChart();
    loadDashboardData();
});

// Chart initialization
function initializeHealthChart() {
    const ctx = document.getElementById('healthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: '<?php _e('Blood Pressure (Systolic)', 'sezer-ai-hospital'); ?>',
                data: [120, 118, 122, 119, 121, 120],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }, {
                label: '<?php _e('Weight (kg)', 'sezer-ai-hospital'); ?>',
                data: [72, 71.5, 71, 70.5, 70, 70],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function joinVideoCall(appointmentId) {
    alert('<?php _e('Video call feature will be implemented', 'sezer-ai-hospital'); ?>');
}

function rescheduleAppointment(appointmentId) {
    alert('<?php _e('Reschedule functionality will be implemented', 'sezer-ai-hospital'); ?>');
}

function addHealthData() {
    openModal('healthDataModal');
}

function editProfile() {
    alert('<?php _e('Profile editing feature will be implemented', 'sezer-ai-hospital'); ?>');
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

// Form submissions
document.getElementById('bookAppointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle appointment booking via AJAX
    alert('<?php _e('Appointment booking will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('bookAppointmentModal');
});

document.getElementById('healthDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle health data submission via AJAX
    alert('<?php _e('Health data saving will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('healthDataModal');
});
</script>

<?php get_footer(); ?>