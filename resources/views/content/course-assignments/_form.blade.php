@php
    $assignment = $assignment ?? null;
    $ca = optional($assignment);
    $isEdit = $assignment !== null;
    $batches = $batches ?? collect();
    $semesters = $semesters ?? collect();
    $courses = $courses ?? collect();
    $sections = $sections ?? collect();
    $assignmentStatuses = ['Active' => 'Active', 'Inactive' => 'Inactive'];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="hidden" id="ca_ajax_base" value="{{ url('/ajax') }}">
<input type="hidden" id="ca_edit_mode" value="{{ $isEdit ? '1' : '0' }}">

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Academic session <span class="text-danger">*</span></label>
        <select name="academic_session_id" class="form-select" id="course_assignment_academic_session_id" required>
            <option value="">Select session</option>
            @foreach ($sessions as $session)
                <option value="{{ $session->id }}"
                    @selected(old('academic_session_id', $ca->academic_session_id) == $session->id)>
                    {{ $session->session_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Program <span class="text-danger">*</span></label>
        <select name="program_id" class="form-select" id="course_assignment_program_id" required>
            <option value="">Select program</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}"
                    @selected(old('program_id', $ca->program_id) == $program->id)>
                    {{ $program->program_code ? $program->program_code.' — ' : '' }}{{ $program->program_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Batch <span class="text-danger">*</span></label>
        <select name="batch_id" class="form-select" id="course_assignment_batch_id" required>
            <option value="">Select batch</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->id }}"
                    @selected(old('batch_id', $ca->batch_id) == $batch->id)>
                    {{ $batch->batch_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Semester <span class="text-danger">*</span></label>
        <select name="semester_id" class="form-select" id="course_assignment_semester_id" required>
            <option value="">Select semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}"
                    @selected(old('semester_id', $ca->semester_id) == $semester->id)>
                    {{ $semester->semester_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Course <span class="text-danger">*</span></label>
        <select name="course_id" class="form-select" id="course_assignment_course_id" required>
            <option value="">Select course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}"
                    @selected(old('course_id', $ca->course_id) == $course->id)>
                    {{ $course->course_code }} — {{ $course->course_title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Teacher <span class="text-danger">*</span></label>
        <select name="teacher_id" class="form-select" id="course_assignment_teacher_id" required>
            <option value="">Select teacher</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $ca->teacher_id) == $teacher->id)>
                    {{ $teacher->teacher_name }}
                    @if ($teacher->employee_id)
                        ({{ $teacher->employee_id }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Section <span class="text-danger">*</span></label>
        <select name="section_id" class="form-select" id="course_assignment_section_id" required>
            <option value="">Select section</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}"
                    @selected(old('section_id', $ca->section_id) == $section->id)>
                    {{ $section->section_name }}
                    @if ($section->section_code)
                        ({{ $section->section_code }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" id="course_assignment_status" required>
            @foreach ($courseAssignmentStatuses as $courseAssignmentStatus)
                <option value="{{ $courseAssignmentStatus->id }}" @selected(old('status_id', $courseAssignment->status_id ?? '') == $courseAssignmentStatus->id)>{{ $courseAssignmentStatus->status_name }}</option>
            @endforeach
        </select>
    </div>
</div>
