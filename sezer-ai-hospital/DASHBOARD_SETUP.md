# SEZER AI Hospital - Dashboard Setup Guide

## 🎯 **Dashboard Overview**

The SEZER AI Hospital plugin now includes **complete, functional dashboards** for all user roles:

- **Admin Dashboard** (`/admin-dashboard/`)
- **Doctor Dashboard** (`/doctor-dashboard/`)
- **Patient Dashboard** (`/patient-dashboard/`)
- **Nurse Dashboard** (`/nurse-dashboard/`)
- **Receptionist Dashboard** (`/receptionist-dashboard/`)

## 🚀 **Installation & Activation**

### 1. **Plugin Installation**
```bash
# Upload the plugin to your WordPress plugins directory
wp-content/plugins/sezer-ai-hospital/

# Or install via WordPress admin:
# Plugins > Add New > Upload Plugin > sezer-ai-hospital.zip
```

### 2. **Activate the Plugin**
```bash
# Via WP-CLI
wp plugin activate sezer-ai-hospital

# Or via WordPress admin:
# Plugins > Installed Plugins > SEZER AI Hospital > Activate
```

### 3. **Flush Rewrite Rules**
After activation, visit:
- **WordPress Admin** > **Settings** > **Permalinks**
- Click **"Save Changes"** (this flushes rewrite rules)

## 🔧 **Configuration**

### 1. **Database Connection**
The plugin uses the `.env` file for database configuration:

```env
DB_HOST=31.210.36.231
DB_PORT=5432
DB_NAME=aihospital_db
DB_USER=aihospital_user
DB_PASSWORD=Atam3466!
API_URL=http://31.210.36.231:5000/api
API_KEY=your_api_key_here
```

### 2. **User Role Assignment**
Assign users to hospital roles:

```php
// Example: Assign doctor role to user
$user_roles = new SezerAIHospital_UserRoles();
$user_roles->assign_role($user_id, 'hospital_doctor');

// Available roles:
// - hospital_admin
// - hospital_doctor
// - hospital_patient
// - hospital_nurse
// - hospital_receptionist
```

## 📱 **Dashboard Features**

### **Admin Dashboard** (`/admin-dashboard/`)
- **Statistics Overview**: Total patients, doctors, appointments
- **Quick Actions**: Add patients, doctors, schedule appointments
- **Recent Appointments**: Real-time appointment list
- **System Status**: Database and API connectivity
- **Appointment Charts**: Visual statistics

### **Doctor Dashboard** (`/doctor-dashboard/`)
- **Today's Schedule**: Patient appointments with details
- **Quick Actions**: Record prescriptions, search patients
- **Upcoming Appointments**: Next 7 days schedule
- **Profile Management**: Doctor information and settings
- **Video Consultation**: Integrated video calling

### **Patient Dashboard** (`/patient-dashboard/`)
- **Next Appointment Alert**: Upcoming appointment details
- **Health Tracking**: Vital signs and health metrics
- **Appointment History**: Past and upcoming appointments
- **Quick Actions**: Book appointments, view records
- **Health Charts**: Personal health data visualization

### **Nurse Dashboard** (`/nurse-dashboard/`)
- **Patient Schedule**: Today's patient list
- **Vital Signs Recording**: Quick vital signs entry
- **Tasks & Reminders**: Daily task management
- **Recent Patients**: Patient interaction history
- **Vital Signs Charts**: Patient trends

### **Receptionist Dashboard** (`/receptionist-dashboard/`)
- **Appointment Management**: Schedule, reschedule, cancel
- **Patient Registration**: New patient enrollment
- **Doctor Availability**: Real-time doctor status
- **Daily Statistics**: Appointment and patient counts
- **Quick Actions**: Search patients, generate reports

## 🎨 **UI Components**

### **Modern Design Features**
- **Responsive Layout**: Works on desktop, tablet, mobile
- **Interactive Charts**: Chart.js integration for data visualization
- **Modal Windows**: Clean popup forms for data entry
- **Real-time Updates**: Live data refresh every 30 seconds
- **Font Awesome Icons**: Professional iconography
- **Gradient Backgrounds**: Modern visual design

### **Accessibility Features**
- **Keyboard Navigation**: Full keyboard support
- **Screen Reader Compatible**: ARIA labels and semantic HTML
- **High Contrast**: Clear visual hierarchy
- **Reduced Motion**: Respects user preferences

## 🔗 **Dashboard URLs**

After plugin activation, these URLs become available:

```
https://yoursite.com/admin-dashboard/
https://yoursite.com/doctor-dashboard/
https://yoursite.com/patient-dashboard/
https://yoursite.com/nurse-dashboard/
https://yoursite.com/receptionist-dashboard/
```

## 🛡️ **Security & Access Control**

### **Role-Based Access**
- Each dashboard checks user roles before displaying content
- Unauthorized users are redirected to login
- Role verification happens on every page load

### **AJAX Security**
- All AJAX requests use WordPress nonces
- User capabilities verified server-side
- SQL injection protection via prepared statements

## 📊 **Database Integration**

### **Real Data Display**
- **Patient Records**: Live patient data from PostgreSQL
- **Appointment Data**: Real appointment scheduling
- **Doctor Information**: Actual doctor profiles
- **Statistics**: Real-time counts and metrics

### **CRUD Operations**
- **Create**: Add patients, doctors, appointments
- **Read**: Display data in tables and charts
- **Update**: Modify existing records
- **Delete**: Remove records with confirmation

## 🔧 **Customization**

### **CSS Customization**
```css
/* Override dashboard styles */
.sezer-dashboard .dashboard-header {
    background: your-custom-gradient;
}

.stat-card.custom {
    border-left-color: #your-color;
}
```

### **JavaScript Extensions**
```javascript
// Extend dashboard functionality
SezerDashboard.customFunction = function() {
    // Your custom code
};
```

## 🚨 **Troubleshooting**

### **Dashboard Not Loading**
1. **Check Permalinks**: Go to Settings > Permalinks > Save Changes
2. **Verify Plugin Activation**: Ensure plugin is active
3. **Check User Roles**: Verify user has correct hospital role
4. **Database Connection**: Check `.env` file configuration

### **Missing Styles/Scripts**
1. **Clear Cache**: Clear any caching plugins
2. **Check File Permissions**: Ensure assets folder is readable
3. **CDN Issues**: Check Font Awesome and Chart.js loading

### **Database Errors**
1. **Connection Test**: Run the database connection test
2. **Check Credentials**: Verify `.env` file values
3. **Network Issues**: Test database host connectivity

## 📞 **Support**

### **Error Logging**
Check WordPress error logs for detailed error messages:
```bash
# WordPress debug log
wp-content/debug.log

# Server error log
/var/log/apache2/error.log
```

### **Debug Mode**
Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 🎉 **Success Confirmation**

After successful setup, you should see:

✅ **Dashboard URLs accessible**  
✅ **Modern UI with charts and statistics**  
✅ **Role-based content display**  
✅ **Interactive forms and modals**  
✅ **Real-time data from PostgreSQL**  
✅ **Responsive design on all devices**  

## 📈 **Next Steps**

1. **Assign User Roles**: Set up users with appropriate hospital roles
2. **Add Sample Data**: Create test patients and doctors
3. **Test Functionality**: Try booking appointments and recording data
4. **Customize Styling**: Adjust colors and branding to match your site
5. **Configure API**: Set up external API integrations

---

**The SEZER AI Hospital dashboards are now fully functional and ready for production use!** 🏥✨