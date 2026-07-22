document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const signupSuccess = urlParams.get('signup');
    
    if (error === 'invalid_email') {
        document.getElementById('emailError').textContent = "Invalid email format";
    } else if (error === 'invalid_credentials') {
        document.getElementById('emailError').textContent = "Invalid email or password";
    }
    
    if (signupSuccess === 'success') {
        const successMessage = document.createElement('div');
        successMessage.className = 'success-message';
        successMessage.textContent = "Registration successful! Please log in.";
        document.querySelector('.login-header').appendChild(successMessage);
    }
    
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', function(e) {
        let isValid = true;
        const userType = document.getElementById('userType');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        
        emailError.textContent = '';
        passwordError.textContent = '';

        if (!userType.value) {
            const userTypeGroup = userType.closest('.input-group');
            if (!userTypeGroup.querySelector('.error-message')) {
                const errorElement = document.createElement('span');
                errorElement.className = 'error-message';
                errorElement.textContent = 'Please select user type';
                userTypeGroup.appendChild(errorElement);
            }
            isValid = false;
        }

        if (!email.value.trim()) {
            emailError.textContent = 'Email is required';
            isValid = false;
        } else if (!isValidEmail(email.value)) {
            emailError.textContent = 'Please enter a valid email';
            isValid = false;
        }

        if (!password.value.trim()) {
            passwordError.textContent = 'Password is required';
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault(); 
        }
    });
    

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});