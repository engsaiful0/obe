@extends('layouts/layoutMaster')

@section('title', 'Edit Student')

@section('page-script')
    <script>
        window.studentBatchesUrl = @json(route('student.batches-by-program'));
        window.studentAjaxBase = @json(url('/ajax'));
    </script>
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('studentEditForm');
                var submitBtn = document.getElementById('studentUpdateBtn');
                var submitSpinner = document.getElementById('studentUpdateSpinner');
                var programSelect = document.getElementById('program_id');
                var batchSelect = document.getElementById('batch_id');
                var sectionSelect = document.getElementById('section_id');

                function loadSectionsForProgram(programId) {
                    if (!sectionSelect || !window.studentAjaxBase || !programId) {
                        if (sectionSelect && !programId) {
                            sectionSelect.innerHTML = '<option value="">— Select program first —</option>';
                            sectionSelect.disabled = true;
                        }
                        return;
                    }
                    var base = String(window.studentAjaxBase).replace(/\/$/, '');
                    sectionSelect.innerHTML = '<option value="">Loading sections...</option>';
                    sectionSelect.disabled = true;

                    fetch(
                            base + '/program/' + encodeURIComponent(programId) + '/sections', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            var rows = data.items || [];
                            sectionSelect.innerHTML = '<option value="">— Select section —</option>';
                            rows.forEach(function(s) {
                                var opt = document.createElement('option');
                                opt.value = s.id;
                                opt.textContent = s.label || '';
                                sectionSelect.appendChild(opt);
                            });
                            sectionSelect.disabled = false;
                        })
                        .catch(function() {
                            sectionSelect.innerHTML = '<option value="">Could not load sections</option>';
                            sectionSelect.disabled = false;
                        });
                }

                if (programSelect && batchSelect && window.studentBatchesUrl) {
                    programSelect.addEventListener('change', function() {
                        var programId = this.value;
                        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
                        batchSelect.disabled = true;

                        if (!programId) {
                            batchSelect.innerHTML = '<option value="">— Select program first —</option>';
                            loadSectionsForProgram('');
                            return;
                        }

                        var url = window.studentBatchesUrl + '?program_id=' + encodeURIComponent(programId) + '&all_for_program=1';

                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            })
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                var rows = data.data || [];
                                batchSelect.innerHTML = '<option value="">— Select batch —</option>';
                                rows.forEach(function(b) {
                                    var label = (b.batch_name || '') + (b.batch_code ? ' (' + b.batch_code + ')' : '');
                                    var opt = document.createElement('option');
                                    opt.value = b.id;
                                    opt.textContent = label;
                                    batchSelect.appendChild(opt);
                                });
                                batchSelect.disabled = false;
                                loadSectionsForProgram(programId);
                            })
                            .catch(function() {
                                batchSelect.innerHTML = '<option value="">Could not load batches</option>';
                                batchSelect.disabled = false;
                            });
                    });
                }

                if (form && submitBtn && submitSpinner) {
                    form.addEventListener('submit', function() {
                        submitBtn.disabled = true;
                        submitSpinner.classList.remove('d-none');
                    });
                }
            });
        })();
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Edit Student</h5>
                </div>
                <div class="card-body">
                    <form id="studentEditForm" method="POST" action="{{ route('student.update', $student->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-0">Academic</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="program_id">Program <span class="text-danger">*</span></label>
                                <select name="program_id" id="program_id" class="form-select" required>
                                    <option value="">— Select program —</option>
                                    @foreach ($programs ?? [] as $p)
                                        <option value="{{ $p->id }}" @selected(old('program_id', $student->program_id) == $p->id)>
                                            {{ $p->program_name }}@if ($p->program_code)
                                                ({{ $p->program_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="batch_id">Batch <span class="text-danger">*</span></label>
                                <select name="batch_id" id="batch_id" class="form-select" required>
                                    <option value="">— Select batch —</option>
                                    @foreach ($batches ?? [] as $b)
                                        <option value="{{ $b->id }}" @selected(old('batch_id', $student->batch_id) == $b->id)>
                                            {{ $b->batch_name }}@if ($b->batch_code)
                                                ({{ $b->batch_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="academic_session_id">Admission Session <span
                                        class="text-danger">*</span></label>
                                <select name="academic_session_id" id="academic_session_id" class="form-select" required>
                                    <option value="">— Select session —</option>
                                    @foreach ($academicSessions ?? [] as $s)
                                        <option value="{{ $s->id }}"
                                            @selected(old('academic_session_id', $student->academic_session_id) == $s->id)>
                                            {{ $s->session_name }} — {{ $s->academic_year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="section_id">Section <span class="text-danger">*</span></label>
                                <select name="section_id" id="section_id" class="form-select" required>
                                    <option value="">— Select section —</option>
                                    @foreach ($sections ?? [] as $s)
                                        <option value="{{ $s->id }}" @selected(old('section_id', $student->section_id) == $s->id)>
                                            {{ $s->section_name }}@if ($s->section_code)
                                                ({{ $s->section_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_code">Student ID <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="student_code" id="student_code" class="form-control"
                                    maxlength="100" required value="{{ old('student_code', $student->student_code) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="admission_date">Admission Date</label>
                                <input type="date" name="admission_date" id="admission_date" class="form-control"
                                    value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="shift">Shift <span class="text-danger">*</span></label>
                                <select name="shift" id="shift" class="form-select" required>
                                    @foreach (['Morning', 'Evening', 'Weekend'] as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift', $student->shift) === $shift)>
                                            {{ $shift }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_type">Student Type <span
                                        class="text-danger">*</span></label>
                                <select name="student_type" id="student_type" class="form-select" required>
                                    @foreach (['Regular', 'Transfer', 'Foreign'] as $type)
                                        <option value="{{ $type }}"
                                            @selected(old('student_type', $student->student_type) === $type)>{{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="status_id">Status <span class="text-danger">*</span></label>
                                <select name="status_id" id="status_id" class="form-select" required>
                                    @foreach ($studentStatuses ?? [] as $ss)
                                        <option value="{{ $ss->id }}" @selected(old('status_id', $student->status_id) == $ss->id)>
                                            {{ $ss->status_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Personal &amp; identity</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_name">Student Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="student_name" id="student_name" class="form-control"
                                    maxlength="255" required value="{{ old('student_name', $student->student_name) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="picture">Picture</label>
                                <input type="file" name="picture" id="picture" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/webp" />
                                @if ($student->picture)
                                    <small class="text-muted">Current:
                                        <a target="_blank" href="{{ asset('storage/' . $student->picture) }}">View
                                            picture</a>
                                    </small>
                                @endif
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="date_of_birth">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                                    value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="gender_id">Gender <span class="text-danger">*</span></label>
                                <select name="gender_id" id="gender_id" class="form-select" required>
                                    <option value="">— Select gender —</option>
                                    @foreach ($genders ?? [] as $g)
                                        <option value="{{ $g->id }}" @selected(old('gender_id', $student->gender_id) == $g->id)>
                                            {{ $g->gender_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="religion_id">Religion <span class="text-danger">*</span></label>
                                <select name="religion_id" id="religion_id" class="form-select" required>
                                    <option value="">— Select religion —</option>
                                    @foreach ($religions ?? [] as $r)
                                        <option value="{{ $r->id }}" @selected(old('religion_id', $student->religion_id) == $r->id)>
                                            {{ $r->religion_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nationality_id">Nationality</label>
                                <select name="nationality_id" id="nationality_id" class="form-select">
                                    <option value="">— Select nationality —</option>
                                    @foreach ($nationalities ?? [] as $n)
                                        <option value="{{ $n->id }}"
                                            @selected(old('nationality_id', $student->nationality_id) == $n->id)>
                                            {{ $n->nationality_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nid_or_birth_cert_no">NID / Birth Certificate No.</label>
                                <input type="text" name="nid_or_birth_cert_no" id="nid_or_birth_cert_no"
                                    class="form-control" maxlength="120"
                                    value="{{ old('nid_or_birth_cert_no', $student->nid_or_birth_cert_no) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="blood_group_id">Blood Group</label>
                                <select name="blood_group_id" id="blood_group_id" class="form-select">
                                    <option value="">— Select blood group —</option>
                                    @foreach ($bloodGroups ?? [] as $b)
                                        <option value="{{ $b->id }}"
                                            @selected(old('blood_group_id', $student->blood_group_id) == $b->id)>
                                            {{ $b->blood_group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="marital_status_id">Marital Status</label>
                                <select name="marital_status_id" id="marital_status_id" class="form-select">
                                    <option value="">— Select marital status —</option>
                                    @foreach ($maritalStatuses ?? [] as $m)
                                        <option value="{{ $m->id }}"
                                            @selected(old('marital_status_id', $student->marital_status_id) == $m->id)>
                                            {{ $m->marital_status_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Parents</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="father_name">Father Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="father_name" id="father_name" class="form-control"
                                    maxlength="255" required value="{{ old('father_name', $student->father_name) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="mother_name">Mother Name</label>
                                <input type="text" name="mother_name" id="mother_name" class="form-control"
                                    maxlength="255" value="{{ old('mother_name', $student->mother_name) }}" />
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Contact</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" maxlength="255"
                                    value="{{ old('email', $student->email) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" maxlength="30"
                                    value="{{ old('phone', $student->phone) }}" />
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Address</h6>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="present_address">Present Address</label>
                                <textarea name="present_address" id="present_address" class="form-control" rows="2" maxlength="2000">{{ old('present_address', $student->present_address) }}</textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="permanent_address">Permanent Address</label>
                                <textarea name="permanent_address" id="permanent_address" class="form-control" rows="2" maxlength="2000">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Guardian</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_name">Guardian Name</label>
                                <input type="text" name="guardian_name" id="guardian_name" class="form-control"
                                    maxlength="255" value="{{ old('guardian_name', $student->guardian_name) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_relation">Relation to Student</label>
                                <select name="guardian_relation" id="guardian_relation" class="form-select">
                                    <option value="">— Select —</option>
                                    @foreach ($guardian_relations ?? [] as $gr)
                                        <option value="{{ $gr }}"
                                            @selected(old('guardian_relation', $student->guardian_relation) === $gr)>
                                            {{ $gr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_phone">Guardian Phone</label>
                                <input type="text" name="guardian_phone" id="guardian_phone" class="form-control"
                                    maxlength="30" value="{{ old('guardian_phone', $student->guardian_phone) }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_email">Guardian Email</label>
                                <input type="email" name="guardian_email" id="guardian_email" class="form-control"
                                    maxlength="255" value="{{ old('guardian_email', $student->guardian_email) }}" />
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label" for="guardian_address">Guardian Address</label>
                                <textarea name="guardian_address" id="guardian_address" class="form-control" rows="2" maxlength="2000">{{ old('guardian_address', $student->guardian_address) }}</textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Documents</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="signature">Signature</label>
                                <input type="file" name="signature" id="signature" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/webp" />
                                @if ($student->signature)
                                    <small class="text-muted">Current:
                                        <a target="_blank" href="{{ asset('storage/' . $student->signature) }}">View
                                            signature</a>
                                    </small>
                                @endif
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nid_document">NID / Certificate Document</label>
                                <input type="file" name="nid_document" id="nid_document" class="form-control"
                                    accept=".pdf,image/jpeg,image/png,image/jpg,image/webp" />
                                @if ($student->nid_document)
                                    <small class="text-muted">Current:
                                        <a target="_blank" href="{{ asset('storage/' . $student->nid_document) }}">View
                                            document</a>
                                    </small>
                                @endif
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Portal login <small class="text-muted">(optional)</small></h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="login_email">Login Email</label>
                                <input type="email" name="login_email" id="login_email" class="form-control"
                                    maxlength="255" autocomplete="off"
                                    value="{{ old('login_email', $student->user->email ?? '') }}" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    minlength="6" maxlength="255" autocomplete="new-password" />
                                <small class="text-muted">Fill only if you want to change or create password.</small>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" id="studentUpdateBtn" class="btn btn-primary">
                                    <span id="studentUpdateSpinner" class="spinner-border spinner-border-sm me-2 d-none"
                                        role="status" aria-hidden="true"></span>
                                    <span class="btn-label">Update Student</span>
                                </button>
                                <a href="{{ route('student.view-student') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
