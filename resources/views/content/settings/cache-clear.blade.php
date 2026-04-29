@extends('layouts/contentNavbarLayout')

@section('title', 'Clear Cache - Settings')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/ui-toasts.js')}}"></script>
@endsection

@section('content')
<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;"></div>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Cache Management</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <h6 class="mb-3">Clear Application Cache</h6>
              <p class="text-muted mb-4">
                Clearing cache can help resolve issues with outdated data, configuration changes, or performance problems. 
                This will clear all cached data including application cache, configuration cache, route cache, and view cache.
              </p>
              
              <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading fw-bold mb-1">Warning!</h6>
                <p class="mb-0">
                  Clearing cache will temporarily slow down your application as it rebuilds the cache. 
                  This operation is safe but should be done during low-traffic periods.
                </p>
              </div>

              <div class="mb-4">
                <h6>What will be cleared:</h6>
                <ul class="list-unstyled">
                  <li><i class="ti ti-check text-success me-2"></i> Application cache</li>
                  <li><i class="ti ti-check text-success me-2"></i> Configuration cache</li>
                  <li><i class="ti ti-check text-success me-2"></i> Route cache</li>
                  <li><i class="ti ti-check text-success me-2"></i> View cache</li>
                  <li><i class="ti ti-check text-success me-2"></i> Compiled services cache</li>
                </ul>
              </div>

              <button type="button" class="btn btn-primary" id="clearCacheBtn">
                <i class="ti ti-trash me-2"></i>Clear All Cache
              </button>
            </div>
            
            <div class="col-md-4">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title">Cache Information</h6>
                  <p class="card-text small">
                    <strong>Cache Driver:</strong> {{ config('cache.default') }}<br>
                    <strong>Config Cache:</strong> {{ file_exists(base_path('bootstrap/cache/config.php')) ? 'Cached' : 'Not Cached' }}<br>
                    <strong>Route Cache:</strong> {{ file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'Cached' : 'Not Cached' }}<br>
                    <strong>View Cache:</strong> {{ count(glob(storage_path('framework/views/*'))) }} files
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mb-0">Clearing cache...</p>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('clearCacheBtn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2 me-2"></i>Clearing...';
    
    // Show loading modal
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    // Make AJAX requesis
    fetch('{{ route("clear-cache") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            // Show success toast
            const toastHtml = `
                <div class="bs-toast toast bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="toast-header">
                        <i class="ti ti-check-circle me-2 text-success"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body text-white">
                        ${data.message}
                    </div>
                </div>
            `;
            
            // Add toast to container
            const toastContainer = document.querySelector('.toast-container');
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Initialize and show the toast
            const newToast = toastContainer.querySelector('.toast:last-child');
            const toast = new bootstrap.Toast(newToast);
            toast.show();
            
        } else {
            // Show error toast
            const toastHtml = `
                <div class="bs-toast toast bg-danger" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="7000">
                    <div class="toast-header">
                        <i class="ti ti-x-circle me-2 text-danger"></i>
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body text-white">
                        ${data.message}
                    </div>
                </div>
            `;
            
            const toastContainer = document.querySelector('.toast-container');
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Initialize and show the toast
            const newToast = toastContainer.querySelector('.toast:last-child');
            const toast = new bootstrap.Toast(newToast);
            toast.show();
        }
    })
    .catch(error => {
        loadingModal.hide();
        console.error('Error:', error);
        
        // Show error toast
        const toastHtml = `
            <div class="bs-toast toast bg-danger" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="7000">
                <div class="toast-header">
                    <i class="ti ti-x-circle me-2 text-danger"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body text-white">
                    An error occurred while clearing cache. Please try again.
                </div>
            </div>
        `;
        
        const toastContainer = document.querySelector('.toast-container');
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show the toast
        const newToast = toastContainer.querySelector('.toast:last-child');
        const toast = new bootstrap.Toast(newToast);
        toast.show();
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
@endsection
