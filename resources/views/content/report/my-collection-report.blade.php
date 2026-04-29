@extends('layouts/contentNavbarLayout')

@section('title', 'My Collection Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.select2').select2();
    
    // Print functionality
    $('#printButton').click(function() {
      var newWindow = window.open('', '_blank');
      var content = $('#printableContent').html();
      newWindow.document.write(`
        <html>
          <head>
            <title>My Collection Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; }
              .summary { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
              .summary h4 { margin: 0 0 10px 0; }
              .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; }
              .section-title { background: #007bff; color: white; padding: 10px; margin: 20px 0 10px 0; }
            </style>
          </head>
          <body>
            ${content}
          </body>
        </html>
      `);
      newWindow.document.close();
      newWindow.print();
    });
  });
</script>
@endsection

@section('content')
<div id="printableContent">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">My Collection Report</h5>
      <div>
        <button id="printButton" class="btn btn-info">Print</button>
        <a href="{{ route('my-collection-report.excel', request()->query()) }}" class="btn btn-success">Export to Excel</a>
        <a href="{{ route('my-collection-report.pdf', request()->query()) }}" class="btn btn-danger">Export to PDF</a>
      </div>
    </div>
    
    <div class="card-body">
      {{-- Search Form --}}
      <form action="{{ route('my-collection-report') }}" method="GET">
        <div class="row">
          @if($userRole === 'Super Admin')
          <div class="col-md-3">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" class="form-select select2">
              <option value="">All Users</option>
              @foreach ($users as $user)
              <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->rule->name ?? 'No Role' }})
              </option>
              @endforeach
            </select>
          </div>
          @endif
          
          <div class="col-md-2">
            <label for="date_range" class="form-label">Date Range</label>
            <select id="date_range" name="date_range" class="form-select select2">
              <option value="">All Time</option>
              <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
              <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
              <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
              <option value="last_six_months" {{ request('date_range') == 'last_six_months' ? 'selected' : '' }}>Last 6 Months</option>
              <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
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
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('my-collection-report') }}" class="btn btn-secondary">Clear</a>
            </div>
          </div>
        </div>
      </form>

      {{-- Summary Cards --}}
      <div class="row mt-4">
        <div class="col-md-4">
          <div class="card bg-primary text-white">
            <div class="card-body">
              <h5  style="color: white;" class="card-title">Total Fee Collection</h5>
              <h3  style=" color: white;" class="mb-0">৳{{ number_format($totalFeeCollection, 2) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-danger text-white">
            <div class="card-body">
              <h5 style=" color: white;" class="card-title">Total Expenses</h5>
              <h3 style=" color: white;" class="mb-0">৳{{ number_format($totalExpenses, 2) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-success text-white">
            <div class="card-body">
              <h5 style=" color: white;" class="card-title">Net Amount</h5>
              <h3 style=" color: white;" class="mb-0">৳{{ number_format($netAmount, 2) }}</h3>
            </div>
          </div>
        </div>
      </div>

      {{-- Fee Collections Section --}}
      <div class="mt-4">
        <h4 class="text-primary">Fee Collections</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Student Name</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Date</th>
                @if($userRole === 'Super Admin')
                <th>Collected By</th>
                @endif
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($feeCollections as $collection)
              <tr>
                <td>{{ $collection->student->full_name_in_english_block_letter ?? '' }}</td>
                <td>{{ $collection->academic_year->academic_year_name ?? '' }}</td>
                <td>{{ $collection->semester->semester_name ?? '' }}</td>
                <td>{{ $collection->payment_method->payment_method_name ?? '' }}</td>
                <td>৳{{ number_format($collection->total_amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($collection->date)->format('d-m-Y') }}</td>
                @if($userRole === 'Super Admin')
                <td>{{ $collection->user->name ?? '' }}</td>
                @endif
                <td>
                  <button type="button" class="btn btn-sm btn-info view-fee-details"
                          data-fee-details="{{ json_encode($collection->fee_heads) }}"
                          data-student-name="{{ $collection->student->full_name_in_english_block_letter ?? '' }}"
                          data-total-amount="{{ $collection->total_amount }}"
                          data-payment-date="{{ $collection->date }}"
                          data-payment-method="{{ $collection->payment_method->payment_method_name ?? '' }}">
                    Details
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="{{ $userRole === 'Super Admin' ? '8' : '7' }}" class="text-center">No fee collections found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-center">
          {{ $feeCollections->links() }}
        </div>
      </div>

      {{-- Expenses Section --}}
      <div class="mt-4">
        <h4 class="text-danger">Expenses</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Expense Head</th>
                <th>Date</th>
                <th>Remarks</th>
                <th>Amount</th>
                @if($userRole === 'Super Admin')
                <th>Created By</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse ($expenses as $expense)
              <tr>
                <td>{{ $expense->expenseHead->name ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}</td>
                <td>{{ $expense->remarks }}</td>
                <td>৳{{ number_format($expense->amount, 2) }}</td>
                @if($userRole === 'Super Admin')
                <td>{{ $expense->user->name ?? '' }}</td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ $userRole === 'Super Admin' ? '5' : '4' }}" class="text-center">No expenses found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-center">
          {{ $expenses->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Fee Details Modal --}}
<div class="modal fade" id="feeDetailsModal" tabindex="-1" aria-labelledby="feeDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="feeDetailsModalLabel">Fee Collection Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Student Name:</strong> <span id="modal-student-name"></span>
          </div>
          <div class="col-md-6">
            <strong>Payment Date:</strong> <span id="modal-payment-date"></span>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Payment Method:</strong> <span id="modal-payment-method"></span>
          </div>
          <div class="col-md-6">
            <strong>Total Amount:</strong> ৳<span id="modal-total-amount"></span>
          </div>
        </div>
        
        <h6>Fee Heads:</h6>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Fee Head</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody id="modal-fee-heads">
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('.view-fee-details').click(function() {
    var feeDetails = $(this).data('fee-details');
    var studentName = $(this).data('student-name');
    var totalAmount = $(this).data('total-amount');
    var paymentDate = $(this).data('payment-date');
    var paymentMethod = $(this).data('payment-method');
    
    $('#modal-student-name').text(studentName);
    $('#modal-payment-date').text(paymentDate);
    $('#modal-payment-method').text(paymentMethod);
    $('#modal-total-amount').text(parseFloat(totalAmount).toFixed(2));
    
    var feeHeadsHtml = '';
    if (Array.isArray(feeDetails)) {
      feeDetails.forEach(function(feeHead) {
        feeHeadsHtml += '<tr><td>' + feeHead.name + '</td><td>৳' + parseFloat(feeHead.amount).toFixed(2) + '</td></tr>';
      });
    }
    $('#modal-fee-heads').html(feeHeadsHtml);
    
    $('#feeDetailsModal').modal('show');
  });
});
</script>
@endsection
