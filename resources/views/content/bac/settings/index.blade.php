@extends('layouts/layoutMaster')

@section('title', __('BAC Settings'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('BAC settings') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('bac-settings.update') }}" autocomplete="off">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="clo_attainment_threshold">{{ __('CLO attainment threshold') }}</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" step="0.01" id="clo_attainment_threshold"
                            name="settings[clo_attainment_threshold]" class="form-control"
                            value="{{ old('settings.clo_attainment_threshold', $settings->get('clo_attainment_threshold')?->value ?? 60) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="direct_assessment_weight">{{ __('Direct assessment weight') }}</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" step="0.01" id="direct_assessment_weight"
                            name="settings[direct_assessment_weight]" class="form-control"
                            value="{{ old('settings.direct_assessment_weight', $settings->get('direct_assessment_weight')?->value ?? 80) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="indirect_assessment_weight">{{ __('Indirect assessment weight') }}</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" step="0.01" id="indirect_assessment_weight"
                            name="settings[indirect_assessment_weight]" class="form-control"
                            value="{{ old('settings.indirect_assessment_weight', $settings->get('indirect_assessment_weight')?->value ?? 20) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>

            @permission('bac-manage')
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">{{ __('Save settings') }}</button>
            </div>
            @endpermission
        </form>
    </div>
</div>
@endsection
