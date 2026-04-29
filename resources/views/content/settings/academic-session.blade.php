@extends('layouts/layoutMaster')

@section('title', 'Academic Session')

@section('page-script')
    <script>
        window.academicSessionUrls = AppUtils.buildApiUrls('app/settings/academic-session');
    </script>
    <script src="{{ asset('assets/js/academic-session-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Session Name</th>
                        <th>Academic Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-academic-session" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="academicSessionCanvasTitle">New Academic Session</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="academic-session-form pt-0 row g-2" id="form-academic-session-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="session_name">Session Name</label>
                    <input type="text" id="session_name" name="session_name" class="form-control dt-session-name"
                        placeholder="e.g. Fall 2025" maxlength="255" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="academic_year">Academic Year</label>
                    <input type="number" id="academic_year" name="academic_year" class="form-control dt-academic-year"
                        min="1990" max="2100" step="1" placeholder="2025" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control dt-start-date" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control dt-end-date" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="session_status">Status</label>
                    <select id="session_status" name="status" class="form-select dt-status">
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
