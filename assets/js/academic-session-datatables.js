/**
 * Academic Session CRUD — AJAX + spinners
 */
'use strict';

let fvAcademic, offCanvasAcademic, dtAcademic;

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
        const t = element.data('original-text');
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
                let message = 'An error occurred. Please try again.';
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

function formatDateDisplay(iso) {
    if (!iso) {
        return '—';
    }
    var d = iso.split(/[T\s]/)[0];
    return d || iso;
}

document.addEventListener('DOMContentLoaded', function () {
    const formEl = document.getElementById('form-academic-session-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var newRecord = document.querySelector('.create-new');
        var canvasEl = document.getElementById('add-new-record-academic-session');

        if (newRecord && canvasEl) {
            newRecord.addEventListener('click', function () {
                offCanvasAcademic =
                    offCanvasAcademic || new bootstrap.Offcanvas(canvasEl);
                formEl.reset();
                $('.academic-session-form .dt-status').val('Active');
                $('#form-academic-session-record').removeAttr('data-record-id');
                $('#academicSessionCanvasTitle').text('New Academic Session');
                offCanvasAcademic.show();
            });
        }
    }, 250);

    fvAcademic = FormValidation.formValidation(formEl, {
        fields: {
            session_name: {
                validators: {
                    notEmpty: {
                        message: 'Session name is required'
                    }
                }
            },
            academic_year: {
                validators: {
                    notEmpty: {
                        message: 'Academic year is required'
                    },
                    regexp: {
                        regexp: /^(19|20)\d{2}$/,
                        message: 'Enter a 4-digit year (1990–2100)'
                    }
                }
            },
            start_date: {
                validators: {
                    notEmpty: {
                        message: 'Start date is required'
                    }
                }
            },
            end_date: {
                validators: {
                    notEmpty: {
                        message: 'End date is required'
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
    if (typeof window.academicSessionUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtAcademic = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.academicSessionUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load academic sessions.');
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
                { data: 'session_name' },
                { data: 'academic_year' },
                {
                    data: 'start_date',
                    render: function (data) {
                        return formatDateDisplay(data);
                    }
                },
                {
                    data: 'end_date',
                    render: function (data) {
                        return formatDateDisplay(data);
                    }
                },
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon academic-session-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon academic-session-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[2, 'desc']],
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
                $('div.head-label').html('<h5 class="card-title mb-0">Academic Sessions</h5>');
            }
        });
    }

    if (fvAcademic) {
        fvAcademic.on('core.form.valid', function () {
            var recordId = $('#form-academic-session-record').attr('data-record-id');
            var url = window.academicSessionUrls.store;
            var method = 'POST';
            var msg = 'Academic session added successfully.';

            if (recordId) {
                url = window.academicSessionUrls.update + '/' + recordId;
                method = 'PUT';
                msg = 'Academic session updated successfully.';
            }

            var data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                session_name: $('.dt-session-name').val(),
                academic_year: $('.dt-academic-year').val(),
                start_date: $('.dt-start-date').val(),
                end_date: $('.dt-end-date').val(),
                status: $('.dt-status').val()
            };

            var $btn = $('#form-academic-session-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: method,
                data: data,
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtAcademic.ajax.reload(null, false);
                    if (offCanvasAcademic) {
                        offCanvasAcademic.hide();
                    }
                    $('#form-academic-session-record').removeAttr('data-record-id');
                    document.getElementById('form-academic-session-record').reset();
                    $('.academic-session-form .dt-status').val('Active');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.academic-session-edit', function () {
        var row = dtAcademic.row($(this).parents('tr'));
        var rd = row.data();
        var $editBtn = $(this);
        SpinnerUtils.show($editBtn, 'Loading...');

        setTimeout(function () {
            offCanvasAcademic =
                offCanvasAcademic ||
                new bootstrap.Offcanvas(document.getElementById('add-new-record-academic-session'));
            $('.dt-session-name').val(rd.session_name || '');
            $('.dt-academic-year').val(rd.academic_year != null ? rd.academic_year : '');
            $('.dt-start-date').val(formatDateDisplay(rd.start_date));
            $('.dt-end-date').val(formatDateDisplay(rd.end_date));
            $('.dt-status').val(rd.status || 'Active');
            $('#form-academic-session-record').attr('data-record-id', rd.id);
            $('#academicSessionCanvasTitle').text('Edit Academic Session');
            offCanvasAcademic.show();
            SpinnerUtils.hide($editBtn);
        }, 100);
    });

    $('.datatables-basic tbody').on('click', '.academic-session-delete', function () {
        var row = dtAcademic.row($(this).parents('tr'));
        var rd = row.data();
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
                url: window.academicSessionUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                showSpinner: false,
                onSuccess: function () {
                    dtAcademic.row($del.parents('tr')).remove().draw(false);
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Academic session deleted.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
