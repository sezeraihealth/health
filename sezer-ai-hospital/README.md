# SEZER AI Hospital - WordPress Plugin

An advanced healthcare management system for WordPress with role-based dashboards, PostgreSQL integration, multi-language support, video consultations, and comprehensive health data tracking.

## Features

### 🏥 **Core Healthcare Management**
- **Role-based User System**: Doctor, Patient, Nurse, Receptionist, and Hospital Admin roles
- **Appointment Management**: Complete scheduling and management system
- **Medical Records**: Comprehensive patient health records with PostgreSQL storage
- **Health Data Tracking**: Personal vital signs and health metrics tracking

### 🎥 **Video Consultations**
- **Real-time Video Calls**: WebRTC-based video consultation system
- **Room Management**: Secure consultation rooms with unique IDs
- **Chat Integration**: In-consultation messaging system
- **Recording Support**: Optional consultation recording capabilities

### 🗃️ **Database Integration**
- **PostgreSQL Support**: Advanced database with full CRUD operations
- **Data Security**: Encrypted sensitive information storage
- **Backup System**: Automated data backup and export functionality
- **API Integration**: RESTful API for external system integration

### 🌍 **Multi-language Support**
- **Internationalization**: Full i18n support with translation files
- **RTL Support**: Right-to-left language compatibility
- **Custom Translations**: Easy translation management system

### 📊 **Analytics & Reporting**
- **Health Analytics**: AI-powered health insights and trends
- **Dashboard Analytics**: Real-time statistics and metrics
- **Export Capabilities**: CSV, JSON, and PDF export options
- **Custom Reports**: Flexible reporting system

## Installation

1. **Download the Plugin**
   ```bash
   git clone https://github.com/sezeraihealth/health.git
   cd health/sezer-ai-hospital
   ```

2. **Upload to WordPress**
   - Upload the `sezer-ai-hospital` folder to `/wp-content/plugins/`
   - Or install via WordPress admin panel

3. **Configure Environment**
   - Copy `.env.example` to `.env` and update with your database credentials
   - Verify PostgreSQL connection settings

4. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Activate "SEZER AI Hospital"

## Configuration

### Database Setup

The plugin uses PostgreSQL for advanced healthcare data management. Configure your database connection in the `.env` file:

```env
DB_HOST=your_postgresql_host
DB_PORT=5432
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password
API_URL=http://your-api-server.com/api
API_KEY=your_api_key_here
```

### User Roles

The plugin creates the following user roles:

- **Hospital Doctor**: Manage patients, appointments, medical records
- **Hospital Patient**: View own records, book appointments, track health
- **Hospital Nurse**: Assist doctors, manage vital signs, patient care
- **Hospital Receptionist**: Manage appointments, patient registration
- **Hospital Admin**: Full system administration and reporting

### Dashboard Access

After activation, users can access role-specific dashboards:

- **Doctors**: `/doctor-dashboard/`
- **Patients**: `/patient-dashboard/`
- **Nurses**: `/nurse-dashboard/`
- **Receptionists**: `/receptionist-dashboard/`
- **Admins**: `/admin-dashboard/`

## Usage

### For Patients

1. **Registration**: Register as a patient through the registration form
2. **Dashboard Access**: Access your personal dashboard after login
3. **Book Appointments**: Schedule appointments with available doctors
4. **Health Tracking**: Record and monitor vital signs and health metrics
5. **Video Consultations**: Join video calls with healthcare providers

### For Healthcare Providers

1. **Patient Management**: View and manage patient records
2. **Appointment Scheduling**: Manage appointment calendars
3. **Medical Records**: Create and update patient medical histories
4. **Video Consultations**: Conduct remote consultations
5. **Health Analytics**: Review patient health trends and insights

### For Administrators

1. **System Management**: Configure hospital settings and preferences
2. **User Management**: Manage staff and patient accounts
3. **Reporting**: Generate comprehensive system reports
4. **Data Export**: Export data for external analysis
5. **API Management**: Configure external system integrations

## Shortcodes

### Appointment Booking Form
```php
[sezer_appointment_form]
[sezer_appointment_form doctor_id="123" show_doctor_selection="false"]
```

### Doctor Listing
```php
[sezer_doctor_list]
[sezer_doctor_list specialization="cardiology" limit="5"]
```

### Patient Portal
```php
[sezer_patient_portal]
```

## API Endpoints

The plugin provides RESTful API endpoints for external integrations:

- `GET /wp-json/sezer-ai-hospital/v1/patients` - List patients
- `GET /wp-json/sezer-ai-hospital/v1/patients/{id}` - Get patient details
- `GET /wp-json/sezer-ai-hospital/v1/appointments` - List appointments
- `POST /wp-json/sezer-ai-hospital/v1/appointments` - Create appointment
- `POST /wp-json/sezer-ai-hospital/v1/vital-signs` - Save vital signs

### Authentication

API requests require authentication via API key:

```bash
curl -H "X-API-Key: your-api-key" \
     https://yoursite.com/wp-json/sezer-ai-hospital/v1/patients
```

## Database Schema

### Core Tables

- **patients**: Patient information and medical history
- **doctors**: Healthcare provider profiles and specializations
- **appointments**: Appointment scheduling and management
- **video_consultations**: Video consultation sessions
- **health_records**: Comprehensive health data records
- **vital_signs**: Patient vital signs and measurements

## Security Features

- **Data Encryption**: Sensitive data encrypted at rest
- **Access Control**: Role-based permissions system
- **API Security**: Secure API key authentication
- **Audit Logging**: Comprehensive activity logging
- **HIPAA Compliance**: Healthcare data protection standards

## Development

### Requirements

- WordPress 5.0+
- PHP 7.4+
- PostgreSQL 12+
- Modern web browser with WebRTC support

### File Structure

```
sezer-ai-hospital/
├── sezer-ai-hospital.php     # Main plugin file
├── .env                      # Environment configuration
├── includes/                 # Core functionality
├── admin/                    # Admin interface
├── public/                   # Public interface
├── assets/                   # CSS, JS, and media files
└── languages/               # Translation files
```

## Support

For support and documentation:

- **Website**: https://sezeraihealth.com
- **Documentation**: https://docs.sezeraihealth.com
- **Support**: support@sezeraihealth.com
- **GitHub**: https://github.com/sezeraihealth/health

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Core healthcare management system
- PostgreSQL database integration
- Role-based user system
- Video consultation functionality
- Health data tracking
- Multi-language support
- RESTful API
- Admin dashboard
- Patient portal

---

**SEZER AI Hospital** - Transforming healthcare management with advanced technology and AI-powered insights.