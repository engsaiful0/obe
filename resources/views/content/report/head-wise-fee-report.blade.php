@extends('layouts/layoutMaster')

@section('title', 'Head Wise Fee Report')
@section('page-script')
<script>
  $(function () {
    // Initialize Select2
    $('#student_id').select2({
      placeholder: "Select a Student",
      allowClear: true
    });

    $('#fee_head_id').select2({
      placeholder: "Select a Fee Head",
      allowClear: true
    });
    $('#per_page').select2({
      placeholder: "Select a Per Page",
      allowClear: true
    });

    // Handle print button click
    $('#printButton').on('click', function () {
      var printContent = document.querySelector('.table-responsive').innerHTML;
      var newWindow = window.open('', '_blank');
      newWindow.document.write('<html><head><title>Head Wise Fee Report - {{ date("Y-m-d H:i:s") }}</title>');
      newWindow.document.write('<style>');
      newWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
      newWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
      newWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
      newWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
      newWindow.document.write('h1 { text-align: center; margin-bottom: 20px; }');
      newWindow.document.write('.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }');
      newWindow.document.write('.bg-success { background-color: #28a745; color: white; }');
      newWindow.document.write('@media print { body { margin: 0; } }');
      newWindow.document.write('</style>');
      newWindow.document.write('</head><body>');
      newWindow.document.write('<h1>Head Wise Fee Report</h1>');
      newWindow.document.write('<p><strong>Generated on:</strong> ' + new Date().toLocaleString() + '</p>');
      newWindow.document.write(printContent);
      newWindow.document.write('</body></html>');
      newWindow.document.close();
      newWindow.print();
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Head Wise Fee Report</h5>
        <div>
            <button id="printButton" class="btn btn-info">Print</button>
            <a href="{{ route('head-wise-fee-report.excel', request()->query()) }}" class="btn btn-success">Export to Excel</a>
        </div>
    </div>
  <div class="card-body">
    {{-- Search Form --}}
    <form action="{{ route('head-wise-fee-report') }}" method="GET">
      <div class="row">
        <div class="col-md-3">
          <label for="student_id" class="form-label">Student</label>
          <select id="student_id" name="student_id" class="form-select">
            <option value="">All Students</option>
            @foreach ($students as $student)
            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
              {{ $student->full_name_in_english_block_letter }} ({{$student->student_unique_id}})
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label for="fee_head_id" class="form-label">Fee Head</label>
          <select id="fee_head_id" name="fee_head_id" class="form-select select2">
            <option value="">All Fee Heads</option>
            @foreach ($feeHeads as $feeHead)
            <option value="{{ $feeHead->id }}" {{ request('fee_head_id') == $feeHead->id ? 'selected' : '' }}>
              {{ $feeHead->name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label for="from_date" class="form-label">From Date</label>
          <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
          <label for="to_date" class="form-label">To Date</label>
          <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2">
          <label for="per_page" class="form-label">Per Page</label>
          <select id="per_page" name="per_page" class="form-select">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
            <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
          </select>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="{{ route('head-wise-fee-report') }}" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>
  </div>
  <div id="printableArea">
    <div class="table-responsive text-nowrap">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Fee Head</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse ($reportData as $data)
          <tr>
            <td>{{ $loop->iteration + ($feeCollections->currentPage() - 1) * $feeCollections->perPage() }}</td>
            <td>{{ $data['student']->student_unique_id }}</td>
            <td>{{ $data['student']->full_name_in_english_block_letter }}</td>
            <td>{{ $data['fee_head']->name }}</td>
            <td>৳{{ number_format($data['amount']) }}</td>
            <td>{{ \Carbon\Carbon::parse($data['payment_date'])->format('d-m-Y') }}</td>
            <td><span class="badge bg-success">{{ $data['status'] }}</span></td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center">No fee collection data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $feeCollections->appends(request()->query())->links() }}
  </div>
</div>
@endsection
