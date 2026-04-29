@extends('layouts/layoutMaster')

@section('title', 'Fee Collection Report')

@section('page-script')
<script>
  $(function () {
    // Initialize datepickers
    $('.datepicker').flatpickr();

    // Initialize Select2
    $('#student_info').select2({
      placeholder: "Select a Student",
      allowClear: true
    });

    // Handle academic year change
    $('#academic_year_id').on('change', function () {
      var academicYearId = $(this).val();
      var studentSelect = $('#student_info');

      studentSelect.empty().append('<option value="">Select a Student</option>');

      if (academicYearId) {
        $.ajax({
          url: '{{ url("app/get-students-by-year") }}/' + academicYearId,
          type: 'GET',
          success: function (data) {
            studentSelect.select2({
                data: $.map(data, function (student) {
                    return {
                        id: student.id,
                        text: student.full_name_in_english_block_letter + ' (' + student.unique_id + ')'
                    };
                })
            });
            studentSelect.val(null).trigger('change');
          }
        });
      } else {
        studentSelect.val(null).trigger('change');
      }
    });

    // Handle view details button click
    $('.view-details').on('click', function () {
        var feeDetails = $(this).data('fee-details');
        var studentName = $(this).data('student-name');
        var studentId = $(this).data('student-id');
        var className = $(this).data('class');
        var totalPayable = $(this).data('total-payable');
        var discount = $(this).data('discount');
        var netAmount = $(this).data('net-amount');
        var paymentMethod = $(this).data('payment-method');
        var collectionId = $(this).data('collection-id');
        var modal = $('#detailsModal');
        var feeDetailsBody = $('#feeDetailsBody');
        var modalPaymentDate = $(this).data('payment-date');

        // Clear previous details
        feeDetailsBody.empty();

        // Populate modal with new details
        $('#modalStudentName').text(studentName);
        $('#modalStudentId').text(studentId);
        $('#modalClass').text(className);
        $('#modalTotalPayable').text(totalPayable);
        $('#modalDiscount').text(discount);
        $('#modalNetAmount').text(netAmount);
        $('#modalPaymentMethod').text(paymentMethod);
        $('#modalPaymentDate').text(modalPaymentDate);

        if (feeDetails && feeDetails.length > 0) {
            var totalAmount = 0;
            feeDetails.forEach(function (detail) {
                var row = '<tr>' +
                    '<td>' + detail.name + '</td>' +
                    '<td>' + detail.amount + '</td>' +
                    '</tr>';
                feeDetailsBody.append(row);
                totalAmount += parseFloat(detail.amount);
            });
            // Add a total row
            var totalRow = '<tr>' +
                '<td><strong>Total</strong></td>' +
                '<td><strong>' + totalAmount.toFixed(2) + '</strong></td>' +
                '</tr>';
            feeDetailsBody.append(totalRow);
        } else {
            var row = '<tr><td colspan="2" class="text-center">No details available.</td></tr>';
            feeDetailsBody.append(row);
        }

        // Set the PDF download link
        var pdfUrl = '{{ url("app/fee-collection/details-pdf") }}/' + collectionId;
        $('#pdfButton').attr('href', pdfUrl);

        // Show the modal
        modal.modal('show');
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
        <h5 class="card-title mb-0">Fee Collection Report</h5>
        <div>
            <a href="{{ route('fee-collection-report.excel', request()->query()) }}" class="btn btn-success">Export to Excel</a>
        </div>
    </div>
  <div class="card-body">
    {{-- Search Form --}}
    <form action="{{ route('fee-collection-report') }}" method="GET">
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
          <label for="student_info" class="form-label">Student</label>
          <select id="student_info" name="student_info" class="form-select"></select>
        </div>
        <div class="col-md-2">
            <label for="date_range" class="form-label">Date Range</label>
            <select id="date_range" name="date_range" class="form-select select2">
                <option value="">Custom</option>
                <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="last_six_months" {{ request('date_range') == 'last_six_months' ? 'selected' : '' }}>Last 6 Months</option>
                <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
            </select>
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
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary mt-4">Search</button>
        </div>
      </div>
    </form>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>Student Name</th>
          <th>Academic Year</th>
          <th>Semester</th>
          <th>Payment Method</th>
          <th>Total Amount</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($feeCollections as $collection)
        <tr>
          <td>{{ $collection->student->full_name_in_english_block_letter ?? '' }}</td>
          <td>{{ $collection->academic_year->academic_year_name ?? '' }}</td>
          <td>{{ $collection->semester->semester_name ?? '' }}</td>
          <td>{{ $collection->payment_method->payment_method_name ?? '' }}</td>
          <td>{{ $collection->total_amount }}</td>
          <td>{{ $collection->date }}</td>
          <td>
            <button type="button" class="btn btn-sm btn-info view-details"
                    data-fee-details="{{ json_encode($collection->fee_heads) }}"
                    data-student-name="{{ $collection->student->full_name_in_english_block_letter ?? '' }}"
                    data-student-id="{{ $collection->student->unique_id ?? '' }}"
                    data-class="{{ $collection->semester->semester_name ?? '' }}"
                    data-total-payable="{{ $collection->total_payable }}"
                    data-discount="{{ $collection->discount }}"
                    data-net-amount="{{ $collection->net_payable }}"
                    data-payment-date="{{ $collection->date }}"
                    data-payment-method="{{ $collection->payment_method->payment_method_name ?? '' }}"
                    data-collection-id="{{ $collection->id }}">
              Details
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center">No fee collections found.</td>
        </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-end">Total Amount:</th>
          <th colspan="3">{{ number_format($totalAmount, 2) }}</th>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="card-footer">
    {{ $feeCollections->links() }}
  </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Fee Collection Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="printableArea">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Student Details:</h6>
                            <p><strong>Name:</strong> <span id="modalStudentName"></span></p>
                            <p><strong>ID:</strong> <span id="modalStudentId"></span></p>
                            <p><strong>Class:</strong> <span id="modalClass"></span></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6>Payment Details:</h6>
                            <p><strong>Payment Method:</strong> <span id="modalPaymentMethod"></span></p>
                            <p><strong>Date:</strong> <span id="modalPaymentDate"></span></p>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Fee Head Name</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody id="feeDetailsBody">
                        {{-- Fee details will be populated here by JavaScript --}}
                        </tbody>
                    </table>
                    <div class="row justify-content-end">
                        <div class="col-md-4 text-end">
                            <p><strong>Payable Amount:</strong> <span id="modalTotalPayable"></span></p>
                            <p><strong>Discount:</strong> <span id="modalDiscount"></span></p>
                            <hr>
                            <p><strong>Net Amount:</strong> <span id="modalNetAmount"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printButton">Print</button>
                <a href="#" id="pdfButton" class="btn btn-success" target="_blank">Download as PDF</a>
            </div>
        </div>
    </div>
</div>
@endsection
