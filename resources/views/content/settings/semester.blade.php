@extends('layouts/layoutMaster')

@section('title', 'Semester Settings')

@section('page-script')
    <script>
        window.semesterUrls = AppUtils.buildApiUrls('app/settings/semester');
    </script>
    <script src="{{ asset('assets/js/semester-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Program</th>
                        <th>Semester Name</th>
                        <th>Semester Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-semester" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="semesterCanvasTitle">New Semester</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="semester-form pt-0 row g-2" id="form-semester-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="program_id">Program</label>
                    <select id="program_id" name="program_id" class="form-select dt-program-id" required>
                        <option value="">Select program</option>
                        @foreach($programs ?? [] as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->program_name }}
                                @if($p->program_code)
                                    ({{ $p->program_code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="semester_name">Semester Name</label>
                    <input type="text" id="semester_name" name="semester_name" class="form-control dt-semester-name"
                        maxlength="255" placeholder="e.g. 1st Semester" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="semester_order">Semester Order</label>
                    <input type="number" id="semester_order" name="semester_order" class="form-control dt-semester-order"
                        min="1" max="32767" step="1" placeholder="1" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="semester_status">Status</label>
                    <select id="semester_status" name="status" class="form-select dt-status">
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
