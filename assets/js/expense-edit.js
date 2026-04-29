$(document).ready(function() {
    // AJAX submit handler
    $('#expense-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const formData = new FormData(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnHtml = submitBtn.html();

        // Clear old validation errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');

        // Show spinner and disable button
        submitBtn.prop('disabled', true).html(`
    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
    Updating...
`);
        $.ajax({
            url: form.attr('action'),
            method: 'POST', // Laravel requires POST for FormData with _method=PUT
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = response.redirect_url;
                    }, 1200);
                } else {
                    toastr.error(response.message || 'Failed to update expense.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        const input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Load vehicle list dynamically based on type/subtype
    $('#vehicle_type_id, #vehicle_sub_type_id').on('change', function() {
        const typeId = $('#vehicle_type_id').val();
        const subTypeId = $('#vehicle_sub_type_id').val();
        loadVehicles(typeId, subTypeId);
    });

    function loadVehicles(typeId, subTypeId) {
        if (!typeId || !subTypeId) {
            $('#vehicle_id').html('<option value="">Select Vehicle</option>');
            return;
        }

        $.ajax({
            url: "{{ route('vehicles.get-vehicles-names-by-type-and-subtype') }}",
            type: 'GET',
            data: {
                vehicle_type_id: typeId,
                vehicle_sub_type_id: subTypeId
            },
            beforeSend: function() {
                $('#vehicle_id').html('<option>Loading...</option>');
            },
            success: function(response) {
                $('#vehicle_id').empty().append('<option value="">Select Vehicle</option>');
                if (response.success && response.vehicles.length > 0) {
                    $.each(response.vehicles, function(index, vehicle) {
                        $('#vehicle_id').append(
                            $('<option>', {
                                value: vehicle.id,
                                text: vehicle.model_name + ' (' + vehicle.registration_number + ')'
                            })
                        );
                    });
                } else {
                    $('#vehicle_id').append('<option value="">No vehicles found</option>');
                }
            },
            error: function() {
                $('#vehicle_id').html('<option value="">Error loading vehicles</option>');
            }
        });
    }
});