@extends('layouts/layoutMaster')

@section('title', 'Salary Sheet')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('.select2').select2({ allowClear: true });

    // Auto-submit when month/year changes
    $('#year, #month').on('change', function() {
        $('#filter-form').trigger('submit');
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        const params = $(this).serialize();
        const $btn = $('#load-btn');
        $btn.prop('disabled', true);
        $('#load-spinner').removeClass('d-none');
        $('#load-text').text('Loading...');

        $('#sheet-loading').show();
        $.ajax({
            url: "{{ route('salary-sheet.index') }}",
            type: 'GET',
            data: params,
            dataType: 'html',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html) {
                $('#sheet-container').html(html);
                // Update export links to reflect current selection
                const query = '?' + params;
                const printBase = "{{ route('salary-sheet.print-list') }}";
                const pdfBase = "{{ route('salary-sheet.pdf') }}";
                const csvBase = "{{ route('salary-sheet.excel') }}";
                $("a[href^='{{ route('salary-sheet.print-list') }}']").attr('href', printBase + query);
                $("a[href^='{{ route('salary-sheet.pdf') }}']").attr('href', pdfBase + query);
                $("a[href^='{{ route('salary-sheet.excel') }}']").attr('href', csvBase + query);
            },
            error: function() {
                alert('Failed to load salary sheet.');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $('#load-spinner').addClass('d-none');
                $('#load-text').text('Load Sheet');
                $('#sheet-loading').hide();
            }
        });
    });

    // AJAX pagination inside sheet
    $('body').on('click', '#sheet-container .pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (!url) return;
        const $btn = $('#load-btn');
        $btn.prop('disabled', true);
        $('#load-spinner').removeClass('d-none');
        $('#load-text').text('Loading...');

        $('#sheet-loading').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html) {
                $('#sheet-container').html(html);
            },
            error: function() {
                alert('Failed to load page.');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $('#load-spinner').addClass('d-none');
                $('#load-text').text('Load Sheet');
                $('#sheet-loading').hide();
            }
        });
    });

    // Trigger load automatically when month or year changes
    $('#year, #month').on('change', function() {
        $('#filter-form').trigger('submit');
    });
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Salary Sheet</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('salary-sheet.print-list', ['year' => $year, 'month' => $month]) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('salary-sheet.pdf', ['year' => $year, 'month' => $month]) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> PDF
            </a>
            <a href="{{ route('salary-sheet.excel', ['year' => $year, 'month' => $month]) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <form id="filter-form" class="mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="year">Year</label>
                    <select id="year" name="year" class="form-select select2">
                        @for($y = date('Y')-5; $y <= date('Y')+5; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="month">Month</label>
                    <select id="month" name="month" class="form-select select2">
                        @foreach($months as $mValue => $mName)
                            <option value="{{ $mValue }}" {{ $mValue == $month ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="load-btn" type="submit" class="btn btn-primary">
                        <span id="load-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span id="load-text">Load Sheet</span>
                    </button>
                </div>
            </div>
        </form>

        @if(!$setting)
            <div class="alert alert-warning">
                <i class="ti ti-alert-triangle me-2"></i>
                No salary configuration found for {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}. Please create it first.
            </div>
        @endif

        <div id="sheet-loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading salary sheet...</div>
        </div>

        <div id="sheet-container">
            @include('content.hr.partials.salary-sheet-table')
        </div>
    </div>
</div>
@endsection


