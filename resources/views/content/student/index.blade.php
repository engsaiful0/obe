@extends('layouts/layoutMaster')

@section('title', 'Student List')

@section('vendor-style')
@endsection

@section('page-script')
    <script>
        window.studentListUrl = @json(route('student.list'));
        window.studentBatchesUrl = @json(route('student.batches-by-program'));
    </script>
    <script src="{{ asset('assets/js/student-index.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Student List</h5>
            @permission('student-add')
                <a href="{{ route('student.add-student') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Add Student
                </a>
            @endpermission
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row g-2 mb-3 align-items-end">
                <div class="col-12">
                    <label class="form-label small mb-0" for="filter-q">Search</label>
                    <input type="search" id="filter-q" class="form-control form-control-sm js-student-filter"
                        placeholder="Name, ID, email, phone, guardian…" autocomplete="off" />
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Program</label>
                    <select id="filter-program_id" class="form-select form-select-sm js-student-filter">
                        <option value="">All programs</option>
                        @foreach ($programs ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->program_name }}@if ($p->program_code)
                                    ({{ $p->program_code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Batch</label>
                    <select id="filter-batch_id" class="form-select form-select-sm js-student-filter"
                        disabled>
                        <option value="">All batches</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Session</label>
                    <select id="filter-academic_session_id"
                        class="form-select form-select-sm js-student-filter">
                        <option value="">All sessions</option>
                        @foreach ($academicSessions ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->session_name }} — {{ $s->academic_year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Gender</label>
                    <select id="filter-gender_id" class="form-select form-select-sm js-student-filter">
                        <option value="">All</option>
                        @foreach ($genders ?? [] as $g)
                            <option value="{{ $g->id }}">{{ $g->gender_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Religion</label>
                    <select id="filter-religion_id" class="form-select form-select-sm js-student-filter">
                        <option value="">All</option>
                        @foreach ($religions ?? [] as $r)
                            <option value="{{ $r->id }}">{{ $r->religion_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Status</label>
                    <select id="status_id" class="form-select form-select-sm js-student-filter">
                        <option value="">All</option>
                        @foreach ($studentStatuses ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->status_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Shift</label>
                    <select id="filter-shift" class="form-select form-select-sm js-student-filter">
                        <option value="">All</option>
                        <option value="Morning">Morning</option>
                        <option value="Evening">Evening</option>
                        <option value="Weekend">Weekend</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Type</label>
                    <select id="filter-student_type" class="form-select form-select-sm js-student-filter">
                        <option value="">All</option>
                        <option value="Regular">Regular</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Foreign">Foreign</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Adm. from</label>
                    <input type="date" id="filter-admission_date_from"
                        class="form-control form-control-sm js-student-filter" />
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Adm. to</label>
                    <input type="date" id="filter-admission_date_to"
                        class="form-control form-control-sm js-student-filter" />
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Sort</label>
                    <select id="filter-sort" class="form-select form-select-sm js-student-filter">
                        <option value="student_name">Name</option>
                        <option value="student_code">Student ID</option>
                        <option value="created_at">Created</option>
                        <option value="admission_date">Admission date</option>
                        <option value="id">ID</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Order</label>
                    <select id="filter-dir" class="form-select form-select-sm js-student-filter">
                        <option value="asc">Asc</option>
                        <option value="desc">Desc</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small mb-0">Per page</label>
                    <select id="filter-per_page" class="form-select form-select-sm js-student-filter">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="40" selected>40</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto ms-md-auto">
                    <button type="button" id="student-filter-reset" class="btn btn-sm btn-outline-secondary">
                        Reset filters
                    </button>
                </div>
            </div>

            <div id="student-list-alert" class="alert alert-danger py-2 d-none" role="alert"></div>

            <div class="position-relative">
                <div id="student-list-spinner"
                    class="d-none align-items-center justify-content-center position-absolute top-0 start-0 end-0 bottom-0 bg-body bg-opacity-75 rounded"
                    style="z-index: 2; min-height: 120px;">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:48px;">#</th>
                                <th style="width:56px;">Photo</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Batch</th>
                                <th>Program</th>
                                <th>Academic Session</th>
                                <th>Gender</th>
                                <th>Shift</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="student-table-body">
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="student-list-footer"
                class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                <p id="student-list-summary" class="text-muted mb-0 small"></p>
                <nav aria-label="Student pagination">
                    <ul id="student-pagination"
                        class="pagination pagination-sm mb-0 justify-content-center"></ul>
                </nav>
            </div>

            <div id="student-list-empty"
                class="text-center py-5 d-none text-muted">
                <i class="ti ti-users mb-2" style="font-size: 3rem; opacity:.4;"></i>
                <p class="mb-0">No students match your filters.</p>
            </div>
        </div>
    </div>
@endsection
