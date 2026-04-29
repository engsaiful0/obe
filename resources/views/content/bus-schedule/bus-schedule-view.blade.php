@extends('layouts/layoutMaster')

@section('title', 'View Bus Schedule')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0"> Bus Schedule Details</h4>
                <div>
                    <a href="{{ route('bus-schedules.schedule-print', $busSchedule->id) }}" class="btn btn-info" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                    <a href="{{ route('bus-schedules.schedule-pdf', $busSchedule->id) }}" class="btn btn-danger">
                        <i class="ti ti-file-pdf me-1"></i> Export PDF
                    </a>
                    <a href="{{ route('bus-schedules.schedule-edit', $busSchedule->id) }}" class="btn btn-warning">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('bus-schedules.schedule-index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <strong>Schedule Category:</strong><br>
                        {{ $busSchedule->keyword->keyword_name ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-label-primary">{{ $busSchedule->status->status_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Bus User:</strong><br>
                        {{ $busSchedule->busUser->bus_user_name ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Effective From:</strong><br>
                        {{ $busSchedule->effective_from ? \Carbon\Carbon::parse($busSchedule->effective_from)->format('Y-m-d') : 'N/A' }}
                    </div>
                </div>

                @if($busSchedule->description)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <strong>Description:</strong><br>
                        <p class="mb-0">{{ $busSchedule->description }}</p>
                    </div>
                </div>
                @endif

                <h5 class="mb-3">Schedule Entries</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Start Time</th>
                                <th>Starting Point</th>
                                <th>Bus Route</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($busSchedule->entries as $entry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($entry->start_time)
                                        @php
                                            $time = \Carbon\Carbon::parse($entry->start_time);
                                            $hour24 = (int)$time->format('G');
                                            if ($hour24 == 0) {
                                                $hour12 = 12;
                                                $amPm = 'AM';
                                            } elseif ($hour24 < 12) {
                                                $hour12 = $hour24;
                                                $amPm = 'AM';
                                            } elseif ($hour24 == 12) {
                                                $hour12 = 12;
                                                $amPm = 'PM';
                                            } else {
                                                $hour12 = $hour24 - 12;
                                                $amPm = 'PM';
                                            }
                                        @endphp
                                        {{ $hour12 }}:{{ $time->format('i') }} {{ $amPm }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $entry->startingPoint->stoppage_name ?? 'N/A' }}</td>
                                <td>{{ $entry->busRoute->route_name ?? 'N/A' }}</td>
                                <td>{{ $entry->description ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No entries found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

