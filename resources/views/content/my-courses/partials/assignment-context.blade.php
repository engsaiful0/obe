@php
    $sectionLabel = trim(
        ($courseAssignment->section?->section_code ?? '') . ' ' . ($courseAssignment->section?->section_name ?? '')
    );
    $batchText = is_array($batchLabels ?? null)
        ? implode(', ', $batchLabels)
        : (is_string($batchLabels ?? null) ? $batchLabels : '');
@endphp
<div class="row g-2 mb-3">
    <div class="col-md-3"><small class="text-muted d-block">{{ __('Academic Session') }}</small><strong>{{ $courseAssignment->academicSession?->session_name ?? '-' }}</strong></div>
    <div class="col-md-3"><small class="text-muted d-block">{{ __('Program') }}</small><strong>{{ $courseAssignment->program?->program_name ?? '-' }}</strong></div>
    <div class="col-md-2"><small class="text-muted d-block">{{ __('Course') }}</small><strong>{{ $courseAssignment->course?->course_code }} — {{ $courseAssignment->course?->course_title }}</strong></div>
    <div class="col-md-2"><small class="text-muted d-block">{{ __('Batch') }}</small><strong>{{ $batchText !== '' ? $batchText : '-' }}</strong></div>
    <div class="col-md-2"><small class="text-muted d-block">{{ __('Section') }}</small><strong>{{ $sectionLabel !== '' ? $sectionLabel : '-' }}</strong></div>
</div>
