$(document).ready(function () {
    console.log('Daily Bus List AJAX script loaded');
    console.log('Available URLs:', window.dailyBusListUrls);

    const form = $('#dailyBusListForm');
    const submitBtn = $('#submitBtn');
    const submitSpinner = $('#submitSpinner');
    const submitText = $('#submitText');
    const alertContainer = $('#alertContainer');

    // --- Helper: Show alert in container
    function showAlert(type, message) {
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.html(alertHTML);
    }

    // ================================
    // 🔄 On buses sub type change
    // ================================
    $('#bus_sub_type_id').on('change', function () {
        let subTypeId = $(this).val();
        let spinner = $('#loadingSpinner');
        let container = $('#busListContainer');
        let placeholder = $('#busPlaceholder');

        if (!subTypeId) {
            container.html('<p class="text-muted text-center py-5">Select a Bus sub type to load buses...</p>');
            return;
        }

        // Show spinner
        spinner.removeClass('d-none');
        placeholder.find('p').text('Loading buses...');

        // AJAX request to load buses
        $.ajax({
            url: window.dailyBusListUrls.getVehicleBusesBySubType,
            method: "GET",
            data: { sub_type_id: subTypeId },
            success: function (response) {
                spinner.addClass('d-none');
                if (response.html) {
                    container.html(response.html);
                    feather.replace(); // refresh icons
                    $('.select2').select2(); // reinit select2
                    
                    // Add validation for start/end stoppages
                    addStoppageValidation();
                } else {
                    container.html('<p class="text-danger text-center py-5">No buses found for this type.</p>');
                }
            },
            error: function (xhr) {
                spinner.addClass('d-none');
                container.html('<p class="text-danger text-center py-5">Error loading buses. Try again.</p>');
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // ================================
    // 🔍 Add stoppage validation
    // ================================
    function addStoppageValidation() {
        $('.start-stoppage-select, .end-stoppage-select').on('change', function() {
            const row = $(this).closest('tr');
            const startSelect = row.find('.start-stoppage-select');
            const endSelect = row.find('.end-stoppage-select');
            
            // Remove previous validation classes
            startSelect.removeClass('is-invalid');
            endSelect.removeClass('is-invalid');
            row.find('.invalid-feedback').remove();
            
            // Check if both are selected and are the same
            if (startSelect.val() && endSelect.val() && startSelect.val() === endSelect.val()) {
                endSelect.addClass('is-invalid');
                endSelect.after('<div class="invalid-feedback">End stoppage must be different from start stoppage.</div>');
            }
        });
    }

    // ================================
    // 💾 Form submit (Save multiple)
    // ================================
    form.on('submit', function (e) {
        e.preventDefault();

        // Clear previous validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Validate form
        let isValid = true;
        let errorMessages = [];

        // Check required fields
        $('select[required], input[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">This field is required.</div>');
                isValid = false;
            }
        });

        // Check start/end stoppage validation
        $('.start-stoppage-select').each(function() {
            const row = $(this).closest('tr');
            const startSelect = row.find('.start-stoppage-select');
            const endSelect = row.find('.end-stoppage-select');
            
            if (startSelect.val() && endSelect.val() && startSelect.val() === endSelect.val()) {
                endSelect.addClass('is-invalid');
                endSelect.after('<div class="invalid-feedback">End stoppage must be different from start stoppage.</div>');
                isValid = false;
                errorMessages.push('Start and end stoppages must be different for each bus.');
            }
        });

        if (!isValid) {
            if (errorMessages.length > 0) {
                toastr.error(errorMessages.join('<br>'), 'Validation Error');
            } else {
                toastr.error('Please fill in all required fields.', 'Validation Error');
            }
            return;
        }

        // Spinner ON
        submitSpinner.removeClass('d-none');
        submitText.html('<i data-feather="loader"></i> Saving...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: window.dailyBusListUrls.storeMultiple,
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message || 'Bus list saved successfully.');

                    // ✅ Redirect after 1.5s delay
                    setTimeout(function () {
                        window.location.href = window.dailyBusListUrls.allBusesList;
                    }, 1500);
                } else {
                    toastr.warning(response.message || 'Something went wrong, please try again.');
                }
            },
            error: function (xhr) {
                console.error('Save error:', xhr);
                let msg = 'An unexpected error occurred.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        let errorList = '';
                        Object.keys(errors).forEach(key => {
                            errorList += `- ${errors[key][0]}<br>`;
                        });
                        toastr.error(errorList, 'Validation Error');
                        msg = '';
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                if (msg) toastr.error(msg);
            },
            complete: function () {
                // Spinner OFF
                submitSpinner.addClass('d-none');
                submitText.html('<i data-feather="save"></i> Save Entry');
                submitBtn.prop('disabled', false);
                feather.replace();
            }
        });
    });

    // ================================
    // 🔁 Reset form
    // ================================
    $('#resetBtn').on('click', function () {
        form.trigger('reset');
        alertContainer.empty();
        toastr.info('Form reset successfully.');
    });

    // ================================
    // 🗑️ Delete using Swal + AJAX
    // ================================
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let deleteUrl = form.attr('action');
        let id = form.find('input[name="id"]').val();

        Swal.fire({
            title: 'Are you sure?',
            text: "This record will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message || 'Deleted successfully!');
                            // Optionally remove row
                            form.closest('tr').fadeOut(500, function () { $(this).remove(); });
                        } else {
                            toastr.warning(response.message || 'Could not delete. Try again.');
                        }
                    },
                    error: function (xhr) {
                        console.error('Delete error:', xhr);
                        toastr.error('Error occurred while deleting.');
                    }
                });
            }
        });
    });
});
