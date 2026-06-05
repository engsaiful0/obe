@php
    $ploAttainment = $ploAttainment ?? ['rows' => [], 'chart' => []];
@endphp
<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>{{ __('PLO') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Target (%)') }}</th>
                <th>{{ __('Achieved (%)') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ploAttainment['rows'] ?? [] as $row)
                @php
                    $achieved = (float) ($row['achieved'] ?? 0);
                    $target = (float) ($row['target'] ?? 60);
                @endphp
                <tr>
                    <td>{{ $row['plo'] }}</td>
                    <td class="small">{{ $row['title'] ?? '' }}</td>
                    <td>{{ $row['target'] }}</td>
                    <td>{{ $row['achieved'] }}</td>
                    <td>
                        <span class="badge {{ $achieved >= $target ? 'bg-success' : 'bg-danger' }}">
                            {{ $achieved >= $target ? __('Achieved') : __('Not Achieved') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        {{ __('No PLO mappings for this course. Configure CLO-PO mappings in admin settings.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
