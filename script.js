// ==================== Smooth Scrolling & Navbar ==================== 
document.addEventListener('DOMContentLoaded', function() {
    // Back to Top Button
    const backToTopBtn = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // Contact Form Handling
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }
});

// ==================== Scroll to Top ==================== 
function toggleContactForm() {
    const form = document.getElementById('contactForm');
    const btn = document.getElementById('showContactFormBtn');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.style.display = 'none';
    } else {
        form.style.display = 'none';
        btn.style.display = 'inline-flex';
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ==================== Contact Form Handler ==================== 
function handleContactForm(event) {
    event.preventDefault();
    
    const firstName = document.getElementById('FirstName').value.trim();
    const lastName = document.getElementById('LastName').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    const formMessage = document.getElementById('formMessage');

    // Reset message
    formMessage.textContent = '';

    // Validation
    const errors = validateForm(firstName, lastName, email, subject, message);
    
    if (errors.length > 0) {
        formMessage.textContent = errors.join(' ');
        formMessage.style.color = '#ef4444';
        return;
    }

    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;

    // Send form data to Web3Forms (static-site friendly, no server/PHP needed)
    const formData = new FormData(event.target);

    fetch('https://api.web3forms.com/submit', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            formMessage.textContent = '✓ Message sent successfully! I\'ll get back to you soon.';
            formMessage.style.color = '#10b981';
            event.target.reset();
            
            // Reset button after delay
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                formMessage.textContent = '';
            }, 3000);
        } else {
            formMessage.textContent = '✗ ' + (data.message || 'Something went wrong.');
            formMessage.style.color = '#ef4444';
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        formMessage.textContent = '✗ An error occurred. Please try again.';
        formMessage.style.color = '#ef4444';
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// ==================== Form Validation ==================== 
function validateForm(firstName, lastName, email, subject, message) {
    const errors = [];

    if (firstName === '') {
        errors.push('First name is required.');
    }

    if (lastName === '') {
        errors.push('Last name is required.');
    }

    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!email.match(emailPattern)) {
        errors.push('Please enter a valid email address.');
    }

    if (subject === '') {
        errors.push('Subject is required.');
    }

    if (message.length < 10) {
        errors.push('Message must be at least 10 characters long.');
    }

    return errors;
}

// ==================== Active Navigation Link ==================== 
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links a');

    window.addEventListener('scroll', () => {
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= sectionTop - 200) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').slice(1) === currentSection) {
                link.classList.add('active');
            }
        });
    });
});

// ==================== Lazy Loading Images ==================== 
function setupLazyLoading() {
    const images = document.querySelectorAll('img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                    }
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            if (img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    }
}

// ==================== Animate on Scroll ==================== 
function setupAnimateOnScroll() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.project-card, .skill-category, .cert-card').forEach(el => {
        observer.observe(el);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    setupLazyLoading();
    setupAnimateOnScroll();
});

// ==================== Mobile Menu Toggle ==================== 
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        navLinks.classList.toggle('active');
    }
}

// ==================== Add to CSS for animations ==================== 
const style = document.createElement('style');
style.textContent = `
    .fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .nav-links a.active::after {
        width: 100% !important;
    }

    img.loaded {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);