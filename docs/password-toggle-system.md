# Password Toggle System Documentation

## Overview

The Password Toggle System provides a comprehensive solution for password visibility toggling across the entire application. Users can click on an eye icon to show/hide password text, enhancing user experience and accessibility.

## Features

### ✨ Core Features
- **Click to Toggle**: Click the eye icon to show/hide password
- **Visual Feedback**: Smooth icon transitions (eye-off ↔ eye)
- **Accessibility**: Proper ARIA labels and keyboard navigation
- **Responsive Design**: Works on all screen sizes
- **Dark Mode Support**: Automatic dark mode detection
- **RTL Support**: Right-to-Left language support
- **High Contrast**: Enhanced visibility for accessibility
- **Print Friendly**: Icons hidden in print mode

### 🎨 Visual Enhancements
- Hover effects with color transitions
- Smooth animations and transitions
- Focus states for keyboard navigation
- Disabled state styling
- Custom styling support

## Files Structure

```
assets/
├── js/
│   └── password-toggle.js          # Main JavaScript functionality
├── css/
│   └── password-toggle.css         # Enhanced styling
resources/views/
├── components/
│   └── password-input.blade.php    # Reusable Blade component
└── content/demo/
    └── password-toggle-demo.blade.php  # Demo page
```

## Usage

### 1. Basic Implementation

#### Using the Blade Component (Recommended)
```blade
<x-password-input 
    name="password" 
    placeholder="Enter your password" 
    label="Password"
    required="true"
/>
```

#### Manual HTML Structure
```html
<div class="form-password-toggle">
    <label class="form-label" for="password">Password</label>
    <div class="input-group input-group-merge">
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" />
        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
    </div>
</div>
```

### 2. Include Required Files

#### In your Blade template:
```blade
@section('page-style')
<link href="{{ asset('assets/css/password-toggle.css') }}" rel="stylesheet">
@endsection

@section('page-script')
<script src="{{ asset('assets/js/password-toggle.js') }}"></script>
@endsection
```

### 3. Component Options

The `x-password-input` component accepts the following props:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | string | 'password' | Input name attribute |
| `id` | string | null | Input ID (defaults to name) |
| `value` | string | '' | Input value |
| `placeholder` | string | 'Enter your password' | Placeholder text |
| `required` | boolean | false | Whether field is required |
| `class` | string | '' | Additional CSS classes |
| `label` | string | 'Password' | Field label |
| `showLabel` | boolean | true | Whether to show label |
| `errorKey` | string | null | Error key for validation |
| `helpText` | string | null | Help text below input |

### 4. Advanced Usage

#### Multiple Password Fields
```blade
<div class="row">
    <div class="col-md-6">
        <x-password-input name="password" label="Password" />
    </div>
    <div class="col-md-6">
        <x-password-input name="confirm_password" label="Confirm Password" />
    </div>
</div>
```

#### With Validation
```blade
<x-password-input 
    name="password" 
    label="Password"
    required="true"
    errorKey="password"
    helpText="Password must be at least 8 characters"
/>
@error('password')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

## JavaScript API

### Global Functions

#### `showAllPasswords()`
Shows all passwords on the page (for debugging).

```javascript
showAllPasswords();
```

#### `hideAllPasswords()`
Hides all passwords on the page (for debugging).

```javascript
hideAllPasswords();
```

### Class Methods

#### `PasswordToggle.addToggleToInput($input)`
Add password toggle to a specific input.

```javascript
const $input = $('#my-password-input');
window.passwordToggle.addToggleToInput($input);
```

#### `PasswordToggle.removeToggleFromInput($input)`
Remove password toggle from a specific input.

```javascript
const $input = $('#my-password-input');
window.passwordToggle.removeToggleFromInput($input);
```

#### `PasswordToggle.getAllToggles()`
Get all password toggle instances.

```javascript
const toggles = window.passwordToggle.getAllToggles();
console.log(`Found ${toggles.length} password toggles`);
```

## CSS Customization

### Custom Styling
```css
/* Custom password toggle styling */
.form-password-toggle .input-group-text {
    background-color: #your-color;
    border-color: #your-border-color;
}

.form-password-toggle .input-group-text:hover {
    background-color: #your-hover-color;
}
```

### Dark Mode Customization
```css
@media (prefers-color-scheme: dark) {
    .form-password-toggle .input-group-text {
        background-color: #your-dark-color;
        border-color: #your-dark-border-color;
    }
}
```

## Accessibility Features

### Keyboard Navigation
- Tab to focus the toggle button
- Enter or Space to toggle password visibility
- Proper focus indicators

### Screen Reader Support
- Proper ARIA labels
- Title attributes for tooltips
- Semantic HTML structure

### High Contrast Mode
- Enhanced border visibility
- Bold icons for better visibility
- Proper color contrast ratios

## Browser Support

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ Internet Explorer 11 (with polyfills)

## Troubleshooting

### Common Issues

#### Toggle Not Working
1. Ensure `password-toggle.js` is loaded
2. Check that the HTML structure is correct
3. Verify no JavaScript errors in console

#### Styling Issues
1. Ensure `password-toggle.css` is loaded
2. Check for CSS conflicts
3. Verify Bootstrap classes are available

#### Multiple Toggles Not Working
1. Ensure each toggle has unique IDs
2. Check for duplicate event listeners
3. Verify proper HTML structure

### Debug Mode
```javascript
// Enable debug logging
window.passwordToggle.debug = true;

// Check all toggles
console.log(window.passwordToggle.getAllToggles());
```

## Examples

### Login Form
```blade
<form method="POST" action="/login">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <x-password-input name="password" label="Password" required="true" />
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
```

### Registration Form
```blade
<form method="POST" action="/register">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <x-password-input 
                name="password" 
                label="Password" 
                required="true"
                helpText="Must be at least 8 characters"
            />
        </div>
        <div class="col-md-6">
            <x-password-input 
                name="password_confirmation" 
                label="Confirm Password" 
                required="true"
            />
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
```

## Performance Considerations

- **Lazy Loading**: Scripts load only when needed
- **Event Delegation**: Efficient event handling
- **Minimal DOM Manipulation**: Optimized for performance
- **CSS Transitions**: Hardware-accelerated animations

## Security Notes

- Password visibility is client-side only
- No password data is transmitted when toggling
- Secure by default (passwords hidden initially)
- No sensitive data in JavaScript

## Future Enhancements

- [ ] Biometric authentication integration
- [ ] Password strength indicator
- [ ] Auto-hide after timeout
- [ ] Voice control support
- [ ] Advanced accessibility features

---

**Last Updated**: {{ date('Y-m-d') }}  
**Version**: 1.0.0  
**Author**: TMS Development Team






