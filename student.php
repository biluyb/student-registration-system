<?php
require_once 'config.php';

$conn = getDBConnection();

// Get filter parameters
$departmentFilter = $_GET['department'] ?? '';
$programFilter = $_GET['program'] ?? '';

// Build query with filters
$query = "SELECT student_id, first_name, last_name, department, program, academic_year, registration_date 
          FROM students 
          WHERE status = 'approved'";
$params = [];
$types = '';

if ($departmentFilter) {
    $query .= " AND department = ?";
    $params[] = $departmentFilter;
    $types .= 's';
}

if ($programFilter) {
    $query .= " AND program = ?";
    $params[] = $programFilter;
    $types .= 's';
}

$query .= " ORDER BY registration_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get unique departments and programs for filters
$departments = $conn->query("SELECT DISTINCT department FROM students WHERE status = 'approved' ORDER BY department");
$programs = $conn->query("SELECT DISTINCT program FROM students WHERE status = 'approved' ORDER BY program");

// Get total counts for display
$totalStudents = $students->num_rows;
$filteredCount = $totalStudents;

// Check if any filters are active
$filtersActive = !empty($departmentFilter) || !empty($programFilter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Students - Woolkite Polytechnic College</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .students-listing {
            padding: 20px 0;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .filters-container {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
        }
        
        .filter-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-results {
            background: var(--primary-light);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .filter-tag {
            background: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-tag .remove {
            cursor: pointer;
            padding: 2px;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
        }
        
        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .student-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .student-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
        }
        
        .student-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .student-department {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .student-program {
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-size: 0.85rem;
        }
        
        .student-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .no-students {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
        }
        
        .no-students i {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 15px;
        }
        
        .navigation {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .view-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        
        .view-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .view-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Mobile Styles */
        @media (max-width: 768px) {
            .students-listing {
                padding: 15px 0;
            }
            
            .filter-toggle {
                display: block;
                width: 100%;
            }
            
            .filter-row {
                display: none;
                grid-template-columns: 1fr;
                gap: 12px;
                margin-top: 15px;
            }
            
            .filter-row.active {
                display: grid;
            }
            
            .filter-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .students-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .student-card {
                padding: 15px;
            }
            
            .student-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .student-name {
                font-size: 1.1rem;
            }
            
            .navigation {
                flex-direction: column;
            }
            
            .navigation .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .filters-container {
                padding: 15px;
            }
            
            .student-meta {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .page-header h2 {
                font-size: 1.5rem;
            }
        }
        
        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="light-mode">
    <div class="theme-toggle">
        <button id="themeToggle" class="theme-btn">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="container">
        <div class="header">
            <div class="college-brand">
                <div class="college-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="college-info">
                    <h1>Our Students</h1>
                    <p>Woolkite Polytechnic College - Meet Our Community</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="students-listing">
                <!-- Navigation -->
                <div class="navigation">
                    <a href="dashboard.php" class="btn btn-small">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-small">
                        <i class="fas fa-user-plus"></i> Join Us
                    </a>
                </div>
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2 style="color: var(--primary); margin-bottom: 10px;">Meet Our Student Community</h2>
                    <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
                        Discover the talented students who are shaping their future at Woolkite Polytechnic College. 
                        Filter by department or program to find students with similar interests.
                    </p>
                </div>
                
                <!-- Filters -->
                <div class="filters-container">
                    <div class="filter-header">
                        <button class="filter-toggle" id="filterToggle">
                            <i class="fas fa-filter"></i> Show Filters
                        </button>
                        
                        <?php if ($filtersActive): ?>
                        <div class="filter-results">
                            <i class="fas fa-info-circle"></i> 
                            Showing <?php echo $filteredCount; ?> of <?php echo $totalStudents; ?> students
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($filtersActive): ?>
                    <div class="active-filters">
                        <?php if ($departmentFilter): ?>
                        <div class="filter-tag">
                            Department: <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $departmentFilter))); ?>
                            <span class="remove" onclick="removeFilter('department')">&times;</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($programFilter): ?>
                        <div class="filter-tag">
                            Program: <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $programFilter))); ?>
                            <span class="remove" onclick="removeFilter('program')">&times;</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="GET" action="" id="filterForm">
                        <div class="filter-row" id="filterRow">
                            <div class="form-group">
                                <label for="department">
                                    <i class="fas fa-building"></i> Department
                                </label>
                                <select id="department" name="department" onchange="this.form.submit()">
                                    <option value="">All Departments</option>
                                    <?php 
                                    if ($departments) {
                                        while ($dept = $departments->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $dept['department']; ?>" 
                                            <?php echo $departmentFilter === $dept['department'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $dept['department']))); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="program">
                                    <i class="fas fa-graduation-cap"></i> Program
                                </label>
                                <select id="program" name="program" onchange="this.form.submit()">
                                    <option value="">All Programs</option>
                                    <?php 
                                    if ($programs) {
                                        while ($program = $programs->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $program['program']; ?>" 
                                            <?php echo $programFilter === $program['program'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $program['program']))); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-small" id="applyFilters">
                                    <i class="fas fa-filter"></i> Apply
                                </button>
                                <a href="students.php" class="btn btn-secondary btn-small">
                                    <i class="fas fa-redo"></i> Clear All
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Students Grid -->
                <div class="students-grid" id="studentsGrid">
                    <?php if ($students && $students->num_rows > 0): ?>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <div class="student-card">
                                <div class="student-avatar">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="student-name">
                                    <a href="student.php?id=<?php echo $student['id'] ?? ''; ?>" 
                                       style="color: inherit; text-decoration: none;"
                                       onmouseover="this.style.color='var(--primary-dark)'"
                                       onmouseout="this.style.color='inherit'">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </a>
                                </div>
                                <div class="student-department">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['department']))); ?>
                                </div>
                                <div class="student-program">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['program']))); ?> Program
                                </div>
                                <div class="student-meta">
                                    <span>ID: <?php echo htmlspecialchars($student['student_id']); ?></span>
                                    <span>Joined: <?php echo date('M Y', strtotime($student['registration_date'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-students">
                            <i class="fas fa-users"></i>
                            <h3 style="color: var(--text-secondary); margin-bottom: 10px;">No Students Found</h3>
                            <p style="color: var(--text-muted); margin-bottom: 20px;">
                                <?php 
                                if ($filtersActive) {
                                    echo 'No students match your current filters. Try adjusting your criteria.';
                                } else {
                                    echo 'No approved students to display yet. Check back later!';
                                }
                                ?>
                            </p>
                            <?php if ($filtersActive): ?>
                                <a href="students.php" class="btn btn-small">
                                    <i class="fas fa-redo"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Call to Action -->
                <div style="text-align: center; padding: 30px 20px; background: var(--bg-secondary); border-radius: var(--border-radius);">
                    <h3 style="color: var(--primary); margin-bottom: 15px;">Ready to Join Our Community?</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Start your journey at Woolkite Polytechnic College and become part of our growing family of technologists and innovators.
                    </p>
                    <a href="index.php" class="btn" style="width: auto; padding: 12px 25px; display: inline-block;">
                        <i class="fas fa-paper-plane"></i> Begin Your Application
                    </a>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <p>&copy; 2024 Woolkite Polytechnic College. All rights reserved.</p>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Mobile filter toggle
        document.addEventListener('DOMContentLoaded', function() {
            const filterToggle = document.getElementById('filterToggle');
            const filterRow = document.getElementById('filterRow');
            const filterForm = document.getElementById('filterForm');
            const applyFilters = document.getElementById('applyFilters');
            
            if (filterToggle && filterRow) {
                filterToggle.addEventListener('click', function() {
                    filterRow.classList.toggle('active');
                    filterToggle.innerHTML = filterRow.classList.contains('active') ? 
                        '<i class="fas fa-times"></i> Hide Filters' : 
                        '<i class="fas fa-filter"></i> Show Filters';
                });
            }
            
            // Auto-submit form when filters change (desktop)
            const departmentSelect = document.getElementById('department');
            const programSelect = document.getElementById('program');
            
            if (departmentSelect) {
                departmentSelect.addEventListener('change', function() {
                    if (window.innerWidth > 768) {
                        showLoading();
                        filterForm.submit();
                    }
                });
            }
            
            if (programSelect) {
                programSelect.addEventListener('change', function() {
                    if (window.innerWidth > 768) {
                        showLoading();
                        filterForm.submit();
                    }
                });
            }
            
            // Manual submit for mobile
            if (applyFilters) {
                applyFilters.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        showLoading();
                        filterForm.submit();
                    }
                });
            }
            
            // Check if filters are active on page load
            checkActiveFilters();
        });
        
        function showLoading() {
            const grid = document.getElementById('studentsGrid');
            const applyBtn = document.getElementById('applyFilters');
            
            if (grid) {
                grid.classList.add('loading', 'pulse');
            }
            
            if (applyBtn) {
                applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
                applyBtn.disabled = true;
            }
        }
        
        function removeFilter(filterType) {
            const url = new URL(window.location.href);
            url.searchParams.delete(filterType);
            window.location.href = url.toString();
        }
        
        function checkActiveFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            const hasFilters = urlParams.has('department') || urlParams.has('program');
            
            if (hasFilters && window.innerWidth <= 768) {
                // Auto-expand filters on mobile when filters are active
                const filterRow = document.getElementById('filterRow');
                const filterToggle = document.getElementById('filterToggle');
                if (filterRow && filterToggle) {
                    filterRow.classList.add('active');
                    filterToggle.innerHTML = '<i class="fas fa-times"></i> Hide Filters';
                }
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const filterRow = document.getElementById('filterRow');
                if (filterRow) {
                    filterRow.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
<?php 
// Close database connections
if (isset($students) && $students) {
    $students->close();
}
if (isset($departments) && $departments) {
    $departments->close();
}
if (isset($programs) && $programs) {
    $programs->close();
}
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>