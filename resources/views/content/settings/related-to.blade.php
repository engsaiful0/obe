@extends('layouts/layoutMaster')

@section('title', 'Related To Settings')

@section('page-script')
    <script>
        window.relatedToUrls = AppUtils.buildApiUrls('app/settings/status-related-to');
    </script>
    <script src="{{ asset('assets/js/related-to-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="related-to-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="relatedToCanvasTitle">New Related To</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="related-to-form pt-0 row g-2" id="form-related-to-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="related_to_name">Name</label>
                    <div class="input-group input-group-merge">
                        <span id="related_to_name_prefix" class="input-group-text"><i class="ti ti-link"></i></span>
                        <input type="text" id="related_to_name" class="form-control dt-related-to-name" name="name"
                            maxlength="255" placeholder="Enter name" aria-describedby="related_to_name_prefix" />
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
