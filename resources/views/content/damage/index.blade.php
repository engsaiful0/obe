@extends('layouts/layoutMaster')

@section('title', 'Damage Management')

@section('page-script')
    <script>
        window.damageUrls = {
            store: '{{ route('app-damage.store') }}',
            destroy: '{{ url('app/damage') }}/:id',
            productRow: '{{ route('app-damage-product-row') }}',
            exportExcel: '{{ route('app-damage-export-excel') }}',
            exportPdf: '{{ route('app-damage-export-pdf') }}'
        };
    </script>
    <script src="{{ asset('assets/js/damage-pagination.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Search & Filter</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('app-damage-view') }}" id="search-form" data-ajax-url="{{ route('app-damage-view') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search by warehouse, item, or remarks">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="warehouse_id">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->warehouse_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="item_id">Item</label>
                        <select class="form-select" id="item_id" name="item_id">
                            <option value="">All Items</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->item_name }}
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
                            <a href="{{ route('app-damage-view') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <a href="{{ route('app-damage-export-excel') }}?{{ http_build_query(request()->all()) }}" 
                               class="btn btn-success" id="export-excel-btn">
                                <i class="ti ti-file-excel"></i> Export Excel
                            </a>
                            <a href="{{ route('app-damage-export-pdf') }}?{{ http_build_query(request()->all()) }}" 
                               class="btn btn-danger" id="export-pdf-btn" target="_blank">
                                <i class="ti ti-file-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Damages Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Damages List</h5>
            <a href="{{ route('app-damage-add') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Add Damage
            </a>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="damages-spinner" class="text-center py-5 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading damages...</p>
            </div>

            <!-- Table Container (AJAX-loaded content will be inserted here) -->
            <div id="damages-table-container">
                @include('content.damage.table', ['damages' => $damages])
                @include('content.damage.pagination', ['damages' => $damages])
            </div>

            @if(false && $damages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Items</th>
                                <th>Total Quantity</th>
                                <th>Total Approximate</th>
                                <th>Remarks</th>
                                @if(auth()->user()->hasPermissionTo('damage-view') || auth()->user()->hasPermissionTo('damage-edit') || auth()->user()->hasPermissionTo('damage-delete'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($damages as $damage)
                                <tr>
                                    <td>{{ $damage->date->format('d M Y') }}</td>
                                    <td>{{ $damage->warehouse->warehouse_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($damage->damageItems->count() > 0)
                                            <ul class="list-unstyled mb-0">
                                                @foreach($damage->damageItems as $item)
                                                    <li>
                                                        <span class="badge bg-primary">{{ $item->item->item_name ?? 'N/A' }}</span>
                                                        <small class="text-muted">(Qty: {{ number_format($item->quantity, 2) }})</small>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="badge bg-secondary">No Items</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($damage->damageItems->sum('quantity'), 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ number_format($damage->damageItems->sum('approximate'), 2) }}</span>
                                    </td>
                                    <td>{{ $damage->remarks ?? 'N/A' }}</td>
                                    @if(auth()->user()->hasPermissionTo('damage-view') || auth()->user()->hasPermissionTo('damage-edit') || auth()->user()->hasPermissionTo('damage-delete'))
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(auth()->user()->hasPermissionTo('damage-edit'))
                                                <a class="dropdown-item" href="{{ route('app-damage-edit', $damage->id) }}">
                                                    <i class="ti ti-edit me-1"></i> Edit
                                                </a>
                                                @endif
                                                @if(auth()->user()->hasPermissionTo('damage-delete'))
                                                <a class="dropdown-item delete-damage" href="#" data-id="{{ $damage->id }}">
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
                            Showing {{ $damages->firstItem() }} to {{ $damages->lastItem() }} of {{ $damages->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $damages->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h5 class="text-muted">No Damages Found</h5>
                    <p class="text-muted">Start by creating your first damage entry.</p>
                    
                    <a href="{{ route('app-damage-add') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Add New Damage
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

