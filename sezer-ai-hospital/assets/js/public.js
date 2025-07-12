/**
 * SEZER AI Hospital Public JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize appointment form
        $('.sezer-appointment-form form').on('submit', function(e) {
            e.preventDefault();
            alert('Appointment booking functionality will be implemented.');
        });
        
        // Add loading states and form validation
        $('.sezer-appointment-form input[type="date"]').attr('min', new Date().toISOString().split('T')[0]);
    });

})(jQuery);