<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();

// Initialize messages
$successMessage = '';
$errorMessage = '';

// Handle status updates
if (isset($_POST['update_status'])) {
    $studentId = intval($_POST['student_id']);
    $status = $_POST['status'];
    $remarks = sanitizeInput($_POST['remarks'] ?? '');
    
    $stmt = $conn->prepare("UPDATE students SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $studentId);
        if ($stmt->execute()) {
            $successMessage = "Student status updated successfully!";
        } else {
            $errorMessage = "Failed to update student status: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Failed to prepare statement: " . $conn->error;
    }
}

// Handle student deletion
if (isset($_POST['delete_student'])) {
    $studentId = intval($_POST['student_id']);
    
    // First, get file paths to delete physical files
    $stmt = $conn->prepare("SELECT photo_path, id_proof_path, marksheet_path, transfer_certificate_path, additional_docs_path FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($student = $result->fetch_assoc()) {
            // Delete files
            $filesToDelete = [
                $student['photo_path'],
                $student['id_proof_path'],
                $student['marksheet_path'],
                $student['transfer_certificate_path']
            ];
            
            // Handle additional docs (JSON array)
            if ($student['additional_docs_path']) {
                $additionalDocs = json_decode($student['additional_docs_path'], true);
                if (is_array($additionalDocs)) {
                    $filesToDelete = array_merge($filesToDelete, $additionalDocs);
                }
            }
            
            foreach ($filesToDelete as $file) {
                if ($file && file_exists('../uploads/' . $file)) {
                    unlink('../uploads/' . $file);
                }
            }
        }
        $stmt->close();
    }
    
    // Delete student record
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $studentId);
        if ($stmt->execute()) {
            $successMessage = "Student record deleted successfully!";
        } else {
            $errorMessage = "Failed to delete student record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Failed to prepare delete statement: " . $conn->error;
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$departmentFilter = $_GET['department'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = '';

if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($departmentFilter) {
    $query .= " AND department = ?";
    $params[] = $departmentFilter;
    $types .= 's';
}

if ($searchQuery) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

$query .= " ORDER BY registration_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($stmt) {
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    if ($stmt->execute()) {
        $students = $stmt->get_result();
    } else {
        $errorMessage = "Failed to execute query: " . $stmt->error;
        $students = false;
    }
} else {
    $errorMessage = "Failed to prepare query: " . $conn->error;
    $students = false;
}

// Get unique departments for filter
$departments = $conn->query("SELECT DISTINCT department FROM students ORDER BY department");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Wolkite Polytechnic College</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .students-management {
            padding: 20px 0;
        }
        
        .filters {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr auto;
            gap: 15px;
            align-items: end;
        }
        
        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
        }
        
        .students-table {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        .students-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .students-table th,
        .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .students-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        .students-table tr:hover {
            background: var(--bg-secondary);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1edff; color: #004085; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.8rem;
            width: auto;
        }
        
        .btn-view { background: var(--info); }
        .btn-edit { background: var(--warning); }
        .btn-delete { background: var(--danger); }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background: var(--bg-primary);
            margin: 5% auto;
            padding: 30px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--box-shadow);
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .student-details {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary);
        }
        
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 90px;
            z-index: 1000;
        }
        
        .navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="light-mode">
    <div class="theme-toggle">
        <button id="themeToggle" class="theme-btn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
    <!-- Navigation -->
<div class="navigation">
    <a href="dashboard.php" class="btn btn-small">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
    <a href="../students.php" class="btn btn-secondary btn-small" target="_blank">
        <i class="fas fa-external-link-alt"></i> Public View
    </a>
</div>
    <div class="logout-btn">
        <a href="dashboard.php" class="btn btn-small" style="background: var(--secondary);">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="logout.php" class="btn btn-danger btn-small">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="container">
        <div class="header">
            <div class="college-brand">
                <div class="college-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="college-info">
                    <h1>Manage Students</h1>
                    <p>Wolkite Polytechnic College - Student Management System</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="students-management">
                <!-- Navigation -->
                <div class="navigation">
                    <a href="dashboard.php" class="btn btn-small">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <!-- Success/Error Messages -->
                <?php if ($successMessage): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="filters">
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="form-group">
                                <label for="status">Status Filter</label>
                                <select id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="department">Department Filter</label>
                                <select id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php 
                                    if ($departments) {
                                        while ($dept = $departments->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $dept['department']; ?>" <?php echo $departmentFilter === $dept['department'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $dept['department']))); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="search">Search Students</label>
                                <input type="text" id="search" name="search" placeholder="Search by name, email, or student ID" value="<?php echo htmlspecialchars($searchQuery); ?>">
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
                
                <!-- Students Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="students-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students && $students->num_rows > 0): ?>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['department']))); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['program']))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $student['status']; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($student['registration_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-view btn-icon" onclick="viewStudent(<?php echo $student['id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-edit btn-icon" onclick="editStatus(<?php echo $student['id']; ?>, '<?php echo $student['status']; ?>')" title="Edit Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-delete btn-icon" onclick="confirmDelete(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])); ?>')" title="Delete Student">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; display: block; color: var(--gray);"></i>
                            No students found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Mobile Cards (hidden on desktop) -->
    <div class="mobile-cards">
        <?php if ($students && $students->num_rows > 0): ?>
            <?php 
            // Reset pointer to beginning for mobile cards
            $students->data_seek(0);
            while ($student = $students->fetch_assoc()): 
            ?>
                <div class="student-mobile-card">
                    <div class="student-mobile-header">
                        <div class="student-mobile-info">
                            <div class="student-mobile-name">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </div>
                            <div class="student-mobile-id">
                                ID: <?php echo htmlspecialchars($student['student_id']); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $student['status']; ?>">
                            <?php echo ucfirst($student['status']); ?>
                        </span>
                    </div>
                    
                    <div class="student-mobile-details">
                        <div class="detail-item">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Department</span>
                            <span class="detail-value"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['department']))); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Program</span>
                            <span class="detail-value"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $student['program']))); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registered</span>
                            <span class="detail-value"><?php echo date('M j, Y', strtotime($student['registration_date'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="mobile-actions">
                        <button class="btn btn-view btn-small" onclick="viewStudent(<?php echo $student['id']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-edit btn-small" onclick="editStatus(<?php echo $student['id']; ?>, '<?php echo $student['status']; ?>')" title="Edit Status">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-delete btn-small" onclick="confirmDelete(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])); ?>')" title="Delete Student">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; display: block; color: var(--gray);"></i>
                No students found
            </div>
        <?php endif; ?>
    </div>
</div>


    <!-- View Student Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-user-graduate"></i> Student Details</h3>
            <div id="studentDetails"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Status Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-edit"></i> Update Student Status</h3>
            <form method="POST" action="">
                <input type="hidden" id="editStudentId" name="student_id">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks (Optional)</label>
                    <textarea id="remarks" name="remarks" rows="3" placeholder="Add any remarks..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" name="update_status" class="btn">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
            <p>Are you sure you want to delete the student record for <strong id="deleteStudentName"></strong>?</p>
            <p style="color: var(--danger); font-size: 0.9rem;"><i class="fas fa-warning"></i> This action cannot be undone and will permanently delete all student data and uploaded files.</p>
            <form method="POST" action="">
                <input type="hidden" id="deleteStudentId" name="student_id">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" name="delete_student" class="btn btn-danger">Delete Student</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../script.js"></script>
    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // View student details
        function viewStudent(studentId) {
            // Show loading state
            document.getElementById('studentDetails').innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                    <p>Loading student details...</p>
                </div>
            `;
            openModal('viewModal');
            
            fetch(`get_student.php?id=${studentId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const details = document.getElementById('studentDetails');
                    if (data.error) {
                        details.innerHTML = `
                            <div style="text-align: center; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>${data.error}</p>
                            </div>
                        `;
                    } else {
                        details.innerHTML = `
                            <div class="student-details">
                                <div class="detail-row">
                                    <span class="detail-label">Student ID:</span>
                                    <span>${data.student_id}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span>${data.first_name} ${data.last_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span>${data.email}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone:</span>
                                    <span>${data.phone}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Department:</span>
                                    <span>${data.department.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Program:</span>
                                    <span>${data.program.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="status-badge status-${data.status}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Registration Date:</span>
                                    <span>${new Date(data.registration_date).toLocaleDateString()}</span>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('studentDetails').innerHTML = `
                        <div style="text-align: center; color: var(--danger);">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error loading student details. Please try again.</p>
                        </div>
                    `;
                });
        }
        
        // Edit student status
        function editStatus(studentId, currentStatus) {
            document.getElementById('editStudentId').value = studentId;
            document.getElementById('status').value = currentStatus;
            openModal('editModal');
        }
        
        // Confirm deletion
        function confirmDelete(studentId, studentName) {
            document.getElementById('deleteStudentId').value = studentId;
            document.getElementById('deleteStudentName').textContent = studentName;
            openModal('deleteModal');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }
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
if (isset($conn)) {
    $conn->close();
}
?>