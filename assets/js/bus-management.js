/**
 * Bus Management JavaScript
 * Handles bus-related functionality including form validation, file uploads, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize bus management functionality
    initBusManagement();
});

function initBusManagement() {
    // Initialize form validation
    initFormValidation();
    
    // Initialize file upload previews
    initFileUploadPreviews();
    
    // Initialize search and filter functionality
    initSearchAndFilter();
    
    // Initialize status indicators
    initStatusIndicators();
    
    // Initialize document expiry warnings
    initDocumentExpiryWarnings();
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-bus-form]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateBusForm(form)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Validate bus form
 */
function validateBusForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Clear previous error states
    clearFormErrors(form);
    
    // Validate required fields
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate unique fields
    const chassisNumber = form.querySelector('input[name="chassis_number"]');
    const registrationNumber = form.querySelector('input[name="registration_number"]');
    
    if (chassisNumber && chassisNumber.value.trim()) {
        if (!validateChassisNumber(chassisNumber.value)) {
            showFieldError(chassisNumber, 'Invalid chassis number format');
            isValid = false;
        }
    }
    
    if (registrationNumber && registrationNumber.value.trim()) {
        if (!validateRegistrationNumber(registrationNumber.value)) {
            showFieldError(registrationNumber, 'Invalid registration number format');
            isValid = false;
        }
    }
    
    // Validate date fields
    const dateFields = form.querySelectorAll('input[type="date"]');
    dateFields.forEach(field => {
        if (field.value && !validateDate(field.value)) {
            showFieldError(field, 'Invalid date format');
            isValid = false;
        }
    });
    
    // Validate file uploads
    const fileInputs = form.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        if (input.files.length > 0) {
            if (!validateFileUpload(input)) {
                showFieldError(input, 'Invalid file type or size');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

/**
 * Initialize file upload previews
 */
function initFileUploadPreviews() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showFilePreview(input, file);
            }
        });
    });
}

/**
 * Show file preview
 */
function showFilePreview(input, file) {
    const previewContainer = input.parentNode.querySelector('.file-preview');
    if (!previewContainer) {
        const preview = document.createElement('div');
        preview.className = 'file-preview mt-2';
        input.parentNode.appendChild(preview);
    }
    
    const preview = input.parentNode.querySelector('.file-preview');
    preview.innerHTML = '';
    
    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'img-thumbnail';
        img.style.maxWidth = '200px';
        img.style.maxHeight = '150px';
        preview.appendChild(img);
    } else {
        const fileInfo = document.createElement('div');
        fileInfo.className = 'alert alert-info';
        fileInfo.innerHTML = `
            <i class="ti ti-file me-2"></i>
            <strong>${file.name}</strong> (${formatFileSize(file.size)})
        `;
        preview.appendChild(fileInfo);
    }
}

/**
 * Initialize search and filter functionality
 */
function initSearchAndFilter() {
    const searchInput = document.querySelector('input[name="search"]');
    const statusFilter = document.querySelector('select[name="status"]');
    const typeFilter = document.querySelector('select[name="bus_type_id"]');
    
    if (searchInput) {
        // Debounce search
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', performSearch);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', performSearch);
    }
}

/**
 * Perform search
 */
function performSearch() {
    const form = document.querySelector('form[method="GET"]');
    if (form) {
        form.submit();
    }
}

/**
 * Initialize status indicators
 */
function initStatusIndicators() {
    const statusBadges = document.querySelectorAll('.status-badge');
    
    statusBadges.forEach(badge => {
        const status = badge.textContent.toLowerCase().replace(/\s+/g, '_');
        badge.className = `badge bg-${getStatusColor(status)}`;
    });
}

/**
 * Initialize document expiry warnings
 */
function initDocumentExpiryWarnings() {
    const expiryElements = document.querySelectorAll('[data-expiry-date]');
    
    expiryElements.forEach(element => {
        const expiryDate = new Date(element.dataset.expiryDate);
        const today = new Date();
        const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
        
        if (daysUntilExpiry < 0) {
            element.classList.add('text-danger');
            element.innerHTML += ' <i class="ti ti-alert-triangle"></i>';
        } else if (daysUntilExpiry <= 30) {
            element.classList.add('text-warning');
            element.innerHTML += ' <i class="ti ti-clock"></i>';
        }
    });
}

/**
 * Utility Functions
 */

function validateChassisNumber(chassisNumber) {
    // Basic chassis number validation (alphanumeric, 8-17 characters)
    const chassisRegex = /^[A-HJ-NPR-Z0-9]{8,17}$/i;
    return chassisRegex.test(chassisNumber);
}

function validateRegistrationNumber(registrationNumber) {
    // Basic registration number validation (alphanumeric with spaces)
    const registrationRegex = /^[A-Z0-9\s-]{3,15}$/i;
    return registrationRegex.test(registrationNumber);
}

function validateDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

function validateFileUpload(input) {
    const file = input.files[0];
    if (!file) return true;
    
    const maxSize = input.dataset.maxSize || 5242880; // 5MB default
    const allowedTypes = input.accept.split(',').map(type => type.trim());
    
    // Check file size
    if (file.size > maxSize) {
        return false;
    }
    
    // Check file type
    const fileType = file.type;
    const fileName = file.name.toLowerCase();
    
    return allowedTypes.some(type => {
        if (type.startsWith('.')) {
            return fileName.endsWith(type);
        } else {
            return fileType.match(type.replace('*', '.*'));
        }
    });
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        field.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

function clearFormErrors(form) {
    const invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    const errorMessages = form.querySelectorAll('.invalid-feedback');
    errorMessages.forEach(message => {
        message.textContent = '';
    });
}

function getStatusColor(status) {
    const statusColors = {
        'active': 'success',
        'inactive': 'secondary',
        'under_maintenance': 'warning'
    };
    
    return statusColors[status] || 'secondary';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Bus-specific functions
 */

function updateMileage(busId, newMileage) {
    // AJAX call to update bus mileage
    fetch(`/buses/${busId}/mileage`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            current_mileage: newMileage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Mileage updated successfully', 'success');
            location.reload();
        } else {
            showNotification('Failed to update mileage', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function markServiceCompleted(busId) {
    // AJAX call to mark service as completed
    fetch(`/buses/${busId}/service-completed`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Service marked as completed', 'success');
            location.reload();
        } else {
            showNotification('Failed to update service status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

/**
 * Export functions for global use
 */
window.BusManagement = {
    updateMileage,
    markServiceCompleted,
    showNotification
};

