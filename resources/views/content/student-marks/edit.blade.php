@extends('layouts/layoutMaster')

@section('title', __('Edit marks'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Edit student marks') }}</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 small text-muted">
                {{ $mark->student?->student_code }} —
                {{ $mark->student?->student_name }} ·
                {{ $mark->assessmentComponent?->component_name }} ·
                {{ $mark->academicSession?->session_name }}
            </div>
            <form method="POST" action="{{ route('student-marks.update', $mark) }}" data-ajax-submit>
                @csrf
                @method('PUT')
                <div data-ajax-errors class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label">{{ __('OBE sheet status') }}</label>
                    <select name="status_id" class="form-select form-select-sm">
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}" @selected(old('status_id', $mark->status_id) == $st->id)>{{ $st->status_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @php
                    $qm = $mark->studentQuestionMarks->keyBy('question_clo_mapping_id');
                @endphp
                <div class="row g-2 mb-2">
                    @foreach ($mappings as $mapping)
                        @php
                            $v = optional($qm->get($mapping->id))->obtained_marks ?? 0;
                        @endphp
                        <div class="col-md-4">
                            <label class="form-label small">
                                {{ $mapping->question_label }} <span class="text-muted">(max {{ $mapping->marks }})</span>
                            </label>
                            <input type="hidden" name="questions[{{ $loop->index }}][question_clo_mapping_id]"
                                value="{{ $mapping->id }}">
                            <input type="text" inputmode="decimal"
                                name="questions[{{ $loop->index }}][obtained_marks]"
                                class="form-control form-control-sm sm-part-input"
                                data-max="{{ $mapping->marks }}" value="{{ old("questions.$loop->index.obtained_marks", $v) }}">
                        </div>
                    @endforeach
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Total marks') }}</label>
                    <input type="text" inputmode="decimal" name="total_marks" id="sm_edit_total"
                        class="form-control form-control-sm"
                        value="{{ old('total_marks', $mark->total_marks) }}">
                </div>
                <button type="submit"
                    class="btn btn-warning obe-ajax-primary d-inline-flex align-items-center gap-2">
                    <span class="obe-btn-label">{{ __('Update') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"></span>
                </button>
                <a href="{{ route('student-marks.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
    <script>
        (function () {
            function clampRow(inputs, totalEl, capStr) {
                var cap = capStr !== undefined && capStr !== '' ? parseFloat(capStr) : NaN;
                inputs.forEach(function (inp) {
                    var mx = inp.getAttribute('data-max');
                    if (!mx) {
                        return;
                    }
                    var v = parseFloat(inp.value.replace(',', '.'));
                    if (!isFinite(v)) {
                        v = 0;
                    }
                    var m = parseFloat(mx);
                    if (v > m) {
                        inp.value = m;
                        if (typeof toastr !== 'undefined') {
                            toastr.warning(@js(__('Part marks cannot exceed the mapping cap.')));
                        }
                    }
                });
                var sum = 0;
                inputs.forEach(function (inp) {
                    var v = parseFloat(String(inp.value).replace(',', '.'));
                    if (isFinite(v)) sum += v;
                });
                sum = Math.round(sum * 100) / 100;
                if (totalEl) {
                    totalEl.value = String(sum);
                    if (!isFinite(cap)) {
                        return;
                    }
                    if (sum > cap + 1e-4) {
                        totalEl.classList.add('is-invalid');
                    } else {
                        totalEl.classList.remove('is-invalid');
                    }
                }
            }
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.querySelector('form[data-ajax-submit]');
                if (!form) {
                    return;
                }
                var totalEl = document.getElementById('sm_edit_total');
                var inputs = Array.prototype.slice.call(form.querySelectorAll('.sm-part-input'));
                var cap = {{ json_encode(isset($mark->assessmentComponent->marks) ? (float) $mark->assessmentComponent->marks : null) }};
                inputs.forEach(function (inp) {
                    inp.addEventListener('input', function () {
                        clampRow(inputs, totalEl, cap);
                    });
                    inp.addEventListener('change', function () {
                        clampRow(inputs, totalEl, cap);
                    });
                });
                if (totalEl) {
                    totalEl.addEventListener('input', function () {
                        var v = parseFloat(String(totalEl.value).replace(',', '.'));
                        var sum = inputs.reduce(function (a, inp) {
                            var x = parseFloat(String(inp.value).replace(',', '.'));
                            return a + (isFinite(x) ? x : 0);
                        }, 0);
                        sum = Math.round(sum * 100) / 100;
                        var capN = parseFloat(cap);
                        if (isFinite(v) && isFinite(sum) && Math.abs(v - sum) > 0.001) {
                            totalEl.classList.add('border-warning');
                        } else {
                            totalEl.classList.remove('border-warning');
                        }
                        if (isFinite(v) && isFinite(capN) && v > capN + 1e-4) {
                            totalEl.classList.add('is-invalid');
                        }
                    });
                }
                clampRow(inputs, totalEl, cap);
            });
        })();
    </script>
@endsection
