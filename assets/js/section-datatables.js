/**
 * Section CRUD — AJAX + spinners + Faculty → Department → Program → Semester cascade
 */
'use strict';

let fvSection, offCanvasSection, dtSection;
let facultiesCachePromise = null;

const SpinnerUtils = {
    show: function (element, text) {
        text = text || 'Loading...';
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                text
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
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
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

function getJson(url, data) {
    return $.ajax({
        url: url,
        type: 'GET',
        data: data,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

function appendMissingOption($select, id, entity, labelFn) {
    if (id == null || id === '' || !entity) {
        return;
    }
    var sid = String(id);
    if ($select.find('option[value="' + sid.replace(/"/g, '\\"') + '"]').length) {
        return;
    }
    var label = labelFn(entity) + ' (inactive)';
    $select.append($('<option></option>').attr('value', sid).text(label));
}

function fillFacultySelect(allRows, preserveRow) {
    var $sel = $('.section-form .dt-faculty-id');
    $sel.empty().append('<option value="">Select faculty</option>');
    var list = (allRows || []).filter(function (f) {
        return f.status === 'Active';
    });
    list.forEach(function (f) {
        var label =
            (f.faculty_name || '') + (f.faculty_code ? ' (' + f.faculty_code + ')' : '');
        $sel.append($('<option></option>').attr('value', f.id).text(label));
    });
    if (preserveRow && preserveRow.faculty) {
        appendMissingOption($sel, preserveRow.faculty_id, preserveRow.faculty, function (e) {
            return (e.faculty_name || '') + (e.faculty_code ? ' (' + e.faculty_code + ')' : '');
        });
    }
    if (preserveRow && preserveRow.faculty_id != null) {
        $sel.val(String(preserveRow.faculty_id));
    }
}

function fillDepartmentSelect(rows, preserveRow) {
    var $sel = $('.section-form .dt-department-id');
    $sel.empty().append('<option value="">Select department</option>');
    (rows || []).forEach(function (d) {
        var label = (d.name || '') + (d.department_code ? ' (' + d.department_code + ')' : '');
        $sel.append($('<option></option>').attr('value', d.id).text(label));
    });
    if (preserveRow && preserveRow.department) {
        appendMissingOption($sel, preserveRow.department_id, preserveRow.department, function (e) {
            return (e.name || '') + (e.department_code ? ' (' + e.department_code + ')' : '');
        });
    }
    $sel.prop('disabled', false);
    if (preserveRow && preserveRow.department_id != null) {
        $sel.val(String(preserveRow.department_id));
    }
}

function fillProgramSelect(rows, preserveRow) {
    var $sel = $('.section-form .dt-program-id');
    $sel.empty().append('<option value="">Select program</option>');
    (rows || []).forEach(function (p) {
        var label =
            (p.program_name || '') + (p.program_code ? ' (' + p.program_code + ')' : '');
        $sel.append($('<option></option>').attr('value', p.id).text(label));
    });
    if (preserveRow && preserveRow.program) {
        appendMissingOption($sel, preserveRow.program_id, preserveRow.program, function (e) {
            return (
                (e.program_name || '') + (e.program_code ? ' (' + e.program_code + ')' : '')
            );
        });
    }
    $sel.prop('disabled', false);
    if (preserveRow && preserveRow.program_id != null) {
        $sel.val(String(preserveRow.program_id));
    }
}

function fillSemesterSelect(rows, preserveRow) {
    var $sel = $('.section-form .dt-semester-id');
    $sel.empty().append('<option value="">Select semester</option>');
    (rows || []).forEach(function (s) {
        var label =
            (s.semester_name || '') +
            (s.semester_order != null ? ' (Order: ' + s.semester_order + ')' : '');
        $sel.append($('<option></option>').attr('value', s.id).text(label));
    });
    if (preserveRow && preserveRow.semester) {
        appendMissingOption($sel, preserveRow.semester_id, preserveRow.semester, function (e) {
            return (
                (e.semester_name || '') +
                (e.semester_order != null ? ' (Order: ' + e.semester_order + ')' : '')
            );
        });
    }
    $sel.prop('disabled', false);
    if (preserveRow && preserveRow.semester_id != null) {
        $sel.val(String(preserveRow.semester_id));
    }
}

function resetDepartmentChain() {
    $('.section-form .dt-department-id')
        .empty()
        .append('<option value="">Select faculty first</option>')
        .prop('disabled', true);
    resetProgramChain();
}

function resetProgramChain() {
    $('.section-form .dt-program-id')
        .empty()
        .append('<option value="">Select department first</option>')
        .prop('disabled', true);
    resetSemesterChain();
}

function resetSemesterChain() {
    $('.section-form .dt-semester-id')
        .empty()
        .append('<option value="">Select program first</option>')
        .prop('disabled', true);
}

function loadFacultyList() {
    if (!window.sectionCascadeUrls || !window.sectionCascadeUrls.facultyList) {
        return $.Deferred().reject().promise();
    }
    if (!facultiesCachePromise) {
        facultiesCachePromise = getJson(window.sectionCascadeUrls.facultyList, {})
            .then(function (res) {
                return res.data || [];
            })
            .fail(function () {
                facultiesCachePromise = null;
            });
    }
    return facultiesCachePromise;
}

function loadDepartmentsForFaculty(facultyId, preserveRow) {
    if (!facultyId) {
        resetDepartmentChain();
        return $.Deferred().resolve().promise();
    }
    $('.section-form .dt-department-id').prop('disabled', true);
    return getJson(window.sectionCascadeUrls.departments, { faculty_id: facultyId })
        .done(function (res) {
            fillDepartmentSelect(res.data || [], preserveRow || null);
            if (!preserveRow) {
                resetProgramChain();
            }
        })
        .fail(function () {
            resetDepartmentChain();
        });
}

function loadProgramsForDepartment(departmentId, preserveRow) {
    if (!departmentId) {
        resetProgramChain();
        return $.Deferred().resolve().promise();
    }
    $('.section-form .dt-program-id').prop('disabled', true);
    return getJson(window.sectionCascadeUrls.programs, { department_id: departmentId })
        .done(function (res) {
            fillProgramSelect(res.data || [], preserveRow || null);
            if (!preserveRow) {
                resetSemesterChain();
            }
        })
        .fail(function () {
            resetProgramChain();
        });
}

function loadSemestersForProgram(programId, preserveRow) {
    if (!programId) {
        resetSemesterChain();
        return $.Deferred().resolve().promise();
    }
    $('.section-form .dt-semester-id').prop('disabled', true);
    return getJson(window.sectionCascadeUrls.semesters, { program_id: programId })
        .done(function (res) {
            fillSemesterSelect(res.data || [], preserveRow || null);
        })
        .fail(function () {
            resetSemesterChain();
        });
}

function resetSectionFormForCreate() {
    document.getElementById('form-section-record').reset();
    $('.section-form .dt-status').val('Active');
    $('.section-form .dt-gender-type').val('Male');
    $('.section-form .dt-faculty-id').val('');
    resetDepartmentChain();
    $('#form-section-record').removeAttr('data-record-id');
    $('#sectionCanvasTitle').text('New Section');
}

function applySectionRowToFields(rd) {
    $('.section-form .dt-section-name').val(rd.section_name || '');
    $('.section-form .dt-section-code').val(rd.section_code || '');
    $('.section-form .dt-gender-type').val(rd.gender_type || 'Male');
    $('.section-form .dt-capacity').val(
        rd.capacity !== null && rd.capacity !== undefined ? rd.capacity : ''
    );
    $('.section-form .dt-class-room').val(rd.class_room || '');
    $('.section-form .dt-status').val(rd.status || 'Active');
}

/**
 * Resolve the clicked row for Responsive layouts (controls may render on a child row).
 */
function resolveSectionDataTableRow($btn) {
    if (!dtSection) {
        return null;
    }
    var $tr = $btn.closest('tr');
    var primary = dtSection.row($tr);
    if (primary.data()) {
        return primary;
    }
    var $prev = $tr.prev();
    if ($prev.length) {
        var parentRow = dtSection.row($prev);
        if (parentRow.data()) {
            return parentRow;
        }
    }
    return null;
}

function openEditSection(rd, $btn) {
    SpinnerUtils.show($btn, 'Loading...');
    offCanvasSection =
        offCanvasSection || new bootstrap.Offcanvas(document.getElementById('add-new-record-section'));

    loadFacultyList()
        .then(function (allFaculties) {
            fillFacultySelect(allFaculties, rd);
            return loadDepartmentsForFaculty(rd.faculty_id, rd);
        })
        .then(function () {
            return loadProgramsForDepartment(rd.department_id, rd);
        })
        .then(function () {
            return loadSemestersForProgram(rd.program_id, rd);
        })
        .then(function () {
            applySectionRowToFields(rd);
            $('#form-section-record').attr('data-record-id', rd.id);
            $('#sectionCanvasTitle').text('Edit Section');
            offCanvasSection.show();
        })
        .fail(function () {
            if (typeof toastr !== 'undefined') {
                toastr.error('Could not load form data.');
            }
        })
        .always(function () {
            SpinnerUtils.hide($btn);
        });
}

$(function () {
    var formEl = document.getElementById('form-section-record');

    function wireSectionCascade() {
        $('.section-form .dt-faculty-id')
            .off('change.sectionCascade')
            .on('change.sectionCascade', function () {
                var fid = $(this).val();
                if (!fid) {
                    resetDepartmentChain();
                    return;
                }
                loadDepartmentsForFaculty(fid, null);
            });

        $('.section-form .dt-department-id')
            .off('change.sectionCascade')
            .on('change.sectionCascade', function () {
                var did = $(this).val();
                if (!did) {
                    resetProgramChain();
                    return;
                }
                loadProgramsForDepartment(did, null);
            });

        $('.section-form .dt-program-id')
            .off('change.sectionCascade')
            .on('change.sectionCascade', function () {
                var pid = $(this).val();
                if (!pid) {
                    resetSemesterChain();
                    return;
                }
                loadSemestersForProgram(pid, null);
            });
    }

    if (formEl && typeof FormValidation !== 'undefined') {
        fvSection = FormValidation.formValidation(formEl, {
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
                section_name: {
                    validators: {
                        notEmpty: {
                            message: 'Section name is required'
                        }
                    }
                },
                section_code: {
                    validators: {
                        notEmpty: {
                            message: 'Section code is required'
                        }
                    }
                },
                gender_type: {
                    validators: {
                        notEmpty: {
                            message: 'Gender type is required'
                        }
                    }
                },
                capacity: {
                    validators: {
                        notEmpty: {
                            message: 'Capacity is required'
                        },
                        integer: {
                            message: 'Capacity must be a whole number'
                        },
                        between: {
                            min: 0,
                            max: 100000,
                            message: 'Capacity must be between 0 and 100000'
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
        wireSectionCascade();
    }

    if (typeof window.sectionUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtSection = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.sectionUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load sections.');
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
                        if (!row.faculty) {
                            return '—';
                        }
                        var n = row.faculty.faculty_name || '';
                        var c = row.faculty.faculty_code ? ' (' + row.faculty.faculty_code + ')' : '';
                        return n + c;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.department ? row.department.name || '—' : '—';
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (!row.program) {
                            return '—';
                        }
                        return (
                            (row.program.program_name || '') +
                            (row.program.program_code ? ' (' + row.program.program_code + ')' : '')
                        );
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
                { data: 'section_name' },
                { data: 'section_code' },
                {
                    data: 'gender_type',
                    render: function (d) {
                        return d
                            ? '<span class="badge bg-label-info">' + d + '</span>'
                            : '—';
                    }
                },
                { data: 'capacity' },
                {
                    data: 'class_room',
                    render: function (d) {
                        return d || '—';
                    }
                },
                {
                    data: 'status',
                    render: function (d) {
                        var cls =
                            d === 'Active' ? 'bg-label-success' : 'bg-label-secondary';
                        return '<span class="badge ' + cls + '">' + (d || '—') + '</span>';
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
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon section-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon section-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[5, 'asc']],
            responsive: true,
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 10,
            lengthMenu: [7, 10, 25, 50],
            buttons: [
                {
                    text:
                        '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className:
                        'create-new btn btn-primary waves-effect waves-light'
                }
            ],
            initComplete: function () {
                $('.card-header').first().after('<hr class="my-0">');
                $('div.head-label').html('<h5 class="card-title mb-0">Sections</h5>');

                var canvas = document.getElementById('add-new-record-section');
                $('.create-new')
                    .off('click.sectionNew')
                    .on('click.sectionNew', function () {
                        if (!canvas) {
                            return;
                        }
                        offCanvasSection = offCanvasSection || new bootstrap.Offcanvas(canvas);
                        resetSectionFormForCreate();
                        loadFacultyList().done(function (all) {
                            fillFacultySelect(all, null);
                        });
                        offCanvasSection.show();
                    });
            }
        });
    }

    if (fvSection) {
        fvSection.on('core.form.valid', function () {
            var recordId = $('#form-section-record').attr('data-record-id');
            var url = window.sectionUrls.store;
            var msg = 'Section added successfully.';

            if (recordId) {
                url = window.sectionUrls.update + '/' + recordId;
                msg = 'Section updated successfully.';
            }

            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                faculty_id: $('.section-form .dt-faculty-id').val(),
                department_id: $('.section-form .dt-department-id').val(),
                program_id: $('.section-form .dt-program-id').val(),
                semester_id: $('.section-form .dt-semester-id').val(),
                section_name: $('.section-form .dt-section-name').val(),
                section_code: $('.section-form .dt-section-code').val(),
                gender_type: $('.section-form .dt-gender-type').val(),
                capacity: $('.section-form .dt-capacity').val(),
                class_room: $('.section-form .dt-class-room').val(),
                status: $('.section-form .dt-status').val()
            };

            if (recordId) {
                payload._method = 'PUT';
            }

            var $btn = $('#form-section-record button[type="submit"]');

            AjaxUtils.request({
                url: url,
                type: 'POST',
                data: payload,
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtSection.ajax.reload(null, false);
                    if (offCanvasSection) {
                        offCanvasSection.hide();
                    }
                    $('#form-section-record').removeAttr('data-record-id');
                    resetSectionFormForCreate();
                    loadFacultyList().done(function (all) {
                        fillFacultySelect(all, null);
                    });
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.section-edit', function () {
        var $icon = $(this);
        var row = resolveSectionDataTableRow($icon);
        var rd = row ? row.data() : null;
        if (!rd || rd.id == null) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Could not read this row. Try reloading the page.');
            }
            return;
        }
        openEditSection(rd, $icon);
    });

    $('.datatables-basic tbody').on('click', '.section-delete', function () {
        var $del = $(this);
        var row = resolveSectionDataTableRow($del);
        var rd = row ? row.data() : null;
        if (!rd || rd.id == null) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Could not read this row. Try reloading the page.');
            }
            return;
        }

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
                url: window.sectionUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                showSpinner: false,
                onSuccess: function () {
                    if (row && row.node()) {
                        row.remove().draw(false);
                    }
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Section deleted successfully.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
