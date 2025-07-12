<?php
/**
 * Nurse Dashboard Template
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and verify nurse role
$current_user = wp_get_current_user();
$user_roles = new SezerAIHospital_UserRoles();

if (!$user_roles->user_has_role($current_user->ID, 'hospital_nurse')) {
    wp_die(__('Access denied. You do not have permission to view this page.', 'sezer-ai-hospital'));
}

// Get database instance for data
$db = SezerAIHospital_Database::get_instance();

// Get nurse statistics
$stats = array(
    'patients_today' => $db->get_count('appointments', "DATE(appointment_date) = CURDATE() AND status IN ('scheduled', 'in_progress')"),
    'completed_today' => $db->get_count('appointments', "DATE(appointment_date) = CURDATE() AND status = 'completed'"),
    'pending_tasks' => 15, // This would come from a tasks table
    'total_patients' => $db->get_count('patients')
);

// Get today's patient schedule
$today_schedule = $db->query("
    SELECT a.*, p.first_name as patient_name, p.last_name as patient_surname, 
           p.phone, p.date_of_birth, d.first_name as doctor_name, d.last_name as doctor_surname
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    LEFT JOIN doctors d ON a.doctor_id = d.id 
    WHERE DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC
");

// Get recent patients
$recent_patients = $db->query("
    SELECT p.*, a.appointment_date, a.status as appointment_status
    FROM patients p 
    LEFT JOIN appointments a ON p.id = a.patient_id 
    WHERE a.appointment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY a.appointment_date DESC 
    LIMIT 10
");

get_header();
?>

<div class="sezer-dashboard nurse-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">
                    <i class="fas fa-user-nurse"></i>
                    <?php _e('Nurse Dashboard', 'sezer-ai-hospital'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php printf(__('Welcome, %s', 'sezer-ai-hospital'), $current_user->display_name); ?>
                </p>
                <div class="shift-info">
                    <span class="shift-badge"><?php _e('Day Shift', 'sezer-ai-hospital'); ?> - <?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openModal('addVitalSignsModal')">
                    <i class="fas fa-heartbeat"></i>
                    <?php _e('Record Vitals', 'sezer-ai-hospital'); ?>
                </button>
                <button class="btn btn-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    <?php _e('Refresh', 'sezer-ai-hospital'); ?>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card patients-today">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['patients_today']); ?></h3>
                    <p><?php _e('Patients Today', 'sezer-ai-hospital'); ?></p>
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

            <div class="stat-card pending-tasks">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['pending_tasks']); ?></h3>
                    <p><?php _e('Pending Tasks', 'sezer-ai-hospital'); ?></p>
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
            <!-- Today's Patient Schedule -->
            <div class="dashboard-card patient-schedule">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> <?php _e('Today\'s Patient Schedule', 'sezer-ai-hospital'); ?></h3>
                    <span class="schedule-date"><?php echo date('F j, Y'); ?></span>
                </div>
                <div class="card-content">
                    <?php if ($today_schedule && $today_schedule->rowCount() > 0): ?>
                        <div class="schedule-list">
                            <?php while ($appointment = $today_schedule->fetch()): ?>
                                <div class="schedule-item status-<?php echo esc_attr($appointment['status']); ?>">
                                    <div class="schedule-time">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="patient-info">
                                        <h4><?php echo esc_html($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?></h4>
                                        <p class="doctor-info">
                                            <?php _e('Dr.', 'sezer-ai-hospital'); ?> <?php echo esc_html($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?>
                                        </p>
                                        <p class="appointment-type"><?php echo esc_html(ucfirst($appointment['type'])); ?></p>
                                    </div>
                                    <div class="schedule-actions">
                                        <?php if ($appointment['status'] === 'scheduled'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="preparePatient(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-user-check"></i>
                                                <?php _e('Prepare', 'sezer-ai-hospital'); ?>
                                            </button>
                                            <button class="btn btn-sm btn-secondary" onclick="recordVitals(<?php echo $appointment['patient_id']; ?>)">
                                                <i class="fas fa-heartbeat"></i>
                                                <?php _e('Vitals', 'sezer-ai-hospital'); ?>
                                            </button>
                                        <?php elseif ($appointment['status'] === 'in_progress'): ?>
                                            <span class="status-badge status-in-progress">
                                                <i class="fas fa-clock"></i>
                                                <?php _e('In Progress', 'sezer-ai-hospital'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-completed">
                                                <i class="fas fa-check"></i>
                                                <?php _e('Completed', 'sezer-ai-hospital'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p><?php _e('No patients scheduled for today', 'sezer-ai-hospital'); ?></p>
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
                        <a href="#" class="action-btn" onclick="openModal('addVitalSignsModal')">
                            <i class="fas fa-heartbeat"></i>
                            <span><?php _e('Record Vital Signs', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('medicationModal')">
                            <i class="fas fa-pills"></i>
                            <span><?php _e('Medication Log', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('patientNotesModal')">
                            <i class="fas fa-sticky-note"></i>
                            <span><?php _e('Patient Notes', 'sezer-ai-hospital'); ?></span>
                        </a>
                        <a href="#" class="action-btn" onclick="openModal('inventoryModal')">
                            <i class="fas fa-boxes"></i>
                            <span><?php _e('Check Inventory', 'sezer-ai-hospital'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Patients -->
            <div class="dashboard-card recent-patients">
                <div class="card-header">
                    <h3><i class="fas fa-user-friends"></i> <?php _e('Recent Patients', 'sezer-ai-hospital'); ?></h3>
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
                                        <div class="patient-id">
                                            <?php _e('ID:', 'sezer-ai-hospital'); ?> <?php echo esc_html($patient['patient_id']); ?>
                                        </div>
                                        <div class="last-visit">
                                            <?php echo date('M j, Y', strtotime($patient['appointment_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="patient-actions">
                                        <button class="btn btn-sm btn-outline" onclick="viewPatientRecord(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline" onclick="recordVitals(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-heartbeat"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-user-times"></i>
                            <p><?php _e('No recent patients', 'sezer-ai-hospital'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tasks & Reminders -->
            <div class="dashboard-card tasks-reminders">
                <div class="card-header">
                    <h3><i class="fas fa-tasks"></i> <?php _e('Tasks & Reminders', 'sezer-ai-hospital'); ?></h3>
                    <button class="btn btn-sm btn-outline" onclick="addTask()">
                        <i class="fas fa-plus"></i>
                        <?php _e('Add Task', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
                <div class="card-content">
                    <div class="tasks-list">
                        <div class="task-item priority-high">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task1">
                                <label for="task1"></label>
                            </div>
                            <div class="task-content">
                                <h4><?php _e('Check medication inventory', 'sezer-ai-hospital'); ?></h4>
                                <p><?php _e('Due: 2:00 PM', 'sezer-ai-hospital'); ?></p>
                            </div>
                            <div class="task-priority">
                                <span class="priority-badge high"><?php _e('High', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                        <div class="task-item priority-medium">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task2">
                                <label for="task2"></label>
                            </div>
                            <div class="task-content">
                                <h4><?php _e('Update patient charts', 'sezer-ai-hospital'); ?></h4>
                                <p><?php _e('Due: 4:00 PM', 'sezer-ai-hospital'); ?></p>
                            </div>
                            <div class="task-priority">
                                <span class="priority-badge medium"><?php _e('Medium', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                        <div class="task-item priority-low">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task3">
                                <label for="task3"></label>
                            </div>
                            <div class="task-content">
                                <h4><?php _e('Prepare tomorrow\'s schedule', 'sezer-ai-hospital'); ?></h4>
                                <p><?php _e('Due: End of shift', 'sezer-ai-hospital'); ?></p>
                            </div>
                            <div class="task-priority">
                                <span class="priority-badge low"><?php _e('Low', 'sezer-ai-hospital'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vital Signs Chart -->
            <div class="dashboard-card vital-signs-chart">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> <?php _e('Patient Vital Signs Trends', 'sezer-ai-hospital'); ?></h3>
                </div>
                <div class="card-content">
                    <canvas id="vitalSignsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Vital Signs Modal -->
<div id="addVitalSignsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Record Vital Signs', 'sezer-ai-hospital'); ?></h3>
            <span class="close" onclick="closeModal('addVitalSignsModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="vitalSignsForm">
                <div class="form-group">
                    <label><?php _e('Select Patient', 'sezer-ai-hospital'); ?></label>
                    <select name="patient_id" required>
                        <option value=""><?php _e('Choose a patient', 'sezer-ai-hospital'); ?></option>
                        <!-- Patients will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Blood Pressure (Systolic)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="bp_systolic" placeholder="120" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Blood Pressure (Diastolic)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="bp_diastolic" placeholder="80" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Heart Rate (BPM)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="heart_rate" placeholder="72" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Temperature (°C)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" step="0.1" name="temperature" placeholder="36.5" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('Respiratory Rate', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="respiratory_rate" placeholder="16">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Oxygen Saturation (%)', 'sezer-ai-hospital'); ?></label>
                        <input type="number" name="oxygen_saturation" placeholder="98">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('Notes', 'sezer-ai-hospital'); ?></label>
                    <textarea name="notes" rows="3" placeholder="<?php _e('Any additional observations', 'sezer-ai-hospital'); ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addVitalSignsModal')">
                        <?php _e('Cancel', 'sezer-ai-hospital'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php _e('Record Vitals', 'sezer-ai-hospital'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeVitalSignsChart();
    loadDashboardData();
});

// Chart initialization
function initializeVitalSignsChart() {
    const ctx = document.getElementById('vitalSignsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM'],
            datasets: [{
                label: '<?php _e('Average Blood Pressure (Systolic)', 'sezer-ai-hospital'); ?>',
                data: [120, 118, 122, 119, 121, 120],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4
            }, {
                label: '<?php _e('Average Heart Rate', 'sezer-ai-hospital'); ?>',
                data: [72, 75, 78, 74, 76, 73],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function preparePatient(appointmentId) {
    alert('<?php _e('Patient preparation checklist will be implemented', 'sezer-ai-hospital'); ?>');
}

function recordVitals(patientId) {
    openModal('addVitalSignsModal');
    // Pre-select patient if provided
    if (patientId) {
        // Set patient selection
    }
}

function viewPatientRecord(patientId) {
    alert('<?php _e('Patient record view will be implemented', 'sezer-ai-hospital'); ?>');
}

function addTask() {
    alert('<?php _e('Task management feature will be implemented', 'sezer-ai-hospital'); ?>');
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
document.getElementById('vitalSignsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Handle vital signs recording via AJAX
    alert('<?php _e('Vital signs recording will be implemented with AJAX', 'sezer-ai-hospital'); ?>');
    closeModal('addVitalSignsModal');
});

// Task management
document.querySelectorAll('.task-checkbox input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const taskItem = this.closest('.task-item');
        if (this.checked) {
            taskItem.classList.add('completed');
        } else {
            taskItem.classList.remove('completed');
        }
    });
});
</script>

<?php get_footer(); ?>