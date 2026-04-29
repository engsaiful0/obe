@extends('layouts/layoutMaster')

@section('title', 'Password Toggle Demo')

@section('page-style')
<link href="{{ asset('assets/css/password-toggle.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Password Toggle System Demo</h5>
                    <p class="card-subtitle">Demonstration of the password visibility toggle functionality</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Password Field -->
                        <div class="col-md-6 mb-4">
                            <h6>Basic Password Field</h6>
                            <x-password-input 
                                name="basic_password" 
                                placeholder="Enter basic password" 
                                label="Basic Password"
                            />
                        </div>

                        <!-- Required Password Field -->
                        <div class="col-md-6 mb-4">
                            <h6>Required Password Field</h6>
                            <x-password-input 
                                name="required_password" 
                                placeholder="Enter required password" 
                                label="Required Password"
                                required="true"
                            />
                        </div>

                        <!-- Password with Help Text -->
                        <div class="col-md-6 mb-4">
                            <h6>Password with Help Text</h6>
                            <x-password-input 
                                name="help_password" 
                                placeholder="Enter password with help" 
                                label="Password with Help"
                                helpText="Password must be at least 8 characters long"
                            />
                        </div>

                        <!-- Password with Error State -->
                        <div class="col-md-6 mb-4">
                            <h6>Password with Error State</h6>
                            <x-password-input 
                                name="error_password" 
                                placeholder="Enter password with error" 
                                label="Password with Error"
                                errorKey="error_password"
                            />
                            @error('error_password')
                                <div class="invalid-feedback d-block">This field has an error</div>
                            @enderror
                        </div>

                        <!-- Multiple Password Fields -->
                        <div class="col-12 mb-4">
                            <h6>Multiple Password Fields</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <x-password-input 
                                        name="password1" 
                                        placeholder="Password 1" 
                                        label="Password 1"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <x-password-input 
                                        name="password2" 
                                        placeholder="Password 2" 
                                        label="Password 2"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <x-password-input 
                                        name="password3" 
                                        placeholder="Password 3" 
                                        label="Password 3"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Custom Styled Password Field -->
                        <div class="col-12 mb-4">
                            <h6>Custom Styled Password Field</h6>
                            <x-password-input 
                                name="custom_password" 
                                placeholder="Enter custom password" 
                                label="Custom Password"
                                class="custom-password-field"
                                style="border: 2px solid #007bff;"
                            />
                        </div>
                    </div>

                    <!-- Demo Controls -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Demo Controls</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="showAllPasswords()">
                                    <i class="ti ti-eye me-1"></i>Show All Passwords
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="hideAllPasswords()">
                                    <i class="ti ti-eye-off me-1"></i>Hide All Passwords
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="toggleAllPasswords()">
                                    <i class="ti ti-refresh me-1"></i>Toggle All Passwords
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Features</h6>
                            <ul class="list-unstyled">
                                <li><i class="ti ti-check text-success me-2"></i>Click eye icon to toggle password visibility</li>
                                <li><i class="ti ti-check text-success me-2"></i>Hover effects and smooth transitions</li>
                                <li><i class="ti ti-check text-success me-2"></i>Accessibility support with proper ARIA labels</li>
                                <li><i class="ti ti-check text-success me-2"></i>Dark mode support</li>
                                <li><i class="ti ti-check text-success me-2"></i>RTL (Right-to-Left) language support</li>
                                <li><i class="ti ti-check text-success me-2"></i>High contrast mode support</li>
                                <li><i class="ti ti-check text-success me-2"></i>Print-friendly (icons hidden in print)</li>
                                <li><i class="ti ti-check text-success me-2"></i>Reusable Blade component</li>
                                <li><i class="ti ti-check text-success me-2"></i>Global JavaScript functions for control</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/password-toggle.js') }}"></script>
<script>
// Additional demo functions
function toggleAllPasswords() {
    $('.form-password-toggle').each(function() {
        const $container = $(this);
        const $input = $container.find('input');
        const $icon = $container.find('i');
        
        if ($input.attr('type') === 'password') {
            window.passwordToggle.togglePasswordVisibility($input, $icon);
        }
    });
}

// Add some demo styling
$(document).ready(function() {
    $('.custom-password-field input').css({
        'border': '2px solid #007bff',
        'border-radius': '8px'
    });
});
</script>
@endsection






