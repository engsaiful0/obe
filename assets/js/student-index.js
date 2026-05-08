/**
 * Student list — Laravel pagination + AJAX filters (default 40 / page).
 */
'use strict';

(function () {
    if (typeof window.studentListUrl === 'undefined') {
        return;
    }

    let currentPage = 1;
    let searchDebounce;
    let lastMeta = null;

    const $tbody = $('#student-table-body');
    const $pager = $('#student-pagination');
    const $summary = $('#student-list-summary');
    const $spinner = $('#student-list-spinner');
    const $empty = $('#student-list-empty');
    const $alert = $('#student-list-alert');
    const $tableWrap = $('.table-responsive').closest('.position-relative');

    function showSpinner(show) {
        if (show) {
            $spinner.removeClass('d-none').addClass('d-flex');
        } else {
            $spinner.addClass('d-none').removeClass('d-flex');
        }
    }

    function buildParams(page) {
        var p = new URLSearchParams();
        p.set('page', String(page));

        var per = $('#filter-per_page').val();
        if (per) {
            p.set('per_page', per);
        }

        var q = $('#filter-q').val();
        if (q && q.trim()) {
            p.set('q', q.trim());
        }

        [
            ['program_id', '#filter-program_id'],
            ['batch_id', '#filter-batch_id'],
            ['section_id', '#filter-section'],
            ['academic_session_id', '#filter-academic_session_id'],
            ['gender_id', '#filter-gender_id'],
            ['religion_id', '#filter-religion_id'],
            ['status_id', '#status_id'],
            ['shift', '#filter-shift'],
            ['student_type', '#filter-student_type'],
            ['admission_date_from', '#filter-admission_date_from'],
            ['admission_date_to', '#filter-admission_date_to'],
            ['sort', '#filter-sort'],
            ['dir', '#filter-dir']
        ].forEach(function (pair) {
            var key = pair[0];
            var sel = pair[1];
            var val = $(sel).val();
            if (val !== null && val !== '') {
                p.set(key, val);
            }
        });

        return p;
    }

    function escapeHtml(str) {
        if (str == null) {
            return '';
        }
        return $('<div/>').text(str).html();
    }

    function cellProgram(row) {
        if (!row.program) {
            return '—';
        }
        var n = row.program.program_name || '';
        var c = row.program.program_code ? ' (' + row.program.program_code + ')' : '';
        return escapeHtml(n + c);
    }

    function cellBatch(row) {
        if (!row.batch) {
            return '—';
        }
        var n = row.batch.batch_name || '';
        var c = row.batch.batch_code ? ' (' + row.batch.batch_code + ')' : '';
        return escapeHtml(n + c);
    }

    function cellSession(row) {
        if (!row.academic_session) {
            return '—';
        }
        var s = row.academic_session.session_name || '';
        var y =
            row.academic_session.academic_year != null ? ' — ' + row.academic_session.academic_year : '';
        return escapeHtml(s + y);
    }

    function badgeStatus(status) {
        var cls =
            status === 'Active'
                ? 'bg-label-success'
                : status === 'Inactive'
                    ? 'bg-label-secondary'
                    : 'bg-label-info';
        return '<span class="badge ' + cls + '">' + escapeHtml(status || '') + '</span>';
    }

    function actionButtons(row) {
        var actions = row.actions || {};
        var viewUrl = actions.show_url || '#';
        var editUrl = actions.edit_url || '#';
        var deleteUrl = actions.delete_url || '#';

        return (
            '<div class="d-flex gap-1">' +
            '<a href="' + escapeHtml(viewUrl) + '" class="btn btn-sm btn-icon btn-outline-info" title="View">' +
            '<i class="ti ti-eye"></i></a>' +
            '<a href="' + escapeHtml(editUrl) + '" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">' +
            '<i class="ti ti-pencil"></i></a>' +
            '<button type="button" class="btn btn-sm btn-icon btn-outline-danger js-student-delete" data-delete-url="' + escapeHtml(deleteUrl) + '" title="Delete">' +
            '<i class="ti ti-trash"></i></button>' +
            '</div>'
        );
    }

    function renderRows(rows, meta) {
        $tbody.empty();
        var from = meta && meta.from != null ? meta.from : 0;
        rows.forEach(function (row, i) {
            var idx = from ? from + i : i + 1;
            var initial =
                (row.student_name && row.student_name.charAt(0) ? row.student_name.charAt(0) : '?')
                    .toUpperCase();
            var photo =
                row.picture_url ?
                    '<img src="' +
                    escapeHtml(row.picture_url) +
                    '" alt="" class="rounded" width="40" height="40" style="object-fit:cover;" />'
                : '<span class="avatar avatar-sm avatar-initial rounded bg-label-secondary">' +
                      escapeHtml(initial) +
                      '</span>';
            var tr =
                '<tr>' +
                '<td>' +
                idx +
                '</td>' +
                '<td>' +
                photo +
                '</td>' +
                '<td>' +
                escapeHtml(row.student_name || '') +
                '</td>' +
                '<td><code>' +
                escapeHtml(row.student_code || '') +
                '</code></td>' +
                '<td>' +
                cellBatch(row) +
                '</td>' +
                '<td>' +
                cellProgram(row) +
                '</td>' +
                '<td>' +
                cellSession(row) +
                '</td>' +
                '<td>' +
                escapeHtml(row.section || '—') +
                '</td>' +
                '<td>' +
                (row.gender && row.gender.gender_name
                    ? escapeHtml(row.gender.gender_name)
                    : '—') +
                '</td>' +
                '<td>' +
                escapeHtml((row.shift || '') + ' / ' + (row.student_type || '')) +
                '</td>' +
                '<td>' +
                badgeStatus(row.status) +
                '</td>' +
                '<td>' +
                actionButtons(row) +
                '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function renderPagination(meta, links) {
        $pager.empty();
        var cur = meta.current_page || 1;
        var last = meta.last_page || 1;

        var addLi = function (label, page, disabled, active) {
            var li =
                $('<li/>').addClass('page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : ''));
            var a = $('<a/>')
                .addClass('page-link')
                .attr('href', 'javascript:void(0)')
                .text(label);
            if (!disabled && !active) {
                a.data('page', page);
            }
            li.append(a);
            $pager.append(li);
        };

        addLi('« Prev', Math.max(1, cur - 1), cur <= 1, false);

        var pad = 2;
        var start = Math.max(1, cur - pad);
        var end = Math.min(last, cur + pad);
        if (start > 1) {
            addLi('1', 1, false, cur === 1);
            if (start > 2) {
                $pager.append(
                    $('<li class="page-item disabled"><span class="page-link">…</span></li>')
                );
            }
        }
        for (var p = start; p <= end; p++) {
            addLi(String(p), p, false, p === cur);
        }
        if (end < last) {
            if (end < last - 1) {
                $pager.append(
                    $('<li class="page-item disabled"><span class="page-link">…</span></li>')
                );
            }
            addLi(String(last), last, false, cur === last);
        }

        addLi('Next »', Math.min(last, cur + 1), cur >= last || last <= 1, false);
    }

    function loadBatchOptions(programId) {
        var $batch = $('#filter-batch_id');
        $batch.prop('disabled', true).find('option:not(:first)').remove();
        $('#filter-section').prop('disabled', true).find('option:not(:first)').remove();
        if (!programId) {
            return;
        }
        var url =
            typeof window.studentBatchesUrl !== 'undefined' ? window.studentBatchesUrl : '';
        if (!url) {
            return;
        }
        $.ajax({
            url: url,
            type: 'GET',
            data: { program_id: programId, all_for_program: 1 },
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (res) {
            var rows = res.data || [];
            rows.forEach(function (b) {
                var label =
                    (b.batch_name || '') +
                    (b.batch_code ? ' (' + b.batch_code + ')' : '');
                $batch.append($('<option/>').attr('value', b.id).text(label));
            });
            $batch.prop('disabled', false);
        });
    }

    function loadSectionOptions(batchId) {
        var $section = $('#filter-section');
        $section.prop('disabled', true).find('option:not(:first)').remove();
        if (!batchId) {
            return;
        }
        $.ajax({
            url: '/ajax/batch/' + encodeURIComponent(batchId) + '/sections',
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (pack) {
            var rows = (pack && pack.items) ? pack.items : [];
            rows.forEach(function (sec) {
                if (!sec.id) {
                    return;
                }
                $section.append($('<option/>').attr('value', sec.id).text(sec.label || 'Section ' + sec.id));
            });
            $section.prop('disabled', false);
        });
    }

    function fetchPage(page) {
        currentPage = page;
        showSpinner(true);
        $alert.addClass('d-none');

        $.ajax({
            url: window.studentListUrl + '?' + buildParams(page).toString(),
            type: 'GET',
            dataType: 'json'
        }).done(function (res) {
            lastMeta = res.meta || {};
            var rows = res.data || [];
            var totalAll = typeof lastMeta.total === 'number' ? lastMeta.total : 0;

            $empty.addClass('d-none');
            if (rows.length === 0) {
                $tbody.html(
                    '<tr><td colspan="12" class="text-center text-muted py-4">' +
                        (totalAll === 0 ? 'No students in the database yet.' : 'No students match your filters.') +
                        '</td></tr>'
                );
                renderPagination(lastMeta || { current_page: 1, last_page: 1 }, res.links || {});
                if (totalAll === 0) {
                    $summary.text('');
                } else if (lastMeta.from != null && lastMeta.to != null) {
                    $summary.text(
                        'Showing 0 rows (filtered from ' + totalAll + ' total)'
                    );
                } else {
                    $summary.text('');
                }
                return;
            }

            renderRows(rows, lastMeta);

            var m = lastMeta;
            if (m.from != null && m.to != null && m.total != null) {
                $summary.text(
                    'Showing ' + m.from + '–' + m.to + ' of ' + m.total + ' student(s)'
                );
            }

            renderPagination(lastMeta, res.links || {});
        }).fail(function (xhr) {
            var msg = 'Could not load students.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $alert.text(msg).removeClass('d-none');
            $tbody.html(
                '<tr><td colspan="12" class="text-center text-danger py-4">Failed to load data.</td></tr>'
            );
        }).always(function () {
            showSpinner(false);
        });
    }

    function reloadCurrent() {
        fetchPage(currentPage);
    }

    $(document).ready(function () {
        fetchPage(1);

        $('#student-pagination').on('click', '.page-link', function (e) {
            e.preventDefault();
            var pg = $(this).data('page');
            if (pg) {
                fetchPage(pg);
                var top = ($tableWrap && $tableWrap.offset() && $tableWrap.offset().top) || 0;
                if (top) {
                    window.scrollTo({ top: top - 12, behavior: 'smooth' });
                }
            }
        });

        $('#filter-program_id').on('change.studentList', function () {
            $('#filter-batch_id').val('');
            $('#filter-batch_id').find('option:not(:first)').remove().end().prop('disabled', true);
            $('#filter-section').val('');
            $('#filter-section').find('option:not(:first)').remove().end().prop('disabled', true);
            var pid = $(this).val();
            if (pid) {
                loadBatchOptions(pid);
            }
            reloadCurrentFromFilters();
        });

        $('#filter-batch_id').on('change.studentList', function () {
            $('#filter-section').val('');
            $('#filter-section').find('option:not(:first)').remove().end().prop('disabled', true);
            var bid = $(this).val();
            if (bid) {
                loadSectionOptions(bid);
            }
            reloadCurrentFromFilters();
        });

        $('.js-student-filter')
            .not('#filter-program_id')
            .not('#filter-batch_id')
            .not('#filter-q')
            .on('change.studentList', reloadCurrentFromFilters);

        $('#filter-q').on('input.studentList', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function () {
                currentPage = 1;
                fetchPage(1);
            }, 450);
        });

        $('#student-filter-reset').on('click', function () {
            $('#filter-q').val('');
            $('#filter-program_id').val('');
            $('#filter-batch_id').empty().append('<option value="">All batches</option>').prop(
                'disabled',
                true
            );
            $('#filter-section').empty().append('<option value="">All sections</option>').prop(
                'disabled',
                true
            );
            $('#filter-academic_session_id').val('');
            $('#filter-gender_id').val('');
            $('#filter-religion_id').val('');
            $('#status_id').val('');
            $('#filter-shift').val('');
            $('#filter-student_type').val('');
            $('#filter-admission_date_from').val('');
            $('#filter-admission_date_to').val('');
            $('#filter-sort').val('student_name');
            $('#filter-dir').val('asc');
            $('#filter-per_page').val('40');
            fetchPage(1);
        });

        $('#student-table-body').on('click', '.js-student-delete', function () {
            var url = $(this).data('delete-url');
            if (!url) {
                return;
            }
            if (!window.confirm('Delete this student? This action cannot be undone.')) {
                return;
            }
            $.ajax({
                url: url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function (res) {
                if (typeof toastr !== 'undefined' && res && res.message) {
                    toastr.success(res.message);
                }
                fetchPage(1);
            }).fail(function (xhr) {
                var msg = 'Could not delete student.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            });
        });

        function reloadCurrentFromFilters() {
            currentPage = 1;
            fetchPage(1);
        }
    });
})();
