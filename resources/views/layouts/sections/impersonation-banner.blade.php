@if(session('impersonator_id'))
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3" role="alert">
        <span>You are signed in as <strong>{{ auth()->user()->name }}</strong> (teacher view).</span>
        <form method="POST" action="{{ route('teachers.stop-impersonating') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">Return to admin</button>
        </form>
    </div>
@endif
