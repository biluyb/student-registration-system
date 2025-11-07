<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();

// Get statistics
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$pendingStudents = $conn->query("SELECT COUNT(*) as pending FROM students WHERE status = 'pending'")->fetch_assoc()['pending'];
$approvedStudents = $conn->query("SELECT COUNT(*) as approved FROM students WHERE status = 'approved'")->fetch_assoc()['approved'];
$rejectedStudents = $conn->query("SELECT COUNT(*) as rejected FROM students WHERE status = 'rejected'")->fetch_assoc()['rejected'];

// Get recent registrations
$recentStudents = $conn->query("SELECT * FROM students ORDER BY registration_date DESC LIMIT 5");

// Get department-wise distribution
$deptStats = $conn->query("SELECT department, COUNT(*) as count FROM students GROUP BY department");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wolkite Polytechnic College</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard {
            padding: 20px 0;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-primary);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card.pending { border-left-color: var(--warning); }
        .stat-card.approved { border-left-color: var(--success); }
        .stat-card.rejected { border-left-color: var(--danger); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: var(--bg-primary);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
        }
        
        .card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .recent-students table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .recent-students th,
        .recent-students td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .recent-students th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--primary);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1edff; color: #004085; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-secondary {
            background: var(--secondary);
        }
        
        .btn-danger {
            background: var(--danger);
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 0.9rem;
            width: auto;
        }
        
        .dept-list {
            list-style: none;
            padding: 0;
        }
        
        .dept-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 90px;
            z-index: 1000;
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
                    <h1>Admin Dashboard</h1>
                    <p>Wolkite Polytechnic College - Student Management</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="dashboard">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
                    <p>Here's an overview of student registrations and system statistics.</p>
                </div>
                
                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalStudents; ?></div>
                        <div class="stat-label">Total Students</div>
                        <i class="fas fa-users" style="font-size: 2rem; color: var(--primary); margin-top: 10px;"></i>
                    </div>
                    
                    <div class="stat-card pending">
                        <div class="stat-number"><?php echo $pendingStudents; ?></div>
                        <div class="stat-label">Pending Applications</div>
                        <i class="fas fa-clock" style="font-size: 2rem; color: var(--warning); margin-top: 10px;"></i>
                    </div>
                    
                    <div class="stat-card approved">
                        <div class="stat-number"><?php echo $approvedStudents; ?></div>
                        <div class="stat-label">Approved Applications</div>
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--success); margin-top: 10px;"></i>
                    </div>
                    
                    <div class="stat-card rejected">
                        <div class="stat-number"><?php echo $rejectedStudents; ?></div>
                        <div class="stat-label">Rejected Applications</div>
                        <i class="fas fa-times-circle" style="font-size: 2rem; color: var(--danger); margin-top: 10px;"></i>
                    </div>
                </div>
                
                <!-- Main Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Left Column -->
                    <div>
                        <!-- Recent Registrations -->
                        <div class="card recent-students">
                            <h3><i class="fas fa-history"></i> Recent Registrations</h3>
                            <?php if ($recentStudents->num_rows > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($student = $recentStudents->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['department']))); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $student['status']; ?>">
                                                        <?php echo ucfirst($student['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($student['registration_date'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No students registered yet.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Actions -->
                        <!-- Quick Actions -->
<div class="card">
    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
    <div class="action-buttons">
        <a href="students.php" class="btn btn-small">
            <i class="fas fa-list"></i> Manage Students
        </a>
        <a href="../students.php" class="btn btn-secondary btn-small" target="_blank">
            <i class="fas fa-eye"></i> View Public Site
        </a>
        <a href="../index.php" class="btn btn-small" style="background: var(--info);">
            <i class="fas fa-user-plus"></i> New Registration
        </a>
        <a href="logout.php" class="btn btn-danger btn-small">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

                        
                    </div>
                    
                    <!-- Right Column -->
                    <div>
                        <!-- Department Statistics -->
                        <div class="card">
                            <h3><i class="fas fa-chart-pie"></i> Department Distribution</h3>
                            <?php if ($deptStats->num_rows > 0): ?>
                                <ul class="dept-list">
                                    <?php while ($dept = $deptStats->fetch_assoc()): ?>
                                        <li>
                                            <span><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $dept['department']))); ?></span>
                                            <strong><?php echo $dept['count']; ?></strong>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>No department data available.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- System Info -->
                        <div class="card">
                            <h3><i class="fas fa-info-circle"></i> System Information</h3>
                            <div style="display: grid; gap: 10px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span>College:</span>
                                    <strong>Wolkite Polytechnic</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Admin Role:</span>
                                    <strong><?php echo ucfirst($_SESSION['admin_role']); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Last Login:</span>
                                    <strong><?php echo date('M j, Y g:i A'); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../script.js"></script>
</body>
</html>
<?php $conn->close(); ?>