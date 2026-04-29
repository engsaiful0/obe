@extends('layouts/layoutMaster')

@section('title', 'Add Lubricant')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-droplet me-2"></i>Add Lubricant
        </h5>
        <a href="{{ route('lubricants.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to List
        </a>
    </div>
    
    <div class="card-body">
        <!-- Alert Messages -->
        <div id="alertContainer" class="d-none mb-3">
            <div class="alert alert-dismissible fade show" role="alert" id="alertMessage">
                <i class="me-2" id="alertIcon"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        @if(count($buses) > 0)
            <form id="lubricantForm">
                @csrf
                
                <!-- Common Fields -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="common_lubricant_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="common_lubricant_date" 
                               name="lubricant_date" 
                               class="form-control" 
                               value="{{ date('Y-m-d') }}" 
                               required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="common_lubricant_time" class="form-label">Time <span class="text-danger">*</span></label>
                        <input type="time" 
                               id="common_lubricant_time" 
                               name="lubricant_time" 
                               class="form-control" 
                               value="{{ date('H:i') }}" 
                               required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="common_concern_employee_id" class="form-label">Concern Employee</label>
                        <select id="common_concern_employee_id" 
                                name="concern_employee_id" 
                                class="form-select">
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->employee_name }} ({{ $employee->employee_unique_id ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Bus lubricant Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">Bus Number</th>
                                <th style="width: 15%;">lubricant Amount <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Unit <span class="text-danger">*</span></th>
                                <th style="width: 30%;">Comment</th>
                                <th style="width: 23%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buses as $index => $bus)
                                <tr data-bus-id="{{ $bus->id }}">
                                    <td>
                                        <strong>{{ $bus->bus_number }}</strong>
                                        <input type="hidden" name="lubricants[{{ $index }}][bus_id]" value="{{ $bus->id }}" class="lubricant-bus-id">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0"
                                               class="form-control lubricant-amount" 
                                               name="lubricants[{{ $index }}][lubricant_amount]" 
                                               placeholder="0.00"
                                               data-bus-id="{{ $bus->id }}">
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <select class="form-select lubricant-unit" 
                                                name="lubricants[{{ $index }}][unit_id]" 
                                                data-bus-id="{{ $bus->id }}"
                                                required>
                                            <option value="">Select Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" {{ $unit->unit_name == 'Liter' || $unit->unit_name == 'Gallon' ? 'selected' : '' }}>
                                                    {{ $unit->unit_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <textarea class="form-control lubricant-comment" 
                                                  name="lubricants[{{ $index }}][comment]" 
                                                  rows="2" 
                                                  data-bus-id="{{ $bus->id }}"
                                                  placeholder="Optional comment..."></textarea>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary save-row-btn" 
                                                data-bus-id="{{ $bus->id }}">
                                            <i class="ti ti-device-floppy me-1"></i>Save
                                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-success" id="saveAllBtn">
                                <i class="ti ti-device-floppy me-1"></i>Save All
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="saveAllSpinner">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-warning">
                <i class="ti ti-alert-circle me-2"></i>
                No own buses found. Please add own buses first.
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Initialize datepicker
        $('#common_lubricant_date').flatpickr({
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        // Show alert
        function showAlert(type, message) {
            $('#alertContainer').removeClass('d-none').addClass('d-block');
            $('#alertMessage').removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
            $('#alertText').text(message);
            $('#alertIcon').removeClass().addClass(type === 'success' ? 'ti ti-check-circle me-2' : 'ti ti-alert-circle me-2');
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }

        // Hide alert
        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Save single row
        $('.save-row-btn').on('click', function() {
            const btn = $(this);
            const busId = btn.data('bus-id');
            const row = btn.closest('tr');
            const spinner = btn.find('.spinner-border');
            
            // Get form data for this row
            const formData = {
                bus_id: row.find('.lubricant-bus-id').val(),
                lubricant_date: $('#common_lubricant_date').val(),
                lubricant_time: $('#common_lubricant_time').val(),
                concern_employee_id: $('#common_concern_employee_id').val(),
                lubricant_amount: row.find('.lubricant-amount').val(),
                unit_id: row.find('.lubricant-unit').val(),
                comment: row.find('.lubricant-comment').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Validate
            if (!formData.lubricant_date || !formData.lubricant_time || !formData.lubricant_amount || formData.lubricant_amount <= 0 || !formData.unit_id) {
                showAlert('danger', 'Please fill in all required fields (Date, Time, Lubricant Amount, and Unit).');
                return;
            }

            // Disable button and show spinner
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            hideAlert();

            // Clear previous validation errors
            row.find('.is-invalid').removeClass('is-invalid');
            row.find('.invalid-feedback').text('');

            $.ajax({
                url: '{{ route("lubricants.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (response.success) {
                        showAlert('success', response.message);
                        toastr.success(response.message);
                        // Clear the row inputs
                        row.find('.lubricant-amount').val('');
                        row.find('.lubricant-comment').val('');
                    } else {
                        showAlert('danger', response.message);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];

                        // Display field-specific errors
                        for (let field in errors) {
                            const fieldElement = row.find('[name*="[' + field + ']"]');
                            if (fieldElement.length) {
                                fieldElement.addClass('is-invalid');
                                fieldElement.siblings('.invalid-feedback').text(errors[field][0]);
                            }
                            errorMessages.push(errors[field][0]);
                        }

                        showAlert('danger', errorMessages.join('<br>'));
                        toastr.error(errorMessages.join(', '));
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while saving.';
                        showAlert('danger', message);
                        toastr.error(message);
                    }
                }
            });
        });

        // Save All
        $('#saveAllBtn').on('click', function() {
            const btn = $(this);
            const spinner = $('#saveAllSpinner');
            
            // Collect all rows with lubricant amount
            const lubricants = [];
            $('tr[data-bus-id]').each(function() {
                const row = $(this);
                const lubricantAmount = row.find('.lubricant-amount').val();
                const busId = row.find('.lubricant-bus-id').val();
                
                if (lubricantAmount && lubricantAmount > 0 && busId) {
                    lubricants.push({
                        bus_id: busId,
                        lubricant_amount: lubricantAmount,
                        unit_id: row.find('.lubricant-unit').val(),
                        comment: row.find('.lubricant-comment').val()
                    });
                }
            });

            if (lubricants.length === 0) {
                showAlert('warning', 'Please enter lubricant amount for at least one bus.');
                return;
            }

            // Validate common fields
            const lubricantDate = $('#common_lubricant_date').val();
            const lubricantTime = $('#common_lubricant_time').val();

            if (!lubricantDate || !lubricantTime) {
                showAlert('danger', 'Please fill in Date and Time.');
                return;
            }

            // Disable button and show spinner
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            hideAlert();

            const formData = {
                lubricant_date: lubricantDate,
                lubricant_time: lubricantTime,
                concern_employee_id: $('#common_concern_employee_id').val(),
                lubricants: lubricants,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: '{{ route("lubricants.store-all") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (response.success) {
                        showAlert('success', response.message);
                        toastr.success(response.message);
                        // Clear all inputs
                        $('.lubricant-amount').val('');
                        $('.lubricant-comment').val('');
                    } else {
                        showAlert('danger', response.message);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];

                        for (let field in errors) {
                            errorMessages.push(errors[field][0]);
                        }

                        showAlert('danger', errorMessages.join('<br>'));
                        toastr.error(errorMessages.join(', '));
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while saving.';
                        showAlert('danger', message);
                        toastr.error(message);
                    }
                }
            });
        });
    });
</script>
@endsection

