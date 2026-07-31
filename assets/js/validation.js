/**
 * Frontend Forms Validation Engine
 * Handles user, login, contact form validations using regexes and Bootstrap classes.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Unified bootstrap standard validation hook
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 2. Specific Validation Rules and Interactions
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', event => {
            let isValid = true;

            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const passInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');

            // Email check
            if (emailInput && !validateEmail(emailInput.value)) {
                showInvalid(emailInput, 'Please provide a valid email format.');
                isValid = false;
            } else if (emailInput) {
                showValid(emailInput);
            }

            // Phone check
            if (phoneInput && !validatePhone(phoneInput.value)) {
                showInvalid(phoneInput, 'Please provide a valid phone number (10-15 digits).');
                isValid = false;
            } else if (phoneInput) {
                showValid(phoneInput);
            }

            // Password strength check
            if (passInput && !validatePasswordStrength(passInput.value)) {
                showInvalid(passInput, 'Password must be at least 8 characters long, containing 1 uppercase, 1 lowercase, 1 number, and 1 special character.');
                isValid = false;
            } else if (passInput) {
                showValid(passInput);
            }

            // Confirm Password check
            if (passInput && confirmInput && passInput.value !== confirmInput.value) {
                showInvalid(confirmInput, 'Passwords do not match.');
                isValid = false;
            } else if (confirmInput) {
                showValid(confirmInput);
            }

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', event => {
            let isValid = true;
            const emailInput = document.getElementById('email');
            
            if (emailInput && !validateEmail(emailInput.value)) {
                showInvalid(emailInput, 'Please check your email address format.');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', event => {
            let isValid = true;
            const emailInput = document.getElementById('email');
            
            if (emailInput && !validateEmail(emailInput.value)) {
                showInvalid(emailInput, 'Please enter a valid email address.');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    // Toggle Show/Hide Password functionality
    const togglePassBtn = document.getElementById('togglePassword');
    if (togglePassBtn) {
        togglePassBtn.addEventListener('click', () => {
            const passInput = document.getElementById('password');
            const passIcon = togglePassBtn.querySelector('i');
            if (passInput) {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    if (passIcon) {
                        passIcon.classList.remove('bi-eye');
                        passIcon.classList.add('bi-eye-slash');
                    }
                } else {
                    passInput.type = 'password';
                    if (passIcon) {
                        passIcon.classList.remove('bi-eye-slash');
                        passIcon.classList.add('bi-eye');
                    }
                }
            }
        });
    }
});

// Helper Validators
function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function validatePhone(phone) {
    const re = /^\+?[0-9]{10,15}$/;
    return re.test(String(phone).replace(/[\s-]/g, ''));
}

function validatePasswordStrength(password) {
    // Length >= 8, contains digit, upper, lower, special
    const minLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasDigit = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);
    return minLength && hasUpper && hasLower && hasDigit && hasSpecial;
}

function showInvalid(inputEl, msg) {
    inputEl.classList.remove('is-valid');
    inputEl.classList.add('is-invalid');
    let feedback = inputEl.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        inputEl.parentNode.appendChild(feedback);
    }
    feedback.innerText = msg;
}

function showValid(inputEl) {
    inputEl.classList.remove('is-invalid');
    inputEl.classList.add('is-valid');
}
