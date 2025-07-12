/**
 * SEZER AI Hospital - Dashboard JavaScript
 * Interactive functionality for all dashboard types
 */

(function($) {
    'use strict';

    // Dashboard namespace
    window.SezerDashboard = {
        init: function() {
            this.bindEvents();
            this.initializeCharts();
            this.startRealTimeUpdates();
            this.initializeNotifications();
        },

        bindEvents: function() {
            // Modal events
            $(document).on('click', '[data-modal]', this.openModal);
            $(document).on('click', '.modal .close', this.closeModal);
            $(document).on('click', '.modal', function(e) {
                if (e.target === this) {
                    SezerDashboard.closeModal.call(this, e);
                }
            });

            // Form submissions
            $(document).on('submit', '#addPatientForm', this.handlePatientForm);
            $(document).on('submit', '#bookAppointmentForm', this.handleAppointmentForm);
            $(document).on('submit', '#vitalSignsForm', this.handleVitalSignsForm);
            $(document).on('submit', '#healthDataForm', this.handleHealthDataForm);
            $(document).on('submit', '#registerPatientForm', this.handlePatientRegistration);
            $(document).on('submit', '#scheduleAppointmentForm', this.handleAppointmentScheduling);

            // Dashboard actions
            $(document).on('click', '[data-action]', this.handleAction);

            // Search functionality
            $(document).on('input', '[data-search]', this.handleSearch);

            // Keyboard shortcuts
            $(document).on('keydown', this.handleKeyboardShortcuts);

            // Auto-save forms
            $(document).on('input', '[data-autosave]', this.autoSaveForm);
        },

        openModal: function(e) {
            e.preventDefault();
            const modalId = $(this).data('modal') || $(this).attr('onclick').match(/openModal\('([^']+)'\)/)?.[1];
            if (modalId) {
                $('#' + modalId).fadeIn(300);
                $('body').addClass('modal-open');
            }
        },

        closeModal: function(e) {
            e.preventDefault();
            $(this).closest('.modal').fadeOut(300);
            $('body').removeClass('modal-open');
        },

        handlePatientForm: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Adding patient...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_add_patient',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Patient added successfully!', 'success');
                        $('#addPatientModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error adding patient', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handleAppointmentForm: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Booking appointment...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_book_appointment',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Appointment booked successfully!', 'success');
                        $('#bookAppointmentModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error booking appointment', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handleVitalSignsForm: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Recording vital signs...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_record_vitals',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Vital signs recorded successfully!', 'success');
                        $('#addVitalSignsModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error recording vital signs', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handleHealthDataForm: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Saving health data...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_save_health_data',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Health data saved successfully!', 'success');
                        $('#healthDataModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error saving health data', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handlePatientRegistration: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Registering patient...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_register_patient',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Patient registered successfully!', 'success');
                        $('#registerPatientModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error registering patient', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handleAppointmentScheduling: function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            SezerDashboard.showLoading('Scheduling appointment...');
            
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_schedule_appointment',
                    nonce: sezer_ajax.nonce,
                    ...Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.showNotification('Appointment scheduled successfully!', 'success');
                        $('#scheduleAppointmentModal').fadeOut(300);
                        SezerDashboard.refreshDashboard();
                    } else {
                        SezerDashboard.showNotification(response.data.message || 'Error scheduling appointment', 'error');
                    }
                },
                error: function() {
                    SezerDashboard.showNotification('Network error. Please try again.', 'error');
                },
                complete: function() {
                    SezerDashboard.hideLoading();
                }
            });
        },

        handleAction: function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            const id = $(this).data('id');
            
            switch(action) {
                case 'check-in':
                    SezerDashboard.checkInPatient(id);
                    break;
                case 'start-consultation':
                    SezerDashboard.startConsultation(id);
                    break;
                case 'reschedule':
                    SezerDashboard.rescheduleAppointment(id);
                    break;
                case 'cancel':
                    SezerDashboard.cancelAppointment(id);
                    break;
                case 'view-patient':
                    SezerDashboard.viewPatientRecord(id);
                    break;
                case 'record-vitals':
                    SezerDashboard.recordVitals(id);
                    break;
                case 'start-video':
                    SezerDashboard.startVideoCall(id);
                    break;
                case 'refresh':
                    SezerDashboard.refreshDashboard();
                    break;
            }
        },

        handleSearch: function(e) {
            const query = $(this).val().toLowerCase();
            const target = $(this).data('search');
            
            $(target + ' .searchable').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        },

        handleKeyboardShortcuts: function(e) {
            // Ctrl/Cmd + shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'n':
                        e.preventDefault();
                        $('#addPatientModal').fadeIn(300);
                        break;
                    case 'a':
                        e.preventDefault();
                        $('#bookAppointmentModal').fadeIn(300);
                        break;
                    case 'r':
                        e.preventDefault();
                        SezerDashboard.refreshDashboard();
                        break;
                }
            }
            
            // Escape key
            if (e.key === 'Escape') {
                $('.modal:visible').fadeOut(300);
                $('body').removeClass('modal-open');
            }
        },

        autoSaveForm: function() {
            const form = $(this).closest('form');
            const formId = form.attr('id');
            
            if (formId) {
                const formData = form.serialize();
                localStorage.setItem('sezer_autosave_' + formId, formData);
            }
        },

        initializeCharts: function() {
            // Initialize Chart.js charts if available
            if (typeof Chart !== 'undefined') {
                this.initAppointmentChart();
                this.initHealthChart();
                this.initVitalSignsChart();
                this.initAppointmentStatsChart();
            }
        },

        initAppointmentChart: function() {
            const ctx = document.getElementById('appointmentChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Scheduled', 'Completed', 'Cancelled'],
                        datasets: [{
                            data: [30, 45, 8],
                            backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        },

        initHealthChart: function() {
            const ctx = document.getElementById('healthChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Blood Pressure (Systolic)',
                            data: [120, 118, 122, 119, 121, 120],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Weight (kg)',
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
        },

        initVitalSignsChart: function() {
            const ctx = document.getElementById('vitalSignsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM'],
                        datasets: [{
                            label: 'Average Blood Pressure (Systolic)',
                            data: [120, 118, 122, 119, 121, 120],
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Average Heart Rate',
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
        },

        initAppointmentStatsChart: function() {
            const ctx = document.getElementById('appointmentStatsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Scheduled', 'Completed', 'Cancelled', 'No Show'],
                        datasets: [{
                            data: [25, 45, 8, 3],
                            backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        },

        startRealTimeUpdates: function() {
            // Update dashboard data every 30 seconds
            setInterval(function() {
                SezerDashboard.updateDashboardData();
            }, 30000);

            // Update time displays every minute
            setInterval(function() {
                SezerDashboard.updateTimeDisplays();
            }, 60000);
        },

        updateDashboardData: function() {
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_get_dashboard_data',
                    nonce: sezer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SezerDashboard.updateStatistics(response.data.stats);
                        SezerDashboard.updateAppointments(response.data.appointments);
                    }
                }
            });
        },

        updateTimeDisplays: function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            $('#currentTime').text(timeString);
        },

        updateStatistics: function(stats) {
            Object.keys(stats).forEach(function(key) {
                $('.stat-card.' + key + ' .stat-content h3').text(stats[key]);
            });
        },

        updateAppointments: function(appointments) {
            // Update appointment lists with new data
            // This would be implemented based on specific dashboard needs
        },

        initializeNotifications: function() {
            // Check for notifications every 60 seconds
            setInterval(function() {
                SezerDashboard.checkNotifications();
            }, 60000);
        },

        checkNotifications: function() {
            $.ajax({
                url: sezer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sezer_check_notifications',
                    nonce: sezer_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.notifications) {
                        response.data.notifications.forEach(function(notification) {
                            SezerDashboard.showNotification(notification.message, notification.type);
                        });
                    }
                }
            });
        },

        checkInPatient: function(appointmentId) {
            if (confirm('Check in this patient?')) {
                $.ajax({
                    url: sezer_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sezer_checkin_patient',
                        appointment_id: appointmentId,
                        nonce: sezer_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            SezerDashboard.showNotification('Patient checked in successfully!', 'success');
                            SezerDashboard.refreshDashboard();
                        } else {
                            SezerDashboard.showNotification(response.data.message || 'Error checking in patient', 'error');
                        }
                    }
                });
            }
        },

        startConsultation: function(appointmentId) {
            if (confirm('Start consultation for this appointment?')) {
                $.ajax({
                    url: sezer_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sezer_start_consultation',
                        appointment_id: appointmentId,
                        nonce: sezer_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            SezerDashboard.showNotification('Consultation started!', 'success');
                            SezerDashboard.refreshDashboard();
                        } else {
                            SezerDashboard.showNotification(response.data.message || 'Error starting consultation', 'error');
                        }
                    }
                });
            }
        },

        rescheduleAppointment: function(appointmentId) {
            // This would open a reschedule modal
            SezerDashboard.showNotification('Reschedule functionality will be implemented', 'info');
        },

        cancelAppointment: function(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                $.ajax({
                    url: sezer_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sezer_cancel_appointment',
                        appointment_id: appointmentId,
                        nonce: sezer_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            SezerDashboard.showNotification('Appointment cancelled successfully!', 'success');
                            SezerDashboard.refreshDashboard();
                        } else {
                            SezerDashboard.showNotification(response.data.message || 'Error cancelling appointment', 'error');
                        }
                    }
                });
            }
        },

        viewPatientRecord: function(patientId) {
            // This would open a patient record modal
            SezerDashboard.showNotification('Patient record view will be implemented', 'info');
        },

        recordVitals: function(patientId) {
            $('#addVitalSignsModal').fadeIn(300);
            // Pre-select patient if provided
            if (patientId) {
                $('#addVitalSignsModal select[name="patient_id"]').val(patientId);
            }
        },

        startVideoCall: function(appointmentId) {
            $('#videoConsultationModal').fadeIn(300);
            // Initialize video call functionality
            SezerDashboard.initializeVideoCall(appointmentId);
        },

        initializeVideoCall: function(appointmentId) {
            // WebRTC video call initialization would go here
            console.log('Initializing video call for appointment:', appointmentId);
        },

        refreshDashboard: function() {
            location.reload();
        },

        showLoading: function(message) {
            if (!$('#sezer-loading').length) {
                $('body').append(`
                    <div id="sezer-loading" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        color: white;
                        font-size: 1.2rem;
                    ">
                        <div style="text-align: center;">
                            <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                            <div>${message || 'Loading...'}</div>
                        </div>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `);
            }
        },

        hideLoading: function() {
            $('#sezer-loading').remove();
        },

        showNotification: function(message, type) {
            type = type || 'info';
            const colors = {
                success: '#27ae60',
                error: '#e74c3c',
                warning: '#f39c12',
                info: '#3498db'
            };

            const notification = $(`
                <div class="sezer-notification" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${colors[type]};
                    color: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    z-index: 10000;
                    max-width: 300px;
                    animation: slideInRight 0.3s ease;
                ">
                    ${message}
                </div>
                <style>
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                </style>
            `);

            $('body').append(notification);

            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize dashboard when document is ready
    $(document).ready(function() {
        SezerDashboard.init();
    });

    // Global functions for backward compatibility
    window.openModal = function(modalId) {
        $('#' + modalId).fadeIn(300);
        $('body').addClass('modal-open');
    };

    window.closeModal = function(modalId) {
        $('#' + modalId).fadeOut(300);
        $('body').removeClass('modal-open');
    };

    window.refreshDashboard = function() {
        SezerDashboard.refreshDashboard();
    };

})(jQuery);