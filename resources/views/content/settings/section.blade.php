@extends('layouts/layoutMaster')

@section('title', 'Section Settings')

@section('page-script')
    <script>
        window.sectionUrls = AppUtils.buildApiUrls('app/settings/section');
        window.sectionCascadeUrls = {
            facultyList: @json(url('app/settings/get-faculty')),
            departments: @json(url('app/settings/section/departments-by-faculty')),
            programs: @json(url('app/settings/section/programs-by-department')),
            semesters: @json(url('app/settings/section/semesters-by-program')),
        };
    </script>
    <script src="{{ asset('assets/js/section-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Faculty</th>
                        <th>Department</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Gender</th>
                        <th>Capacity</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-section" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="sectionCanvasTitle">New Section</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="section-form pt-0 row g-2" id="form-section-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="faculty_id">Faculty</label>
                    <select id="faculty_id" name="faculty_id" class="form-select dt-faculty-id" required>
                        <option value="">Select faculty</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-select dt-department-id" required disabled>
                        <option value="">Select faculty first</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="program_id">Program</label>
                    <select id="program_id" name="program_id" class="form-select dt-program-id" required disabled>
                        <option value="">Select department first</option>
                    </select>
                </div>
             
                <div class="col-sm-12">
                    <label class="form-label" for="semester_id">Semester</label>
                    <select id="semester_id" name="semester_id" class="form-select dt-semester-id" required disabled>
                        <option value="">Select program first</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="section_name">Section Name</label>
                    <input type="text" id="section_name" name="section_name" class="form-control dt-section-name"
                        maxlength="255" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="section_code">Section Code</label>
                    <input type="text" id="section_code" name="section_code" class="form-control dt-section-code"
                        maxlength="80" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="gender_type">Gender Type</label>
                    <select id="gender_type" name="gender_type" class="form-select dt-gender-type" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Combined">Combined</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-control dt-capacity" min="0"
                        max="100000" step="1" placeholder="0" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="class_room">Class Room</label>
                    <input type="text" id="class_room" name="class_room" class="form-control dt-class-room"
                        maxlength="255" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="section_status">Status</label>
                    <select id="section_status" name="status" class="form-select dt-status">
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
