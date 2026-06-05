<div class="mb-3">
    <input type="text" id="dashboard-student-search" class="form-control form-control-sm" style="max-width: 320px;"
        placeholder="{{ __('Search students...') }}">
</div>
<div class="table-responsive">
    <table class="table table-sm table-striped" id="dashboard-students-table">
        <thead class="table-light">
            <tr>
                <th>{{ __('Serial') }}</th>
                <th>{{ __('Student Code') }}</th>
                <th>{{ __('Student Name') }}</th>
                <th>{{ __('Batch') }}</th>
                <th class="text-end">{{ __('Attendance %') }}</th>
                <th class="text-end">{{ __('Total Marks') }}</th>
                <th>{{ __('Grade') }}</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
