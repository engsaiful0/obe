@extends('layouts/layoutMaster')

@section('title', 'Vehicle AJAX Demo')

@section('page-style')
<link href="{{ asset('assets/css/vehicle-form-ajax.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Vehicle AJAX Form Demo</h5>
                    <p class="card-subtitle">Demonstration of AJAX vehicle form submission with spinner and form state management</p>
                </div>
                <div class="card-body">
                    <!-- Demo Form -->
                    <form id="demo-vehicle-form" action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data" data-ajax-vehicle="true">
                        @csrf
                        
                        <!-- Basic Vehicle Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="ti ti-car me-2"></i>Basic Vehicle Information
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="demo_model_name" class="form-label">Model Name <span class="text-danger">*</span></label>
                                <input type="text" name="model_name" id="demo_model_name" class="form-control" 
                                       placeholder="Enter model name" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="demo_chassis_number" class="form-label">Chassis Number <span class="text-danger">*</span></label>
                                <input type="text" name="chassis_number" id="demo_chassis_number" class="form-control" 
                                       placeholder="Enter chassis number" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="demo_engine_number" class="form-label">Engine Number <span class="text-danger">*</span></label>
                                <input type="text" name="engine_number" id="demo_engine_number" class="form-control" 
                                       placeholder="Enter engine number" required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="demo_registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" name="registration_number" id="demo_registration_number" class="form-control" 
                                       placeholder="Enter registration number" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="demo_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="demo_status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="under_maintenance">Under Maintenance</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- File Upload Demo -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="ti ti-paperclip me-2"></i>File Upload Demo
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="demo_vehicle_photo" class="form-label">Vehicle Photo</label>
                                <input type="file" name="vehicle_photo" id="demo_vehicle_photo" class="form-control" 
                                       accept="image/*" data-max-size="2097152">
                                <div class="form-text">Max size: 2MB, Formats: JPEG, PNG, JPG, GIF</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="demo_registration_document" class="form-label">Registration Document</label>
                                <input type="file" name="registration_document" id="demo_registration_document" class="form-control" 
                                       accept=".pdf,.jpg,.jpeg,.png" data-max-size="5242880">
                                <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                            </div>
                        </div>
                        
                        <!-- Demo Controls -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="ti ti-settings me-2"></i>Demo Controls
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="demo_vehicle_type_id" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                <select name="vehicle_type_id" id="demo_vehicle_type_id" class="form-select" required>
                                    <option value="">Select Vehicle Type</option>
                                    <option value="1">Bus</option>
                                    <option value="2">Car</option>
                                    <option value="3">Truck</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="demo_brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                                <select name="brand_id" id="demo_brand_id" class="form-select" required>
                                    <option value="">Select Brand</option>
                                    <option value="1">Toyota</option>
                                    <option value="2">Honda</option>
                                    <option value="3">Ford</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="demo_fuel_type_id" class="form-label">Fuel Type <span class="text-danger">*</span></label>
                                <select name="fuel_type_id" id="demo_fuel_type_id" class="form-select" required>
                                    <option value="">Select Fuel Type</option>
                                    <option value="1">Petrol</option>
                                    <option value="2">Diesel</option>
                                    <option value="3">Electric</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="resetDemoForm()">
                                        <i class="ti ti-refresh me-1"></i>Reset Form
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="testValidation()">
                                        <i class="ti ti-check me-1"></i>Test Validation
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="demo-submit-vehicle">
                                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                        <i class="ti ti-check me-1"></i>Save Vehicle (AJAX)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Demo Features -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <h6>AJAX Features Demonstrated</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="ti ti-check text-success me-2"></i>Form submission via AJAX</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Spinner animation during submission</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Form disabled state during submission</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Real-time validation</li>
                                        <li><i class="ti ti-check text-success me-2"></i>File upload with preview</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="ti ti-check text-success me-2"></i>Success/error message display</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Form reset after successful submission</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Automatic redirect after save</li>
                                        <li><i class="ti ti-check text-success me-2"></i>File size validation</li>
                                        <li><i class="ti ti-check text-success me-2"></i>Progress indicators</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Demo Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="ti ti-info-circle me-2"></i>Demo Instructions</h6>
                                <ol>
                                    <li>Fill in the required fields (marked with *)</li>
                                    <li>Try uploading files to see the preview functionality</li>
                                    <li>Click "Save Vehicle (AJAX)" to see the spinner and form state management</li>
                                    <li>Try submitting with empty required fields to see validation</li>
                                    <li>Use "Test Validation" to see client-side validation</li>
                                    <li>Use "Reset Form" to clear all fields</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/vehicle-form-ajax.js') }}"></script>
<script>
$(document).ready(function() {
    // Demo-specific functionality
    window.resetDemoForm = function() {
        $('#demo-vehicle-form')[0].reset();
        $('.file-preview').remove();
        $('.alert').remove();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    };
    
    window.testValidation = function() {
        const form = $('#demo-vehicle-form');
        const requiredFields = form.find('[required]');
        let hasErrors = false;
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Test required fields
        requiredFields.each(function() {
            if (!$(this).val() || $(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">This field is required</div>');
                hasErrors = true;
            }
        });
        
        if (hasErrors) {
            $('<div class="alert alert-danger mt-3">Please fill in all required fields</div>').insertAfter(form);
        } else {
            $('<div class="alert alert-success mt-3">All required fields are filled correctly!</div>').insertAfter(form);
        }
    };
    
    // File preview functionality
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const preview = $(this).siblings('.file-preview');
            if (preview.length === 0) {
                $(this).after('<div class="file-preview mt-2"></div>');
            }
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(this).siblings('.file-preview').html(`
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <div class="mt-1">
                            <small class="text-muted">${file.name} (${formatFileSize(file.size)})</small>
                        </div>
                    `);
                }.bind(this);
                reader.readAsDataURL(file);
            } else {
                $(this).siblings('.file-preview').html(`
                    <div class="alert alert-info">
                        <i class="ti ti-file me-2"></i>
                        ${file.name} (${formatFileSize(file.size)})
                    </div>
                `);
            }
        }
    });
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection






