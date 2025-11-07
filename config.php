<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'woolkite_college');
define('COLLEGE_NAME', 'Woolkite Polytechnic College');

// File upload configuration
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create tables if not exists
function createTablesIfNotExists($conn) {
    // Students table
    $sql = "CREATE TABLE IF NOT EXISTS students (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        dob DATE NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        address TEXT NOT NULL,
        nationality VARCHAR(50),
        id_number VARCHAR(50),
        
        department VARCHAR(100) NOT NULL,
        program VARCHAR(50) NOT NULL,
        semester INT(2) NOT NULL,
        academic_year VARCHAR(20) NOT NULL,
        previous_school VARCHAR(100) NOT NULL,
        qualification VARCHAR(50) NOT NULL,
        percentage DECIMAL(5,2) NOT NULL,
        board_university VARCHAR(100),
        achievements TEXT,
        
        photo_path VARCHAR(255),
        id_proof_path VARCHAR(255),
        marksheet_path VARCHAR(255),
        transfer_certificate_path VARCHAR(255),
        additional_docs_path TEXT,
        
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error creating students table: " . $conn->error);
    }

    // Admin users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'moderator') DEFAULT 'moderator',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error creating admin_users table: " . $conn->error);
    }

    // Insert default admin user if not exists
    $checkAdmin = $conn->query("SELECT id FROM admin_users WHERE username = 'admin'");
    if ($checkAdmin->num_rows === 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin_users (username, password, full_name, role) VALUES ('admin', '$hashedPassword', 'System Administrator', 'admin')");
    }
}

// File upload function
function uploadFile($file, $type) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds maximum limit of 2MB');
    }
    
    if (!in_array($file['type'], ALLOWED_FILE_TYPES)) {
        throw new Exception('Invalid file type. Allowed types: JPEG, PNG, PDF');
    }
    
    // Create upload directory if not exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = $type . '_' . uniqid() . '.' . $fileExtension;
    $filePath = UPLOAD_PATH . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return $fileName;
}

// Generate student ID
function generateStudentID($conn) {
    $year = date('Y');
    $prefix = 'WPC';
    
    $result = $conn->query("SELECT COUNT(*) as count FROM students WHERE student_id LIKE '$prefix$year%'");
    $row = $result->fetch_assoc();
    $sequence = str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);
    
    return $prefix . $year . $sequence;
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>