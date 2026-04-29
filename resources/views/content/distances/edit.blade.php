@extends('layouts/layoutMaster')

@section('title', 'Edit Distance')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Edit Distance</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="distanceForm" action="{{ route('distances.update', $distance) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-route me-2"></i>Distance Information
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label for="distance_name" class="form-label">Distance Name (Optional)</label>
                    <input type="text" name="distance_name" id="distance_name" class="form-control @error('distance_name') is-invalid @enderror"
                           value="{{ old('distance_name', $distance->distance_name) }}" placeholder="Enter distance name">
                    @error('distance_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Route Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-map-pin me-2"></i>Route Details
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="start_stoppage_id" class="form-label">Start Stoppage</label>
                    <select name="start_stoppage_id" id="start_stoppage_id" class="form-select @error('start_stoppage_id') is-invalid @enderror">
                        <option value="">Select Start Stoppage</option>
                        @foreach($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}" {{ old('start_stoppage_id', $distance->start_stoppage_id) == $stoppage->id ? 'selected' : '' }}>
                                {{ $stoppage->stoppage_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('start_stoppage_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="end_stoppage_id" class="form-label">End Stoppage</label>
                    <select name="end_stoppage_id" id="end_stoppage_id" class="form-select @error('end_stoppage_id') is-invalid @enderror">
                        <option value="">Select End Stoppage</option>
                        @foreach($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}" {{ old('end_stoppage_id', $distance->end_stoppage_id) == $stoppage->id ? 'selected' : '' }}>
                                {{ $stoppage->stoppage_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('end_stoppage_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Distance Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-ruler me-2"></i>Distance Information
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="distance_km" class="form-label">Distance (KM)</label>
                    <div class="input-group">
                        <input type="number" name="distance_km" id="distance_km" 
                               class="form-control @error('distance_km') is-invalid @enderror"
                               value="{{ old('distance_km', $distance->distance_km) }}" 
                               placeholder="0.00" 
                               step="0.01" 
                               min="0.01" 
                               max="9999.99">
                        <span class="input-group-text">KM</span>
                    </div>
                    @error('distance_km')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea name="description" id="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3" 
                              placeholder="Enter description">{{ old('description', $distance->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <span id="submitText">
                                <i class="ti ti-check me-1"></i>Update Distance
                            </span>
                            <span id="submitSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Updating...
                            </span>
                        </button>
                        <a href="{{ route('distances.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize Select2
    if ($.fn.select2) {
        $('#start_stoppage_id, #end_stoppage_id').select2({
            width: '100%',
            placeholder: 'Select a stoppage'
        });
    }
    
    // Validate that start and end stoppages are different
    $('#start_stoppage_id, #end_stoppage_id').on('change', function() {
        const startId = $('#start_stoppage_id').val();
        const endId = $('#end_stoppage_id').val();
        
        if (startId && endId && startId === endId) {
            toastr.error('Start and End stoppages must be different.');
            $(this).val('').trigger('change');
        }
    });
    
    // Auto-generate route name when both stoppages are selected
    $('#start_stoppage_id, #end_stoppage_id').on('change', function() {
        const startId = $('#start_stoppage_id').val();
        const endId = $('#end_stoppage_id').val();
        
        if (startId && endId) {
            const startName = $('#start_stoppage_id option:selected').text();
            const endName = $('#end_stoppage_id option:selected').text();
            
            if (!$('#distance_name').val()) {
                $('#distance_name').val(startName + ' → ' + endName);
            }
        }
    });
    
    // AJAX form submission
    $('#distanceForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const submitSpinner = $('#submitSpinner');
        
        // Show spinner
        submitBtn.prop('disabled', true);
        submitText.addClass('d-none');
        submitSpinner.removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                toastr.success('Distance updated successfully!');
                setTimeout(function() {
                    window.location.href = '{{ route("distances.index") }}';
                }, 1500);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                    toastr.error('Please fix the validation errors.');
                } else {
                    toastr.error('An error occurred while updating the distance.');
                }
            },
            complete: function() {
                // Hide spinner
                submitBtn.prop('disabled', false);
                submitText.removeClass('d-none');
                submitSpinner.addClass('d-none');
            }
        });
    });
});
</script>
@endsection
@endsection
