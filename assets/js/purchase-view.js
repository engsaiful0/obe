/**
 * Purchase View with Pagination and Filtering
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Initialize filter form
  const filterForm = document.getElementById('filter-form');
  if (filterForm) {
    // Auto-submit form when date fields change
    const dateInputs = filterForm.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
      input.addEventListener('change', function() {
        // Add a small delay to allow both date fields to be set
        setTimeout(() => {
          filterForm.submit();
        }, 100);
      });
    });

    // Auto-submit form when supplier changes
    const supplierSelect = filterForm.querySelector('#supplier_id');
    if (supplierSelect) {
      supplierSelect.addEventListener('change', function() {
        filterForm.submit();
      });
    }
  }
});

// Handle dropdown actions
document.addEventListener('DOMContentLoaded', function() {
  // Add event listeners for dropdown actions
  document.addEventListener('click', function(e) {
    if (e.target.closest('.dropdown-item[data-action]')) {
      e.preventDefault();
      const action = e.target.closest('.dropdown-item').getAttribute('data-action');
      const purchaseId = e.target.closest('.dropdown-item').getAttribute('data-purchase-id');
      
      switch(action) {
        case 'view':
          viewPurchase(purchaseId);
          break;
        case 'edit':
          editPurchase(purchaseId);
          break;
        case 'delete':
          deletePurchase(purchaseId);
          break;
      }
    }
  });
});

// View purchase details
function viewPurchase(id) {
  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  // Redirect to view details page
  window.location.href = baseUrl + '/app/purchase/view-details/' + id;
}

// Edit purchase
function editPurchase(id) {
  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  // Redirect to edit page
  window.location.href = baseUrl + '/app/purchase/edit/' + id;
}

// Delete purchase
function deletePurchase(id) {
  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading
      Swal.fire({
        title: 'Deleting...',
        text: 'Please wait while we delete the purchase.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Make AJAX request
      $.ajax({
        url: baseUrl + '/app/purchase/' + id,
        type: 'DELETE',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          Swal.fire({
            title: 'Deleted!',
            text: 'Purchase has been deleted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            // Reload the page to refresh the data
            location.reload();
          });
        },
        error: function(xhr) {
          let errorMessage = 'An error occurred while deleting the purchase.';
          
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
}

// Export PDF with current filters
function exportPdf() {
  // Get current filter parameters
  const urlParams = new URLSearchParams(window.location.search);
  const exportUrl = window.purchaseUrls.exportPdf + (urlParams.toString() ? '?' + urlParams.toString() : '');
  
  // Open PDF in new tab
  window.open(exportUrl, '_blank');
}

// Initialize tooltips if Bootstrap tooltips are available
if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

// Initialize popovers if Bootstrap popovers are available
if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
}

// Add loading state to filter button
document.addEventListener('DOMContentLoaded', function() {
  const filterButton = document.querySelector('#filter-form button[type="submit"]');
  if (filterButton) {
    filterButton.addEventListener('click', function() {
      // Add loading state
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> Filtering...';
      this.disabled = true;
      
      // Reset after form submission
      setTimeout(() => {
        this.innerHTML = originalText;
        this.disabled = false;
      }, 2000);
    });
  }
});

// Add loading state to PDF export button
document.addEventListener('DOMContentLoaded', function() {
  const pdfButton = document.querySelector('a[href*="export-pdf"]');
  if (pdfButton) {
    pdfButton.addEventListener('click', function(e) {
      // Add loading state
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> Generating PDF...';
      this.style.pointerEvents = 'none';
      
      // Reset after a delay
      setTimeout(() => {
        this.innerHTML = originalText;
        this.style.pointerEvents = 'auto';
      }, 3000);
    });
  }
});

// Add smooth scrolling for pagination links
document.addEventListener('DOMContentLoaded', function() {
  const paginationLinks = document.querySelectorAll('.pagination a');
  paginationLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Add loading indicator
      const paginationContainer = document.querySelector('.pagination');
      if (paginationContainer) {
        paginationContainer.style.opacity = '0.6';
        paginationContainer.style.pointerEvents = 'none';
      }
    });
  });
});

// Initialize date picker enhancements
document.addEventListener('DOMContentLoaded', function() {
  // Set default date range (last 30 days) if no dates are selected
  const dateFromInput = document.getElementById('date_from');
  const dateToInput = document.getElementById('date_to');
  
  if (dateFromInput && !dateFromInput.value) {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    dateFromInput.value = thirtyDaysAgo.toISOString().split('T')[0];
  }
  
  if (dateToInput && !dateToInput.value) {
    const today = new Date();
    dateToInput.value = today.toISOString().split('T')[0];
  }
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
  // Ctrl + F to focus on filter form
  if (e.ctrlKey && e.key === 'f') {
    e.preventDefault();
    const filterInput = document.querySelector('#filter-form input[name="purchase_number"]');
    if (filterInput) {
      filterInput.focus();
    }
  }
  
  // Ctrl + E to export PDF
  if (e.ctrlKey && e.key === 'e') {
    e.preventDefault();
    const pdfButton = document.querySelector('a[href*="export-pdf"]');
    if (pdfButton) {
      pdfButton.click();
    }
  }
});

// Add responsive table enhancements
document.addEventListener('DOMContentLoaded', function() {
  const table = document.querySelector('.table-responsive table');
  if (table) {
    // Add horizontal scroll indicator
    const tableContainer = table.closest('.table-responsive');
    if (tableContainer) {
      tableContainer.style.position = 'relative';
      
      // Add scroll indicators
      const leftIndicator = document.createElement('div');
      leftIndicator.className = 'scroll-indicator scroll-left';
      leftIndicator.innerHTML = '<i class="ti ti-chevron-left"></i>';
      leftIndicator.style.cssText = `
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 5px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        display: none;
      `;
      
      const rightIndicator = document.createElement('div');
      rightIndicator.className = 'scroll-indicator scroll-right';
      rightIndicator.innerHTML = '<i class="ti ti-chevron-right"></i>';
      rightIndicator.style.cssText = `
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 5px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        display: none;
      `;
      
      tableContainer.appendChild(leftIndicator);
      tableContainer.appendChild(rightIndicator);
      
      // Show/hide indicators based on scroll position
      tableContainer.addEventListener('scroll', function() {
        const scrollLeft = this.scrollLeft;
        const maxScroll = this.scrollWidth - this.clientWidth;
        
        leftIndicator.style.display = scrollLeft > 0 ? 'block' : 'none';
        rightIndicator.style.display = scrollLeft < maxScroll ? 'block' : 'none';
      });
      
      // Add click handlers for scroll indicators
      leftIndicator.addEventListener('click', function() {
        tableContainer.scrollBy({ left: -200, behavior: 'smooth' });
      });
      
      rightIndicator.addEventListener('click', function() {
        tableContainer.scrollBy({ left: 200, behavior: 'smooth' });
      });
    }
  }
});
