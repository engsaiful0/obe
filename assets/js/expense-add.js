$(document).ready(function () {

    $('#expense-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        const submitBtn = $('#submit-btn');
        const originalBtnHtml = submitBtn.html();

        // Clear old errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');

        // Show spinner instead of button text
        submitBtn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Saving...
        `);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
             success: function (response) {
                 if (response.success) {
                     toastr.success(response.message || 'Expense added successfully!');
                     // redirect to expenses index page
                     const redirectUrl = response.redirect_url || form.data('redirect-url') || '/app/expenses';
                     window.location.href = redirectUrl;
                 } else {
                     toastr.error(response.message || 'Failed to save expense.');
                 }
             },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function (field) {
                        const input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
});
