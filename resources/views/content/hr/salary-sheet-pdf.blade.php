<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Sheet {{ $year }}-{{ sprintf('%02d', $month) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #eee; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
    </style>
    </head>
<body>
    <h3>Salary Sheet - {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th class="text-center">Present Days</th>
                <th class="text-end">Basic</th>
                <th class="text-end">Daily Rate</th>
                <th class="text-end">Base Salary</th>
                <th class="text-end">Overtime</th>
                <th class="text-end">Deductions</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $i => $employee)
                @php($calc = $employee->salary_calculation)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $employee->employee_name ?? ($employee->full_name ?? 'Employee #'.$employee->id) }}</td>
                    <td class="text-center">{{ $employee->present_days }}</td>
                    <td class="text-end">{{ number_format($calc['basic_salary'] ?? ($employee->basic_salary ?? 0), 2) }}</td>
                    <td class="text-end">{{ number_format($calc['daily_rate'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['base_salary'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['overtime_amount'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['deductions'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['total_salary'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


