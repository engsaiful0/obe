@php
    $readonly = (bool) ($readonly ?? false);
    $compact = (bool) ($compact ?? false);
    $assignment = $assignment ?? null;
@endphp
@if ($assignment)
    <div class="btn-group btn-group-sm {{ $compact ? '' : 'flex-wrap' }}" role="group">
        <a href="{{ route('teacher-courses.dashboard', $assignment) }}" class="btn btn-outline-primary" title="{{ __('View Course') }}">
            <i class="ti ti-eye"></i> {{ $compact ? '' : __('View') }}
        </a>
        <a href="{{ route('teacher-courses.students', $assignment) }}" class="btn btn-outline-secondary" title="{{ __('Student List') }}">
            <i class="ti ti-users"></i> {{ $compact ? '' : __('Students') }}
        </a>
        @if ($readonly)
            <a href="{{ route('teacher-courses.attendance', $assignment) }}" class="btn btn-outline-info" title="{{ __('Attendance Report') }}">
                <i class="ti ti-calendar"></i> {{ $compact ? '' : __('Attendance') }}
            </a>
            <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-outline-warning" title="{{ __('Marks Report') }}">
                <i class="ti ti-report"></i> {{ $compact ? '' : __('Marks') }}
            </a>
        @else
            <a href="{{ route('teacher-courses.attendance', $assignment) }}" class="btn btn-outline-info" title="{{ __('Attendance') }}">
                <i class="ti ti-calendar"></i> {{ $compact ? '' : __('Attendance') }}
            </a>
            <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-primary" title="{{ __('Marks Entry') }}">
                <i class="ti ti-edit"></i> {{ $compact ? '' : __('Marks') }}
            </a>
        @endif
        <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-info" title="{{ __('Grade Sheet') }}">
            <i class="ti ti-table"></i> {{ $compact ? '' : __('Grade') }}
        </a>
        <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-warning" title="{{ __('Course File') }}">
            <i class="ti ti-folder"></i> {{ $compact ? '' : __('File') }}
        </a>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                {{ __('More') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('teacher-courses.clo', $assignment) }}">{{ __('CLO Assessment') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('teacher-courses.plo', $assignment) }}">{{ __('PLO Assessment') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('teacher-courses.reports', $assignment) }}">{{ __('Reports') }}</a></li>
                @if ($readonly)
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('teacher-courses.export-previous-pdf', $assignment) }}">{{ __('Export PDF') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('my-courses.grade-sheet.excel', $assignment) }}">{{ __('Export Excel') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('my-courses.grade-sheet.print', $assignment) }}" target="_blank">{{ __('Print') }}</a></li>
                @else
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('my-courses.import', $assignment) }}">{{ __('Bulk Marks Import') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('my-courses.grade-sheet.print', $assignment) }}" target="_blank">{{ __('Print') }}</a></li>
                @endif
            </ul>
        </div>
    </div>
@endif
