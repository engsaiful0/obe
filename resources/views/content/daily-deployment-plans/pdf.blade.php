<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stop Wise Daily Deployment Plan - PDF</title>
    <style>
        
        body {
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .info-item {
            flex: 1;
            padding: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background-color: #343a40;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .stoppage-name {
            font-weight: bold;
            text-align: left;
        }

        .bus-info {
            font-size: 11px;
        }

        .bus-number {
            display: block;
            font-weight: bold;
            color: #28a745;
        }

        .reg-number {
            display: block;
            font-size: 10px;
            color: #666;
        }

        .empty-cell {
            color: #999;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .remarks {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }

        .remarks-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
    <style>
    @font-face {
        font-family: 'Kalpurush';
        src: url("{{ storage_path('fonts/kalpurush.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'Nikosh';
        src: url("{{ storage_path('fonts/nikoshban.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'SolaimanLipi';
        src: url("{{ storage_path('fonts/solaimanlipi.ttf') }}") format('truetype');
    }
    @font-face {
        font-family: 'Kalpurush';
        src: url("{{ storage_path('fonts/kalpurush.ttf') }}") format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family: 'Kalpurush', DejaVu Sans, sans-serif;
    }
</style>
<style>
   

    * {
        font-family: 'Kalpurush', DejaVu Sans, sans-serif;
    }

    body {
        font-size: 12px;
        margin: 0;
        padding: 20px;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }

    .header h2 {
        margin: 0;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table th {
        background-color: #343a40;
        color: white;
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
        font-weight: bold;
    }

    table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
    }

    table tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #666;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }
</style>
</head>

<body>
    <div class="header">
        <h2>Daily Deployment Plan</h2>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td> <span class="info-label">Deployment Date</span></td>
                <td>

                    {{ $deploymentPlan->deployment_date->format('Y-m-d') }}
                </td>
                <td>
                    <span class="info-label">Trip Time</span>
                </td>

                <td>
                    @if($deploymentPlan->tripTime)
                    <span class="badge">{{ \Carbon\Carbon::parse($deploymentPlan->tripTime->time_value)->format('h:i') }} {{ $deploymentPlan->tripTime->time_period }}</span>
                    @else
                    <span class="badge">N/A</span>
                    @endif

                </td>
                <td><span class="info-label">Deployment Type</span></td>
                <td>
                    {{ $deploymentPlan->deploymentType->deployment_type_name ?? 'N/A' }}
                </td>
               
            </tr>
            <tr>
            <td><span class="info-label">Trip Type</span></td>
                <td>
                    {{ strtoupper($deploymentPlan->trip_type) }}
                </td>
                <td><span class="info-label">Bus User</span></td>
                <td>
                    {{ $deploymentPlan->busUser->bus_user_name ?? 'N/A' }}
                </td>
                <td><span class="info-label">Created By</span></td>
                <td>
                    {{ $deploymentPlan->user->name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><span class="info-label">Deployment Type</span></td>
                <td>
                    {{ $deploymentPlan->deploymentType->deployment_type_name ?? 'N/A' }}
                </td>
                <td><span class="info-label">Trip Type</span></td>
                <td>
                    {{ strtoupper($deploymentPlan->trip_type) }}
                </td>
            </tr>
        </table>


    </div>

    @if($deploymentPlan->remarks)
    <div class="remarks">
        <div class="remarks-label">Remarks:</div>
        <div>{{ $deploymentPlan->remarks }}</div>
    </div>
    @endif

    <h3 style="margin-top: 20px; margin-bottom: 10px;">Daily Stop Wise Bus Deployment</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th style="width: 20%;">Start Point</th>
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

    <div class="footer">
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>

</html>