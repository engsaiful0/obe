@extends('layouts/layoutMaster')

@php
use App\Models\BusSubType;
@endphp

@section('title', 'View Daily Deployment Plan')

@section('page-style')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-content, .print-content * {
            visibility: visible;
        }
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
        .card {
            border: none;
            box-shadow: none;
        }
        .table {
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .badge {
            border: 1px solid #000;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header no-print">
                <h4 class="card-title">Daily Deployment Plan Details</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('deployment-plans.pdf', $deploymentPlan->id) }}" class="btn btn-danger" target="_blank">
                        <i class="ti ti-file-pdf me-1"></i> Export PDF
                    </a>
                    @permission('daily-deployment-plan-edit')
                    <a href="{{ route('deployment-plans.edit', $deploymentPlan->id) }}" class="btn btn-primary">
                        <i data-feather="edit"></i> Edit
                    </a>
                    @endpermission
                    <a href="{{ route('deployment-plans.view-daily-deployment-plan') }}" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body print-content">
                <table class="table table-bordered table-striped">
                    <tr>
                        <td><b>Deployment Date</b></td>
                        <td>{{ $deploymentPlan->deployment_date->format('Y-m-d') }}</td>
                        <td><b>Trip Time</b></td>
                        <td>
                            @if($deploymentPlan->tripTime)
                            <span>{{ \Carbon\Carbon::parse($deploymentPlan->tripTime->time_value)->format('h:i') }} {{ $deploymentPlan->tripTime->time_period }}</span>
                            @else
                            <span class="badge">N/A</span>
                            @endif
                        </td>
                        <td><b>Deployment Type</b></td>
                        <td>{{ $deploymentPlan->deploymentType->deployment_type_name ?? 'N/A' }}</td>
                       
                    </tr>
                    <tr>
                    <td><b>Trip Type</b></td>
                    <td>{{ ucfirst($deploymentPlan->trip_type) ?? 'N/A' }}</td>
                        <td><b></b>Bus User</b></td>
                        <td>{{ $deploymentPlan->busUser->bus_user_name ?? 'N/A' }}</td>
                    
                        <td><b>Created By</b></td>
                        <td>{{ $deploymentPlan->user->name ?? 'N/A' }}</td>
                    </tr>
                   
                </table>
             

                @if($deploymentPlan->remarks)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <strong>Remarks</strong><br>    
                        {{ $deploymentPlan->remarks }}
                    </div>
                </div>
                @endif

                <hr>

                <!-- Deployment Plan Items -->
                <h5 class="mb-3">Daily Stop Wise Bus Deployment</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Start Point</th>
                                @php
                                    $allBusSubTypes = BusSubType::orderBy('sub_type_name')->get();
                                    $itemsByStoppage = $deploymentPlan->items->groupBy('stoppage_id');
                                @endphp
                                @foreach($allBusSubTypes as $busSubType)
                                    <th>{{ $busSubType->sub_type_name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itemsByStoppage as $stoppageId => $items)
                                @php
                                    $stoppage = $items->first()->stoppage;
                                    // Group all items for this stoppage by bus sub-type
                                    $assignmentsBySubType = $items->groupBy('bus_sub_type_id');
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $stoppage->stoppage_name }}</strong></td>
                                    @foreach($allBusSubTypes as $busSubType)
                                        @php
                                            $assignments = $assignmentsBySubType->get($busSubType->id, collect());
                                        @endphp
                                        <td>
                                            @if($assignments && $assignments->count())
                                                @foreach($assignments as $assignment)
                                                    @if($assignment->bus)
                                                        <span>{{ $assignment->bus->bus_number }}</span>@if(!$loop->last), @endif
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

