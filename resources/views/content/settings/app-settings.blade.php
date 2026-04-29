@extends('layouts/layoutMaster')

@section('title', 'App Settings')

@section('page-style')
<style>
.form-loading {
    opacity: 0.6;
    pointer-events: none;
}

.submit-btn-loading {
    opacity: 0.8;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 0.875rem;
    height: 0.875rem;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">App Settings</h5>
            </div>
            <div class="card-body">
                <form id="app-settings-form" method="POST" action="{{ route('app-settings.update', $settings->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="university_name">University Name</label>
                                <input type="text" class="form-control" id="university_name" name="university_name" value="{{ $settings->university_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="short_name">Short Name</label>
                                <input type="text" class="form-control" id="short_name" name="short_name" value="{{ $settings->short_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="logo">Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                @if($settings->logo)
                                <img src="{{ asset('assets/img/branding/' . $settings->logo) }}" alt="logo" class="mt-2" width="100">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $settings->address }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ $settings->phone }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $settings->email }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="website">Website</label>
                                <input type="text" class="form-control" id="website" name="website" value="{{ $settings->website }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="established_year">Established Year</label>
                                <input type="number" class="form-control" id="established_year" name="established_year" min="1800" max="{{ date('Y') }}" value="{{ $settings->established_year }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="vc_name">VC Name</label>
                                <input type="text" class="form-control" id="vc_name" name="vc_name" value="{{ $settings->vc_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="registrar_name">Registrar Name</label>
                                <input type="text" class="form-control" id="registrar_name" name="registrar_name" value="{{ $settings->registrar_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="controller_name">Controller Name</label>
                                <input type="text" class="form-control" id="controller_name" name="controller_name" value="{{ $settings->controller_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="time_zone">Time Zone</label>
                                <select class="form-select" id="time_zone" name="time_zone">
                                    @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ $settings->time_zone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="academic_system">Academic System</label>
                                <select class="form-select" id="academic_system" name="academic_system">
                                    <option value="Semester" {{ $settings->academic_system == 'Semester' ? 'selected' : '' }}>Semester</option>
                                    <option value="Trimester" {{ $settings->academic_system == 'Trimester' ? 'selected' : '' }}>Trimester</option>
                                    <option value="Yearly" {{ $settings->academic_system == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Active" {{ $settings->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $settings->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="submit-btn" class="btn btn-primary">
                        <span id="spinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        <span id="button-text">Update</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#app-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submit-btn');
        const spinner = $('#spinner');
        const buttonText = $('#button-text');
        const formData = new FormData(this);
        
        // Disable form and show spinner
        form.addClass('form-loading');
        form.find('input, select, textarea, button').prop('disabled', true);
        spinner.removeClass('d-none');
        buttonText.text('Updating...');
        submitBtn.addClass('submit-btn-loading').prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success toast
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    
                    // Update logo image if it was changed
                    if (response.data.logo) {
                        $('img[alt="logo"]').attr('src', '{{ asset("assets/img/branding/") }}/' + response.data.logo);
                    }
                } else {
                    // Show error toast
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.error || 'An error occurred');
                    } else {
                        alert(response.error || 'An error occurred');
                    }
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while updating settings.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Show error toast
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable form and hide spinner
                form.removeClass('form-loading');
                form.find('input, select, textarea, button').prop('disabled', false);
                spinner.addClass('d-none');
                buttonText.text('Update');
                submitBtn.removeClass('submit-btn-loading').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection