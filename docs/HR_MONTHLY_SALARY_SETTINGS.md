# HR Monthly Salary Settings System

## Overview

The HR Monthly Salary Settings system provides a comprehensive solution for configuring monthly salary settings for employees. The system allows HR administrators to set up detailed salary configurations for any given month and year, including working days, holidays, attendance rules, overtime rules, and special adjustments.

## Features

### 1. Monthly Salary Configuration
- **Total Working Days**: Set the number of working days for each month
- **Official Holidays**: Configure the number of official holidays
- **Attendance Rules**: Define rules for different attendance types (full day, half day, leave, absence)
- **Overtime Rules**: Configure overtime calculation parameters
- **Notes**: Add special adjustments or notes for specific months

### 2. Yearly Management Interface
- **Year Selection**: Choose a year to manage all 12 months
- **Bulk Creation**: Create settings for all 12 months at once with default values
- **Individual Month Management**: Edit, create, or delete settings for individual months
- **Visual Overview**: Grid-based interface showing all months with their current status

### 3. AJAX-Powered Interface
- **Real-time Updates**: All operations use AJAX for smooth user experience
- **Loading Spinners**: Visual feedback during operations
- **Modal Dialogs**: Clean interface for editing individual months
- **SweetAlert Integration**: User-friendly notifications and confirmations

## Database Structure

### Table: `monthly_salary_settings`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `year` | year | Year for the setting |
| `month` | integer | Month (1-12) |
| `total_working_days` | integer | Number of working days in the month |
| `official_holidays` | integer | Number of official holidays |
| `attendance_rules` | json | Attendance rules configuration |
| `overtime_rules` | json | Overtime calculation rules |
| `notes` | text | Special notes or adjustments |
| `default_overtime_rate` | decimal(10,2) | Default overtime rate |
| `is_active` | boolean | Whether the setting is active |
| `user_id` | bigint | Foreign key to users table |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Attendance Rules Structure
```json
{
    "full_day": {
        "label": "Full Day",
        "value": 1.0,
        "description": "Complete working day"
    },
    "half_day": {
        "label": "Half Day", 
        "value": 0.5,
        "description": "Half working day"
    },
    "leave": {
        "label": "Leave",
        "value": 0.0,
        "description": "Authorized leave"
    },
    "absence": {
        "label": "Absence",
        "value": 0.0,
        "description": "Unauthorized absence"
    }
}
```

### Overtime Rules Structure
```json
{
    "enabled": true,
    "rate_multiplier": 1.5,
    "minimum_hours": 1,
    "maximum_hours_per_day": 4,
    "calculation_method": "hourly"
}
```

## API Endpoints

### Main Routes
- `GET /app/settings/salary-configuration` - List all settings
- `GET /app/settings/salary-configuration/create` - Create new setting form
- `POST /app/settings/salary-configuration` - Store new setting
- `GET /app/settings/salary-configuration/{id}` - View setting details
- `GET /app/settings/salary-configuration/{id}/edit` - Edit setting form
- `PUT /app/settings/salary-configuration/{id}` - Update setting
- `DELETE /app/settings/salary-configuration/{id}` - Delete setting

### Yearly Management Routes
- `GET /app/settings/salary-configuration-yearly/management` - Yearly management interface
- `GET /app/settings/salary-configuration-yearly/get-settings` - Get settings for a year (AJAX)
- `POST /app/settings/salary-configuration-yearly/update-monthly` - Update/create monthly setting (AJAX)
- `POST /app/settings/salary-configuration-yearly/create-yearly-ajax` - Create all 12 months (AJAX)
- `DELETE /app/settings/salary-configuration-yearly/delete-monthly` - Delete monthly setting (AJAX)

### Utility Routes
- `POST /app/settings/salary-configuration/calculate` - Calculate salary (AJAX)
- `PATCH /app/settings/salary-configuration/{id}/toggle-status` - Toggle active status

## Usage Guide

### 1. Creating Yearly Settings

1. Navigate to "Manage Yearly Settings"
2. Select a year from the dropdown
3. Click "Create All Months" to set up all 12 months with default values
4. Or configure individual months by clicking "Create" or "Edit" on each month card

### 2. Managing Individual Months

1. Select a year to view all months
2. Each month shows:
   - Current status (Active/Inactive/Not Set)
   - Working days and holidays
   - Overtime rate
   - Quick actions (Edit/Create/Delete)

### 3. Editing Month Settings

1. Click "Edit" or "Create" on a month card
2. Configure:
   - Working days (required)
   - Official holidays
   - Overtime rate
   - Active status
   - Notes and special adjustments
3. Save changes

### 4. Salary Calculation

The system includes a built-in salary calculator that uses the monthly settings to calculate:
- Daily rate (Basic Salary ÷ Working Days)
- Base salary (Daily Rate × Present Days)
- Overtime amount (if applicable)
- Total salary (Base + Overtime - Deductions)

## Key Features

### 1. Visual Month Overview
- Grid layout showing all 12 months
- Color-coded status indicators
- Quick access to edit/create/delete actions
- Real-time updates via AJAX

### 2. Smart Defaults
- Pre-configured attendance rules
- Standard overtime rules (1.5x multiplier)
- Reasonable default working days (22)
- Automatic month name and day calculations

### 3. Validation
- Working days cannot exceed total days in month
- Holiday count validation
- Year range validation (2020-2030)
- Required field validation

### 4. User Experience
- Loading spinners during operations
- Confirmation dialogs for destructive actions
- Real-time form validation
- Responsive design for all screen sizes

## Security Features

- User-specific settings (each user can only manage their own settings)
- CSRF protection on all forms
- Input validation and sanitization
- Authorization checks on all operations

## Technical Implementation

### Frontend
- Bootstrap 5 for responsive design
- jQuery for AJAX operations
- Select2 for enhanced dropdowns
- SweetAlert2 for notifications
- Real-time form validation

### Backend
- Laravel 10+ framework
- Eloquent ORM for database operations
- Form validation with custom rules
- JSON storage for complex configurations
- RESTful API design

### Database
- MySQL/MariaDB compatible
- Proper indexing for performance
- Foreign key constraints
- Unique constraints to prevent duplicates

## Future Enhancements

1. **Bulk Import/Export**: CSV import/export functionality
2. **Templates**: Save and reuse common configurations
3. **Audit Trail**: Track changes to settings
4. **Advanced Overtime Rules**: More complex overtime calculations
5. **Integration**: Connect with attendance and payroll systems
6. **Reporting**: Generate reports on salary settings and calculations

## Troubleshooting

### Common Issues

1. **Settings not saving**: Check if user has proper permissions
2. **AJAX errors**: Verify CSRF token and network connectivity
3. **Validation errors**: Ensure all required fields are filled
4. **Duplicate settings**: Check if settings already exist for the year/month

### Debug Mode

Enable Laravel debug mode to see detailed error messages:
```php
APP_DEBUG=true
```

## Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.
