@include('content.my-courses.partials.grade-sheet-content', [
    'report' => $gradeReport,
    'appSettings' => null,
    'generatedAt' => now(),
])
<div class="d-flex gap-1 mt-2">
    <a href="{{ route('my-courses.grade-sheet.excel', $courseAssignment) }}" class="btn btn-success btn-sm">{{ __('Excel') }}</a>
    <a href="{{ route('my-courses.grade-sheet.pdf', $courseAssignment) }}" class="btn btn-danger btn-sm">{{ __('PDF') }}</a>
</div>
