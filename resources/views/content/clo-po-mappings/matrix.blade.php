@extends('layouts/layoutMaster')

@section('title', __('CLO–PO mapping matrix'))

@php
    $cascadeUrls = [
        'courses' => url('/ajax/clo-po/program/__PROGRAM_ID__/courses'),
    ];
    $levelBadges = [
        1 => ['secondary', __('Low')],
        2 => ['info', __('Medium')],
        3 => ['success', __('High')],
    ];
@endphp

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('CLO–PO / PLO matrix') }}</h5>
        <a href="{{ route('clo-po-mappings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('List view') }}</a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('clo-po-mappings.matrix') }}" id="cpm-matrix-form"
            data-url-courses="{{ $cascadeUrls['courses'] }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-0" for="matrix_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
                    <select name="program_id" id="matrix_program_id" class="form-select" required data-matrix-program>
                        <option value="">{{ __('Select program') }}</option>
                        @foreach ($programs as $p)
                            <option value="{{ $p->id }}" @selected((int) ($programId ?? 0) === (int) $p->id)>
                                {{ $p->program_code }} — {{ $p->program_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-0" for="matrix_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
                    <select name="course_id" id="matrix_course_id" class="form-select" required data-matrix-course
                        data-ph-need-program="{{ __('Select program first') }}"
                        data-ph-loading="{{ __('Loading…') }}"
                        data-ph-select="{{ __('Select course') }}"
                        data-ph-error="{{ __('Could not load courses') }}">
                        <option value="">{{ __('Select program first') }}</option>
                        @foreach ($coursesForMatrix as $cr)
                            <option value="{{ $cr->id }}" @selected((int) ($courseId ?? 0) === (int) $cr->id)>
                                {{ $cr->course_code }} — {{ $cr->course_title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Show matrix') }}</button>
                </div>
            </div>
        </form>

        @if (($programId ?? 0) && ($courseId ?? 0) && $clos->isNotEmpty() && $outcomes->isNotEmpty())
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-sm text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">{{ __('CLO') }}</th>
                            @foreach ($outcomes as $po)
                                <th class="small">
                                    {{ $po->outcome_code }}
                                    @if ($po->outcome_type)
                                        <span class="text-muted">{{ $po->outcome_type }}</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clos as $clo)
                            <tr>
                                <th scope="row" class="text-start small">
                                    <span class="fw-medium">{{ $clo->clo_code }}</span>
                                    @if ($clo->title)
                                        <div class="text-muted fw-normal">{{ \Illuminate\Support\Str::limit($clo->title, 48) }}</div>
                                    @endif
                                </th>
                                @foreach ($outcomes as $po)
                                    @php
                                        $lvl = $levels[$clo->id][$po->id] ?? null;
                                        $lbl = $lvl ? ($levelBadges[(int) $lvl] ?? null) : null;
                                    @endphp
                                    <td class="small">
                                        @if ($lvl && $lbl)
                                            <span class="badge bg-{{ $lbl[0] }}" title="{{ $lbl[1] }}">{{ $lvl }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif (($programId ?? 0) && ($courseId ?? 0))
            <p class="text-muted mt-3 mb-0">{{ __('No CLOs or PO/PLO records found for this program/course.') }}</p>
        @else
            <p class="text-muted mt-3 mb-0">{{ __('Choose a program and course, then click Show matrix.') }}</p>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/clo-po-matrix-filter.js') }}"></script>
@endsection
