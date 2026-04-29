@extends('layouts/layoutMaster')

@section('title', 'Employee List Report')

@section('page-script')
<script>
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
    <h4>Employee List Report</h4>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('employee-list-report') }}">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="designation_id">Designation</label>
            <select name="designation_id" id="designation_id" class="form-control select2">
              <option value="">All Designations</option>
              @foreach($designations as $designation)
              <option value="{{ $designation->id }}" {{ request('designation_id')==$designation->id ? 'selected' : '' }}>
                {{ $designation->designation_name }}
              </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="employee_unique_id">Employee ID</label>
            <input type="text" name="employee_unique_id" id="employee_unique_id" class="form-control"
              value="{{ request('employee_unique_id') }}" placeholder="Enter Employee ID">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="year">Year of Joining</label>
            <input type="number" name="year" id="year" class="form-control" value="{{ request('year') }}"
              placeholder="Enter Year">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="per_page">Rows per page</label>
            <select name="per_page" id="per_page" class="form-control select2">
              <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10</option>
              <option value="25" {{ request('per_page')==25 ? 'selected' : '' }}>25</option>
              <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50</option>
              <option value="100" {{ request('per_page')==100 ? 'selected' : '' }}>100</option>
              <option value="200" {{ request('per_page')==200 ? 'selected' : '' }}>200</option>
              <option value="500" {{ request('per_page')==500 ? 'selected' : '' }}>500</option>
            </select>
          </div>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12 d-flex justify-content-start align-items-center flex-wrap">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="{{ route('employee-list-report') }}" class="btn btn-secondary ms-2">Reset</a>
          <button type="submit" name="excel" value="1" class="btn btn-success ms-2">
              <i class="fas fa-file-excel me-1"></i>Excel
          </button>
          <a href="{{ route('employee-list-report.print-list', request()->query()) }}" 
             target="_blank" 
             class="btn btn-outline-primary ms-2">
              <i class="ti ti-printer me-1"></i> Print
          </a>
          <button type="submit" name="pdf" value="1" class="btn btn-danger ms-2">
              <i class="fas fa-file-pdf me-1"></i>PDF
          </button>
          <small class="text-muted ms-3">
              <i class="fas fa-info-circle me-1"></i>
              PDF and Excel downloads include all employees matching your filters
          </small>
        </div>
      </div>
    </form>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Employee ID</th>
          <th>Employee Name</th>
          <th>Designation</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Gender</th>
          <th>Date of Join</th>
          <th>Basic Salary</th>
          <th>Gross Salary</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($employees as $key => $employee)
        <tr>
          <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}</td>
          <td>{{ $employee->employee_unique_id }}</td>
          <td>{{ $employee->employee_name }}</td>
          <td>{{ $employee->designation->designation_name ?? '' }}</td>
          <td>{{ $employee->email }}</td>
          <td>{{ $employee->mobile }}</td>
          <td>{{ ucfirst($employee->gender) }}</td>
          <td>{{ $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('d-m-Y') : '' }}</td>
          <td>{{ $employee->basic_salary ? '৳' . number_format($employee->basic_salary) : '' }}</td>
          <td>{{ $employee->gross_salary ? '৳' . number_format($employee->gross_salary) : '' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $employees->appends(request()->query())->links() }}
  </div>
</div>
@endsection
