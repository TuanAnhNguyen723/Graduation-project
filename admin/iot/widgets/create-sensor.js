// JavaScript cho modal thêm mới cảm biến IoT

// Biến global
let isModalOpen = false;

// Mở modal
function openCreateSensorModal() {
    const modal = document.getElementById('createSensorModal');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            modal.querySelector('.custom-modal').classList.add('show');
        }, 10);
        isModalOpen = true;
        
        // Reset form
        resetCreateSensorForm();
        
        // Focus vào field đầu tiên
        document.getElementById('sensorName').focus();
        
        // Disable scroll của body
        document.body.style.overflow = 'hidden';
    }
}

// Đóng modal
function closeCreateSensorModal() {
    const modal = document.getElementById('createSensorModal');
    if (modal) {
        modal.querySelector('.custom-modal').classList.remove('show');
        setTimeout(() => {
            modal.classList.remove('show');
        }, 300);
        isModalOpen = false;
        
        // Enable scroll của body
        document.body.style.overflow = '';
        
        // Reset form
        resetCreateSensorForm();
    }
}

// Reset form
function resetCreateSensorForm() {
    const form = document.getElementById('createSensorForm');
    if (form) {
        form.reset();
        
        // Clear error messages
        clearAllErrors();
        
        // Hide success message
        hideSuccessMessage();
        
        // Reset form validation classes
        const inputs = form.querySelectorAll('.form-input, .form-select, .form-textarea');
        inputs.forEach(input => {
            input.classList.remove('error', 'success');
        });
    }
}

// Clear tất cả error messages
function clearAllErrors() {
    const errorElements = document.querySelectorAll('.field-error');
    errorElements.forEach(error => {
        error.textContent = '';
        error.classList.remove('show');
    });
}

// Show error message cho field cụ thể
function showFieldError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + 'Error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        
        // Add error class to input
        const input = document.getElementById(fieldId);
        if (input) {
            input.classList.add('error');
            input.classList.remove('success');
        }
    }
}

// Clear error message cho field cụ thể
function clearFieldError(fieldId) {
    const errorElement = document.getElementById(fieldId + 'Error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
        
        // Remove error class from input
        const input = document.getElementById(fieldId);
        if (input) {
            input.classList.remove('error');
        }
    }
}

// Show success message
function showSuccessMessage() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.classList.add('show');
    }
}

// Hide success message
function hideSuccessMessage() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.classList.remove('show');
    }
}

// Validate form
function validateCreateSensorForm() {
    let isValid = true;
    
    // Clear all previous errors
    clearAllErrors();
    
    // Validate required fields
    const requiredFields = [
        { id: 'sensorName', name: 'Tên cảm biến' },
        { id: 'sensorCode', name: 'Mã cảm biến' },
        { id: 'sensorType', name: 'Loại cảm biến' },
        { id: 'locationId', name: 'Vị trí lắp đặt' },
        { id: 'status', name: 'Trạng thái' }
    ];
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field.id);
        if (!input || !input.value.trim()) {
            showFieldError(field.id, `${field.name} là bắt buộc`);
            isValid = false;
        } else {
            clearFieldError(field.id);
            input.classList.add('success');
        }
    });
    
    // Validate sensor code format (alphanumeric + underscore, 3-20 characters)
    const sensorCode = document.getElementById('sensorCode');
    if (sensorCode && sensorCode.value.trim()) {
        const codeRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!codeRegex.test(sensorCode.value.trim())) {
            showFieldError('sensorCode', 'Mã cảm biến chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự');
            isValid = false;
        }
    }
    
    // Validate thresholds if provided
    const minThreshold = document.getElementById('minThreshold');
    const maxThreshold = document.getElementById('maxThreshold');
    
    if (minThreshold && maxThreshold && minThreshold.value && maxThreshold.value) {
        const min = parseFloat(minThreshold.value);
        const max = parseFloat(maxThreshold.value);
        
        if (min >= max) {
            showFieldError('maxThreshold', 'Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu');
            isValid = false;
        }
    }
    
    return isValid;
}

// Submit form
async function submitCreateSensorForm() {
    if (!validateCreateSensorForm()) {
        return;
    }
    
    // Get form data
    const form = document.getElementById('createSensorForm');
    const formData = new FormData(form);
    
    // Add current timestamp
    formData.append('created_at', new Date().toISOString());
    formData.append('updated_at', new Date().toISOString());
    
    let originalText = '';
    try {
        // Show loading state
        const submitBtn = document.getElementById('createSensorSubmitBtn');
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="iconoir-loading me-1"></i>Đang tạo...';
            submitBtn.disabled = true;
        }
        
        // Send request to API
        const response = await fetch('api/create-sensor.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            showSuccessMessage();
            
            // Reset form
            resetCreateSensorForm();
            
            // Close modal after 2 seconds
            setTimeout(() => {
                closeCreateSensorModal();
                
                // Refresh page or update sensor list
                if (typeof refreshSensorList === 'function') {
                    refreshSensorList();
                } else {
                    location.reload();
                }
            }, 2000);
            
        } else {
            // Show error message
            showToast('error', 'Lỗi: ' + (result.message || 'Không thể tạo cảm biến'));
            
            // Show field-specific errors if available
            if (result.errors) {
                Object.keys(result.errors).forEach(field => {
                    showFieldError(field, result.errors[field]);
                });
            }
        }
        
    } catch (error) {
        console.error('Error creating sensor:', error);
        showToast('error', 'Lỗi kết nối: ' + error.message);
    } finally {
        // Reset button state
        const submitBtn = document.getElementById('createSensorSubmitBtn');
        if (submitBtn) {
            submitBtn.innerHTML = originalText || 'Tạo cảm biến';
            submitBtn.disabled = false;
        }
    }
}

// Show toast notification
function showToast(type, message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast bg-${type === 'error' ? 'danger' : 'success'} text-white`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: slideInRight 0.3s ease-out;
    `;
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="iconoir-${type === 'error' ? 'warning-triangle' : 'check-circle'} me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside
    const modal = document.getElementById('createSensorModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCreateSensorModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isModalOpen) {
            closeCreateSensorModal();
        }
    });
    
            // Real-time validation
        const form = document.getElementById('createSensorForm');
        if (form) {
            const inputs = form.querySelectorAll('.form-input, .form-select, .form-textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        clearFieldError(this.id);
                    }
                });
            });
        }
});

// Validate individual field
function validateField(input) {
    const fieldId = input.id;
    const value = input.value.trim();
    
    // Clear previous error
    clearFieldError(fieldId);
    
    // Required field validation
    if (input.hasAttribute('required') && !value) {
        const fieldName = input.previousElementSibling?.textContent?.replace('*', '').trim() || 'Trường này';
        showFieldError(fieldId, `${fieldName} là bắt buộc`);
        return false;
    }
    
    // Specific field validations
    if (fieldId === 'sensorCode' && value) {
        const codeRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!codeRegex.test(value)) {
            showFieldError(fieldId, 'Mã cảm biến chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự');
            return false;
        }
    }
    
    // If validation passes, add success class
    if (value) {
        input.classList.add('success');
    }
    
    return true;
}

// Export functions for global use
window.openCreateSensorModal = openCreateSensorModal;
window.closeCreateSensorModal = closeCreateSensorModal;
window.submitCreateSensorForm = submitCreateSensorForm;
