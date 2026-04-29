@extends('layouts/layoutMaster')

@section('title', 'View Daily Bus List Entry')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Daily Bus List Entry Details</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('daily-bus-lists.all-buses-list') }}" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('daily-bus-lists.edit', $dailyBusList->id) }}" class="btn btn-primary">
                        <i data-feather="edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <p class="form-control-plaintext">{{ $dailyBusList->list_date->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bus Type</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->bus_type)
                                    {{ \App\Models\DailyBusList::getBusTypeOptions()[$dailyBusList->bus_type] ?? ucfirst($dailyBusList->bus_type) }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vehicle</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->vehicle)
                                    {{ $dailyBusList->vehicle->name }} ({{ $dailyBusList->vehicle->registration_number }})
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vehicle Sub Type</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->vehicleSubType)
                                    {{ $dailyBusList->vehicleSubType->name }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Driver</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->driver)
                                    {{ $dailyBusList->driver->name }} ({{ $dailyBusList->driver->phone }})
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assistant</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->assistant)
                                    {{ $dailyBusList->assistant->name }} ({{ $dailyBusList->assistant->phone }})
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Stoppage</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->startStoppage)
                                    {{ $dailyBusList->startStoppage->name }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">End Stoppage</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->endStoppage)
                                    {{ $dailyBusList->endStoppage->name }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Time</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->start_time)
                                    {{ $dailyBusList->start_time->format('H:i') }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created By</label>
                            <p class="form-control-plaintext">
                                @if($dailyBusList->user)
                                    {{ $dailyBusList->user->name }}
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if($dailyBusList->remarks)
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Remarks</label>
                            <p class="form-control-plaintext">{{ $dailyBusList->remarks }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <p class="form-control-plaintext">{{ $dailyBusList->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Updated At</label>
                            <p class="form-control-plaintext">{{ $dailyBusList->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <a href="{{ route('daily-bus-lists.edit', $dailyBusList->id) }}" class="btn btn-primary">
                                <i data-feather="edit"></i> Edit Entry
                            </a>
                            <form action="{{ route('daily-bus-lists.destroy', $dailyBusList->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i data-feather="trash-2"></i> Delete Entry
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    // Initialize feather icons
    feather.replace();
</script>
@endsection
