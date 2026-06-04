<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>{{ __('Assessment') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Weight %') }}</th>
                <th>{{ __('Marks') }}</th>
                <th>{{ __('Related CLO') }}</th>
                <th>{{ __('Method') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assessments as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['weight'] }}</td>
                    <td>{{ $row['marks'] }}</td>
                    <td>{{ $row['related_clos'] }}</td>
                    <td>{{ $row['method'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">{{ __('No active assessments configured.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
