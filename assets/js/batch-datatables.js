/**
 * Batch CRUD — AJAX + spinners
 */
'use strict';

let fvBatch, offCanvasBatch, dtBatch;

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

function formatDateInput(iso) {
    if (!iso) {
        return '';
    }
    return String(iso).split(/[T\s]/)[0];
}

function legacyStatusBadge(status) {
    if (!status) {
        return '—';
    }
    var cls = 'bg-label-secondary';
    if (status === 'Running') {
        cls = 'bg-label-success';
    } else if (status === 'Completed') {
        cls = 'bg-label-info';
    } else if (status === 'Inactive') {
        cls = 'bg-label-secondary';
    }
    return `<span class="badge ${cls}">${status}</span>`;
}

function batchStatusBadgeFromRow(row) {
    var rel = row.batch_status || row.batchStatus;
    var name = rel && rel.status_name ? rel.status_name : '';
    if (!name && row.status) {
        return legacyStatusBadge(row.status);
    }
    if (!name) {
        return '—';
    }
    var escaped = $('<div/>').text(name).html();
    var cls = 'bg-label-primary';
    var s = (row.status || '').toLowerCase();
    if (s === 'running') {
        cls = 'bg-label-success';
    } else if (s === 'completed') {
        cls = 'bg-label-info';
    } else if (s === 'inactive') {
        cls = 'bg-label-secondary';
    }
    return `<span class="badge ${cls}">${escaped}</span>`;
}

document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.getElementById('form-batch-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var addBtn = document.querySelector('.create-new');
        var canvas = document.getElementById('add-new-record-batch');

        if (addBtn && canvas) {
            addBtn.addEventListener('click', function () {
                offCanvasBatch =
                    offCanvasBatch || new bootstrap.Offcanvas(canvas);
                formEl.reset();
                $('.batch-form .dt-batch-status-id').val('');
                $('#form-batch-record').removeAttr('data-record-id');
                $('#batchCanvasTitle').text('New Batch');
                offCanvasBatch.show();
            });
        }
    }, 250);

    fvBatch = FormValidation.formValidation(formEl, {
        fields: {
            program_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a program'
                    }
                }
            },
            batch_name: {
                validators: {
                    notEmpty: {
                        message: 'Batch name is required'
                    }
                }
            },
            batch_code: {
                validators: {
                    notEmpty: {
                        message: 'Batch code is required'
                    }
                }
            },
            academic_session_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select admission session'
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
            expected_passing_year: {
                validators: {
                    notEmpty: {
                        message: 'Expected passing year is required'
                    },
                    regexp: {
                        regexp: /^(19|20)\d{2}$/,
                        message: 'Enter a 4-digit year'
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
    if (typeof window.batchUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtBatch = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.batchUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load batches.');
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
                        var n = row.program.program_name || '';
                        var c = row.program.program_code ? ' (' + row.program.program_code + ')' : '';
                        return n + c;
                    }
                },
                { data: 'batch_name' },
                { data: 'batch_code' },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (!row.academic_session) {
                            return '—';
                        }
                        var s = row.academic_session.session_name || '';
                        var y = row.academic_session.academic_year != null ? ' — ' + row.academic_session.academic_year : '';
                        return s + y;
                    }
                },
                {
                    data: 'start_date',
                    render: function (d) {
                        return formatDateInput(d) || '—';
                    }
                },
                { data: 'expected_passing_year' },
                {
                    data: null,
                    render: function (data, type, row) {
                        return batchStatusBadgeFromRow(row);
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon batch-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon batch-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[5, 'desc']],
            responsive: true,
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
                $('div.head-label').html('<h5 class="card-title mb-0">Batches</h5>');
            }
        });
    }

    if (fvBatch) {
        fvBatch.on('core.form.valid', function () {
            var recordId = $('#form-batch-record').attr('data-record-id');
            var url = window.batchUrls.store;
            var method = 'POST';
            var msg = 'Batch added successfully.';

            if (recordId) {
                url = window.batchUrls.update + '/' + recordId;
                method = 'PUT';
                msg = 'Batch updated successfully.';
            }

            var statusIdRaw = $('.dt-batch-status-id').val();
            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                program_id: $('.dt-program-id').val(),
                batch_name: $('.dt-batch-name').val(),
                batch_code: $('.dt-batch-code').val(),
                academic_session_id: $('.dt-academic-session-id').val(),
                start_date: $('.dt-start-date').val(),
                expected_passing_year: $('.dt-expected-year').val(),
                status_id: parseInt(statusIdRaw, 10)
            };

            var $btn = $('#form-batch-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: method,
                data: payload,
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtBatch.ajax.reload(null, false);
                    if (offCanvasBatch) {
                        offCanvasBatch.hide();
                    }
                    $('#form-batch-record').removeAttr('data-record-id');
                    document.getElementById('form-batch-record').reset();
                    $('.batch-form .dt-batch-status-id').val('');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.batch-edit', function () {
        var rd = dtBatch.row($(this).parents('tr')).data();
        var $btn = $(this);
        SpinnerUtils.show($btn, 'Loading...');

        offCanvasBatch =
            offCanvasBatch ||
            new bootstrap.Offcanvas(document.getElementById('add-new-record-batch'));

        setTimeout(function () {
            $('.dt-program-id').val(rd.program_id != null ? String(rd.program_id) : '');
            $('.dt-batch-name').val(rd.batch_name || '');
            $('.dt-batch-code').val(rd.batch_code || '');
            $('.dt-academic-session-id').val(
                rd.academic_session_id != null ? String(rd.academic_session_id) : ''
            );
            $('.dt-start-date').val(formatDateInput(rd.start_date));
            $('.dt-expected-year').val(
                rd.expected_passing_year != null ? rd.expected_passing_year : ''
            );
            $('.dt-batch-status-id').val(
                rd.status_id != null && rd.status_id !== ''
                    ? String(rd.status_id)
                    : ''
            );
            $('#form-batch-record').attr('data-record-id', rd.id);
            $('#batchCanvasTitle').text('Edit Batch');
            offCanvasBatch.show();
            SpinnerUtils.hide($btn);
        }, 100);
    });

    $('.datatables-basic tbody').on('click', '.batch-delete', function () {
        var rd = dtBatch.row($(this).parents('tr')).data();
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
                url: window.batchUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                showSpinner: false,
                onSuccess: function () {
                    dtBatch.row($del.parents('tr')).remove().draw(false);
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Batch deleted successfully.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
