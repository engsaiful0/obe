@extends('layouts/layoutMaster')

@section('title', 'Reward Type Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.rewardTypeUrls = AppUtils.buildApiUrls('app/settings/reward-type');
        console.log('Reward Type URLs:', window.rewardTypeUrls);
    </script>
    <script src="{{ asset('assets/js/reward-type-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Reward Type</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="name">Name</label>
                    <div class="input-group input-group-merge">
                        <span id="name2" class="input-group-text"><i class="ti ti-trophy"></i></span>
                        <input type="text" id="name" class="form-control dt-full-name" name="name"
                            placeholder="Enter Reward Type Name" aria-label="Enter Reward Type Name"
                            aria-describedby="name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="description">Description</label>
                    <div class="input-group input-group-merge">
                        <span id="description2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                        <textarea id="description" class="form-control" name="description" rows="3"
                            placeholder="Enter Description" aria-label="Enter Description"
                            aria-describedby="description2"></textarea>
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

