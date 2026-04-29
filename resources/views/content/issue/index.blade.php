@extends('layouts/layoutMaster')

@section('title', 'Issue Management')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.issueUrls = {
            store: '{{ route('app-issue.store') }}',
            destroy: '{{ url('app/issue') }}/:id',
            productRow: '{{ route('app-issue-product-row') }}'
        };
        console.log('Issue URLs:', window.issueUrls);
    </script>
    <script src="{{ asset('assets/js/issue-pagination.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Search & Filter</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('app-issue-view') }}" id="search-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search by issue number, employee, or remarks">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="employee_id">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search"></i> Search
                            </button>
                            <a href="{{ route('app-issue-view') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Issues Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Issues List</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('app-issue-export-pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-outline-danger">
                    <i class="ti ti-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('app-issue-print-list') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                   target="_blank" 
                   class="btn btn-outline-primary">
                    <i class="ti ti-printer"></i> Print List
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($issues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Issue Number</th>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Items Count</th>
                                <th>Total Quantity</th>
                                <th>Remarks</th>
                                @if(auth()->user()->hasPermissionTo('issue-view') || auth()->user()->hasPermissionTo('issue-edit') || auth()->user()->hasPermissionTo('issue-delete'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($issues as $issue)
                                <tr>
                                    <td>
                                        <strong>{{ $issue->issue_number }}</strong>
                                    </td>
                                    <td>{{ $issue->employee->employee_name ?? 'N/A' }}</td>
                                    <td>{{ $issue->date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $issue->issueItems->count() }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $quantityByUnit = [];
                                            foreach($issue->issueItems as $item) {
                                                $unitName = $item->unit ? $item->unit->unit_name : 'No Unit';
                                                if (!isset($quantityByUnit[$unitName])) {
                                                    $quantityByUnit[$unitName] = 0;
                                                }
                                                $quantityByUnit[$unitName] += $item->quantity;
                                            }
                                        @endphp
                                        @if(count($quantityByUnit) > 0)
                                            @foreach($quantityByUnit as $unit => $total)
                                                <span class="badge bg-info me-1">{{ number_format($total, 2) }} {{ $unit }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $issue->remarks ?? 'N/A' }}</td>
                                    @if(auth()->user()->hasPermissionTo('issue-view') || auth()->user()->hasPermissionTo('issue-edit') || auth()->user()->hasPermissionTo('issue-delete'))
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(auth()->user()->hasPermissionTo('issue-view'))
                                                <a class="dropdown-item" href="{{ route('app-issue-view-details', $issue->id) }}">
                                                    <i class="ti ti-eye me-1"></i> View Details
                                                </a>
                                                @endif
                                                @if(auth()->user()->hasPermissionTo('issue-edit'))
                                                <a class="dropdown-item" href="{{ route('app-issue-edit', $issue->id) }}">
                                                    <i class="ti ti-edit me-1"></i> Edit
                                                </a>
                                                @endif
                                                @if(auth()->user()->hasPermissionTo('issue-print'))
                                                <a class="dropdown-item" href="{{ route('app-issue-print', $issue->id) }}" target="_blank">
                                                    <i class="ti ti-printer me-1"></i> Print
                                                </a>
                                                @endif
                                                @if(auth()->user()->hasPermissionTo('issue-delete'))
                                                <a class="dropdown-item delete-issue" href="#" data-id="{{ $issue->id }}">
                                                    <i class="ti ti-trash me-1"></i> Delete
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $issues->firstItem() }} to {{ $issues->lastItem() }} of {{ $issues->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $issues->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h5 class="text-muted">No Issues Found</h5>
                    <p class="text-muted">Start by creating your first issue.</p>
                    
                    <a href="{{ route('app-issue-add') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Add New Issue
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Issue</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-6">
                    <label class="form-label" for="issue_number">Issue Number *</label>
                    <div class="input-group input-group-merge">
                        <span id="issue_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                        <input type="text" id="issue_number" class="form-control dt-full-name" name="issue_number"
                            placeholder="Enter Issue Number" aria-label="Enter Issue Number"
                            aria-describedby="issue_number2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="employee_id">Employee *</label>
                    <select id="employee_id" class="form-select" name="employee_id">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="date">Date *</label>
                    <div class="input-group input-group-merge">
                        <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="date" id="date" class="form-control" name="date"
                            aria-label="Enter Date" aria-describedby="date2" />
                    </div>
                </div>
                
                <!-- Issue Items Section -->
                <div class="col-sm-12">
                    <h6 class="mb-3">Issue Items</h6>
                    <div id="issue-items-container">
                        <div class="issue-item-row row g-2 mb-3">
                            <div class="col-sm-4">
                                <label class="form-label">Item *</label>
                                <select class="form-select item-select" name="items[0][item_id]">
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">Unit</label>
                                <select class="form-select unit-select" name="items[0][unit_id]">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label">Quantity *</label>
                                <input type="number" class="form-control quantity-input" name="items[0][quantity]" 
                                    step="0.01" min="0.01" placeholder="0.00" />
                            </div>
                            <div class="col-sm-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-btn">
                        <i class="ti ti-plus"></i> Add Item
                    </button>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea id="remarks" class="form-control" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
                
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection