/**
 * Department CRUD with AJAX and Spinner
 * Enhanced with proper spinner utilities and error handling
 */

'use strict';

let fv, offCanvasEl, dt_basic;

// Utility functions for spinner and loading states
const SpinnerUtils = {
    show: function(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`);
    },

    hide: function(element, originalText = null) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        const text = originalText || element.data('original-text');
        if (text) {
            element.html(text);
        }
    },

    showTable: function() {
        if (dt_basic && typeof dt_basic.processing === 'function') {
            dt_basic.processing(true);
        }
    },

    hideTable: function() {
        if (dt_basic && typeof dt_basic.processing === 'function') {
            dt_basic.processing(false);
        }
    }
};

const AjaxUtils = {
    request: function(options) {
        const defaults = {
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.show(options.spinnerElement, options.spinnerText);
                }
                if (options.showTableSpinner) {
                    SpinnerUtils.showTable();
                }
            },
            complete: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
                if (options.showTableSpinner) {
                    SpinnerUtils.hideTable();
                }
            },
            success: function(response) {
                if (options.onSuccess) {
                    options.onSuccess(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                AjaxUtils.handleError(xhr, options);
            }
        };

        return $.ajax($.extend(defaults, options));
    },

    handleError: function(xhr, options = {}) {
        let message = 'An error occurred. Please try again.';

        if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                message = Object.values(errors).flat().join('<br>');
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }

        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert('Error: ' + message);
        }

        if (options.onError) {
            options.onError(xhr, message);
        }
    }
};

function resetDepartmentForm() {
    const form = document.getElementById('form-add-new-record');
    if (!form) {
        return;
    }
    form.reset();
    $('#faculty_id').val('');
}

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const formAddNewRecord = document.getElementById('form-add-new-record');

        setTimeout(() => {
            const newRecord = document.querySelector('.create-new');
            const offCanvasElement = document.querySelector('#add-new-record');

            if (newRecord) {
                newRecord.addEventListener('click', function () {
                    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                    resetDepartmentForm();
                    $('#form-add-new-record').removeAttr('data-id');
                    document.querySelector('#exampleModalLabel').textContent = 'New Department';
                    offCanvasEl.show();
                });
            }
        }, 200);

        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                faculty_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a faculty'
                        }
                    }
                },
                department_name: {
                    validators: {
                        notEmpty: {
                            message: 'The department name is required'
                        }
                    }
                },
              
                status: {
                    validators: {
                        notEmpty: {
                            message: 'Status is required'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-sm-12'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            },
            init: instance => {
                instance.on('plugins.message.placed', function (e) {
                    if (e.element.parentElement && e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
            }
        });
    })();
});

$(function () {
    if (typeof window.departmentUrls === 'undefined') {
        console.error('Department URLs not defined');
        return;
    }

    var dt_basic_table = $('.datatables-basic');

    if (dt_basic_table.length) {
        dt_basic = dt_basic_table.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.departmentUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr) {
                    console.error('DataTable AJAX Error:', xhr);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load departments. Please try again.');
                    }
                }
            },
            columns: [
                {
                    data: 'id',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.faculty && row.faculty.faculty_name ? row.faculty.faculty_name : '—';
                    }
                },
                { data: 'name' },
                {
                    data: 'department_code',
                    render: function (data) {
                        return data != null && data !== '' ? data : '—';
                    }
                },
                {
                    data: 'head_chairman_name',
                    render: function (data) {
                        return data != null && data !== '' ? data : '—';
                    }
                },
                {
                    data: 'email',
                    render: function (data) {
                        return data != null && data !== '' ? data : '—';
                    }
                },
                {
                    data: 'phone',
                    render: function (data) {
                        return data != null && data !== '' ? data : '—';
                    }
                },
                {
                    data: 'status',
                    render: function (data) {
                        const label = data || '';
                        const badgeClass =
                            label === 'Active'
                                ? 'bg-label-success'
                                : label === 'Inactive'
                                    ? 'bg-label-secondary'
                                    : 'bg-label-primary';
                        return `<span class="badge ${badgeClass}">${label || '—'}</span>`;
                    }
                },
                { data: null, orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: -1,
                    title: 'Actions',
                    render: function () {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon department-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[0, 'desc']],
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            language: {
                paginate: {
                    next: '<i class="ti ti-chevron-right ti-sm"></i>',
                    previous: '<i class="ti ti-chevron-left ti-sm"></i>'
                }
            },
            buttons: [
                {
                    text:
                        '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className: 'create-new btn btn-primary waves-effect waves-light'
                }
            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details of ' + (data['name'] || '');
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col) {
                            return col.title !== ''
                                ? '<tr data-dt-row="' +
                                  col.rowIndex +
                                  '" data-dt-column="' +
                                  col.columnIndex +
                                  '">' +
                                  '<td>' +
                                  col.title +
                                  ':' +
                                  '</td> ' +
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
            initComplete: function () {
                $('.card-header').after('<hr class="my-0">');
            }
        });
        $('div.head-label').html('<h5 class="card-title mb-0">Departments</h5>');
    }

    fv.on('core.form.valid', function () {
        var id = $('#form-add-new-record').attr('data-id');

        var url = window.departmentUrls.store;
        var method = 'POST';
        var message = 'Department added successfully.';

        var data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            faculty_id: $('.dt-faculty-id').val(),
            department_name: $('.dt-department-name').val(),
            department_code: $('.dt-department-code').val(),
            head_chairman_name: $('.dt-head-name').val(),
            email: $('.dt-email').val(),
            phone: $('.dt-phone').val(),
            status: $('.dt-status').val()
        };

        if (id) {
            url = window.departmentUrls.update + '/' + id;
            method = 'PUT';
            message = 'Department updated successfully.';
        }

        var $submitBtn = $('#form-add-new-record button[type="submit"]');

        AjaxUtils.request({
            url: url,
            type: method,
            data: data,
            showSpinner: true,
            spinnerElement: $submitBtn,
            spinnerText: id ? 'Updating...' : 'Saving...',
            showTableSpinner: false,
            onSuccess: function () {
                if (dt_basic && typeof dt_basic.ajax !== 'undefined' && typeof dt_basic.ajax.reload === 'function') {
                    dt_basic.ajax.reload(null, false);
                }
                if (offCanvasEl) {
                    offCanvasEl.hide();
                }
                $('#form-add-new-record').removeAttr('data-id');
                resetDepartmentForm();

                if (typeof toastr !== 'undefined') {
                    toastr.success(message);
                }
            }
        });
    });

    $('.datatables-basic tbody').on('click', '.delete-record', function () {
        var row = dt_basic.row($(this).parents('tr'));
        var data = row.data();
        var $deleteBtn = $(this);

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
                SpinnerUtils.show($deleteBtn, 'Deleting...');

                AjaxUtils.request({
                    url: window.departmentUrls.destroy + '/' + data.id,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    showSpinner: false,
                    showTableSpinner: false,
                    onSuccess: function () {
                        if (dt_basic && typeof dt_basic.row === 'function') {
                            dt_basic.row($deleteBtn.closest('tr')).remove().draw();
                        }
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Department deleted successfully.');
                        }
                        SpinnerUtils.hide($deleteBtn);
                    },
                    onError: function () {
                        SpinnerUtils.hide($deleteBtn);
                    }
                });
            }
        });
    });

    $('.datatables-basic tbody').on('click', '.department-edit', function () {
        var row = dt_basic.row($(this).parents('tr'));
        var data = row.data();
        var $editBtn = $(this);

        SpinnerUtils.show($editBtn, 'Loading...');

        setTimeout(function () {
            offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
            $('#faculty_id').val(data.faculty_id != null ? String(data.faculty_id) : '');
            $('.dt-department-name').val(data.name || '');
            $('.dt-department-code').val(data.department_code || '');
            $('.dt-head-name').val(data.head_chairman_name || '');
            $('.dt-email').val(data.email || '');
            $('.dt-phone').val(data.phone || '');
            $('.dt-status').val(data.status || 'Active');
            $('#form-add-new-record').attr('data-id', data.id);
            document.querySelector('#exampleModalLabel').textContent = 'Edit Department';
            offCanvasEl.show();

            SpinnerUtils.hide($editBtn);
        }, 100);
    });
});
