@extends('layouts/layoutMaster')

@section('title', 'Bus Sub-Type Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.busSubTypeUrls = AppUtils.buildApiUrls('app/settings/bus-sub-type');
        console.log('Bus Sub-Type URLs:', window.busSubTypeUrls);
    </script>
    <script src="{{ asset('assets/js/bus-sub-type-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Sub-Type Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Bus Sub-Type</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="sub_type_name">Sub-Type Name</label>
                    <div class="input-group input-group-merge">
                        <span id="sub_type_name2" class="input-group-text"><i class="ti ti-car"></i></span>
                        <input type="text" id="sub_type_name" class="form-control dt-full-name" name="sub_type_name"
                            placeholder="Enter Sub-Type Name" aria-label="Enter Sub-Type Name"
                            aria-describedby="sub_type_name2" />
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

