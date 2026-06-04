<div class="row g-3">
    <div class="col-lg-7">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>{{ __('CLO') }}</th>
                    <th>{{ __('Target (%)') }}</th>
                    <th>{{ __('Achieved (%)') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cloAttainment['rows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['clo'] }}</td>
                        <td>{{ $row['target'] }}</td>
                        <td>{{ $row['achieved'] }}</td>
                        <td>
                            <span class="badge {{ $row['status'] === 'Achieved' ? 'bg-success' : 'bg-danger' }}">{{ $row['status'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">{{ __('No CLO attainment data.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="col-lg-5">
        <canvas id="cf-clo-bar-chart" height="220"></canvas>
        <canvas id="cf-clo-pie-chart" height="220" class="mt-3"></canvas>
    </div>
</div>
