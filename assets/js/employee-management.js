'use strict';

$(function () {
    var dt_responsive = $('#employee-datatable'),
        dt_basic;
    if (dt_responsive.length) {
        dt_basic = dt_responsive.DataTable({
            ajax: {
                url: window.employeeAjaxUrl || '/polytechnic/test-employees',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                { data: null }, // Sr. No
                { data: 'employee_name' },
                { data: 'picture' },
                { data: 'designation' },
                { data: 'email' },
                { data: 'mobile' },
                { data: null } // Actions
            ],
            columnDefs: [
                {
                    // Sr. No
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    // Profile Picture
                    targets: 2,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        if (data) {
                            return '<img src="/polytechnic/profile_pictures/' + data + '" alt="' + full.employee_name + '" class="rounded-circle" width="40" height="40">';
                        } else {
                            return '<img src="/polytechnic/images/default-avatar.png" alt="default" class="rounded-circle" width="40" height="40">';
                        }
                    }
                },
                {
                    // Designation
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return full.designation ? full.designation.designation_name : 'N/A';
                    }
                },
                {
                    // Actions
                    targets: -1,
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var editUrl = '/polytechnic/app/employees/' + full.id + '/edit';
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="' + editUrl + '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon teacher-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-employee" data-id="' + full.id + '" data-url="/polytechnic/app/employees/' + full.id + '"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[0, 'desc']],
            dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            language: {
                paginate: {
                    next: '<i class="ti ti-chevron-right ti-sm"></i>',
                    previous: '<i class="ti ti-chevron-left ti-sm"></i>'
                }
            },
            buttons: [

            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details of ' + data['employee_name'];
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== ''
                                ? '<tr data-dt-row="' +
                                col.rowIndex +
                                '" data-dt-column="' +
                                col.columnIndex +
                                '">' +
                                '<td>' +
                                col.title +
                                ':</td> ' +
                                '<td>' +
                                col.data +
                                '</td>' +
                                '</tr>'
                                : '';
                        }).join('');

                        return data ? $('<table class="table"/><tbody />').append(data) : false;
                    }
                }
            },
            initComplete: function (settings, json) {
                $('.card-header').after('<hr class="my-0">');
            }
        });
    }

    // Delete Record
    $('#employee-datatable').on('click', '.delete-employee', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message);
                            dt_basic.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (error) {
                        toastr.error('Something went wrong!');
                    }
                });
            }
        });
    });

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // create employee
    // create or edit employee
    $('#createEmployeeForm, #editEmployeeForm').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');
        var data = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var spinner = submitBtn.find('.spinner-border');

        // Check if form has method spoofing (PUT/PATCH)
        var method = form.find('input[name="_method"]').val();
        if (method) {
            // Method spoofing is already in FormData, use POST
            var ajaxMethod = 'POST';
        } else {
            var ajaxMethod = 'POST';
        }

        // disable entire form
        form.find(':input').prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: url,
            method: ajaxMethod,
            data: data,
            processData: false,
            contentType: false,
            success: function (response) {
                // enable form again
                form.find(':input').prop('disabled', false);
                spinner.addClass('d-none');

                if (response.success) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 2000,
                        closeButton: true
                    });

                    // redirect after short delay so user sees the toast
                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = form.data('redirect-url') || '/app/employees/view-employee';
                        }
                    }, 2000);
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 5000,
                        closeButton: true
                    });
                }
            },
            error: function (xhr) {
                // enable form again
                form.find(':input').prop('disabled', false);
                spinner.addClass('d-none');

                var response = xhr.responseJSON;
                var errorMessages = '';

                // Check if response has a message
                if (response && response.message) {
                    errorMessages = response.message;
                } else if (response && response.errors) {
                    // Handle validation errors
                    $.each(response.errors, function (key, value) {
                        errorMessages += value[0] + '<br>';
                    });
                } else {
                    errorMessages = 'An unknown error occurred.';
                }

                toastr.error(errorMessages, 'Error', {
                    timeOut: 5000,
                    closeButton: true,
                    escapeHtml: false
                });
            }
        });
    });

});

