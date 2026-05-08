@extends('layouts/layoutMaster')

@section('title', 'Add Student')

@section('page-script')
    <script>
        window.studentCreateUrls = {
            meta: @json(route('student.create-meta')),
            batches: @json(route('student.batches-by-program')),
            store: @json(route('students.store'))
        };
    </script>
    <script src="{{ asset('assets/js/student-create.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Add Student</h5>
                </div>
                <div class="card-body">
                    <form id="studentCreateForm" enctype="multipart/form-data" onsubmit="return false">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-0">Academic</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="program_id">Program <span class="text-danger">*</span></label>
                                <select name="program_id" id="program_id" class="form-select" required>
                                    <option value="">— Load programs —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="batch_id">Batch <span class="text-danger">*</span></label>
                                <select name="batch_id" id="batch_id" class="form-select" required disabled>
                                    <option value="">— Select program first —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="academic_session_id">Admission Session <span class="text-danger">*</span></label>
                                <select name="academic_session_id" id="academic_session_id" class="form-select" required>
                                    <option value="">— Load sessions —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="section_id">Section <span class="text-danger">*</span></label>
                                <select name="section_id" id="section_id" class="form-select" required>
                                    <option value="">— Load sections —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_code">Student ID <span class="text-danger">*</span></label>
                                <input type="text" name="student_code" id="student_code" class="form-control" maxlength="100" required />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="admission_date">Admission Date</label>
                                <input type="date" name="admission_date" id="admission_date" class="form-control" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="shift">Shift <span class="text-danger">*</span></label>
                                <select name="shift" id="shift" class="form-select" required>
                                    <option value="Morning" selected>Morning</option>
                                    <option value="Evening">Evening</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_type">Student Type <span class="text-danger">*</span></label>
                                <select name="student_type" id="student_type" class="form-select" required>
                                    <option value="Regular" selected>Regular</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Foreign">Foreign</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status_id" id="status_id" class="form-select" required>
                                    @foreach ($studentStatuses ?? [] as $ss)
                                        <option value="{{ $ss->id }}">{{ $ss->status_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Personal &amp; identity</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="student_name">Student Name <span class="text-danger">*</span></label>
                                <input type="text" name="student_name" id="student_name" class="form-control" maxlength="255" required />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="picture">Picture</label>
                                <input type="file" name="picture" id="picture" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="date_of_birth">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="gender_id">Gender <span class="text-danger">*</span></label>
                                <select name="gender_id" id="gender_id" class="form-select" required>
                                    <option value="">— Load —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="religion_id">Religion <span class="text-danger">*</span></label>
                                <select name="religion_id" id="religion_id" class="form-select" required>
                                    <option value="">— Load —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nationality_id">Nationality</label>
                                <select name="nationality_id" id="nationality_id" class="form-select">
                                    <option value="">— Load —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nid_or_birth_cert_no">NID / Birth Certificate No.</label>
                                <input type="text" name="nid_or_birth_cert_no" id="nid_or_birth_cert_no" class="form-control" maxlength="120" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="blood_group_id">Blood Group</label>
                                <select name="blood_group_id" id="blood_group_id" class="form-select">
                                    <option value="">— Load —</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="marital_status_id">Marital Status</label>
                                <select name="marital_status_id" id="marital_status_id" class="form-select">
                                    <option value="">— Load —</option>
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Parents</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="father_name">Father Name <span class="text-danger">*</span></label>
                                <input type="text" name="father_name" id="father_name" class="form-control" maxlength="255" required />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="mother_name">Mother Name</label>
                                <input type="text" name="mother_name" id="mother_name" class="form-control" maxlength="255" />
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Contact</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" maxlength="255" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" maxlength="30" />
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Address</h6>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="present_address">Present Address</label>
                                <textarea name="present_address" id="present_address" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="permanent_address">Permanent Address</label>
                                <textarea name="permanent_address" id="permanent_address" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Guardian</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_name">Guardian Name</label>
                                <input type="text" name="guardian_name" id="guardian_name" class="form-control" maxlength="255" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_relation">Relation to Student</label>
                                <select name="guardian_relation" id="guardian_relation" class="form-select">
                                    <option value="">— Select —</option>
                                    @foreach ($guardian_relations ?? [] as $gr)
                                        <option value="{{ $gr }}">{{ $gr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_phone">Guardian Phone</label>
                                <input type="text" name="guardian_phone" id="guardian_phone" class="form-control" maxlength="30" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="guardian_email">Guardian Email</label>
                                <input type="email" name="guardian_email" id="guardian_email" class="form-control" maxlength="255" />
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label" for="guardian_address">Guardian Address</label>
                                <textarea name="guardian_address" id="guardian_address" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Documents</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="signature">Signature</label>
                                <input type="file" name="signature" id="signature" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="nid_document">NID / Certificate Document</label>
                                <input type="file" name="nid_document" id="nid_document" class="form-control" accept=".pdf,image/jpeg,image/png,image/jpg,image/webp" />
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-0">Portal login <small class="text-muted">(optional)</small></h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="login_email">Login Email</label>
                                <input type="email" name="login_email" id="login_email" class="form-control" maxlength="255" autocomplete="off" />
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control" minlength="6" maxlength="255" autocomplete="new-password" />
                                <small class="text-muted">Required if login email is set.</small>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" id="studentSubmitBtn" class="btn btn-primary">
                                    <span class="btn-label">Save Student</span>
                                </button>
                                <button type="reset" class="btn btn-outline-secondary ms-2">Clear form</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
