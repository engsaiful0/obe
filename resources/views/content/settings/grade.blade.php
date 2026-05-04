@extends('layouts/layoutMaster')

@section('title', __('Grade settings'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ __('Grades') }}</h5>
            <button type="button" class="btn btn-primary btn-sm" id="grade-btn-open-add">
                <i class="ti ti-plus me-1"></i>{{ __('Add grade') }}
            </button>
        </div>

        <div class="card-body position-relative">
            <div id="grade-table-overlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-body d-flex align-items-center justify-content-center rounded"
                 style="z-index: 5; min-height: 120px; opacity: 0.85;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading') }}…</span>
                    </div>
                    <div class="small text-muted mt-2">{{ __('Loading grades') }}…</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('From marks') }}</th>
                            <th>{{ __('To marks') }}</th>
                            <th>{{ __('Grade name') }}</th>
                            <th>{{ __('Grade point') }}</th>
                            <th class="text-end" style="width: 140px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="grade-table-body">
                        <tr id="grade-table-empty">
                            <td colspan="5" class="text-muted text-center py-4">{{ __('No grades yet.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="grade-offcanvas" aria-labelledby="grade-offcanvas-title">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="grade-offcanvas-title">{{ __('Add grade') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body">
            <div id="grade-form-errors" class="alert alert-danger d-none mb-3" role="alert"></div>

            <form id="grade-form" class="row g-3" onsubmit="return false">
                @csrf
                <input type="hidden" name="id" id="grade_id" value="">

                <div class="col-12">
                    <label class="form-label" for="from_marks">{{ __('From marks') }}</label>
                    <input type="text" inputmode="decimal" class="form-control" id="from_marks" name="from_marks" required autocomplete="off">
                </div>
                <div class="col-12">
                    <label class="form-label" for="to_marks">{{ __('To marks') }}</label>
                    <input type="text" inputmode="decimal" class="form-control" id="to_marks" name="to_marks" required autocomplete="off">
                </div>
                <div class="col-12">
                    <label class="form-label" for="grade_name">{{ __('Grade name') }}</label>
                    <input type="text" class="form-control" id="grade_name" name="grade_name" required maxlength="100" autocomplete="off">
                </div>
                <div class="col-12">
                    <label class="form-label" for="grade_point">{{ __('Grade point') }}</label>
                    <input type="text" inputmode="decimal" class="form-control" id="grade_point" name="grade_point" autocomplete="off" placeholder="0">
                    <div class="form-text">{{ __('Use 0 for a fail grade. Leave blank to save as 0.') }}</div>
                </div>

                <div class="col-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-primary" id="grade-form-submit">
                        <span class="obe-btn-label">{{ __('Save') }}</span>
                        <span class="spinner-border spinner-border-sm d-none obe-btn-spinner ms-1" role="status" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        window.__gradeSettings = {
            listUrl: @json(route('grade.data')),
            storeUrl: @json(route('grade.store')),
            updateUrlBase: @json(url('/app/settings/grade')),
            csrf: @json(csrf_token()),
            strings: {
                empty: @json(__('No grades yet.')),
                edit: @json(__('Edit')),
                delete: @json(__('Delete')),
                addTitle: @json(__('Add grade')),
                editTitle: @json(__('Edit grade')),
                deleteConfirm: @json(__('Delete this grade?')),
                loadFailed: @json(__('Could not load grades.')),
                validationFailed: @json(__('Validation failed.')),
                network: @json(__('Network error.')),
                deleteFailed: @json(__('Delete failed.'))
            }
        };
    </script>
    <script src="{{ asset('assets/js/settings-grade.js') }}?v={{ time() }}"></script>
@endsection
