@php
$wiz = [
'storeUrl' => route('question-clo-mappings.store'),
'csrf' => csrf_token(),
'cascade' => $cascadeUrls,
'blooms' => $wizardBlooms,
'statuses' => $wizardStatuses,
];
@endphp

<div id="qcm-wizard-root" class="qcm-wizard" data-ph-select-status="{{ __('Select status') }}" data-ph-part="{{ __('Part') }}" data-ph-add-part="{{ __('Add part') }}" data-ph-remaining="{{ __('Remaining for main question') }}" data-err-fill-step1="{{ __('Select program, course, and assessment component first.') }}" data-err-save="{{ __('Unable to save. Check highlighted fields.') }}">
    <script type="application/json" id="qcm-wizard-json">@json($wiz)</script>
    <div class="alert alert-danger d-none mb-3" role="alert" data-qcm-wizard-errors></div>

    <h6 class="text-muted mb-2">{{ __('Step 1: Context') }}</h6>
    <div class="row g-3 mb-4 qcm-step1-grid pb-3 border-bottom">
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="wiz_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
            <select id="wiz_program_id" class="form-select" data-wiz-program required>
                <option value="">{{ __('Select program') }}</option>
                @foreach ($programs as $p)
                <option value="{{ $p->id }}">{{ $p->program_code }} — {{ $p->program_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="wiz_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
            <select id="wiz_course_id" class="form-select" data-wiz-course required disabled>
                <option value="">{{ __('Select program first') }}</option>
            </select>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="wiz_ac_id">{{ __('Assessment component') }} <span class="text-danger">*</span></label>
            <select id="wiz_ac_id" class="form-select" data-wiz-ac required disabled>
                <option value="">{{ __('Select course first') }}</option>
            </select>
            <div class="form-text" data-wiz-component-cap aria-hidden="true"></div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h6 class="text-muted mb-0">{{ __('Step 2: Main questions & parts') }}</h6>
        <button type="button" class="btn btn-outline-primary btn-sm" data-qcm-add-main>
            {{ __('+ Add main question') }}
        </button>
    </div>

    <div id="qcm-main-list" class="mb-4"></div>

    <p class="text-muted small mb-2" data-wiz-total-hint aria-hidden="true"></p>

    @csrf
</div>

<template id="qcm-tpl-main">
    <div class="card mb-3 qcm-main-card" data-main-card>
        <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-medium">{{ __('Main question') }}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-main>{{ __('Remove') }}</button>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small">{{ __('Main Q no.') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" maxlength="20" data-main-no placeholder="Q1" required>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label small">{{ __('Main marks') }} <span class="text-danger">*</span></label>
                <input type="number" class="form-control form-control-sm" step="0.01" min="0.01" max="100" data-main-cap required>
            </div>
            <div class="col-12">
                <span class="form-label small d-block">{{ __('Multiple parts') }} <span class="text-danger">*</span></span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" data-main-multi-yes name="REPLACE_MULTI_GRP" value="1">
                    <label class="form-check-label">{{ __('Yes') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" data-main-multi-no name="REPLACE_MULTI_GRP" value="0" checked>
                    <label class="form-check-label">{{ __('No') }}</label>
                </div>
            </div>
            <div class="col-12 small text-info" data-main-remaining style="display:none;"></div>
            <div class="col-12">
                <div class="table-responsive border rounded">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Part') }}</th>
                                <th>{{ __('Label') }}</th>
                                <th>{{ __('Marks') }}</th>
                                <th>{{ __('CLO') }}</th>
                                <th>{{ __("Bloom") }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody data-parts-body></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-add-part style="display:none;">
                    {{ __('+ Add part') }}
                </button>
            </div>
        </div>
    </div>
</template>

<template id="qcm-tpl-part">
    <tr data-part-row>
        <td>
            <select class="form-select form-select-sm" data-field="question_part">
                <option value="">—</option>
                <option value="a">a</option>
                <option value="b">b</option>
                <option value="c">c</option>
                <option value="d">d</option>
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm" maxlength="50" data-field="question_label" required></td>
        <td style="width:6rem;"><input type="number" class="form-control form-control-sm" step="0.01" min="0.01" max="100" data-field="marks" required></td>
        <td><select class="form-select form-select-sm" data-field="clo_id" required>
                <option value="">{{ __('CLO') }}</option>
            </select></td>
        <td><select class="form-select form-select-sm" data-field="bloom_id">
                <option value="">{{ __('—') }}</option>
            </select></td>
        <td><select class="form-select form-select-sm" data-field="status_id" required>
                <option value="">{{ __('Select status') }}</option>
            </select></td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-part>×</button>
        </td>
    </tr>
</template>
