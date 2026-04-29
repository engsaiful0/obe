/**
 * Global Utilities for Laravel Application
 */

// URL Utilities
window.AppUtils = {
    /**
     * Get the base URL from the HTML data attribute
     */
    getBaseUrl: function() {
        return $('html').attr('data-base-url') || '';
    },

    /**
     * Build a full URL by combining base URL with path
     */
    buildUrl: function(path) {
        var baseUrl = this.getBaseUrl();
        // Ensure baseUrl ends with exactly one slash
        if (!baseUrl.endsWith('/')) {
            baseUrl += '/';
        }
        // Remove leading slash from path if it exists
        if (path.startsWith('/')) {
            path = path.substring(1);
        }
        // Combine baseUrl and path
        return baseUrl + path;
    },

    /**
     * Build API URLs for common CRUD operations
     */
    buildApiUrls: function(basePath) {
        console.log("base path:",basePath)
        var pathParts = basePath.split('/');
        var moduleName = pathParts.pop(); // Get the last part (e.g., 'academic-year')
        var baseDir = pathParts.join('/'); // Get the base directory (e.g., 'app/settings')
        
        var urls = {
            getData: this.buildUrl(baseDir + '/get-' + moduleName),
            store: this.buildUrl(basePath),
            update: this.buildUrl(basePath),
            destroy: this.buildUrl(basePath)
        };
        
        // Add productRow URL for issue and purchase modules
        if (moduleName === 'issue' || moduleName === 'purchase') {
            urls.productRow = this.buildUrl(basePath + '/product-row');
        }
        
        // Add view URL if path contains '/add'
        if (basePath.includes('/add')) {
            urls.view = this.buildUrl(basePath.replace('/add', ''));
        }
        
        console.log("Generated URLs:", urls);
        return urls;
    },

    /**
     * Show success toast message
     */
    showSuccess: function(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    },

    /**
     * Show error toast message
     */
    showError: function(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert('Error: ' + message);
        }
    },

    /**
     * Show warning toast message
     */
    showWarning: function(message) {
        if (typeof toastr !== 'undefined') {
            toastr.warning(message);
        } else {
            alert('Warning: ' + message);
        }
    }
};

// Initialize when document is ready
$(document).ready(function() {
    console.log('AppUtils initialized. Base URL:', AppUtils.getBaseUrl());
});
