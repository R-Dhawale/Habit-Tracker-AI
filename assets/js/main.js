// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check for saved dark mode preference
    const darkModeEnabled = localStorage.getItem('darkMode');
    
    if (darkModeEnabled === 'enabled') {
        body.classList.add('dark-mode');
        if (darkModeToggle) {
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
    }
    
    // Dark mode toggle click event
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
    }
});

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Add loading state to buttons on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
        }
    });
});

// Confirm delete actions
function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.createElement('div');
    toastContainer.className = `position-fixed bottom-0 end-0 p-3`;
    toastContainer.style.zIndex = '11';
    
    const toastColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    
    toastContainer.innerHTML = `
        <div class="toast show ${toastColor} text-white" role="alert">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            </div>
        </div>
    `;
    
    document.body.appendChild(toastContainer);
    
    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    return strength;
}

// Real-time password strength display
const passwordInput = document.getElementById('password');
if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const strengthColor = ['danger', 'warning', 'info', 'primary', 'success'];
        
        let strengthIndicator = document.getElementById('password-strength');
        if (!strengthIndicator) {
            strengthIndicator = document.createElement('div');
            strengthIndicator.id = 'password-strength';
            strengthIndicator.className = 'mt-2';
            this.parentNode.appendChild(strengthIndicator);
        }
        
        if (this.value.length > 0) {
            strengthIndicator.innerHTML = `
                <small class="text-${strengthColor[strength - 1]}">
                    Password Strength: ${strengthText[strength - 1]}
                </small>
            `;
        } else {
            strengthIndicator.innerHTML = '';
        }
    });
}