<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>{{ __('CLO') }}</th>
                <th>{{ __('Statement') }}</th>
                <th>{{ __('Bloom') }}</th>
                <th>{{ __('PLO Mapping') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clos as $clo)
                <tr>
                    <td>{{ $clo['clo_code'] }}</td>
                    <td>{{ $clo['statement'] }}</td>
                    <td>{{ $clo['bloom'] }}</td>
                    <td class="small">{{ $clo['plo_mapping'] }}</td>
                    <td>
                        {{ $clo['status'] }}
                        @if ($clo['is_locked'])
                            <span class="badge bg-secondary">{{ __('Read only') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">{{ __('No CLOs defined for this course.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="small text-muted mb-0">{{ __('CLO data is loaded from the OBE database. Approved or active CLOs cannot be edited here.') }}</p>
