@extends('layouts/layoutMaster')

@section('title', 'Status Settings')

<!-- Page Scripts -->
@section('page-script')
<script>
    window.statusUrls = AppUtils.buildApiUrls('app/settings/status');
    window.statusRelatedToListUrl = @json(route('app-settings-get-status-related-to'));

</script>
<script src="{{ asset('assets/js/status-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-datatable table-responsive pt-0">
        <table class="datatables-basic table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Status Name</th>
                    <th>Related To</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">New Status</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
            <div class="col-sm-12">
                <label class="form-label" for="status_name">Status Name</label>
                <div class="input-group input-group-merge">
                    <span id="status_name2" class="input-group-text"><i class="ti ti-flag"></i></span>
                    <input type="text" id="status_name" class="form-control dt-full-name" name="status_name" placeholder="Enter Status Name" aria-label="Enter Status Name" aria-describedby="status_name2" />
                </div>
            </div>
            <div class="col-sm-12">
                <label class="form-label" for="related_to_id">Related To</label>
                <select id="related_to_id" class="form-select dt-related-to" name="related_to_id" required>
                    <option value="">Select Related To</option>
                    @isset($relatedTos)
                    @foreach($relatedTos as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                    @endisset
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
