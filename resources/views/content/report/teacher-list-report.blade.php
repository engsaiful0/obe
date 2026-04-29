@extends('layouts/layoutMaster')
@section('title', 'Teacher List Report')
@section('page-script')
<script>
    function printReport() {
        var printContent = document.querySelector('.table-responsive').innerHTML;
        var newWindow = window.open('', '_blank');
        newWindow.document.write('<html><head><title>Teacher List Report - {{ date("Y-m-d H:i:s") }}</title>');
        newWindow.document.write('<style>');
        newWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
        newWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        newWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
        newWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
        newWindow.document.write('h1 { text-align: center; margin-bottom: 20px; }');
        newWindow.document.write('@media print { body { margin: 0; } }');
        newWindow.document.write('</style>');
        newWindow.document.write('</head><body>');
        newWindow.document.write('<h1>Teacher List Report</h1>');
        newWindow.document.write('<p><strong>Generated on:</strong> ' + new Date().toLocaleString() + '</p>');
        newWindow.document.write(printContent);
        newWindow.document.write('</body></html>');
        newWindow.document.close();
        newWindow.print();
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
        <h5 class="card-title">Teacher List Report</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher-list-report') }}" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <label for="designation_id" class="form-label">Designation</label>
                    <select id="designation_id" name="designation_id" class="form-select select2">
                        <option value="">All</option>
                        @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                            {{ $designation->designation_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="teacher_unique_id" class="form-label">Teacher ID</label>
                    <input type="text" id="teacher_unique_id" name="teacher_unique_id" class="form-control" value="{{ request('teacher_unique_id') }}">
                </div>
                <div class="col-md-4">
                  <label for="per_page" class="form-label">Per Page</label>
                  <select id="per_page" name="per_page" class="form-select select2">
                      <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                      <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                      <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                      <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                      <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                      <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
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
                    PDF and Excel downloads include all teachers matching your filters
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
                    <th>Teacher ID</th>
                    <th>Teacher Name</th>
                    <th>Designation</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Joining Date</th>
                    <th>Basic Salary</th>
                    <th>Gross Salary</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr>
                        <td>{{ ($teachers->currentPage() - 1) * $teachers->perPage() + $loop->iteration }}</td>
                        <td>{{ $teacher->teacher_unique_id }}</td>
                        <td>{{ $teacher->teacher_name }}</td>
                        <td>{{ $teacher->designation->designation_name ?? '' }}</td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->mobile }}</td>
                        <td>{{ ucfirst($teacher->gender) }}</td>
                        <td>{{ $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('d-m-Y') : '' }}</td>
                        <td>{{ $teacher->basic_salary ? '৳' . number_format($teacher->basic_salary) : '' }}</td>
                        <td>{{ $teacher->gross_salary ? '৳' . number_format($teacher->gross_salary) : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No teachers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $teachers->appends(request()->query())->links() }}
    </div>
</div>
@endsection
