// Student Registration System - Main JavaScript File
// Wolkite Polytechnic College

// Global variables
let currentStep = 1;
const totalSteps = 4;

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Student Registration System...');
    initializeApp();
});

// Main initialization function
async function initializeApp() {
    try {
        // Show loading animation
        showLoading();
        
        // Initialize all components
        await Promise.all([
            initializeTheme(),
            initializeStepNavigation(),
            initializeCountryDropdown(),
            initializeFormValidation(),
            initializeAnimations(),
            initializeMobileNavigation()
        ]);
        
        // Hide loading animation
        setTimeout(hideLoading, 1000);
        
        console.log('Application initialized successfully');
        
    } catch (error) {
        console.error('Error initializing application:', error);
        hideLoading();
    }
}

// Loading states
function showLoading() {
    if (document.querySelector('.page-loading')) return;
    
    const loadingEl = document.createElement('div');
    loadingEl.className = 'page-loading';
    loadingEl.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Loading Wolkite Polytechnic College Portal...</p>
        </div>
    `;
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

// Step Navigation System
function initializeStepNavigation() {
    updateProgressBar();
    
    // Add event listeners to next/prev buttons
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const step = parseInt(this.getAttribute('onclick').match(/nextStep\((\d+)\)/)[1]);
            nextStep(step);
        });
    });
    
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const step = parseInt(this.getAttribute('onclick').match(/prevStep\((\d+)\)/)[1]);
            prevStep(step);
        });
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            nextStep(currentStep);
        } else if (e.key === 'Escape' && currentStep > 1) {
            prevStep(currentStep);
        }
    });
    
    console.log('Step navigation initialized');
}

function nextStep(step) {
    console.log('Moving to next step from:', step);
    
    if (validateStep(step)) {
        // Hide current step with animation
        const currentStepEl = document.getElementById(`step${step}`);
        if (currentStepEl) {
            currentStepEl.style.animation = 'slideOutLeft 0.4s ease forwards';
        }
        
        setTimeout(() => {
            // Remove active class from current step and step indicator
            if (currentStepEl) {
                currentStepEl.classList.remove('active');
            }
            document.querySelectorAll('.step')[step-1]?.classList.remove('active');
            
            // Move to next step
            currentStep = step + 1;
            
            // Show next step with animation
            const nextStepEl = document.getElementById(`step${currentStep}`);
            const nextStepIndicator = document.querySelectorAll('.step')[currentStep-1];
            
            if (nextStepEl && nextStepIndicator) {
                nextStepEl.classList.add('active');
                nextStepIndicator.classList.add('active');
                nextStepEl.style.animation = 'slideInRight 0.4s ease forwards';
            }
            
            updateProgressBar();
            
            // If moving to review step, populate review content
            if (currentStep === 4) {
                setTimeout(populateReview, 100);
            }
            
            // Scroll to top of form
            scrollToFormTop();
            
        }, 400);
    } else {
        // Show validation error animation
        showValidationError(step);
    }
}

function prevStep(step) {
    console.log('Moving to previous step from:', step);
    
    // Hide current step with animation
    const currentStepEl = document.getElementById(`step${step}`);
    if (currentStepEl) {
        currentStepEl.style.animation = 'slideOutRight 0.4s ease forwards';
    }
    
    setTimeout(() => {
        // Remove active class from current step and step indicator
        if (currentStepEl) {
            currentStepEl.classList.remove('active');
        }
        document.querySelectorAll('.step')[step-1]?.classList.remove('active');
        
        // Move to previous step
        currentStep = step - 1;
        
        // Show previous step with animation
        const prevStepEl = document.getElementById(`step${currentStep}`);
        const prevStepIndicator = document.querySelectorAll('.step')[currentStep-1];
        
        if (prevStepEl && prevStepIndicator) {
            prevStepEl.classList.add('active');
            prevStepIndicator.classList.add('active');
            prevStepEl.style.animation = 'slideInLeft 0.4s ease forwards';
        }
        
        updateProgressBar();
        scrollToFormTop();
        
    }, 400);
}

function updateProgressBar() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.documentElement.style.setProperty('--progress', `${progress}%`);
}

function scrollToFormTop() {
    const formContainer = document.querySelector('.form-container');
    if (formContainer) {
        formContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

function showValidationError(step) {
    const currentStepEl = document.getElementById(`step${step}`);
    if (currentStepEl) {
        currentStepEl.style.animation = 'shake 0.5s ease';
        setTimeout(() => {
            currentStepEl.style.animation = '';
        }, 500);
    }
    
    // Scroll to first error
    const firstError = document.querySelector('.error[style*="display: block"]');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Step Validation System
function validateStep(step) {
    console.log('Validating step:', step);
    let isValid = true;
    
    switch(step) {
        case 1:
            isValid = validateStep1();
            break;
        case 2:
            isValid = validateStep2();
            break;
        case 3:
            isValid = validateStep3();
            break;
        default:
            isValid = true;
    }
    
    console.log('Step validation result:', isValid);
    return isValid;
}

function validateStep1() {
    let isValid = true;
    const fields = ['firstName', 'lastName', 'email', 'phone', 'dob', 'gender', 'address'];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateStep2() {
    let isValid = true;
    const fields = [
        'department', 'program', 'semester', 'academicYear', 
        'previousSchool', 'qualification', 'percentage'
    ];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateStep3() {
    let isValid = true;
    
    // Validate required files
    const photo = document.getElementById('photo');
    const idProof = document.getElementById('idProof');
    
    if (photo && (!photo.files || photo.files.length === 0)) {
        showFieldError(photo, 'Passport photo is required');
        isValid = false;
    } else {
        clearFieldError(photo);
    }
    
    if (idProof && (!idProof.files || idProof.files.length === 0)) {
        showFieldError(idProof, 'ID proof is required');
        isValid = false;
    } else {
        clearFieldError(idProof);
    }
    
    return isValid;
}

// Field Validation System
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Form submission validation
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
            
            // Clear errors on input
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    clearFieldError(this);
                }
            });
        });
    });
    
    console.log('Form validation initialized');
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Additional validation for step 4
    if (form.id === 'registrationForm' && currentStep === 4) {
        const agreeTerms = document.getElementById('agreeTerms');
        const declareInfo = document.getElementById('declareInfo');
        
        if (!agreeTerms?.checked) {
            showFieldError(agreeTerms, 'You must agree to the terms and conditions');
            isValid = false;
        }
        
        if (!declareInfo?.checked) {
            showFieldError(declareInfo, 'You must declare the information is correct');
            isValid = false;
        }
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        isValid = false;
    }
    
    // Skip further validation if empty and not required
    if (!value && !field.hasAttribute('required')) {
        return true;
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
            showFieldError(field, 'Please enter a valid phone number (at least 10 digits)');
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
    
    // Date of birth validation
    if (field.id === 'dob' && value) {
        const birthDate = new Date(value);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        
        if (age < 16) {
            showFieldError(field, 'You must be at least 16 years old');
            isValid = false;
        }
        
        if (age > 100) {
            showFieldError(field, 'Please enter a valid date of birth');
            isValid = false;
        }
    }
    
    // Address validation
    if (field.id === 'address' && value) {
        if (value.length < 10) {
            showFieldError(field, 'Address must be at least 10 characters long');
            isValid = false;
        }
    }
    
    // Percentage validation
    if (field.id === 'percentage' && value) {
        const percentage = parseFloat(value);
        if (isNaN(percentage) || percentage < 0 || percentage > 100) {
            showFieldError(field, 'Please enter a valid percentage between 0 and 100');
            isValid = false;
        }
    }
    
    // Nationality validation
    if (field.id === 'nationality' && value) {
        if (!isValidCountryCode(value)) {
            showFieldError(field, 'Please select a valid nationality');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.style.borderColor = 'var(--danger)';
    field.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.1)';
    
    const errorElement = document.getElementById(field.id + 'Error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function clearFieldError(field) {
    field.style.borderColor = '';
    field.style.boxShadow = '';
    
    const errorElement = document.getElementById(field.id + 'Error');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

function animateFormError(form) {
    form.style.animation = 'shake 0.5s ease';
    setTimeout(() => {
        form.style.animation = '';
    }, 500);
}

// Country Dropdown with API
async function initializeCountryDropdown() {
    const countrySelect = document.getElementById('nationality');
    if (!countrySelect) return;

    try {
        // Show loading state
        countrySelect.innerHTML = '<option value="">Loading countries...</option>';
        countrySelect.disabled = true;

        // Fetch countries from REST Countries API
        const response = await fetch('https://restcountries.com/v3.1/all?fields=name,demonyms,flags,idd');
        
        if (!response.ok) {
            throw new Error('Failed to fetch countries');
        }
        
        const countries = await response.json();

        // Sort countries alphabetically
        countries.sort((a, b) => a.name.common.localeCompare(b.name.common));

        // Clear loading message and add options
        countrySelect.innerHTML = '<option value="">Select your nationality</option>';

        countries.forEach(country => {
            const option = document.createElement('option');
            const countryCode = country.cca2.toLowerCase();
            const demonym = country.demonyms?.eng?.m || country.name.common;
            const flagEmoji = getFlagEmoji(countryCode);
            
            option.value = countryCode;
            option.textContent = `${flagEmoji} ${demonym}`;
            option.setAttribute('data-flag', flagEmoji);
            option.setAttribute('data-country', country.name.common);
            
            countrySelect.appendChild(option);
        });

        countrySelect.disabled = false;
        initializeCountrySearch(countrySelect);

    } catch (error) {
        console.error('Error loading countries:', error);
        loadFallbackCountries(countrySelect);
    }
}

function getFlagEmoji(countryCode) {
    const codePoints = countryCode
        .toUpperCase()
        .split('')
        .map(char => 127397 + char.charCodeAt());
    return String.fromCodePoint(...codePoints);
}

function initializeCountrySearch(select) {
    const wrapper = select.parentElement;
    
    // Create search container
    const searchContainer = document.createElement('div');
    searchContainer.className = 'country-search-container';
    searchContainer.innerHTML = `
        <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="country-search-input" placeholder="Search countries...">
            <button type="button" class="search-clear-btn" style="display: none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    const searchInput = searchContainer.querySelector('.country-search-input');
    const clearBtn = searchContainer.querySelector('.search-clear-btn');

    wrapper.insertBefore(searchContainer, select);

    // Toggle search on select focus
    select.addEventListener('focus', () => {
        searchContainer.classList.add('active');
        searchInput.focus();
    });

    // Filter countries based on search
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        const options = Array.from(select.options);
        let hasVisibleOptions = false;

        clearBtn.style.display = searchTerm ? 'flex' : 'none';

        options.forEach(option => {
            if (option.value === '') {
                option.style.display = searchTerm ? 'none' : '';
                return;
            }

            const text = option.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            option.style.display = isVisible ? '' : 'none';
            
            if (isVisible) hasVisibleOptions = true;
        });

        showNoResultsMessage(select, searchTerm, hasVisibleOptions);
    });

    // Clear search
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        Array.from(select.options).forEach(option => {
            option.style.display = '';
        });
        hideNoResultsMessage(select);
        searchInput.focus();
    });

    // Hide search when clicking outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            searchContainer.classList.remove('active');
            searchInput.value = '';
            clearBtn.style.display = 'none';
            Array.from(select.options).forEach(option => {
                option.style.display = '';
            });
            hideNoResultsMessage(select);
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchContainer.classList.remove('active');
            select.focus();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            select.focus();
        }
    });
}

function showNoResultsMessage(select, searchTerm, hasVisibleOptions) {
    if (!searchTerm || hasVisibleOptions) {
        hideNoResultsMessage(select);
        return;
    }

    let noResultsOption = select.querySelector('option[data-no-results]');
    if (!noResultsOption) {
        noResultsOption = document.createElement('option');
        noResultsOption.value = '';
        noResultsOption.textContent = `No countries found for "${searchTerm}"`;
        noResultsOption.setAttribute('data-no-results', 'true');
        noResultsOption.disabled = true;
        select.appendChild(noResultsOption);
    }
    noResultsOption.style.display = '';
}

function hideNoResultsMessage(select) {
    const noResultsOption = select.querySelector('option[data-no-results]');
    if (noResultsOption) {
        noResultsOption.remove();
    }
}

function loadFallbackCountries(select) {
    const fallbackCountries = [
        { code: 'us', name: 'American', flag: '🇺🇸' }, { code: 'gb', name: 'British', flag: '🇬🇧' },
        { code: 'ca', name: 'Canadian', flag: '🇨🇦' }, { code: 'au', name: 'Australian', flag: '🇦🇺' },
        { code: 'in', name: 'Indian', flag: '🇮🇳' }, { code: 'cn', name: 'Chinese', flag: '🇨🇳' },
        { code: 'jp', name: 'Japanese', flag: '🇯🇵' }, { code: 'de', name: 'German', flag: '🇩🇪' },
        { code: 'fr', name: 'French', flag: '🇫🇷' }, { code: 'it', name: 'Italian', flag: '🇮🇹' },
        { code: 'br', name: 'Brazilian', flag: '🇧🇷' }, { code: 'mx', name: 'Mexican', flag: '🇲🇽' },
        { code: 'es', name: 'Spanish', flag: '🇪🇸' }, { code: 'kr', name: 'South Korean', flag: '🇰🇷' },
        { code: 'ng', name: 'Nigerian', flag: '🇳🇬' }, { code: 'za', name: 'South African', flag: '🇿🇦' }
    ];

    select.innerHTML = '<option value="">Select your nationality</option>';
    fallbackCountries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.code;
        option.textContent = `${country.flag} ${country.name}`;
        select.appendChild(option);
    });
    select.disabled = false;
}

function isValidCountryCode(code) {
    return /^[a-z]{2}$/.test(code);
}

// Review Section
function populateReview() {
    const reviewContent = document.getElementById('reviewContent');
    if (!reviewContent) return;
    
    try {
        const formData = {
            'Personal Information': {
                'First Name': document.getElementById('firstName')?.value,
                'Last Name': document.getElementById('lastName')?.value,
                'Email': document.getElementById('email')?.value,
                'Phone': document.getElementById('phone')?.value,
                'Date of Birth': document.getElementById('dob')?.value,
                'Gender': document.getElementById('gender')?.options[document.getElementById('gender')?.selectedIndex]?.text,
                'Nationality': document.getElementById('nationality')?.options[document.getElementById('nationality')?.selectedIndex]?.text,
                'Address': document.getElementById('address')?.value
            },
            'Academic Details': {
                'Department': document.getElementById('department')?.options[document.getElementById('department')?.selectedIndex]?.text,
                'Program': document.getElementById('program')?.options[document.getElementById('program')?.selectedIndex]?.text,
                'Semester': document.getElementById('semester')?.options[document.getElementById('semester')?.selectedIndex]?.text,
                'Academic Year': document.getElementById('academicYear')?.options[document.getElementById('academicYear')?.selectedIndex]?.text,
                'Previous School': document.getElementById('previousSchool')?.value,
                'Qualification': document.getElementById('qualification')?.options[document.getElementById('qualification')?.selectedIndex]?.text,
                'Percentage': document.getElementById('percentage')?.value + '%',
                'Board/University': document.getElementById('board')?.value || 'Not specified',
                'Achievements': document.getElementById('achievements')?.value || 'Not specified'
            },
            'Documents': {
                'Passport Photo': document.getElementById('photo')?.files[0]?.name || 'Not uploaded',
                'ID Proof': document.getElementById('idProof')?.files[0]?.name || 'Not uploaded',
                'Marksheet': document.getElementById('marksheet')?.files[0]?.name || 'Not uploaded',
                'Transfer Certificate': document.getElementById('transferCertificate')?.files[0]?.name || 'Not uploaded'
            }
        };

        let reviewHTML = '';
        
        for (const [section, fields] of Object.entries(formData)) {
            reviewHTML += `
                <div class="review-section">
                    <h3>${section}</h3>
            `;
            
            for (const [label, value] of Object.entries(fields)) {
                reviewHTML += `
                    <div class="review-item">
                        <span class="review-label">${label}:</span>
                        <span class="review-value">${value || 'Not provided'}</span>
                    </div>
                `;
            }
            
            reviewHTML += `</div>`;
        }
        
        reviewContent.innerHTML = reviewHTML;
        
    } catch (error) {
        console.error('Error populating review:', error);
        reviewContent.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error loading review information. Please check your form inputs.</p>
            </div>
        `;
    }
}

// Animations System
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

// Mobile Navigation
function initializeMobileNavigation() {
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            icon.className = navLinks.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        });
    }
    
    // Set active navigation link
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    const navLinksAll = document.querySelectorAll('.nav-link');
    navLinksAll.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });
}

// Utility Functions
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

// Export functions for global use
window.nextStep = nextStep;
window.prevStep = prevStep;
window.validateStep = validateStep;
window.validateField = validateField;

// Add CSS animations dynamically
const dynamicStyles = `
    @keyframes slideOutLeft {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-30px); }
    }
    
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(30px); }
    }
    
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .loading-content {
        text-align: center;
        color: var(--text-primary);
    }
    
    .loading-content p {
        margin-top: 15px;
        font-size: 1.1rem;
    }
    
    .error-message {
        text-align: center;
        padding: 40px;
        color: var(--danger);
    }
    
    .error-message i {
        font-size: 3rem;
        margin-bottom: 15px;
    }
`;

// Inject dynamic styles
const styleSheet = document.createElement('style');
styleSheet.textContent = dynamicStyles;
document.head.appendChild(styleSheet);

console.log('Student Registration System JavaScript loaded successfully');