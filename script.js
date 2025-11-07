// Current step tracker
let currentStep = 1;
const totalSteps = 4;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    updateProgressBar();
    initializeTheme();
    initializeRealTimeValidation();
});

// Theme functionality
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    
    // Check for saved theme preference or default to light
    const savedTheme = localStorage.getItem('theme') || 'light-mode';
    document.body.className = savedTheme;
    updateThemeIcon(themeIcon, savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.body.className;
        const newTheme = currentTheme === 'light-mode' ? 'dark-mode' : 'light-mode';
        
        document.body.className = newTheme;
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(themeIcon, newTheme);
    });
}

function updateThemeIcon(icon, theme) {
    if (theme === 'dark-mode') {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

// Progress bar functionality
function updateProgressBar() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.documentElement.style.setProperty('--progress', progress);
}

// Step navigation
function nextStep(step) {
    if (validateStep(step)) {
        document.getElementById(`step${step}`).classList.remove('active');
        document.querySelectorAll('.step')[step-1].classList.remove('active');
        
        currentStep = step + 1;
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.querySelectorAll('.step')[currentStep-1].classList.add('active');
        
        updateProgressBar();
        
        if (currentStep === 4) {
            populateReview();
        }
        
        // Scroll to top of form
        document.querySelector('.form-container').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

function prevStep(step) {
    document.getElementById(`step${step}`).classList.remove('active');
    document.querySelectorAll('.step')[step-1].classList.remove('active');
    
    currentStep = step - 1;
    document.getElementById(`step${currentStep}`).classList.add('active');
    document.querySelectorAll('.step')[currentStep-1].classList.add('active');
    
    updateProgressBar();
    
    // Scroll to top of form
    document.querySelector('.form-container').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Validation functions
function validateStep(step) {
    let isValid = true;
    
    switch(step) {
        case 1:
            isValid = validatePersonalInfo();
            break;
        case 2:
            isValid = validateAcademicInfo();
            break;
        case 3:
            isValid = validateDocuments();
            break;
    }
    
    return isValid;
}

function validatePersonalInfo() {
    let isValid = true;
    
    const fields = [
        { id: 'firstName', validator: validateName },
        { id: 'lastName', validator: validateName },
        { id: 'email', validator: validateEmail },
        { id: 'phone', validator: validatePhone },
        { id: 'dob', validator: validateDOB },
        { id: 'gender', validator: validateRequired },
        { id: 'address', validator: validateAddress }
    ];
    
    fields.forEach(field => {
        if (!field.validator(field.id)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateAcademicInfo() {
    let isValid = true;
    
    const fields = [
        { id: 'department', validator: validateRequired },
        { id: 'program', validator: validateRequired },
        { id: 'semester', validator: validateRequired },
        { id: 'academicYear', validator: validateRequired },
        { id: 'previousSchool', validator: validateRequired },
        { id: 'qualification', validator: validateRequired },
        { id: 'percentage', validator: validatePercentage }
    ];
    
    fields.forEach(field => {
        if (!field.validator(field.id)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateDocuments() {
    let isValid = true;
    
    // Only validate required documents
    if (!validateFile('photo', ['image/jpeg', 'image/png', 'image/jpg'])) {
        isValid = false;
    }
    
    if (!validateFile('idProof', ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])) {
        isValid = false;
    }
    
    return isValid;
}

// Individual validation functions
function validateName(fieldId) {
    const value = document.getElementById(fieldId).value.trim();
    if (value.length < 2) {
        showError(`${fieldId}Error`, 'Must be at least 2 characters long');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validateEmail(fieldId) {
    const value = document.getElementById(fieldId).value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(value)) {
        showError(`${fieldId}Error`, 'Please enter a valid email address');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validatePhone(fieldId) {
    const value = document.getElementById(fieldId).value.trim();
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    const cleanedPhone = value.replace(/[\s\-\(\)]/g, '');
    
    if (!phoneRegex.test(cleanedPhone) || cleanedPhone.length < 10) {
        showError(`${fieldId}Error`, 'Please enter a valid phone number');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validateDOB(fieldId) {
    const value = document.getElementById(fieldId).value;
    if (!value) {
        showError(`${fieldId}Error`, 'Date of birth is required');
        return false;
    }
    
    const birthDate = new Date(value);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    
    if (age < 16) {
        showError(`${fieldId}Error`, 'You must be at least 16 years old');
        return false;
    }
    
    hideError(`${fieldId}Error`);
    return true;
}

function validateRequired(fieldId) {
    const value = document.getElementById(fieldId).value;
    if (!value) {
        showError(`${fieldId}Error`, 'This field is required');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validateAddress(fieldId) {
    const value = document.getElementById(fieldId).value.trim();
    if (value.length < 10) {
        showError(`${fieldId}Error`, 'Address must be at least 10 characters long');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validatePercentage(fieldId) {
    const value = parseFloat(document.getElementById(fieldId).value);
    if (isNaN(value) || value < 0 || value > 100) {
        showError(`${fieldId}Error`, 'Please enter a valid percentage between 0 and 100');
        return false;
    }
    hideError(`${fieldId}Error`);
    return true;
}

function validateFile(fieldId, allowedTypes) {
    const fileInput = document.getElementById(fieldId);
    if (!fileInput.files || fileInput.files.length === 0) {
        showError(`${fieldId}Error`, 'This file is required');
        return false;
    }
    
    const file = fileInput.files[0];
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
        showError(`${fieldId}Error`, 'File size must be less than 2MB');
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showError(`${fieldId}Error`, `File type not allowed. Allowed: ${allowedTypes.join(', ')}`);
        return false;
    }
    
    hideError(`${fieldId}Error`);
    return true;
}

// Error handling
function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    const inputId = elementId.replace('Error', '');
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
        inputElement.style.borderColor = 'var(--danger)';
    }
}

function hideError(elementId) {
    const errorElement = document.getElementById(elementId);
    errorElement.style.display = 'none';
    
    const inputId = elementId.replace('Error', '');
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
        inputElement.style.borderColor = '';
    }
}

// Real-time validation
function initializeRealTimeValidation() {
    document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('blur', function() {
            const fieldId = this.id;
            if (this.hasAttribute('required') || this.value.trim() !== '') {
                validateField(fieldId);
            }
        });
        
        // Real-time validation for typing in required fields
        if (element.hasAttribute('required')) {
            element.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    validateField(this.id);
                }
            });
        }
    });
}

function validateField(fieldId) {
    switch(fieldId) {
        case 'firstName':
        case 'lastName':
            validateName(fieldId);
            break;
        case 'email':
            validateEmail(fieldId);
            break;
        case 'phone':
            validatePhone(fieldId);
            break;
        case 'dob':
            validateDOB(fieldId);
            break;
        case 'address':
            validateAddress(fieldId);
            break;
        case 'percentage':
            validatePercentage(fieldId);
            break;
        default:
            if (document.getElementById(fieldId).hasAttribute('required')) {
                validateRequired(fieldId);
            }
            break;
    }
}

// Review section population
function populateReview() {
    const reviewContent = document.getElementById('reviewContent');
    
    const formData = {
        'Personal Information': {
            'First Name': document.getElementById('firstName').value,
            'Last Name': document.getElementById('lastName').value,
            'Email': document.getElementById('email').value,
            'Phone': document.getElementById('phone').value,
            'Date of Birth': document.getElementById('dob').value,
            'Gender': document.getElementById('gender').options[document.getElementById('gender').selectedIndex].text,
            'Address': document.getElementById('address').value,
            'Nationality': document.getElementById('nationality').value || 'Not provided',
            'ID Number': document.getElementById('idNumber').value || 'Not provided'
        },
        'Academic Details': {
            'Department': document.getElementById('department').options[document.getElementById('department').selectedIndex].text,
            'Program': document.getElementById('program').options[document.getElementById('program').selectedIndex].text,
            'Semester': document.getElementById('semester').options[document.getElementById('semester').selectedIndex].text,
            'Academic Year': document.getElementById('academicYear').options[document.getElementById('academicYear').selectedIndex].text,
            'Previous School': document.getElementById('previousSchool').value,
            'Qualification': document.getElementById('qualification').options[document.getElementById('qualification').selectedIndex].text,
            'Percentage/GPA': document.getElementById('percentage').value + '%',
            'Board/University': document.getElementById('board').value || 'Not provided',
            'Achievements': document.getElementById('achievements').value || 'Not provided'
        },
        'Documents': {
            'Passport Photo': document.getElementById('photo').files[0]?.name || 'Not uploaded',
            'ID Proof': document.getElementById('idProof').files[0]?.name || 'Not uploaded',
            'Marksheet': document.getElementById('marksheet').files[0]?.name || 'Not uploaded',
            'Transfer Certificate': document.getElementById('transferCertificate').files[0]?.name || 'Not uploaded',
            'Additional Documents': document.getElementById('additionalDocs').files.length > 0 ? 
                `${document.getElementById('additionalDocs').files.length} file(s) uploaded` : 'No additional files'
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
                    <span class="review-value">${value}</span>
                </div>
            `;
        }
        
        reviewHTML += `</div>`;
    }
    
    reviewContent.innerHTML = reviewHTML;
}

// Form submission
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate final step
    const agreeTerms = document.getElementById('agreeTerms').checked;
    const declareInfo = document.getElementById('declareInfo').checked;
    
    if (!agreeTerms) {
        showError('agreeTermsError', 'You must agree to the terms and conditions');
        return;
    } else {
        hideError('agreeTermsError');
    }
    
    if (!declareInfo) {
        showError('declareInfoError', 'You must declare the information is correct');
        return;
    } else {
        hideError('declareInfoError');
    }
    
    // Validate all steps
    if (validateStep(1) && validateStep(2) && validateStep(3) && agreeTerms && declareInfo) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;
        
        // Submit the form
        this.submit();
    } else {
        // If validation fails, go back to first invalid step
        if (!validateStep(1)) currentStep = 1;
        else if (!validateStep(2)) currentStep = 2;
        else if (!validateStep(3)) currentStep = 3;
        
        // Show the first invalid step
        document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
        document.querySelectorAll('.step').forEach(step => step.classList.remove('active'));
        
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.querySelectorAll('.step')[currentStep-1].classList.add('active');
        updateProgressBar();
        
        // Scroll to top
        document.querySelector('.form-container').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
});