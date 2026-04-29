<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th class="text-center">Present Days</th>
                <th class="text-end">Basic Salary</th>
                <th class="text-end">Daily Rate</th>
                <th class="text-end">Base Salary</th>
                <th class="text-end">Overtime</th>
                <th class="text-end">Deductions</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $employee)
                @php($calc = $employee->salary_calculation)
                <tr>
                    <td>{{ $employees->firstItem() + $index }}</td>
                    <td>{{ $employee->employee_name ?? ($employee->full_name ?? 'Employee #'.$employee->id) }}</td>
                    <td class="text-center">{{ $employee->present_days }}</td>
                    <td class="text-end">{{ number_format($calc['basic_salary'] ?? ($employee->basic_salary ?? 0), 2) }}</td>
                    <td class="text-end">{{ number_format($calc['daily_rate'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['base_salary'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['overtime_amount'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($calc['deductions'] ?? 0, 2) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($calc['total_salary'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $employees->appends(['year' => $year, 'month' => $month])->links() }}
    </div>


