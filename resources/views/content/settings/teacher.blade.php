@extends('layouts/layoutMaster')

@section('title', 'Teacher Settings')

@section('page-script')
    <script>
        window.teacherUrls = AppUtils.buildApiUrls('app/settings/teacher');
        window.teacherMetaUrls = {
            departments: @json(url('app/settings/get-department')) ,
            designations: @json(url('app/settings/get-teacher-designations')) ,
        };
    </script>
    <script src="{{ asset('assets/js/teacher-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Department</th>
                        <th>Teacher Name</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Employee ID</th>
                        <th>Login Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Teacher</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" class="form-select dt-department-id" name="department_id">
                        <option value="">Select department</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="teacher_name">Teacher Name</label>
                    <input type="text" id="teacher_name" class="form-control dt-teacher-name" name="teacher_name"
                        placeholder="Full name" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="designation_id">Designation</label>
                    <select id="designation_id" class="form-select dt-designation-id" name="designation_id">
                        <option value="">Select designation</option>
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" class="form-control dt-email" name="email"
                        placeholder="Contact email" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" class="form-control dt-phone" name="phone"
                        placeholder="Phone number" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" class="form-control dt-employee-id" name="employee_id"
                        placeholder="Unique employee ID" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="login_email">Login Email</label>
                    <input type="email" id="login_email" class="form-control dt-login-email" name="login_email"
                        placeholder="Optional — portal login" />
                    <small class="text-muted">If set, password is required for a new login account.</small>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" class="form-control dt-password" name="password"
                        placeholder="Optional — required with login email for new account" autocomplete="new-password" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" class="form-select dt-status" name="status">
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
