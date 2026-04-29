/**
 * Semester CRUD — AJAX + spinners
 */
'use strict';

let fvSemester, offCanvasSemester, dtSemester;

const SpinnerUtils = {
    show: function (element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(
            `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`
        );
    },
    hide: function (element) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        var t = element.data('original-text');
        if (t) {
            element.html(t);
        }
    }
};

const AjaxUtils = {
    request: function (options) {
        const defaults = {
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.show(options.spinnerElement, options.spinnerText);
                }
            },
            complete: function () {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
            },
            success: function (response) {
                if (options.onSuccess) {
                    options.onSuccess(response);
                }
            },
            error: function (xhr) {
                var message = 'An error occurred. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
                if (options.onError) {
                    options.onError(xhr);
                }
            }
        };

        return $.ajax($.extend(defaults, options));
    }
};

document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.getElementById('form-semester-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var addBtn = document.querySelector('.create-new');
        var canvas = document.getElementById('add-new-record-semester');

        if (addBtn && canvas) {
            addBtn.addEventListener('click', function () {
                offCanvasSemester =
                    offCanvasSemester || new bootstrap.Offcanvas(canvas);
                formEl.reset();
                $('.semester-form .dt-status').val('Active');
                $('#form-semester-record').removeAttr('data-record-id');
                $('#semesterCanvasTitle').text('New Semester');
                offCanvasSemester.show();
            });
        }
    }, 250);

    fvSemester = FormValidation.formValidation(formEl, {
        fields: {
            program_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a program'
                    }
                }
            },
            semester_name: {
                validators: {
                    notEmpty: {
                        message: 'Semester name is required'
                    }
                }
            },
            semester_order: {
                validators: {
                    notEmpty: {
                        message: 'Semester order is required'
                    },
                    regexp: {
                        regexp: /^[1-9]\d{0,4}$/,
                        message: 'Enter a positive integer (max 32767)'
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
        }
    });
});

$(function () {
    if (typeof window.semesterUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtSemester = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.semesterUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load semesters.');
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
                        if (!row.program) {
                            return '—';
                        }
                        var name = row.program.program_name || '';
                        var code = row.program.program_code ? ' (' + row.program.program_code + ')' : '';
                        return name + code;
                    }
                },
                { data: 'semester_name' },
                { data: 'semester_order' },
                {
                    data: 'status',
                    render: function (data) {
                        var badgeClass =
                            data === 'Active' ? 'bg-label-success' : 'bg-label-secondary';
                        return `<span class="badge ${badgeClass}">${data || '—'}</span>`;
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon semester-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon semester-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[3, 'asc']],
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 10,
            lengthMenu: [7, 10, 25, 50],
            buttons: [
                {
                    text:
                        '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className: 'create-new btn btn-primary waves-effect waves-light'
                }
            ],
            initComplete: function () {
                $('.card-header').first().after('<hr class="my-0">');
                $('div.head-label').html('<h5 class="card-title mb-0">Semesters</h5>');
            }
        });
    }

    if (fvSemester) {
        fvSemester.on('core.form.valid', function () {
            var recordId = $('#form-semester-record').attr('data-record-id');
            var url = window.semesterUrls.store;
            var method = 'POST';
            var msg = 'Semester added successfully.';

            if (recordId) {
                url = window.semesterUrls.update + '/' + recordId;
                method = 'PUT';
                msg = 'Semester updated successfully.';
            }

            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                program_id: $('.dt-program-id').val(),
                semester_name: $('.dt-semester-name').val(),
                semester_order: $('.dt-semester-order').val(),
                status: $('.dt-status').val()
            };

            var $btn = $('#form-semester-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: method,
                data: payload,
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtSemester.ajax.reload(null, false);
                    if (offCanvasSemester) {
                        offCanvasSemester.hide();
                    }
                    $('#form-semester-record').removeAttr('data-record-id');
                    document.getElementById('form-semester-record').reset();
                    $('.semester-form .dt-status').val('Active');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.semester-edit', function () {
        var rd = dtSemester.row($(this).parents('tr')).data();
        var $btn = $(this);
        SpinnerUtils.show($btn, 'Loading...');

        offCanvasSemester =
            offCanvasSemester ||
            new bootstrap.Offcanvas(document.getElementById('add-new-record-semester'));

        setTimeout(function () {
            $('.dt-program-id').val(rd.program_id != null ? String(rd.program_id) : '');
            $('.dt-semester-name').val(rd.semester_name || '');
            $('.dt-semester-order').val(rd.semester_order != null ? rd.semester_order : '');
            $('.dt-status').val(rd.status || 'Active');
            $('#form-semester-record').attr('data-record-id', rd.id);
            $('#semesterCanvasTitle').text('Edit Semester');
            offCanvasSemester.show();
            SpinnerUtils.hide($btn);
        }, 100);
    });

    $('.datatables-basic tbody').on('click', '.semester-delete', function () {
        var rd = dtSemester.row($(this).parents('tr')).data();
        var $del = $(this);

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
        }).then(function (res) {
            if (!res.value) {
                return;
            }
            SpinnerUtils.show($del, 'Deleting...');
            AjaxUtils.request({
                url: window.semesterUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                showSpinner: false,
                onSuccess: function () {
                    dtSemester.row($del.parents('tr')).remove().draw(false);
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Semester deleted successfully.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
