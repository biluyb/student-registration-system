// Enhanced JavaScript with animations and interactions

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

async function initializeApp() {
    // Show loading animation
    showLoading();
    
    // Initialize all components
    await Promise.all([
        initializeTheme(),
        initializeAnimations(),
        initializeFormValidation(),
        initializeNavigation()
    ]);
    
    // Hide loading animation
    hideLoading();
}

// Loading states
function showLoading() {
    const loadingEl = document.createElement('div');
    loadingEl.className = 'page-loading';
    loadingEl.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingEl);
}

function hideLoading() {
    const loadingEl = document.querySelector('.page-loading');
    if (loadingEl) {
        loadingEl.style.opacity = '0';
        setTimeout(() => loadingEl.remove(), 500);
    }
}

// Theme functionality
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle?.querySelector('i');
    
    if (!themeToggle || !themeIcon) return;
    
    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        document.body.className = savedTheme;
    } else if (systemPrefersDark) {
        document.body.className = 'dark-mode';
    } else {
        document.body.className = 'light-mode';
    }
    
    updateThemeIcon(themeIcon, document.body.className);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.body.className;
        const newTheme = currentTheme === 'light-mode' ? 'dark-mode' : 'light-mode';
        
        document.body.className = newTheme;
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(themeIcon, newTheme);
        
        // Add theme change animation
        document.documentElement.style.setProperty('--transition', 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)');
        setTimeout(() => {
            document.documentElement.style.setProperty('--transition', 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)');
        }, 600);
    });
}

function updateThemeIcon(icon, theme) {
    if (theme === 'dark-mode') {
        icon.className = 'fas fa-sun';
        icon.style.color = '#fbbf24';
    } else {
        icon.className = 'fas fa-moon';
        icon.style.color = '#4b5563';
    }
}

// Animation initialization
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.student-card, .stat-card, .card').forEach(el => {
        el.style.animation = 'fadeInUp 0.6s ease forwards paused';
        observer.observe(el);
    });
}

// Enhanced form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                animateFormError(this);
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    clearFieldError(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldId = field.id;
    let isValid = true;
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    // Phone validation
    if (field.id === 'phone' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleanedPhone = value.replace(/[\s\-\(\)]/g, '');
        if (!phoneRegex.test(cleanedPhone) || cleanedPhone.length < 10) {
            showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }
    }
    
    // Name validation
    if ((field.id === 'firstName' || field.id === 'lastName') && value) {
        if (value.length < 2) {
            showFieldError(field, 'Must be at least 2 characters long');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.style.borderColor = 'var(--danger)';
    field.style.animation = 'shake 0.5s ease';
    
    const errorDiv = document.getElementById(field.id + 'Error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    // Create floating error message
    const floatingError = document.createElement('div');
    floatingError.className = 'floating-error';
    floatingError.textContent = message;
    floatingError.style.cssText = `
        position: absolute;
        background: var(--danger);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        z-index: 1000;
        animation: fadeInUp 0.3s ease;
    `;
    
    const rect = field.getBoundingClientRect();
    floatingError.style.top = (rect.top - 40) + 'px';
    floatingError.style.left = rect.left + 'px';
    
    document.body.appendChild(floatingError);
    
    setTimeout(() => {
        floatingError.remove();
    }, 3000);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    field.style.animation = '';
    
    const errorDiv = document.getElementById(field.id + 'Error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function animateFormError(form) {
    form.style.animation = 'shake 0.5s ease';
    setTimeout(() => {
        form.style.animation = '';
    }, 500);
}

// Navigation initialization
function initializeNavigation() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            document.body.classList.toggle('mobile-menu-open');
        });
    }
}

// Progress bar functionality
function updateProgressBar() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.documentElement.style.setProperty('--progress', progress + '%');
}

// Step navigation with animations
let currentStep = 1;
const totalSteps = 4;

function nextStep(step) {
    if (validateStep(step)) {
        animateStepTransition(step, step + 1);
    }
}

function prevStep(step) {
    animateStepTransition(step, step - 1);
}

function animateStepTransition(fromStep, toStep) {
    const currentStepEl = document.getElementById(`step${fromStep}`);
    const nextStepEl = document.getElementById(`step${toStep}`);
    
    // Animate out current step
    currentStepEl.style.animation = 'slideOutLeft 0.4s ease forwards';
    
    setTimeout(() => {
        currentStepEl.classList.remove('active');
        nextStepEl.classList.add('active');
        nextStepEl.style.animation = 'slideInRight 0.4s ease forwards';
        
        currentStep = toStep;
        updateProgressBar();
        
        // Update step indicators
        document.querySelectorAll('.step').forEach((step, index) => {
            if (index < toStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
        
        // Scroll to top of form
        document.querySelector('.form-container').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }, 400);
}

// Additional animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutLeft {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-30px);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .floating-error {
        position: absolute;
        background: var(--danger);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        z-index: 1000;
        animation: fadeInUp 0.3s ease;
    }
`;
document.head.appendChild(style);

// Export functions for global use
window.nextStep = nextStep;
window.prevStep = prevStep;
window.validateStep = validateStep;

// Enhanced table responsiveness
function initializeTableResponsive() {
    const tableContainer = document.querySelector('.table-container');
    const table = document.querySelector('.students-table');
    
    if (!tableContainer || !table) return;
    
    // Add horizontal scroll indicators
    const addScrollIndicators = () => {
        if (tableContainer.querySelector('.scroll-indicator')) {
            tableContainer.querySelector('.scroll-indicator').remove();
        }
        
        const indicator = document.createElement('div');
        indicator.className = 'scroll-indicator';
        indicator.innerHTML = '<i class="fas fa-chevron-right"></i>';
        indicator.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            opacity: 0.7;
            animation: bounceRight 2s infinite;
            z-index: 5;
            pointer-events: none;
        `;
        
        tableContainer.style.position = 'relative';
        tableContainer.appendChild(indicator);
        
        // Remove indicator after first scroll
        const removeIndicator = () => {
            indicator.style.opacity = '0';
            setTimeout(() => indicator.remove(), 300);
            tableContainer.removeEventListener('scroll', removeIndicator);
        };
        
        tableContainer.addEventListener('scroll', removeIndicator);
    };
    
    // Check if table needs horizontal scrolling
    const checkTableOverflow = () => {
        if (table.scrollWidth > tableContainer.clientWidth) {
            addScrollIndicators();
        }
    };
    
    // Initial check
    checkTableOverflow();
    
    // Check on resize
    window.addEventListener('resize', checkTableOverflow);
}

// Add this animation to your CSS
const responsiveStyles = `
    @keyframes bounceRight {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(-50%) translateX(0);
        }
        40% {
            transform: translateY(-50%) translateX(-5px);
        }
        60% {
            transform: translateY(-50%) translateX(-3px);
        }
    }
    
    .scroll-indicator {
        animation: bounceRight 2s infinite;
    }
`;

// Inject the styles
const styleSheet = document.createElement('style');
styleSheet.textContent = responsiveStyles;
document.head.appendChild(styleSheet);

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeTableResponsive();
});