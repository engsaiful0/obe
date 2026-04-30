@extends('layouts/layoutMaster')

@section('title', 'Course Settings')

@section('page-script')
    <script>
        window.courseUrls = AppUtils.buildApiUrls('app/settings/course');
        window.courseSemesters = @json($semesters ?? []);
    </script>
    <script src="{{ asset('assets/js/course-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Credit</th>
                        <th>Type</th>
                        <th>Contact Hr</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-course" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="courseCanvasTitle">New Course</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="course-form pt-0 row g-2" id="form-course-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="program_id">Program</label>
                    <select id="program_id" name="program_id" class="form-select dt-program-id" required>
                        <option value="">Select program</option>
                        @foreach($programs ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->program_name }} @if($p->program_code)({{ $p->program_code }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="semester_id">Semester</label>
                    <select id="semester_id" name="semester_id" class="form-select dt-semester-id" required disabled>
                        <option value="">Select program first</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code" class="form-control dt-course-code" maxlength="50" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="course_title">Course Title</label>
                    <input type="text" id="course_title" name="course_title" class="form-control dt-course-title" maxlength="255" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="credit">Credit</label>
                    <input type="number" id="credit" name="credit" class="form-control dt-credit" min="0" max="999.99" step="0.01" placeholder="3.00" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="course_type">Course Type</label>
                    <select id="course_type" name="course_type" class="form-select dt-course-type">
                        <option value="Theory">Theory</option>
                        <option value="Lab">Lab</option>
                        <option value="Project">Project</option>
                        <option value="Thesis">Thesis</option>
                        <option value="Viva">Viva</option>
                        <option value="Internship">Internship</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="contact_hour">Contact Hour</label>
                    <input type="number" id="contact_hour" name="contact_hour" class="form-control dt-contact-hour" min="0" max="32767" step="1" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="marks">Marks</label>
                    <input type="number" id="marks" name="marks" class="form-control dt-marks" min="0" max="32767" step="1" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="course_status">Status</label>
                    <select id="course_status" name="status" class="form-select dt-status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
