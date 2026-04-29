# Vehicle AJAX System Documentation

## Overview

The Vehicle AJAX System provides seamless form submission for vehicle add/edit operations with comprehensive user experience enhancements including spinner animations, form state management, and real-time validation.

## Features

### ✨ Core AJAX Features
- **Seamless Form Submission**: Submit forms without page reload
- **Spinner Animation**: Visual feedback during submission
- **Form State Management**: Disable/enable form during operations
- **Real-time Validation**: Client-side and server-side validation
- **File Upload Support**: Handle file uploads with preview
- **Success/Error Handling**: User-friendly feedback messages
- **Auto-redirect**: Automatic navigation after successful operations

### 🎨 User Experience Features
- **Loading States**: Visual indicators during processing
- **Form Validation**: Real-time field validation
- **File Previews**: Image and document previews
- **Error Display**: Clear error messages and field highlighting
- **Success Feedback**: Confirmation messages and animations
- **Form Reset**: Automatic form clearing after successful submission

## Files Structure

```
assets/
├── js/
│   └── vehicle-form-ajax.js      # Main AJAX functionality
├── css/
│   └── vehicle-form-ajax.css     # Form state styling
app/Http/Controllers/
└── VehicleController.php         # Updated with AJAX support
resources/views/
├── content/vehicles/
│   ├── create.blade.php          # Updated create form
│   └── edit.blade.php            # Updated edit form
└── content/demo/
    └── vehicle-ajax-demo.blade.php  # Demo page
```

## Implementation

### 1. Controller Updates

The `VehicleController` has been updated to handle AJAX requests:

```php
// In store() method
if ($request->ajax()) {
    return response()->json([
        'status' => 'success',
        'message' => 'Vehicle created successfully.',
        'data' => $vehicle->load(['vehicleType', 'brand', 'yearOfManufacture', 'color', 'supplier', 'fuelType']),
        'redirect_url' => route('vehicles.index')
    ]);
}

// In update() method
if ($request->ajax()) {
    return response()->json([
        'status' => 'success',
        'message' => 'Vehicle updated successfully.',
        'data' => $vehicle->load(['vehicleType', 'brand', 'yearOfManufacture', 'color', 'supplier', 'fuelType']),
        'redirect_url' => route('vehicles.index')
    ]);
}
```

### 2. Form Updates

Forms are updated with AJAX attributes:

```blade
<form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data" data-ajax-vehicle="true">
    @csrf
    <!-- Form fields -->
    <button type="submit" class="btn btn-primary" id="submit-vehicle">
        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
        <i class="ti ti-check me-1"></i>Create Vehicle
    </button>
</form>
```

### 3. JavaScript Integration

Include the AJAX JavaScript file:

```blade
@section('page-script')
<script src="{{ asset('assets/js/vehicle-form-ajax.js') }}"></script>
@endsection
```

## Usage

### Basic Implementation

1. **Add AJAX attribute to form**:
```blade
<form data-ajax-vehicle="true">
```

2. **Include required files**:
```blade
@section('page-style')
<link href="{{ asset('assets/css/vehicle-form-ajax.css') }}" rel="stylesheet">
@endsection

@section('page-script')
<script src="{{ asset('assets/js/vehicle-form-ajax.js') }}"></script>
@endsection
```

3. **Add spinner to submit button**:
```blade
<button type="submit" class="btn btn-primary">
    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
    <i class="ti ti-check me-1"></i>Save Vehicle
</button>
```

### Advanced Configuration

#### Custom Validation
```javascript
// Add custom validation rules
$(document).ready(function() {
    $('form[data-ajax-vehicle]').on('submit', function(e) {
        // Custom validation logic
        if (!validateCustomField()) {
            e.preventDefault();
            return false;
        }
    });
});
```

#### File Upload Configuration
```blade
<input type="file" name="vehicle_photo" 
       data-max-size="2097152" 
       accept="image/*">
```

#### Custom Success Handling
```javascript
$(document).on('vehicle:saved', function(event, data) {
    console.log('Vehicle saved:', data);
    // Custom success logic
});
```

## API Reference

### JavaScript Class: VehicleFormAjax

#### Methods

##### `init()`
Initialize the AJAX functionality.

##### `handleFormSubmit(event)`
Handle form submission with validation and AJAX.

##### `validateForm()`
Validate form fields before submission.

##### `submitForm()`
Submit form via AJAX with proper headers.

##### `showSpinner()` / `hideSpinner()`
Control spinner visibility.

##### `disableForm()` / `enableForm()`
Control form state during submission.

##### `resetForm()`
Reset form to initial state.

##### `clearFormErrors()`
Remove validation errors from form.

##### `showFieldError($field, message)`
Display field-specific error message.

##### `showAlert(type, message)`
Display alert message to user.

### CSS Classes

#### Form States
- `.form-disabled` - Disabled form state
- `.form-loading` - Loading state with overlay
- `.form-success` - Success animation
- `.form-error` - Error shake animation

#### Validation States
- `.is-invalid` - Invalid field styling
- `.invalid-feedback` - Error message styling

#### Alert Types
- `.alert-success` - Success message
- `.alert-danger` - Error message
- `.alert-info` - Information message

## Configuration Options

### Form Attributes

| Attribute | Description | Example |
|-----------|-------------|---------|
| `data-ajax-vehicle` | Enable AJAX functionality | `data-ajax-vehicle="true"` |
| `data-max-size` | Max file size in bytes | `data-max-size="2097152"` |
| `data-reset-form` | Reset form button | `data-reset-form` |

### File Upload Settings

```blade
<!-- Image upload (2MB max) -->
<input type="file" name="vehicle_photo" 
       data-max-size="2097152" 
       accept="image/*">

<!-- Document upload (5MB max) -->
<input type="file" name="registration_document" 
       data-max-size="5242880" 
       accept=".pdf,.jpg,.jpeg,.png">
```

## Event Handling

### Custom Events

#### `vehicle:saved`
Triggered when vehicle is successfully saved.

```javascript
$(document).on('vehicle:saved', function(event, vehicleData) {
    console.log('Vehicle saved:', vehicleData);
    // Custom logic
});
```

#### `vehicle:error`
Triggered when vehicle save fails.

```javascript
$(document).on('vehicle:error', function(event, errorData) {
    console.log('Save failed:', errorData);
    // Custom error handling
});
```

### Form Events

#### `form:validating`
Triggered before form validation.

#### `form:validated`
Triggered after form validation.

#### `form:submitting`
Triggered before AJAX submission.

#### `form:submitted`
Triggered after AJAX submission.

## Error Handling

### Validation Errors
- Field-level error display
- Form-level error messages
- Real-time validation feedback

### Server Errors
- HTTP error responses
- Validation error handling
- Network error handling

### File Upload Errors
- File size validation
- File type validation
- Upload error handling

## Performance Considerations

### Optimization Features
- **Lazy Loading**: Scripts load only when needed
- **Event Delegation**: Efficient event handling
- **Minimal DOM Manipulation**: Optimized for performance
- **Debounced Validation**: Prevents excessive validation calls

### Best Practices
- Use `data-ajax-vehicle` attribute for automatic initialization
- Include required CSS for proper styling
- Handle file uploads with appropriate size limits
- Provide fallback for non-JavaScript users

## Browser Support

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ Internet Explorer 11 (with polyfills)

## Troubleshooting

### Common Issues

#### AJAX Not Working
1. Ensure `data-ajax-vehicle="true"` is set on form
2. Check that `vehicle-form-ajax.js` is loaded
3. Verify CSRF token is present
4. Check browser console for JavaScript errors

#### Spinner Not Showing
1. Ensure CSS file is loaded
2. Check that spinner HTML is present in button
3. Verify JavaScript is executing

#### Form Not Disabling
1. Check CSS file is loaded
2. Verify JavaScript is working
3. Check for conflicting JavaScript

#### File Upload Issues
1. Verify `enctype="multipart/form-data"` on form
2. Check file size limits
3. Verify file type restrictions

### Debug Mode
```javascript
// Enable debug logging
window.vehicleFormAjax.debug = true;

// Check form state
console.log(window.vehicleFormAjax.isSubmitting);
```

## Examples

### Basic Vehicle Form
```blade
<form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data" data-ajax-vehicle="true">
    @csrf
    <input type="text" name="model_name" required>
    <input type="file" name="vehicle_photo" accept="image/*">
    <button type="submit" class="btn btn-primary">
        <span class="spinner-border spinner-border-sm d-none me-2"></span>
        Save Vehicle
    </button>
</form>
```

### Custom Validation
```javascript
$(document).ready(function() {
    $('form[data-ajax-vehicle]').on('submit', function(e) {
        const chassisNumber = $(this).find('input[name="chassis_number"]').val();
        if (!isValidChassisNumber(chassisNumber)) {
            e.preventDefault();
            alert('Invalid chassis number format');
            return false;
        }
    });
});
```

### Custom Success Handler
```javascript
$(document).on('vehicle:saved', function(event, data) {
    // Show custom success message
    toastr.success('Vehicle saved successfully!');
    
    // Update UI
    updateVehicleList(data);
    
    // Track analytics
    gtag('event', 'vehicle_saved', {
        'vehicle_id': data.id
    });
});
```

## Security Considerations

- CSRF protection included
- File upload validation
- Input sanitization
- XSS prevention
- Secure file handling

## Future Enhancements

- [ ] Progress bars for file uploads
- [ ] Drag and drop file upload
- [ ] Auto-save functionality
- [ ] Offline form support
- [ ] Advanced validation rules
- [ ] Multi-step form support

---

**Last Updated**: {{ date('Y-m-d') }}  
**Version**: 1.0.0  
**Author**: TMS Development Team






