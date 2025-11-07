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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Students - Wolkite Polytechnic College</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .students-listing {
            padding: 30px 0;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .filters {
            background: var(--bg-secondary);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
        }
        
        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .student-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .student-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2.5rem;
        }
        
        .student-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .student-department {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .student-program {
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .student-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .no-students {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-students i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        .navigation {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body class="light-mode">
        <?php include 'navigation.php'; ?>

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
                    <p>Wolkite Polytechnic College - Meet Our Community</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="students-listing">
                <!-- Navigation -->
               <!-- Enhanced Navigation -->
<div class="page-navigation">
    <div class="nav-breadcrumb">
        <a href="index.php" class="breadcrumb-link">
            <i class="fas fa-home"></i> Home
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Our Students</span>
    </div>
    
    <div class="nav-actions">
        <a href="index.php" class="btn btn-outline btn-small">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        <a href="index.php" class="btn btn-secondary btn-small">
            <i class="fas fa-user-plus"></i> Join Us
        </a>
    </div>
</div>
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2 style="color: var(--primary); margin-bottom: 10px;">Meet Our Student Community</h2>
                    <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
                        Discover the talented students who are shaping their future at Wolkite Polytechnic College. 
                        Filter by department or program to find students with similar interests.
                    </p>
                </div>
                
                <!-- Filters -->
                <div class="filters">
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['department']; ?>" <?php echo $departmentFilter === $dept['department'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(str_replace('-', ' ', $dept['department'])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="program">Program</label>
                                <select id="program" name="program">
                                    <option value="">All Programs</option>
                                    <?php while ($program = $programs->fetch_assoc()): ?>
                                        <option value="<?php echo $program['program']; ?>" <?php echo $programFilter === $program['program'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(str_replace('-', ' ', $program['program'])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-small">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="students.php" class="btn btn-secondary btn-small">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Students Grid -->
                <div class="students-grid">
                    <?php if ($students->num_rows > 0): ?>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <div class="student-card">
                                <div class="student-avatar">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            <div class="student-name">
                                <a href="student.php?id=<?php echo $student['id']; ?>" style="color: inherit; text-decoration: none;">
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
                                <?php echo ($departmentFilter || $programFilter) ? 
                                    'No students match your current filters. Try adjusting your criteria.' : 
                                    'No approved students to display yet.'; ?>
                            </p>
                            <?php if ($departmentFilter || $programFilter): ?>
                                <a href="students.php" class="btn btn-small">
                                    <i class="fas fa-redo"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Call to Action -->
                <div style="text-align: center; padding: 40px 20px; background: var(--bg-secondary); border-radius: var(--border-radius);">
                    <h3 style="color: var(--primary); margin-bottom: 15px;">Ready to Join Our Community?</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Start your journey at Wolkite Polytechnic College and become part of our growing family of technologists and innovators.
                    </p>
                    <a href="index.php" class="btn" style="width: auto; padding: 15px 30px; display: inline-block;">
                        <i class="fas fa-paper-plane"></i> Begin Your Application
                    </a>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <p>&copy; 2025 Wolkite Polytechnic College. All rights reserved.</p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
<?php 
$conn->close();
$departments->close();
$programs->close();
?>