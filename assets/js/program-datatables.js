/**
 * Program CRUD — AJAX + spinners + Faculty → Department cascade
 */
'use strict';

let fvProgram, programOffCanvas, dtProgram;

const SpinnerUtils = {
    show: function (element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-html', element.html());
        element.html(
            `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`
        );
    },

    hide: function (element) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        var html = element.data('original-html');
        if (html) {
            element.html(html);
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
                var message = 'An error occurred.';
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

/**
 * Populate department dropdown for selected faculty (optionally keep one selected).
 */
function refreshDepartmentOptions(facultyId, preserveDepartmentId) {
    var $dep = $('.program-form .dt-department-id');
    $dep.empty();

    var placeholder =
        facultyId !== '' && facultyId !== null
            ? '<option value="">Select department</option>'
            : '<option value="">Select faculty first</option>';
    $dep.append(placeholder);

    if (facultyId === '' || facultyId === null) {
        $dep.prop('disabled', true);
        return;
    }

    $dep.prop('disabled', false);

    if (!window.programDepartments || !window.programDepartments.length) {
        return;
    }

    window.programDepartments.forEach(function (d) {
        if (String(d.faculty_id) !== String(facultyId)) {
            return;
        }
        var opt = $('<option></option>').attr('value', d.id).text(d.name);
        if (
            preserveDepartmentId != null &&
            preserveDepartmentId !== '' &&
            String(d.id) === String(preserveDepartmentId)
        ) {
            opt.prop('selected', true);
        }
        $dep.append(opt);
    });
}

function resetProgramForm() {
    var form = document.getElementById('form-program-record');
    if (!form) {
        return;
    }
    form.reset();
    $('#form-program-record').removeAttr('data-record-id');
    $('.program-form .dt-degree-level').val('Bachelor');
    $('.program-form .dt-status').val('Active');
    $('.program-form .dt-faculty-id').val('');
    $('.program-form .dt-department-id')
        .empty()
        .append('<option value="">Select faculty first</option>')
        .prop('disabled', true);

    $('#programCanvasTitle').text('New Program');
}

document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.getElementById('form-program-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var newRecord = document.querySelector('.create-new');
        var offCanvasElProgram = document.getElementById('add-new-record-program');

        if (newRecord && offCanvasElProgram) {
            newRecord.addEventListener('click', function () {
                programOffCanvas =
                    programOffCanvas ||
                    new bootstrap.Offcanvas(offCanvasElProgram);
                resetProgramForm();
                programOffCanvas.show();
            });
        }

        $('.program-form .dt-faculty-id')
            .off('change.programDept')
            .on('change.programDept', function () {
                var fid = $(this).val();
                refreshDepartmentOptions(fid, null);
            });
    }, 300);

    fvProgram = FormValidation.formValidation(formEl, {
        fields: {
            faculty_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a faculty'
                    }
                }
            },
            department_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a department'
                    }
                }
            },
            program_name: {
                validators: {
                    notEmpty: {
                        message: 'Program name is required'
                    }
                }
            },
            program_code: {
                validators: {
                    notEmpty: {
                        message: 'Program code is required'
                    }
                }
            },
            degree_level: {
                validators: {
                    notEmpty: {
                        message: 'Degree level is required'
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
        init: function (instance) {
            instance.on('plugins.message.placed', function (e) {
                if (
                    e.element.parentElement &&
                    e.element.parentElement.classList.contains('input-group')
                ) {
                    e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
            });
        }
    });
});

$(function () {
    if (typeof window.programUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtProgram = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.programUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load programs.');
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
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.department && row.department.name ? row.department.name : '—';
                    }
                },
                { data: 'program_name' },
                { data: 'program_code' },
                {
                    data: 'degree_level',
                    render: function (d) {
                        return d ? `<span class="badge bg-label-info">${d}</span>` : '—';
                    }
                },
                {
                    data: 'duration',
                    render: function (d) {
                        return d != null && d !== '' ? d : '—';
                    }
                },
                {
                    data: 'total_semester',
                    render: function (d) {
                        return d !== null && d !== undefined && d !== '' ? d : '—';
                    }
                },
                {
                    data: 'total_credit',
                    render: function (d) {
                        return d !== null && d !== undefined && d !== '' ? d : '—';
                    }
                },
                {
                    data: 'status',
                    render: function (data) {
                        var label = data || '';
                        var badgeClass =
                            label === 'Active' ? 'bg-label-success' : 'bg-label-secondary';
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon program-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon program-delete"><i class="ti ti-trash ti-md"></i></a>' +
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
            responsive: true,
            initComplete: function () {
                $('.card-header').first().after('<hr class="my-0">');
                $('div.head-label').html('<h5 class="card-title mb-0">Programs</h5>');
            }
        });
    }

    if (fvProgram) {
        fvProgram.on('core.form.valid', function () {
            var recordId = $('#form-program-record').attr('data-record-id');
            var url = window.programUrls.store;
            var method = 'POST';
            var toastMsg = 'Program added successfully.';

            if (recordId) {
                url = window.programUrls.update + '/' + recordId;
                method = 'PUT';
                toastMsg = 'Program updated successfully.';
            }

            var data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                faculty_id: $('.program-form .dt-faculty-id').val(),
                department_id: $('.program-form .dt-department-id').val(),
                program_name: $('.program-form .dt-program-name').val(),
                program_code: $('.program-form .dt-program-code').val(),
                degree_level: $('.program-form .dt-degree-level').val(),
                duration: $('.program-form .dt-duration').val(),
                total_semester:
                    $('.program-form .dt-total-semester').val() === ''
                        ? null
                        : $('.program-form .dt-total-semester').val(),
                total_credit:
                    $('.program-form .dt-total-credit').val() === ''
                        ? null
                        : $('.program-form .dt-total-credit').val(),
                status: $('.program-form .dt-status').val()
            };

            var $submitBtn = $('#form-program-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: method,
                data: data,
                showSpinner: true,
                spinnerElement: $submitBtn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    if (dtProgram && dtProgram.ajax) {
                        dtProgram.ajax.reload(null, false);
                    }
                    if (programOffCanvas) {
                        programOffCanvas.hide();
                    }
                    resetProgramForm();
                    if (typeof toastr !== 'undefined') {
                        toastr.success(toastMsg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.program-delete', function () {
        var row = dtProgram.row($(this).parents('tr'));
        var rd = row.data();
        var $btn = $(this);

        Swal.fire({
            title: 'Are you sure?',
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
            SpinnerUtils.show($btn, 'Deleting...');
            AjaxUtils.request({
                url: window.programUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                showSpinner: false,
                onSuccess: function () {
                    dtProgram.row($btn.parents('tr')).remove().draw(false);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Program deleted.');
                    }
                    SpinnerUtils.hide($btn);
                },
                onError: function () {
                    SpinnerUtils.hide($btn);
                }
            });
        });
    });

    $('.datatables-basic tbody').on('click', '.program-edit', function () {
        var rd = dtProgram.row($(this).parents('tr')).data();
        var $btn = $(this);
        SpinnerUtils.show($btn, 'Loading...');

        programOffCanvas =
            programOffCanvas ||
            new bootstrap.Offcanvas(document.getElementById('add-new-record-program'));

        setTimeout(function () {
            $('#programCanvasTitle').text('Edit Program');
            $('#form-program-record').attr('data-record-id', rd.id);

            $('.program-form .dt-faculty-id').val(rd.faculty_id != null ? String(rd.faculty_id) : '');
            refreshDepartmentOptions(rd.faculty_id, rd.department_id);

            $('.program-form .dt-program-name').val(rd.program_name || '');
            $('.program-form .dt-program-code').val(rd.program_code || '');
            $('.program-form .dt-degree-level').val(rd.degree_level || 'Bachelor');
            $('.program-form .dt-duration').val(rd.duration || '');
            $('.program-form .dt-total-semester').val(
                rd.total_semester !== null && rd.total_semester !== undefined ? rd.total_semester : ''
            );
            $('.program-form .dt-total-credit').val(
                rd.total_credit !== null && rd.total_credit !== undefined ? rd.total_credit : ''
            );
            $('.program-form .dt-status').val(rd.status || 'Active');

            programOffCanvas.show();
            SpinnerUtils.hide($btn);

            fvProgram.resetForm();
            fvProgram.resetField('faculty_id');
            fvProgram.resetField('department_id');
            fvProgram.resetField('program_name');
            fvProgram.resetField('program_code');
            fvProgram.resetField('degree_level');
            fvProgram.resetField('status');
        }, 120);
    });
});
