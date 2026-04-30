@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Permission - Settings')

@section('page-script')
    <script>
        window.permissionUrls = AppUtils.buildApiUrls('app/settings/permission');
    </script>
    <script src="{{ asset('assets/js/permission-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
              
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatables-basic table table-bordered" id="permission-table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Permission Name</th>
                                    <th>Assigned Rules</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <div class="offcanvas offcanvas-end" id="add-new-record">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="exampleModalLabel">New Permission</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body flex-grow-1">
                    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                        <div class="col-sm-12">
                            <label class="form-label" for="permission_name">Permission Name</label>
                            <div class="input-group input-group-merge">
                                <span id="permission_name2" class="input-group-text"><i class="ti ti-shield-lock"></i></span>
                                <input type="text" id="permission_name" class="form-control dt-permission-name"
                                    name="name" placeholder="e.g. settings-view" maxlength="255" autocomplete="off"
                                    aria-describedby="permission_name2" />
                            </div>
                            <small class="text-muted">Use a short slug-style name; assign rules under Rules &amp; Permissions.</small>
                        </div>
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
