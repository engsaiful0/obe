@extends('layouts/layoutMaster')

@section('title', 'Fee Collection List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
    $('.datepicker').flatpickr();

    $('#print_btn').on('click', function () {
      window.print();
    });
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Student wise Report</h5>
    <div>
      <button id="print_btn" class="btn btn-primary">Print</button>
      <a href="{{ route('student-wise-report.pdf', request()->all()) }}" class="btn btn-primary">Download PDF</a>
    </div>
  </div>
  <div class="card-body">
    {{-- Search Form --}}
    <form action="{{ route('student-wise-report') }}" method="GET">
      <div class="row">
        <div class="col-md-3">
          <label for="academic_year_id" class="form-label">Academic Year</label>
          <select id="academic_year_id" name="academic_year_id" class="form-select">
            <option value="">All</option>
            @foreach ($academicYears as $year)
            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
              {{ $year->academic_year_name }}
            </option>
            @endforeach
          </select>
        </div>
      
        <div class="col-md-2">
          <label for="student_info" class="form-label">Student Info (Name/ID)</label>
          <input type="text" id="student_info" name="student_info" class="form-control"
            value="{{ request('student_info') }}">
        </div>
        <div class="col-md-2">
          <label for="from_date" class="form-label">From Date</label>
          <input type="text" id="from_date" name="from_date" class="form-control datepicker"
            value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
          <label for="to_date" class="form-label">To Date</label>
          <input type="text" id="to_date" name="to_date" class="form-control datepicker"
            value="{{ request('to_date') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary mt-4">Search</button>
        </div>
      </div>
    </form>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Academic Year</th>
          <th>Email</th>
          <th>Phone</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($students as $student)
        <tr>
          <td>{{ $student->student_id }}</td>
          <td>{{ $student->student_name }}</td>
          <td>{{ $student->academicYear->academic_year_name ?? '' }}</td>
          <td>{{ $student->email }}</td>
          <td>{{ $student->phone }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No students found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $students->links() }}
  </div>
</div>
@endsection
