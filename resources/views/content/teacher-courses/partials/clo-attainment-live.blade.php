@php
    $cloAttainment = $cloAttainment ?? ['rows' => [], 'chart' => []];
    $closAchieved = $closAchieved ?? 0;
    $closTotal = $closTotal ?? 0;
@endphp
@if ($closTotal > 0)
    <div class="alert alert-light border mb-3 py-2">
        {{ __('CLOs Achieved') }}: <strong>{{ $closAchieved }}</strong> / {{ $closTotal }}
        ({{ $closTotal > 0 ? round(($closAchieved / $closTotal) * 100, 1) : 0 }}%)
    </div>
@endif
<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
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
                        <span class="badge {{ ($row['status'] ?? '') === 'Achieved' ? 'bg-success' : 'bg-danger' }}">
                            {{ __($row['status'] ?? '-') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        {{ __('No CLO attainment data. Ensure CLOs and question mappings are configured, and enter question-level marks for accurate results.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
