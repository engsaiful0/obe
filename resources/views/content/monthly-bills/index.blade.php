@extends('layouts/layoutMaster')

@section('title', 'Monthly Bills')


@section('page-script')
<script>
    window.monthlyBillsRoutes = {
        index: "{{ route('monthly-bills.index') }}",
        export: "{{ route('monthly-bills.export') }}"
    };
</script>
<script src="{{asset('assets/js/app-monthly-bills-list.js')}}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Monthly Bills Report</h5>
                    <p class="text-muted mb-0">Hired Buses & BRTC Buses with Rewards, Punishments & Final Amounts</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('monthly-bills.print-list', request()->query()) }}" 
                       target="_blank" 
                       class="btn btn-outline-primary">
                        <i class="ti ti-printer"></i> Print
                    </a>
                    <a href="{{ route('monthly-bills.pdf', request()->query()) }}" 
                       class="btn btn-outline-danger">
                        <i class="ti ti-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card-body">
                <form id="filterForm" class="row g-3 mb-4">
                <div class="col-md-2">
                        <label for="bus_type" class="form-label">Bus Type</label>
                        <select class="form-select" id="bus_type" name="bus_type">
                            <option value="">All Types</option>
                            <option value="hired" {{ request('bus_type') == 'hired' ? 'selected' : '' }}>Hired Bus</option>
                            <option value="brtc" {{ request('bus_type') == 'brtc' ? 'selected' : '' }}>BRTC Bus</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bus_id" class="form-label">Vehicle</label>
                        <select class="form-select" id="bus_id" name="bus_id">
                            <option value="">All Buses</option>
                            @foreach($allBuses as $bus)
                                <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                                    {{ $bus->bus_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                  
                    <div class="col-md-2">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Buses</h6>
                                        <h4 class="mb-0">{{ $rawBills->count() }}</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="ti ti-bus fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Hired Buses</h6>
                                        <h4 class="mb-0">{{ $rawBills->where('bus_type', 'hired')->count() }}</h4>
                                        <small>৳{{ number_format($rawBills->where('bus_type', 'hired')->sum('final_amount'), 2) }}</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="ti ti-bus fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">BRTC Buses</h6>
                                        <h4 class="mb-0">{{ $rawBills->where('bus_type', 'brtc')->count() }}</h4>
                                        <small>৳{{ number_format($rawBills->where('bus_type', 'brtc')->sum('final_amount'), 2) }}</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="ti ti-bus fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th width="6%">#</th>
                                <th width="18%">Vehicle Details</th>
                                <th width="10%">Month</th>
                                <th width="8%">Bus Type</th>
                                
                                <th width="10%">Rate</th>
                                <th width="10%">Trips</th>
                                <th width="12%">Base Amount</th>
                                <th width="10%">Rewards</th>
                                <th width="10%">Punishments</th>
                                <th width="12%">Final Amount</th>
                                <th width="4%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="ti ti-bus text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <strong class="text-dark">{{ $bill['bus']->bus_number }}</strong><br>
                                            <small class="text-muted">{{ $bill['bus']->model_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $bill['formatted_bill_month'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $bill['bus_type'] == 'hired' ? 'primary' : 'warning' }} fs-6">
                                        {{ strtoupper($bill['bus_type']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($bill['bus_type'] == 'hired')
                                        <strong class="text-primary">৳{{ number_format($bill['daily_rate'], 2) }}</strong><br>
                                        <small class="text-muted">Per Day</small>
                                    @else
                                        <strong class="text-warning">৳{{ number_format($bill['rate_per_km'], 2) }}</strong><br>
                                        <small class="text-muted">Per KM</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bill['bus_type'] == 'hired')
                                        <strong>{{ $bill['total_trips'] }}</strong><br>
                                        <small class="text-muted">
                                            {{ $bill['full_days'] }} full + {{ $bill['half_days'] }} half
                                        </small>
                                    @else
                                        <strong>{{ $bill['total_trips'] }}</strong><br>
                                        <small class="text-muted">{{ number_format($bill['total_distance'], 1) }} KM</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="text-dark">৳{{ number_format($bill['base_amount'], 2) }}</strong>
                                </td>
                                <td class="text-end text-success">
                                    <i class="ti ti-plus-circle me-1"></i>+৳{{ number_format($bill['total_rewards'], 2) }}
                                </td>
                                <td class="text-end text-danger">
                                    <i class="ti ti-minus-circle me-1"></i>-৳{{ number_format($bill['total_punishments'], 2) }}
                                </td>
                                <td class="text-end">
                                    <strong class="text-success fs-5">৳{{ number_format($bill['final_amount'], 2) }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('monthly-bills.show', $bill['id']) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-bus fs-1 mb-3 d-block"></i>
                                        <h5>No Monthly Bills Found</h5>
                                        <p>No bills available for the selected filters. Try adjusting your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        @if($bills->total() > 0)
                            <i class="ti ti-info-circle me-1"></i>
                            Showing {{ $bills->firstItem() }} to {{ $bills->lastItem() }} of {{ $bills->total() }} buses
                        @else
                            <i class="ti ti-alert-circle me-1"></i>
                            No buses found
                        @endif
                    </div>
                    <div>
                        @if($bills->hasPages())
                            <nav aria-label="Monthly Bills Pagination">
                                {{ $bills->appends(request()->query())->links() }}
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
