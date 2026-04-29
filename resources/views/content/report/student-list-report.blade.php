@extends('layouts/layoutMaster')
@section('title', 'Student List Report')
@section('page-script')
<script>
    function printReport() {
        var printContent = document.querySelector('.table-responsive').innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
    }

    // Add loading state for PDF and Excel downloads
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const pdfBtn = form.querySelector('button[name="pdf"]');
        const excelBtn = form.querySelector('button[name="excel"]');
        
        if (pdfBtn) {
            pdfBtn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating PDF...';
                this.disabled = true;
            });
        }
        
        if (excelBtn) {
            excelBtn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating Excel...';
                this.disabled = true;
            });
        }
    });
</script>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Student List Report</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('student-list-report') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select id="academic_year_id" name="academic_year_id" class="form-select select2">
                        <option value="">All</option>
                        @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->academic_year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester_id" class="form-label">Semester</label>
                    <select id="semester_id" name="semester_id" class="form-select select2">
                        <option value="">All</option>
                        @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                            {{ $semester->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="technology_id" class="form-label">Technology</label>
                    <select id="technology_id" name="technology_id" class="form-select select2">
                        <option value="">All</option>
                        @foreach ($technologies as $technology)
                        <option value="{{ $technology->id }}" {{ request('technology_id') == $technology->id ? 'selected' : '' }}>
                            {{ $technology->technology_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                  <label for="per_page" class="form-label">Per Page</label>
                  <select id="per_page" name="per_page" class="form-select select2">
                      <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                      <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                      <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                      <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                  </select>
              </div>

            </div>
            <div class="row">
              <div class="col-md-8 d-flex justify-content-start align-items-center flex-wrap">
                <button type="submit" class="btn btn-primary mt-4">Search</button>
                <button type="button" class="btn btn-secondary mt-4 ms-2" onclick="printReport()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
                <button type="submit" class="btn btn-success mt-4 ms-2" name="excel" value="1">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </button>
                <button type="submit" class="btn btn-danger mt-4 ms-2" name="pdf" value="1">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </button>
                <small class="text-muted mt-4 ms-3">
                    <i class="fas fa-info-circle me-1"></i>
                    PDF and Excel downloads include all students matching your filters
                </small>
            </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table border-top table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Student Picture</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Technology</th>
                    <!-- <th>Email</th> -->
                    <th>Student Phone</th>
                    <th>Guardian Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->student_unique_id }}</td>
                        <td>{{ $student->full_name_in_english_block_letter }}</td>
                        <td> @if($student->picture)
                  <img src="{{ asset('images/pictures/' . $student->picture) }}" 
                       alt="{{ $student->full_name_in_english_block_letter }}" 
                       class="rounded-circle" width="40" height="40">
                @else
                  <img src="{{ asset('images/students/default.png') }}" 
                       alt="default" class="rounded-circle" width="40" height="40">
                @endif</td>
                        <td>{{ $student->academicYear->academic_year_name ?? '' }}</td>
                        <td>{{ $student->semester->semester_name ?? '' }}</td>
                        <td>{{ $student->technology->technology_name ?? '' }}</td>
                        <!-- <td>{{ $student->email }}</td> -->
                        <td>{{ $student->personal_number }}</td>
                        <td>{{ $student->guardian_phone }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $students->appends(request()->query())->links() }}
    </div>
</div>
@endsection

