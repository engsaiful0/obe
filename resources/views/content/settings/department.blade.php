@extends('layouts/layoutMaster')

@section('title', 'Department Settings')

@section('page-script')
    <script>
        window.departmentUrls = AppUtils.buildApiUrls('app/settings/department');
    </script>
    <script src="{{ asset('assets/js/department-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Faculty</th>
                        <th>Department Name</th>
                        <th>Department Code</th>
                        <th>Head / Chairman</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Department</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="faculty_id">Faculty <span class="text-danger">*</span></label>
                    <select id="faculty_id" class="form-select dt-faculty-id" name="faculty_id" required>
                        <option value="">Select faculty</option>
                        @foreach($faculties ?? [] as $f)
                            <option value="{{ $f->id }}">{{ $f->faculty_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_name">Department Name</label>
                    <div class="input-group input-group-merge">
                        <span id="department_name2" class="input-group-text"><i class="ti ti-building"></i></span>
                        <input type="text" id="department_name" class="form-control dt-department-name"
                            name="department_name" placeholder="Enter department name"
                            aria-describedby="department_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_code">Department Code</label>
                    <input type="text" id="department_code" class="form-control dt-department-code" name="department_code"
                        placeholder="e.g. CS" maxlength="50" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="head_chairman_name">Head / Chairman Name</label>
                    <input type="text" id="head_chairman_name" class="form-control dt-head-name" name="head_chairman_name"
                        placeholder="Full name" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_email">Email</label>
                    <input type="email" id="department_email" class="form-control dt-email" name="email"
                        placeholder="email@domain.com" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_phone">Phone</label>
                    <input type="text" id="department_phone" class="form-control dt-phone" name="phone"
                        placeholder="Phone number" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="department_status">Status</label>
                    <select id="department_status" class="form-select dt-status" name="status">
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
    <!--/ DataTable with Buttons -->
@endsection
