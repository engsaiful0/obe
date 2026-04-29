@extends('layouts/layoutMaster')

@section('title', 'Login')

@section('page-style')
<style>
  #login-btn {
  position: relative;
}

#login-spinner {
  margin-right: 8px;
}

</style>
<link href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/password-toggle.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
      <!-- Login -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{url('/')}}" class="app-brand-link">
              <img style="height: 100px;width: 100px;"
                src="{{ $appSettings?->logo ? asset('assets/img/branding/'.$appSettings->logo) : asset('assets/img/default-logo.png') }}"
                alt="Logo">
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-1">Welcome to {{ $appSettings->app_name ?? config('app.name') }}! 👋</h4>
          <p class="mb-6">Please sign-in to your account and start the adventure</p>

          <div id="auth-error" class="alert alert-danger mt-3 d-none"></div>

          <form id="formAuthentication" class="mb-4" action="{{ route('auth-login-basic.post') }}" method="POST">
            @csrf
            <div class="mb-6">
              <label for="email" class="form-label">Email or Username</label>
              <input type="text" value="test@example.com" class="form-control" id="email" name="email-username" placeholder="Enter your email or username" autofocus>
            </div>
            <div class="mb-6">
              <x-password-input
                name="password"
                value="123456"
                placeholder="********"
                label="Password"
                required="true" />
            </div>

            <div class="mb-6 mt-3 text-center">
              <button class="btn btn-primary d-flex align-items-center justify-content-center w-100" id="login-btn" type="button">
                <span class="spinner-border spinner-border-sm text-light me-2 d-none" id="login-spinner" role="status" aria-hidden="true"></span>
                <span class="btn-text">Sign in</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/password-toggle.js') }}"></script>
<script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endsection