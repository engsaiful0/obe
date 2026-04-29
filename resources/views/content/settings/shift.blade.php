@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Shift - Settings')

@section('page-script')
    <script>
        window.shiftUrls = AppUtils.buildApiUrls('app/settings/shift');
        console.log('Shift URLs:', window.shiftUrls);
    </script>
    <script src="{{ asset('assets/js/shift-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shift</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatables-basic table table-bordered" id="shift-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Shift Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal to add new record -->
            <div class="offcanvas offcanvas-end" id="add-new-record">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="exampleModalLabel">New Shift</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body flex-grow-1">
                    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                        <div class="col-sm-12">
                            <label class="form-label" for="semester_name">Shift Name</label>
                            <div class="input-group input-group-merge">
                                <span id="shift_name2" class="input-group-text"><i class="ti ti-user"></i></span>
                                <input type="text" id="shift_name" class="form-control dt-full-name"
                                    name="shift_name" placeholder="Enter Shift Name" aria-label="Enter Shift Name"
                                    aria-describedby="shift_name2" />
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <!--/ DataTable with Buttons -->
        </div>
    </div>
@endsection
