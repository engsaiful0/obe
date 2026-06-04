<div class="row g-3">
    <div class="col-lg-7">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>{{ __('PLO') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Target (%)') }}</th>
                    <th>{{ __('Achieved (%)') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ploAttainment['rows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['plo'] }}</td>
                        <td class="small">{{ $row['title'] }}</td>
                        <td>{{ $row['target'] }}</td>
                        <td>{{ $row['achieved'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">{{ __('No PLO mappings for this course.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="col-lg-5">
        <canvas id="cf-plo-bar-chart" height="260"></canvas>
    </div>
</div>
