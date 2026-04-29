@extends('layouts/layoutMaster')

@section('title', 'Issue Details')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Issue Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th>Issue Number:</th>
                        <td>{{ $issue->issue_number }}</td>
                    </tr>
                    <tr>
                        <th>Employee:</th>
                        <td>{{ $issue->employee->employee_name }}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $issue->date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Remarks:</th>
                        <td>{{ $issue->remarks ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th>Created By:</th>
                        <td>{{ $issue->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $issue->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Total Items:</th>
                        <td>{{ $issue->issueItems->count() }}</td>
                    </tr>
                    <tr>
                        <th>Total Quantity:</th>
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
                                    <span class="badge bg-primary me-2">{{ number_format($total, 2) }} {{ $unit }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <h6>Issue Items</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($issue->issueItems as $index => $issueItem)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $issueItem->item->item_name }}</td>
                            <td>{{ $issueItem->unit->unit_name ?? 'N/A' }}</td>
                            <td>{{ $issueItem->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            @if(auth()->user()->hasPermissionTo('issue-edit'))
            <a href="{{ route('app-issue-edit', $issue->id) }}" class="btn btn-primary">
                <i class="ti ti-edit"></i> Edit Issue
            </a>
            @endif
            @if(auth()->user()->hasPermissionTo('issue-print'))
            <a href="{{ route('app-issue-print', $issue->id) }}" class="btn btn-info" target="_blank">
                <i class="ti ti-printer"></i> Print Issue
            </a>
            @endif
            @if(auth()->user()->hasPermissionTo('issue-view'))
            <a href="{{ route('app-issue-view') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left"></i> Back to List
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
