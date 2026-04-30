@extends('layouts/layoutMaster')

@section('title', 'Batch Settings')

@section('page-script')
    <script>
        window.batchUrls = AppUtils.buildApiUrls('app/settings/batch');
    </script>
    <script src="{{ asset('assets/js/batch-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Program</th>
                        <th>Batch Name</th>
                        <th>Batch Code</th>
                        <th>Admission Session</th>
                        <th>Start Date</th>
                        <th>Expected Passing Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record-batch" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="batchCanvasTitle">New Batch</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="batch-form pt-0 row g-2" id="form-batch-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="program_id">Program</label>
                    <select id="program_id" name="program_id" class="form-select dt-program-id" required>
                        <option value="">Select program</option>
                        @foreach($programs ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->program_name }} @if($p->program_code)({{ $p->program_code }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="batch_name">Batch Name</label>
                    <input type="text" id="batch_name" name="batch_name" class="form-control dt-batch-name"
                        maxlength="255" placeholder="e.g. 45th Batch" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="batch_code">Batch Code</label>
                    <input type="text" id="batch_code" name="batch_code" class="form-control dt-batch-code"
                        maxlength="50" placeholder="e.g. B45" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="academic_session_id">Admission Session</label>
                    <select id="academic_session_id" name="academic_session_id" class="form-select dt-academic-session-id" required>
                        <option value="">Select admission session</option>
                        @foreach($academicSessions ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->session_name }} — {{ $s->academic_year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control dt-start-date" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="expected_passing_year">Expected Passing Year</label>
                    <input type="number" id="expected_passing_year" name="expected_passing_year"
                        class="form-control dt-expected-year" min="1990" max="2100" step="1" placeholder="2029" />
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="batch_status_id">Status</label>
                    <select id="batch_status_id" name="status_id" class="form-select dt-batch-status-id" required>
                        <option value="">Select status</option>
                        @foreach($batchStatuses ?? [] as $st)
                            <option value="{{ $st->id }}">{{ $st->status_name }}</option>
                        @endforeach
                    </select>
                    @if(($batchStatuses ?? collect())->isEmpty())
                        <div class="form-text text-warning mt-1">Add statuses with Related To = Batch under Status settings first.</div>
                    @endif
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
