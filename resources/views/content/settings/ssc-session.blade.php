@extends('layouts/layoutMaster')

@section('title', 'SSC Session Settings')
@section('page-script')
    <script>
        window.sscSessionUrls = AppUtils.buildApiUrls('app/settings/ssc-session');
        console.log('SSC Session URLs:', window.sscSessionUrls);
    </script>
    <script src="{{ asset('assets/js/ssc-session-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Session Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New SSC Session</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="session_name">Session Name</label>
                    <div class="input-group input-group-merge">
                        <span id="session_name2" class="input-group-text"><i class="ti ti-list"></i></span>
                        <input type="text" id="session_name" class="form-control dt-full-name" name="session_name"
                            placeholder="Enter Session Name" aria-label="Enter Session Name"
                            aria-describedby="session_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
