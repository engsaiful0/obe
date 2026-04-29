@extends('layouts/layoutMaster')

@section('title', 'Department Settings')

@section('page-script')
    <script>
        window.departmentUrls = AppUtils.buildApiUrls('app/settings/department');
        console.log('Department URLs:', window.departmentUrls);
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
                        <th>Department Name</th>
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
                    <label class="form-label" for="department_name">Department Name</label>
                    <div class="input-group input-group-merge">
                        <span id="department_name2" class="input-group-text"><i class="ti ti-building"></i></span>
                        <input type="text" id="department_name" class="form-control dt-full-name"
                            name="department_name" placeholder="Enter Department Name"
                            aria-label="Enter Department Name" aria-describedby="department_name2" />
                    </div>
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

