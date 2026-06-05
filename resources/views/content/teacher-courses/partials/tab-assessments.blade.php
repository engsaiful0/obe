@php
    $categories = [
        'attendance' => __('Attendance Marks'),
        'assignment' => __('Assignment Marks'),
        'class_test' => __('Class Test Marks'),
        'mid' => __('Mid Marks'),
        'final' => __('Final Marks'),
    ];
@endphp
<div class="row g-3">
    @foreach ($categories as $key => $label)
        <div class="col-md-6 col-lg-4">
            <div class="card border h-100">
                <div class="card-body d-flex flex-column">
                    <h6>{{ $label }}</h6>
                    <p class="text-muted small flex-grow-1">{{ __('Enter and review :category through marks entry or bulk import.', ['category' => strtolower($label)]) }}</p>
                    @if ($readonly)
                        <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-sm btn-outline-secondary">{{ __('View Report') }}</a>
                    @else
                        <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-sm btn-primary">{{ __('Enter Marks') }}</a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
