@extends('layouts/layoutMaster')

@section('title', 'Faculty Settings')

@section('page-script')
    <script>
        window.facultyUrls = AppUtils.buildApiUrls('app/settings/faculty');
    </script>
    <script src="{{ asset('assets/js/faculty-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Faculty Name</th>
                        <th>Faculty Code</th>
                        <th>Dean Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Faculty</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="faculty_name">Faculty Name</label>
                    <input type="text" id="faculty_name" class="form-control dt-faculty-name" name="faculty_name" placeholder="Enter faculty name" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="faculty_code">Faculty Code</label>
                    <input type="text" id="faculty_code" class="form-control dt-faculty-code" name="faculty_code" placeholder="Enter faculty code" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="dean_name">Dean Name</label>
                    <input type="text" id="dean_name" class="form-control dt-dean-name" name="dean_name" placeholder="Enter dean name" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" class="form-control dt-email" name="email" placeholder="Enter email address" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" class="form-control dt-phone" name="phone" placeholder="Enter phone number" />
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
