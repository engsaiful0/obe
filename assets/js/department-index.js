/**
 * Department Index Page - AJAX Delete Operations
 */

'use strict';

$(document).ready(function() {
    // Delete Department
    $(document).on('click', '.delete-record', function(e) {
        e.preventDefault();
        const departmentId = $(this).data('id');
        const deleteUrl = window.departmentUrls.destroy + departmentId;
        
        // Confirm deletion
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {
                // Show spinner
                $('#departments-spinner').removeClass('d-none');
                
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#departments-spinner').addClass('d-none');
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Department deleted successfully.');
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Department deleted successfully.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        }
                        
                        // Reload the page to refresh the table
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        $('#departments-spinner').addClass('d-none');
                        
                        const message = xhr.responseJSON?.message || 'An error occurred while deleting the department.';
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: message,
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    }
                });
            }
        });
    });
});
