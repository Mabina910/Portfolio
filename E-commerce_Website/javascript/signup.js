

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    const password = document.getElementById('password');
    const toggleButton = document.querySelector('.toggle-password');
   
    toggleButton.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.textContent = type === 'password' ? 'Show' : 'Hide';
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;
  
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
        });
        document.querySelectorAll('.error-input').forEach(el => {
            el.classList.remove('error-input');
        });
        
        const fullName = document.querySelector('input[name="full_name"]');
        if (!fullName.value.trim() || fullName.value.length < 2) {
            document.getElementById('fullNameError').textContent = 'Full name is required and must be at least 2 characters';
            fullName.classList.add('error-input');
            isValid = false;
        }

        const phoneNumber = document.querySelector('input[name="phone_number"]');
        if (!phoneNumber.value.match(/^[0-9]{10}$/)) {
            document.getElementById('phoneError').textContent = 'Phone number must be exactly 10 digits';
            phoneNumber.classList.add('error-input');
            isValid = false;
        }

        const email = document.querySelector('input[name="email"]');
        if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            document.getElementById('emailError').textContent = 'Please enter a valid email address';
            email.classList.add('error-input');
            isValid = false;
        }
 
        const gender = document.querySelector('select[name="gender"]');
        if (!gender.value) {
            document.getElementById('genderError').textContent = 'Please select a gender';
            gender.classList.add('error-input');
            isValid = false;
        }

        const day = document.querySelector('select[name="day"]');
        const month = document.querySelector('select[name="month"]');
        const year = document.querySelector('select[name="year"]');
        if (!day.value || !month.value || !year.value) {
            document.getElementById('dobError').textContent = 'Please select a complete date of birth';
            day.classList.add('error-input');
            month.classList.add('error-input');
            year.classList.add('error-input');
            isValid = false;
        }

        if (!password.value.match(/^(?=.*[A-Z])(?=.*[@_#])(?=.*\d)[A-Za-z\d@_#]{8,}$/)) {
            document.getElementById('passwordError').textContent = 'Password must be at least 8 characters, include one uppercase letter, one number, and one special character (@, _, #)';
            password.classList.add('error-input');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault(); 
        }
    });
});