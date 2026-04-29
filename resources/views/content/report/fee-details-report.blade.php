@extends('layouts/layoutMaster')

@section('title', 'Fee Details Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
    // Initialize Select2
    $('#student_id').select2({
      placeholder: "Select a Student",
      allowClear: true
    });

    // Handle print button click
    $('#printButton').on('click', function () {
      var printContents = document.getElementById('printableArea').innerHTML;
      var originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      location.reload(); // Reload to restore the original page
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Fee Details Report</h5>
        <div>
            <button id="printButton" class="btn btn-info">Print</button>
            <a href="{{ route('fee-details-report.excel', request()->query()) }}" class="btn btn-success">Export to Excel</a>
        </div>
    </div>
  <div class="card-body">
    {{-- Search Form --}}
    <form action="{{ route('fee-details-report') }}" method="GET">
      <div class="row">
        <div class="col-md-4">
          <label for="student_id" class="form-label">Student</label>
          <select id="student_id" name="student_id" class="form-select">
            <option value="">All</option>
            @foreach ($students as $student)
            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
              {{ $student->full_name_in_english_block_letter }} ({{$student->student_unique_id}})
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary mt-4">Search</button>
        </div>
      </div>
    </form>
  </div>
  <div id="printableArea">
    <div class="table-responsive text-nowrap">
      <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Student ID</th>
                <th rowspan="2">Student Name</th>
                @foreach ($feeHeads as $feeHead)
                    <th rowspan="2">{{ $feeHead->name }}</th>
                @endforeach
                @if ($monthlyFeeHead)
                    <th colspan="12" class="text-center">Year 1</th>
                    <th colspan="12" class="text-center">Year 2</th>
                    <th colspan="12" class="text-center">Year 3</th>
                    <th colspan="12" class="text-center">Year 4</th>
                @endif
            </tr>
            @if ($monthlyFeeHead)
                <tr>
                    @for ($y = 1; $y <= 4; $y++)
                        @for ($m = 1; $m <= 12; $m++)
                            <th>{{ date('M', mktime(0, 0, 0, $m, 10)) }}</th>
                        @endfor
                    @endfor
                </tr>
            @endif
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($reportData as $data)
                <tr>
                    <td>{{ $loop->iteration + $paginatedStudents->firstItem() - 1 }}</td>
                    <td>{{ $data['student']->student_unique_id }}</td>
                    <td>{{ $data['student']->full_name_in_english_block_letter }}</td>
                    @foreach ($feeHeads as $feeHead)
                        <td>
                            @if ($data['paid_fee_heads']->contains($feeHead->id))
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </td>
                    @endforeach
                    @if ($monthlyFeeHead)
                        @for ($y = 1; $y <= 4; $y++)
                            @for ($m = 1; $m <= 12; $m++)
                                <td>
                                    @if ($data['monthly_payments'][$y][$m])
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            @endfor
                        @endfor
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + $feeHeads->count() + ($monthlyFeeHead ? 48 : 0) }}" class="text-center">No data found.</td>
                </tr>
            @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $paginatedStudents->appends(request()->query())->links() }}
  </div>
</div>
@endsection
