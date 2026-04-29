# Vehicle Form Frontend Validation

## Overview
Comprehensive frontend validation has been implemented for the vehicle module using the **FormValidation** library (https://formvalidation.io/). This provides real-time validation feedback to users before form submission.

## Features

### 1. **Required Field Validation**
All mandatory fields are validated to ensure they're not empty:
- Vehicle Type
- Vehicle Sub-Type
- Model Name
- Brand
- Year of Manufacture
- Color
- Chassis Number
- Engine Number
- Registration Number
- Fuel Type
- Transmission Type
- Status

### 2. **String Length Validation**
Fields with character limits are validated:
- **Model Name**: 2-255 characters
- **Chassis Number**: 10-50 characters (alphanumeric only)
- **Engine Number**: max 255 characters
- **Registration Number**: 5-20 characters

### 3. **Numeric Validation**
Number fields are validated for proper numeric values and ranges:
- **Engine Capacity**: Must be a positive number
- **Seating Capacity**: Must be a positive integer (≥ 1)
- **Gross Weight**: Must be positive
- **Vehicle Dimensions** (length, height, width): Must be positive
- **Current Mileage**: Must be non-negative
- **Fixed Price**: Must be greater than 0 (when visible)
- **Rate Per KM**: Must be greater than 0 (when visible)

### 4. **Date Validation**
Date fields have special validation rules:
- **Registration Expiry**: Must be after Registration Date
- **Insurance Expiry**: Should be in the future
- All date fields use the Flatpickr date picker for consistent input

### 5. **File Upload Validation**
File uploads are validated for:
- **Vehicle Photo**:
  - Allowed formats: JPEG, JPG, PNG, GIF
  - Maximum size: 2MB
  - Shows preview after selection
  
- **Documents** (Registration, Insurance, Fitness Certificate):
  - Allowed formats: PDF, JPEG, JPG, PNG
  - Maximum size: 5MB each

### 6. **Conditional Validation**
The validation adapts based on the Vehicle Sub-Type selection:

- **Own**: No pricing fields required
- **Hired (Fixed Price)**: Fixed Price field becomes required
- **BRTC Rate/Per Kilometer**: Rate Per KM field becomes required

This is handled dynamically - when the sub-type changes, the validation rules automatically adjust.

### 7. **Real-time Validation**
- Fields are validated as the user types or changes values
- Invalid fields are highlighted with red borders
- Error messages appear below each field
- Valid fields get a green checkmark (optional, can be enabled)

### 8. **Format Validation**
- **Chassis Number**: Must contain only letters and numbers
- **Registration Number**: Basic format checking

## User Experience Enhancements

### Visual Feedback
- **Invalid Fields**: Red border with error message below
- **Submit Button**: Disabled with spinner during form submission
- **Success/Error Notifications**: Toast notifications using Toastr
- **File Previews**: Image thumbnails shown for uploaded photos

### Auto-Focus
The first invalid field automatically receives focus when validation fails, helping users quickly identify and fix errors.

### AJAX Form Submission
Forms submit via AJAX without page reload:
- Smooth user experience
- Instant feedback
- Automatic redirect to vehicle list on success
- Error messages displayed in-place for failed submissions

## Technical Implementation

### Files Modified/Created
1. **resources/views/content/vehicles/create.blade.php**
   - Added FormValidation library dependencies
   - Included validation script

2. **resources/views/content/vehicles/edit.blade.php**
   - Added FormValidation library dependencies
   - Included validation script

3. **assets/js/vehicle-form-validation.js** (NEW)
   - Complete validation logic
   - Field-specific validation rules
   - AJAX submission handling
   - Dynamic pricing field validation

### Dependencies
- FormValidation library (already included in the project)
- Select2 for enhanced dropdowns
- Flatpickr for date picking
- Toastr for notifications
- jQuery for DOM manipulation

## Validation Rules Reference

### Basic Information
| Field | Rules | Error Message |
|-------|-------|---------------|
| Vehicle Type | Required | Please select a vehicle type |
| Vehicle Sub-Type | Required | Please select a vehicle sub-type |
| Model Name | Required, 2-255 chars | Model name must be between 2 and 255 characters |
| Brand | Required | Please select a brand |
| Year | Required | Please select the year of manufacture |
| Color | Required | Please select a color |

### Technical Details
| Field | Rules | Error Message |
|-------|-------|---------------|
| Chassis Number | Required, 10-50 chars, alphanumeric | Various messages based on violation |
| Engine Number | Required, max 255 chars | Please enter the engine number |
| Registration Number | Required, 5-20 chars | Registration number must be between 5 and 20 characters |

### Pricing (Conditional)
| Field | Condition | Rules |
|-------|-----------|-------|
| Fixed Price | Sub-type = "Hired (Fixed Price)" | Required, numeric, > 0 |
| Rate Per KM | Sub-type = "BRTC Rate/Per Kilometer" | Required, numeric, > 0 |

### File Uploads
| Field | Max Size | Formats |
|-------|----------|---------|
| Vehicle Photo | 2MB | JPEG, JPG, PNG, GIF |
| Registration Document | 5MB | PDF, JPEG, JPG, PNG |
| Insurance Document | 5MB | PDF, JPEG, JPG, PNG |
| Fitness Certificate | 5MB | PDF, JPEG, JPG, PNG |

## Testing

### Test Cases
1. ✅ Try submitting empty form - should show all required field errors
2. ✅ Enter invalid chassis number (too short/special chars) - should show format error
3. ✅ Upload oversized file - should show file size error
4. ✅ Upload wrong file format - should show format error
5. ✅ Set registration expiry before registration date - should show date error
6. ✅ Enter negative mileage - should show value error
7. ✅ Change sub-type and check pricing field validation
8. ✅ Submit valid form - should submit successfully and redirect

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Future Enhancements
- Server-side duplicate check for chassis/registration numbers (AJAX)
- VIN decoder integration
- Advanced pattern matching for registration numbers by country
- Multi-language error messages
- Custom validation messages per organization

## Support
For issues or questions about the validation system, please contact the development team.

