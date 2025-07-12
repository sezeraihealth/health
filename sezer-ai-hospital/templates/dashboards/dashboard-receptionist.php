<?php
/**
 * Receptionist Dashboard Template
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and verify receptionist role
$current_user = wp_get_current_user();
$user_roles = new SezerAIHospital_UserRoles();

if (!$user_roles->user_has_role($current_user->ID, 'hospital_receptionist')) {
    wp_die(__('Access denied. You do not have permission to view this page.', 'sezer-ai-hospital'));
}

// Get database instance for data
$db = SezerAIHospital_Database::get_instance();

// Get receptionist statistics
$stats = array(
    'appointments_today' => $db->get_count('appointments', "DATE(appointment_date) = CURDATE()"),
    'new_patients_today' => $db->get_count('patients', "DATE(created_at) = CURDATE()"),
    'pending_appointments' => $db->get_count('appointments', "status = 'scheduled' AND appointment_date >= NOW()"),
    'total_patients' => $db->get_count('patients')
);

// Get today's appointments
$today_appointments = $db->query("
    SELECT a.*, p.first_name as patient_name, p.last_name as patient_surname, 
           p.phone, d.first_name as doctor_name, d.last_name as doctor_surname, d.specialization
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    LEFT JOIN doctors d ON a.doctor_id = d.id 
    WHERE DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC
");

// Get recent patient registrations
$recent_patients = $db->query("
    SELECT * FROM patients 
    ORDER BY created_at DESC 
    LIMIT 10
");

// Get available doctors
$available_doctors = $db->query("
    SELECT * FROM doctors 
    ORDER BY specialization, first_name
");

get_header();
?>

<div class="sezer-dashboard receptionist-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">
                    <i class="fas fa-user-tie"></i>
                    <?php _e('Receptionist Dashboard', 'sezer-ai-hospital'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php printf(__('Welcome, %s', 'sezer-ai-hospital'), $current_user->display_name); ?>
                </p>
                <div class="current-time">
                    <span class="time-badge" id="currentTime"><?php echo date('g:i A'); ?></span>
                    <span class="date-badge"><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openModal('scheduleAppointmentModal')">
                    <i class="fas fa-calendar-plus"></i>
                    <?php _e('Schedule Appointment', 'sezer-ai-hospital'); ?>
                </button>
                <button class="btn btn-success" onclick="openModal('registerPatientModal')">
                    <i class="fas fa-user-plus"></i>
                    <?php _e('Register Patient', 'sezer-ai-hospital'); ?>
                </button>
                <button class="btn btn-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    <?php _e('Refresh', 'sezer-ai-hospital'); ?>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card appointments-today">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['appointments_today']); ?></h3>
                    <p><?php _e('Appointments Today', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>

            <div class="stat-card new-patients">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['new_patients_today']); ?></h3>
                    <p><?php _e('New Patients Today', 'sezer-ai-hospital'); ?></p>
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

            <div class="stat-card total-patients">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_patients']); ?></h3>
                    <p><?php _e('Total Patients', 'sezer-ai-hospital'); ?></p>
                </div>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
            <!-- Today's Appointments -->
            <div class="dashboard-card appointments-schedule">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> <?php _e('Today\'s Appointments', 'sezer-ai-hospital'); ?></h3>
                    <div class="header-actions">
                        <button class="btn btn-sm btn-primary" onclick="openModal('scheduleAppointmentModal')">
                            <i class="fas fa-plus"></i>
                            <?php _e('Add', 'sezer-ai-hospital'); ?>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <?php if ($today_appointments && $today_appointments->rowCount() > 0): ?>
                        <div class="appointments-list">
                            <?php while ($appointment = $today_appointments->fetch()): ?>
                                <div class="appointment-item status-<?php echo esc_attr($appointment['status']); ?>">
                                    <div class="appointment-time">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="patient-info">
                                            <h4><?php echo esc_html($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?></h4>
                                            <p class="doctor-info">
                                                <?php _e('Dr.', 'sezer-ai-hospital'); ?> <?php echo esc_html($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?>
                                            </p>
                                            <p class="specialization"><?php echo esc_html($appointment['specialization']); ?></p>
                                        </div>
                                        <div class="contact-info">
                                            <span class="phone">
                                                <i class="fas fa-phone"></i>
                                                <?php echo esc_html($appointment['phone']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <?php if ($appointment['status'] === 'scheduled'): ?>
                                            <button class="btn btn-sm btn-success" onclick="checkInPatient(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                                <?php _e('Check In', 'sezer-ai-hospital'); ?>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php _e('Reschedule', 'sezer-ai-hospital'); ?>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                                <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="status-badge status-<?php echo esc_attr($appointment['status']); ?>">
                                                <?php echo esc_html(ucfirst($appointment['status'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p><?php _e('No appointments scheduled for today', 'sezer-ai-hospital'); ?></p>
                            <button class="btn btn-primary" onclick="openModal('scheduleAppointmentModal')">
                                <?php _e('Schedule First Appointment', 'sezer-ai-hospital'); ?>
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
                        <a href="#" class="action-btn" onclick="openModal('registerPatientModal')">
                            <i class="fas fa-user-plus"></i>
                            <span><?php _e('Register Patient', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('scheduleAppointmentModal')">
                            <i class="fas fa-calendar-plus"></i>
                            <span><?php _e('Schedule Appointment', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('patientSearchModal')">
                            <i class="fas fa-search"></i>
                            <span><?php _e('Search Patient', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="generateDailyReport()">
                            <i class="fas fa-file-alt"></i>
                            <span><?php _e('Daily Report', 'sezer-ai-hospital'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Patient Registrations -->
            <div class="dashboard-card recent-patients">
                <div class="card-header">
                    <h3><i class="fas fa-user-friends"></i> <?php _e('Recent Patient Registrations', 'sezer-ai-hospital'); ?></h3>
                    <a href="#" class="view-all"><?php _e('View All', 'sezer-ai-hospital'); ?></a>
                </div>
                <div class="card-content">
                    <?php if ($recent_patients && $recent_patients->rowCount() > 0): ?>
                        <div class="patients-list">
                            <?php while ($patient = $recent_patients->fetch()): ?>
                                <div class="patient-item">
                                    <div class="patient-info">
                                        <div class="patient-name">
                                            <?php echo esc_html($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                        </div>
                                        <div class="patient-details">
                                            <span class="patient-id"><?php echo esc_html($patient['patient_id']); ?></span>
                                            <span class="registration-date"><?php echo date('M j, Y', strtotime($patient['created_at'])); ?></span>
                                        </div>
                                        <div class="contact-info">
                                            <span class="phone"><?php echo esc_html($patient['phone']); ?></span>
                                        </div>
                                    </div>
                                    <div class="patient-actions">
                                        <button class="btn btn-sm btn-outline" onclick="viewPatientDetails(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="scheduleForPatient(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-user-times"></i>
                            <p><?php _e('No recent patient registrations', 'sezer-ai-hospital'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Doctor Availability -->
            <div class="dashboard-card doctor-availability">
                <div class="card-header">
                    <h3><i class="fas fa-user-md"></i> <?php _e('Doctor Availability', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <?php if ($available_doctors && $available_doctors->rowCount() > 0): ?>
                        <div class="doctors-list">
                            <?php while ($doctor = $available_doctors->fetch()): ?>
                                <div class="doctor-item">
                                    <div class="doctor-info">
                                        <div class="doctor-name">
                                            <?php _e('Dr.', 'sezer-ai-hospital'); ?> <?php echo esc_html($doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                                        </div>
                                        <div class="specialization">
                                            <?php echo esc_html($doctor['specialization']); ?>
                                        </div>
                                        <div class="department">
                                            <?php echo esc_html($doctor['department']); ?>
                                        </div>
                                    </div>
                                    <div class="availability-status">
                                        <span class="status-indicator available"></span>
                                        <span class="status-text"><?php _e('Available', 'sezer-ai-hospital'); ?></span>
                                    </div>
                                    <div class="doctor-actions">
                                        <button class="btn btn-sm btn-primary" onclick="scheduleWithDoctor(<?php echo $doctor['id']; ?>)">
                                            <i class="fas fa-calendar-plus"></i>
                                            <?php _e('Schedule', 'sezer-ai-hospital'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-user-md-times"></i>
                            <p><?php _e('No doctors available', 'sezer-ai-hospital'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Appointment Statistics -->
            <div class="dashboard-card appointment-stats">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> <?php _e('Appointment Statistics', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <canvas id="appointmentStatsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Patient Modal -->
<div id="registerPatientModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3><?php _e('Register New Patient', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('registerPatientModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="registerPatientForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('First Name', 'sezer-ai-hospital'); ?> *</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Last Name', 'sezer-ai-hospital'); ?> *</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Date of Birth', 'sezer-ai-hospital'); ?> *</label>
                        <input type="date" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Gender', 'sezer-ai-hospital'); ?> *</label>
                        <select name="gender" required>
                            <option value=""><?php _e('Select Gender', 'sezer-ai-hospital'); ?></option>
                            <option value="male"><?php _e('Male', 'sezer-ai-hospital'); ?></option>
                            <option value="female"><?php _e('Female', 'sezer-ai-hospital'); ?></option>
                            <option value="other"><?php _e('Other', 'sezer-ai-hospital'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Phone Number', 'sezer-ai-hospital'); ?> *</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Email', 'sezer-ai-hospital'); ?></label>
                        <input type="email" name="email">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Address', 'sezer-ai-hospital'); ?></label>
                    <textarea name="address" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Emergency Contact Name', 'sezer-ai-hospital'); ?></label>
                        <input type="text" name="emergency_contact_name">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Emergency Contact Phone', 'sezer-ai-hospital'); ?></label>
                        <input type="tel" name="emergency_contact_phone">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Medical History', 'sezer-ai-hospital'); ?></label>
                    <textarea name="medical_history" rows="3" placeholder="<?php _e('Any relevant medical history', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-group">
                    <label><?php _e('Allergies', 'sezer-ai-hospital'); ?></label>
                    <textarea name="allergies" rows="2" placeholder="<?php _e('Known allergies', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('registerPatientModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-success">
                        <?php _e('Register Patient', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Appointment Modal -->
<div id="scheduleAppointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Schedule New Appointment', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('scheduleAppointmentModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="scheduleAppointmentForm">
                <div class="form-group">
                    <label><?php _e('Patient', 'sezer-ai-hospital'); ?> *</label>
                    <select name="patient_id" required>
                        <option value=""><?php _e('Search and select patient', 'sezer-ai-hospital'); ?></option>
                        <!-- Patients will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('Doctor', 'sezer-ai-hospital'); ?> *</label>
                    <select name="doctor_id" required>
                        <option value=""><?php _e('Select doctor', 'sezer-ai-hospital'); ?></option>
                        <?php if ($available_doctors): ?>
                            <?php $available_doctors->execute(); ?>
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
                        <label><?php _e('Date', 'sezer-ai-hospital'); ?> *</label>
                        <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Time', 'sezer-ai-hospital'); ?> *</label>
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
                    <label><?php _e('Appointment Type', 'sezer-ai-hospital'); ?> *</label>
                    <select name="appointment_type" required>
                        <option value=""><?php _e('Select type', 'sezer-ai-hospital'); ?></option>
                        <option value="consultation"><?php _e('Consultation', 'sezer-ai-hospital'); ?></option>
                        <option value="follow_up"><?php _e('Follow-up', 'sezer-ai-hospital'); ?></option>
                        <option value="emergency"><?php _e('Emergency', 'sezer-ai-hospital'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('Notes', 'sezer-ai-hospital'); ?></label>
                    <textarea name="notes" rows="3" placeholder="<?php _e('Additional notes or reason for visit', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('scheduleAppointmentModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php _e('Schedule Appointment', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeAppointmentStatsChart();
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000); // Update every minute
    loadDashboardData();
});

// Chart initialization
function initializeAppointmentStatsChart() {
    const ctx = document.getElementById('appointmentStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['<?php _e('Scheduled', 'sezer-ai-hospital'); ?>', '<?php _e('Completed', 'sezer-ai-hospital'); ?>', '<?php _e('Cancelled', 'sezer-ai-hospital'); ?>', '<?php _e('No Show', 'sezer-ai-hospital'); ?>'],
            datasets: [{
                data: [<?php echo $stats['pending_appointments']; ?>, 45, 8, 3],
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12'],
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

// Time update
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    document.getElementById('currentTime').textContent = timeString;
}

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function checkInPatient(appointmentId) {
    if (confirm('<?php _e('Check in this patient?', 'sezer-ai-hospital'); ?>')) {
        // Update appointment status via AJAX
        alert('<?php _e('Patient check-in functionality will be implemented', 'sezer-ai-hospital'); ?>');
    }
}

function rescheduleAppointment(appointmentId) {
    alert('<?php _e('Reschedule functionality will be implemented', 'sezer-ai-hospital'); ?>');
}

function cancelAppointment(appointmentId) {
    if (confirm('<?php _e('Are you sure you want to cancel this appointment?', 'sezer-ai-hospital'); ?>')) {
        // Cancel appointment via AJAX
        alert('<?php _e('Appointment cancellation will be implemented', 'sezer-ai-hospital'); ?>');
    }
}

function viewPatientDetails(patientId) {
    alert('<?php _e('Patient details view will be implemented', 'sezer-ai-hospital'); ?>');
}

function scheduleForPatient(patientId) {
    openModal('scheduleAppointmentModal');
    // Pre-select patient
}

function scheduleWithDoctor(doctorId) {
    openModal('scheduleAppointmentModal');
    // Pre-select doctor
}

function generateDailyReport() {
    alert('<?php _e('Daily report generation will be implemented', 'sezer-ai-hospital'); ?>');
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
document.getElementById('registerPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle patient registration via AJAX
    alert('<?php _e('Patient registration will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('registerPatientModal');
});

document.getElementById('scheduleAppointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle appointment scheduling via AJAX
    alert('<?php _e('Appointment scheduling will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('scheduleAppointmentModal');
});
</script>

<?php get_footer(); ?>