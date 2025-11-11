<?php
// navigation.php - Reusable navigation component
?>
<!-- Main Navigation -->
<nav class="main-navigation">
    <div class="nav-container">
        <a href="dashboard.php" class="nav-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Wolkite Polytechnic</span>
        </a>
        
        <button class="nav-toggle" id="navToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="nav-links" id="navLinks">
            <a href="index.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="students.php" class="nav-link">
                <i class="fas fa-users"></i> Our Students
            </a>
            <!-- <a href="index.php" class="nav-link">
                <i class="fas fa-user-plus"></i> Apply Now
            </a> -->
            <a href="admin/login.php" class="nav-link">
                <i class="fas fa-lock"></i> Admin
            </a>
        </div>
    </div>
</nav>

<script>
// Mobile navigation toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.nav-container') && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                navToggle.querySelector('i').className = 'fas fa-bars';
            }
        });
    }
    
    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const navLinksAll = document.querySelectorAll('.nav-link');
    navLinksAll.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'dashboard.php')) {
            link.classList.add('active');
        }
    });
});
</script>