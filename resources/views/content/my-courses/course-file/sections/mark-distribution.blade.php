<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>{{ __('Component') }}</th>
                <th>{{ __('Average') }}</th>
                <th>{{ __('Max') }}</th>
                <th>{{ __('Students with marks') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($markDistribution as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['average'] }}</td>
                    <td>{{ $row['max'] }}</td>
                    <td>{{ $row['students_with_marks'] }} / {{ $row['total_students'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">{{ __('No marks data yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
