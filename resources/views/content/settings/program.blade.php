@extends('layouts/layoutMaster')

@section('title', 'Program Settings')

@section('page-script')
    <script>
        window.programUrls = AppUtils.buildApiUrls('app/settings/program');
        window.programDepartments = @json($departments ?? []);
    </script>
    <script src="{{ asset('assets/js/program-datatables.js') }}?v={{ time() }}"></script>
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
                        <th>Program Name</th>
                        <th>Code</th>
                        <th>Degree</th>
                        <th>Duration</th>
                        <th>Semesters</th>
                        <th>Credits</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-program" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="programCanvasTitle">New Program</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="program-form pt-0 row g-2" id="form-program-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="faculty_id">Faculty</label>
                    <select id="faculty_id" name="faculty_id" class="form-select dt-faculty-id" required>
                        <option value="">Select faculty</option>
                        @foreach($faculties ?? [] as $f)
                            <option value="{{ $f->id }}">{{ $f->faculty_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-select dt-department-id" required disabled>
                        <option value="">Select faculty first</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="program_name">Program Name</label>
                    <input type="text" id="program_name" name="program_name" class="form-control dt-program-name"
                        placeholder="Program name" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="program_code">Program Code</label>
                    <input type="text" id="program_code" name="program_code" class="form-control dt-program-code"
                        maxlength="50" placeholder="e.g. BBA" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="degree_level">Degree Level</label>
                    <select id="degree_level" name="degree_level" class="form-select dt-degree-level">
                        <option value="Bachelor">Bachelor</option>
                        <option value="Masters">Masters</option>
                        <option value="PhD">PhD</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" class="form-control dt-duration"
                        placeholder="e.g. 4 years" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="total_semester">Total Semester</label>
                    <input type="number" id="total_semester" name="total_semester" min="0" step="1"
                        class="form-control dt-total-semester" placeholder="8" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="total_credit">Total Credit</label>
                    <input type="number" id="total_credit" name="total_credit" min="0" step="1"
                        class="form-control dt-total-credit" placeholder="140" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="program_status">Status</label>
                    <select id="program_status" name="status" class="form-select dt-status">
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
