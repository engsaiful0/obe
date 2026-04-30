@extends('layouts/layoutMaster')

@section('title', 'Create Teacher')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Create Teacher</h5></div>
    <div class="card-body">
        <div id="teacher-form-errors" class="alert alert-danger d-none" role="alert"></div>
        <form id="teacher-create-form" method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data">
            @csrf
            @include('content.teachers._form', ['teacher' => null])
            <div class="mt-3">
                <button type="submit" id="teacher-save-btn" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="btn-label">Save Teacher</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
(function () {
    const form = document.getElementById('teacher-create-form');
    const btn = document.getElementById('teacher-save-btn');
    if (!form || !btn) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const errBox = document.getElementById('teacher-form-errors');
        const spinner = btn.querySelector('.spinner-border');

        errBox?.classList.add('d-none');
        if (errBox) errBox.textContent = '';

        btn.disabled = true;
        spinner?.classList.remove('d-none');

        try {
            const fd = new FormData(form);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || '',
                },
                body: fd,
                credentials: 'same-origin',
            });

            let data = {};
            try {
                data = await res.json();
            } catch (_) {
                /* non-JSON */
            }

            if (res.ok) {
                window.location.href = data.redirect_url || @json(route('teachers.index'));
                return;
            }

            let msg = data.message || 'Could not save teacher.';
            if (data.errors && typeof data.errors === 'object') {
                const lines = [];
                Object.keys(data.errors).forEach(function (k) {
                    const arr = data.errors[k];
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
