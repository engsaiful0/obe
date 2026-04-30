/**
 * Course CRUD — AJAX + spinners + Program → Semester cascade
 */
'use strict';

let fvCourse, offCanvasCourse, dtCourse;

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

function refreshSemesterOptions(programId, preserveSemesterId) {
    var $sem = $('.course-form .dt-semester-id');
    $sem.empty();

    if (!programId) {
        $sem.append('<option value="">Select program first</option>');
        $sem.prop('disabled', true);
        return;
    }

    $sem.append('<option value="">Select semester</option>');
    $sem.prop('disabled', false);

    if (!window.courseSemesters || !window.courseSemesters.length) {
        return;
    }

    window.courseSemesters.forEach(function (s) {
        if (String(s.program_id) !== String(programId)) {
            return;
        }
        var label =
            (s.semester_name || '') +
            (s.semester_order != null ? ' (Order: ' + s.semester_order + ')' : '');
        var opt = $('<option></option>').attr('value', s.id).text(label);
        if (
            preserveSemesterId != null &&
            preserveSemesterId !== '' &&
            String(s.id) === String(preserveSemesterId)
        ) {
            opt.prop('selected', true);
        }
        $sem.append(opt);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.getElementById('form-course-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var addBtn = document.querySelector('.create-new');
        var canvas = document.getElementById('add-new-record-course');

        if (addBtn && canvas) {
            addBtn.addEventListener('click', function () {
                offCanvasCourse =
                    offCanvasCourse || new bootstrap.Offcanvas(canvas);
                formEl.reset();
                $('.course-form .dt-status').val('Active');
                $('.course-form .dt-course-type').val('Theory');
                $('.course-form .dt-program-id').val('');
                refreshSemesterOptions('', null);
                $('#form-course-record').removeAttr('data-record-id');
                $('#courseCanvasTitle').text('New Course');
                offCanvasCourse.show();
            });
        }

        $('.course-form .dt-program-id')
            .off('change.courseSem')
            .on('change.courseSem', function () {
                refreshSemesterOptions($(this).val(), null);
            });
    }, 250);

    fvCourse = FormValidation.formValidation(formEl, {
        fields: {
            program_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a program'
                    }
                }
            },
            semester_id: {
                validators: {
                    notEmpty: {
                        message: 'Please select a semester'
                    }
                }
            },
            course_code: {
                validators: {
                    notEmpty: {
                        message: 'Course code is required'
                    }
                }
            },
            course_title: {
                validators: {
                    notEmpty: {
                        message: 'Course title is required'
                    }
                }
            },
            credit: {
                validators: {
                    notEmpty: {
                        message: 'Credit is required'
                    }
                }
            },
            course_type: {
                validators: {
                    notEmpty: {
                        message: 'Course type is required'
                    }
                }
            },
            contact_hour: {
                validators: {
                    notEmpty: {
                        message: 'Contact hour is required'
                    }
                }
            },
            marks: {
                validators: {
                    notEmpty: {
                        message: 'Marks are required'
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
    if (typeof window.courseUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtCourse = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.courseUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load courses.');
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
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.semester && row.semester.semester_name
                            ? row.semester.semester_name
                            : '—';
                    }
                },
                { data: 'course_code' },
                {
                    data: 'course_title',
                    render: function (d) {
                        return d && d.length > 40 ? d.substring(0, 40) + '…' : d || '—';
                    }
                },
                {
                    data: 'credit',
                    render: function (d) {
                        if (d === null || d === undefined || d === '') {
                            return '—';
                        }
                        return parseFloat(d).toFixed(2);
                    }
                },
                {
                    data: 'course_type',
                    render: function (d) {
                        return d
                            ? '<span class="badge bg-label-primary">' + d + '</span>'
                            : '—';
                    }
                },
                { data: 'contact_hour' },
                { data: 'marks' },
                {
                    data: 'status',
                    render: function (d) {
                        var cls =
                            d === 'Active' ? 'bg-label-success' : 'bg-label-secondary';
                        return `<span class="badge ${cls}">${d || '—'}</span>`;
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon course-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon course-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[3, 'asc']],
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
                $('div.head-label').html('<h5 class="card-title mb-0">Courses</h5>');
            }
        });
    }

    if (fvCourse) {
        fvCourse.on('core.form.valid', function () {
            var recordId = $('#form-course-record').attr('data-record-id');
            var url = window.courseUrls.store;
            var method = 'POST';
            var msg = 'Course added successfully.';

            if (recordId) {
                url = window.courseUrls.update + '/' + recordId;
                method = 'PUT';
                msg = 'Course updated successfully.';
            }

            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                program_id: $('.dt-program-id').val(),
                semester_id: $('.dt-semester-id').val(),
                course_code: $('.dt-course-code').val(),
                course_title: $('.dt-course-title').val(),
                credit: $('.dt-credit').val(),
                course_type: $('.dt-course-type').val(),
                contact_hour: $('.dt-contact-hour').val(),
                marks: $('.dt-marks').val(),
                status: $('.dt-status').val()
            };

            var $btn = $('#form-course-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: method,
                data: payload,
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtCourse.ajax.reload(null, false);
                    if (offCanvasCourse) {
                        offCanvasCourse.hide();
                    }
                    $('#form-course-record').removeAttr('data-record-id');
                    document.getElementById('form-course-record').reset();
                    $('.course-form .dt-status').val('Active');
                    $('.course-form .dt-course-type').val('Theory');
                    $('.course-form .dt-program-id').val('');
                    refreshSemesterOptions('', null);
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.course-edit', function () {
        var rd = dtCourse.row($(this).parents('tr')).data();
        var $btn = $(this);
        SpinnerUtils.show($btn, 'Loading...');

        offCanvasCourse =
            offCanvasCourse ||
            new bootstrap.Offcanvas(document.getElementById('add-new-record-course'));

        setTimeout(function () {
            $('.dt-program-id').val(rd.program_id != null ? String(rd.program_id) : '');
            refreshSemesterOptions(rd.program_id, rd.semester_id);
            $('.dt-course-code').val(rd.course_code || '');
            $('.dt-course-title').val(rd.course_title || '');
            $('.dt-credit').val(
                rd.credit !== null && rd.credit !== undefined ? rd.credit : ''
            );
            $('.dt-course-type').val(rd.course_type || 'Theory');
            $('.dt-contact-hour').val(
                rd.contact_hour !== null && rd.contact_hour !== undefined
                    ? rd.contact_hour
                    : ''
            );
            $('.dt-marks').val(rd.marks !== null && rd.marks !== undefined ? rd.marks : '');
            $('.dt-status').val(rd.status || 'Active');
            $('#form-course-record').attr('data-record-id', rd.id);
            $('#courseCanvasTitle').text('Edit Course');
            offCanvasCourse.show();
            SpinnerUtils.hide($btn);
        }, 100);
    });

    $('.datatables-basic tbody').on('click', '.course-delete', function () {
        var rd = dtCourse.row($(this).parents('tr')).data();
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
                url: window.courseUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                showSpinner: false,
                onSuccess: function () {
                    dtCourse.row($del.parents('tr')).remove().draw(false);
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Course deleted successfully.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
