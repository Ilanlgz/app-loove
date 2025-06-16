class AuthFormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            // console.warn(`Form with ID "${formId}" not found.`);
            return;
        }
        this.inputs = this.form.querySelectorAll('input[required], select[required]');
        this.init();
    }

    init() {
        this.form.addEventListener('submit', (event) => {
            if (!this.validateForm()) {
                event.preventDefault(); // Prevent submission if validation fails
                if (window.looveApp && typeof window.looveApp.showMessage === 'function') {
                    // window.looveApp.showMessage('error', 'Please fill all required fields correctly.');
                } else {
                    // alert('Please fill all required fields correctly.');
                }
            }
        });

        this.inputs.forEach(input => {
            input.addEventListener('input', () => this.validateInput(input));
            input.addEventListener('blur', () => this.validateInput(input)); // Validate on blur as well
        });
    }

    validateForm() {
        let isValid = true;
        this.inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });
        return isValid;
    }

    validateInput(input) {
        let isValid = true;
        const errorElement = input.nextElementSibling && input.nextElementSibling.classList.contains('error-text') 
                           ? input.nextElementSibling 
                           : this.findErrorElementFor(input);
        
        // Clear previous custom error message
        if (errorElement) errorElement.textContent = ''; 
        input.classList.remove('input-error');

        if (input.hasAttribute('required') && !input.value.trim()) {
            this.setError(input, errorElement, `${this.getLabelText(input)} is required.`);
            isValid = false;
        } else if (input.type === 'email' && input.value.trim() && !this.isValidEmail(input.value.trim())) {
            this.setError(input, errorElement, 'Please enter a valid email address.');
            isValid = false;
        } else if (input.id === 'password' && input.value && input.value.length < 6) {
            this.setError(input, errorElement, 'Password must be at least 6 characters.');
            isValid = false;
        } else if (input.id === 'confirm_password') {
            const passwordInput = this.form.querySelector('#password');
            if (passwordInput && input.value !== passwordInput.value) {
                this.setError(input, errorElement, 'Passwords do not match.');
                isValid = false;
            }
        }
        // Add more specific validations here if needed

        if (isValid) {
            this.clearError(input, errorElement);
        }
        return isValid;
    }

    isValidEmail(email) {
        // Basic email validation regex
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    getLabelText(input) {
        const label = this.form.querySelector(`label[for="${input.id}"]`);
        return label ? label.textContent.replace(':', '') : (input.name || input.id);
    }

    setError(input, errorElement, message) {
        input.classList.add('input-error');
        if (errorElement) {
            errorElement.textContent = message;
        } else {
            // Create error element if not found (e.g. not provided by PHP)
            const newErrorElement = document.createElement('span');
            newErrorElement.className = 'error-text';
            newErrorElement.textContent = message;
            input.parentNode.insertBefore(newErrorElement, input.nextSibling);
        }
    }

    clearError(input, errorElement) {
        input.classList.remove('input-error');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    findErrorElementFor(input) {
        // Try to find a sibling .error-text, or one inside the parent .form-group
        let sibling = input.nextElementSibling;
        if (sibling && sibling.classList.contains('error-text')) {
            return sibling;
        }
        const parentGroup = input.closest('.form-group');
        if (parentGroup) {
            return parentGroup.querySelector('.error-text');
        }
        return null;
    }
}

class AuthManager {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            // Login form
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.classList.add('ajax-form');
            }

            // Register form
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.classList.add('ajax-form');
            }

            // Logout links
            const logoutLinks = document.querySelectorAll('a[href*="logout"]');
            logoutLinks.forEach(link => {
                link.classList.add('ajax-link');
            });

            // Navigation links
            const navLinks = document.querySelectorAll('.navbar a, .nav a');
            navLinks.forEach(link => {
                if (!link.getAttribute('target') && !link.classList.contains('no-ajax')) {
                    link.classList.add('ajax-link');
                }
            });
        });
    }
}

// Initialiser
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('loginForm')) {
        new AuthFormValidator('loginForm');
    }
    if (document.getElementById('registerForm')) {
        new AuthFormValidator('registerForm');
    }
    new AuthManager();
});
