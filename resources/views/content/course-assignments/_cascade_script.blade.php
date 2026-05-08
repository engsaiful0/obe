{{-- Include from create/edit with ['assignment' => $assignment|null] --}}
@php
    $assignment = $assignment ?? null;
    $aid = optional($assignment);
    $courseAssignmentStateEncoded = json_encode([
        'semesterId' => old('semester_id', $aid->semester_id),
        'courseId' => old('course_id', $aid->course_id),
        'sectionId' => old('section_id', $aid->section_id),
        'programId' => old('program_id', $aid->program_id),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp
<script>
(function () {
    const BASE = @json(url('/ajax'));
    const EDIT = {{ $assignment ? 'true' : 'false' }};
    const STATE = {!! $courseAssignmentStateEncoded !!};

    function str(v) { return v !== undefined && v !== null ? String(v) : ''; }

    const programEl = document.getElementById('course_assignment_program_id');
    const semesterEl = document.getElementById('course_assignment_semester_id');
    const courseEl = document.getElementById('course_assignment_course_id');
    const sectionEl = document.getElementById('course_assignment_section_id');
    if (!programEl || !semesterEl || !courseEl || !sectionEl) return;

    let lastSections = [];

    async function fetchJson(url) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token || ''
            },
            credentials: 'same-origin'
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    function fillSelect(sel, placeholder, items) {
        sel.innerHTML = '';
        const p = document.createElement('option');
        p.value = '';
        p.textContent = placeholder;
        sel.appendChild(p);
        (items || []).forEach(function (row) {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.label || '';
            sel.appendChild(opt);
        });
    }

    function selectValue(sel, value) {
        const v = str(value);
        if (!v) return;
        if ([...sel.options].some(function (o) { return String(o.value) === v; })) {
            sel.value = v;
        }
    }

    function sectionsForSemester() {
        const sid = Number(semesterEl.value || 0);
        return (lastSections || []).filter(function (r) { return Number(r.semester_id) === sid; });
    }

    function renderSections() {
        fillSelect(sectionEl, 'Select section', sectionsForSemester());
        selectValue(sectionEl, STATE.sectionId);
    }

    async function loadCoursesForSemester() {
        const sid = semesterEl.value ? Number(semesterEl.value) : 0;
        if (!sid) {
            fillSelect(courseEl, 'Select course', []);
            renderSections();
            return;
        }
        const res = await fetchJson(BASE + '/semester/' + sid + '/courses');
        fillSelect(courseEl, 'Select course', res.items || []);
        selectValue(courseEl, STATE.courseId);
        renderSections();
    }

    async function loadProgramChains() {
        const pid = programEl.value ? Number(programEl.value) : 0;
        if (!pid) {
            fillSelect(semesterEl, 'Select semester', []);
            fillSelect(courseEl, 'Select course', []);
            fillSelect(sectionEl, 'Select section', []);
            lastSections = [];
            return;
        }
        try {
            const [semRes, secRes] = await Promise.all([
                fetchJson(BASE + '/program/' + pid + '/semesters'),
                fetchJson(BASE + '/program/' + pid + '/sections')
            ]);
            fillSelect(semesterEl, 'Select semester', semRes.items || []);
            selectValue(semesterEl, STATE.semesterId);
            fillSelect(courseEl, 'Select course', []);
            lastSections = secRes.items || [];
            renderSections();
            if (semesterEl.value) await loadCoursesForSemester();
            else renderSections();
        } catch (e) {
            console.error(e);
        }
    }

    programEl.addEventListener('change', function () {
        STATE.programId = programEl.value;
        STATE.semesterId = '';
        STATE.courseId = '';
        STATE.sectionId = '';
        loadProgramChains();
    });
    semesterEl.addEventListener('change', function () {
        STATE.semesterId = semesterEl.value;
        loadCoursesForSemester().catch(console.error);
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (programEl.value) {
            loadProgramChains().catch(console.error);
        }
    });
})();
</script>
