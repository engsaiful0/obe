$(document).ready(function() {
    // Initialize Select2 for dropdowns
    if ($.fn.select2) {
        $('.form-select').select2({
            placeholder: 'Select an option',
            allowClear: true
        });
    }



    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = window.monthlyBillsRoutes.index + "?" + params.toString();
    });

    // Clear filters
    $('.btn-clear-filters').on('click', function() {
        $('#filterForm')[0].reset();
        window.location.href = window.monthlyBillsRoutes.index;
    });

    // Auto-submit filter form on change
    $('#vehicle_id, #bus_type').on('change', function() {
        $('#filterForm').submit();
    });


    // Export functionality
    $('.btn-export').on('click', function() {
        const params = new URLSearchParams($('#filterForm').serialize());
        window.open(`${window.monthlyBillsRoutes.export}?${params.toString()}`, '_blank');
    });

    // Initialize tooltips
    if ($.fn.tooltip) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    if ($.fn.popover) {
        $('[data-bs-toggle="popover"]').popover();
    }
});
