@extends('layouts/layoutMaster')

@section('title', 'Academic Year Settings')

@section('page-script')
    <script>
        console.log('Base URL from HTML:', $('html').attr('data-base-url'));
        window.academicYearUrls = AppUtils.buildApiUrls('app/settings/academic-year');
        console.log('Academic Year URLs:', window.academicYearUrls);
        console.log('getData URL:', window.academicYearUrls.getData);
    </script>
    <script src="{{ asset('assets/js/academic-year-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Academic Year</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Academic Year</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="academic_year_name">Academic Year</label>
                    <div class="input-group input-group-merge">
                        <span id="academic_year_name2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" id="academic_year_name" class="form-control dt-full-name"
                            name="academic_year_name" placeholder="Enter Academic Year" aria-label="Enter Academic Year"
                            aria-describedby="academic_year_name2" />
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
    </div>

@endsection
