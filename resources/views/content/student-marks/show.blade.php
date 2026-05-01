@extends('layouts/layoutMaster')

@section('title', __('Marks detail'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">{{ __('Marks sheet') }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('student-marks.edit', $mark) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                <a href="{{ route('student-marks.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
        <div class="card-body">
            <dl class="row small mb-4">
                <dt class="col-sm-3">{{ __('Student') }}</dt>
                <dd class="col-sm-9">{{ $mark->student?->student_code }} — {{ $mark->student?->student_name }}</dd>
                <dt class="col-sm-3">{{ __('Session') }}</dt>
                <dd class="col-sm-9">{{ $mark->academicSession?->session_name }}</dd>
                <dt class="col-sm-3">{{ __('Course') }}</dt>
                <dd class="col-sm-9">{{ $mark->course?->course_code }} — {{ $mark->course?->course_title }}</dd>
                <dt class="col-sm-3">{{ __('Component') }}</dt>
                <dd class="col-sm-9">
                    {{ $mark->assessmentComponent?->component_name }}
                    <span class="text-muted">({{ __('max') }} {{ $mark->assessmentComponent?->marks }})</span>
                </dd>
                <dt class="col-sm-3">{{ __('Total obtained') }}</dt>
                <dd class="col-sm-9 fw-medium">{{ $mark->total_marks }}</dd>
                <dt class="col-sm-3">{{ __('Status') }}</dt>
                <dd class="col-sm-9">{{ $mark->status?->status_name }}</dd>
            </dl>
            <h6 class="mb-2">{{ __('Question parts') }}</h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Label') }}</th>
                            <th>{{ __('Max') }}</th>
                            <th>{{ __('Obtained') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mark->studentQuestionMarks->sortBy(fn ($qm) => $qm->questionCloMapping?->question_label ?? '') as $qm)
                            <tr>
                                <td>{{ $qm->questionCloMapping?->question_label }}</td>
                                <td>{{ $qm->questionCloMapping?->marks }}</td>
                                <td>{{ $qm->obtained_marks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
