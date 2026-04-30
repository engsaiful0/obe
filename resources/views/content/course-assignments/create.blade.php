@extends('layouts/layoutMaster')

@section('title', __('Add course assignment'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('New course assignment') }}</h5></div>
    <div class="card-body">
        <div id="course-assignment-form-errors" class="alert alert-danger d-none" role="alert"></div>
        <form id="course-assignment-form" method="POST" action="{{ route('course-assignment.store') }}">
            @csrf
            @include('content.course-assignments._form', ['assignment' => null])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" id="course-assignment-save-btn" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="btn-label">{{ __('Save assignment') }}</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
                <a href="{{ route('course-assignment.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
@include('content.course-assignments._cascade_script', ['assignment' => null])
<script>
(function () {
    var indexUrl = @json(route('course-assignment.index'));
    var fallbackErr = @json(__('Could not save assignment.'));
    var form = document.getElementById('course-assignment-form');
    var btn = document.getElementById('course-assignment-save-btn');
    if (!form || !btn) return;
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var errBox = document.getElementById('course-assignment-form-errors');
        var spinner = btn.querySelector('.spinner-border');
        errBox?.classList.add('d-none');
        if (errBox) errBox.textContent = '';
        btn.disabled = true;
        spinner?.classList.remove('d-none');
        try {
            var fd = new FormData(form);
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            var res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || ''
                },
                body: fd,
                credentials: 'same-origin'
            });
            var data = {};
            try { data = await res.json(); } catch (_) {}
            if (res.ok) {
                window.location.href = data.redirect_url || indexUrl;
                return;
            }
            var msg = data.message || fallbackErr;
            if (data.errors && typeof data.errors === 'object') {
                var lines = [];
                Object.keys(data.errors).forEach(function (k) {
                    var arr = data.errors[k];
                    if (Array.isArray(arr)) arr.forEach(function (line) { lines.push(line); });
                });
                if (lines.length) msg = lines.join(' ');
            }
            if (errBox) {
                errBox.textContent = msg;
                errBox.classList.remove('d-none');
            }
        } finally {
            btn.disabled = false;
            spinner?.classList.add('d-none');
        }
    });
})();
</script>
@endsection
