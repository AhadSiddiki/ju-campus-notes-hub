/**
 * Main JavaScript file for JU Campus Notes Hub
 */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if any
    initTooltips();
    
    // Auto-hide alerts after 5 seconds
    autoHideAlerts();
    
    // Initialize file upload drag and drop
    initFileDragDrop();
    
    // Search autocomplete
    initSearchAutocomplete();
});

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Auto-hide alert messages
 */
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Initialize file drag and drop
 */
function initFileDragDrop() {
    const dropZone = document.querySelector('.file-upload-area');
    if (!dropZone) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('drag-over');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('drag-over');
        }, false);
    });
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const fileInput = document.getElementById('file');
        if (fileInput) {
            fileInput.files = files;
            displayFileName(fileInput);
        }
    }
}

/**
 * Initialize search autocomplete
 */
function initSearchAutocomplete() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;
    
    let timeout = null;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value;
        
        if (query.length < 2) return;
        
        timeout = setTimeout(() => {
            // You can implement AJAX autocomplete here
            // For now, it's a placeholder
        }, 300);
    });
}

/**
 * Display selected file name
 */
function displayFileName(input) {
    const fileNameDisplay = document.getElementById('fileName');
    if (input.files && input.files[0] && fileNameDisplay) {
        const file = input.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        fileNameDisplay.textContent = `Selected: ${file.name} (${fileSize} MB)`;
        fileNameDisplay.classList.remove('hidden');
    }
}

/**
 * Confirm before delete
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

/**
 * Show loading spinner
 */
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.remove();
    }
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show notification
    showNotification('Copied to clipboard!', 'success');
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 
        'bg-blue-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@juniv\.edu$/;
    return re.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    const minLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    
    return {
        valid: minLength && hasUpper && hasLower && hasNumber,
        minLength,
        hasUpper,
        hasLower,
        hasNumber
    };
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
