<?php
require_once 'config.php';

$errors = [];
$success = false;
$studentData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        createTablesIfNotExists($conn);
        
        // Sanitize and validate personal information
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $address = sanitizeInput($_POST['address']);
        $nationality = sanitizeInput($_POST['nationality'] ?? '');
        $idNumber = sanitizeInput($_POST['idNumber'] ?? '');
        
        // Sanitize and validate academic information
        $department = $_POST['department'];
        $program = $_POST['program'];
        $semester = intval($_POST['semester']);
        $academicYear = $_POST['academicYear'];
        $previousSchool = sanitizeInput($_POST['previousSchool']);
        $qualification = $_POST['qualification'];
        $percentage = floatval($_POST['percentage']);
        $board = sanitizeInput($_POST['board'] ?? '');
        $achievements = sanitizeInput($_POST['achievements'] ?? '');
        
        // Validate required fields
        if (empty($firstName) || strlen($firstName) < 2) {
            $errors[] = "First name must be at least 2 characters long.";
        }
        
        if (empty($lastName) || strlen($lastName) < 2) {
            $errors[] = "Last name must be at least 2 characters long.";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "This email address is already registered.";
        }
        $stmt->close();
        
        // File upload handling
        $photoPath = $idProofPath = $marksheetPath = $transferCertificatePath = '';
        $additionalDocs = [];
        
        try {
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = uploadFile($_FILES['photo'], 'photo');
            } else {
                throw new Exception("Passport photo is required.");
            }
            
            if (!empty($_FILES['idProof']['name'])) {
                $idProofPath = uploadFile($_FILES['idProof'], 'id_proof');
            } else {
                throw new Exception("ID proof is required.");
            }
            
            if (!empty($_FILES['marksheet']['name'])) {
                $marksheetPath = uploadFile($_FILES['marksheet'], 'marksheet');
            }
            
            if (!empty($_FILES['transferCertificate']['name'])) {
                $transferCertificatePath = uploadFile($_FILES['transferCertificate'], 'transfer_certificate');
            }
            
            // Handle multiple additional documents
            if (!empty($_FILES['additionalDocs']['name'][0])) {
                foreach ($_FILES['additionalDocs']['name'] as $key => $name) {
                    if ($_FILES['additionalDocs']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['additionalDocs']['name'][$key],
                            'type' => $_FILES['additionalDocs']['type'][$key],
                            'tmp_name' => $_FILES['additionalDocs']['tmp_name'][$key],
                            'error' => $_FILES['additionalDocs']['error'][$key],
                            'size' => $_FILES['additionalDocs']['size'][$key]
                        ];
                        $additionalDocs[] = uploadFile($file, 'additional_' . $key);
                    }
                }
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            $studentId = generateStudentID($conn);
            $additionalDocsPath = !empty($additionalDocs) ? json_encode($additionalDocs) : null;
            
            $stmt = $conn->prepare("INSERT INTO students (
                student_id, first_name, last_name, email, phone, dob, gender, address, 
                nationality, id_number, department, program, semester, academic_year, 
                previous_school, qualification, percentage, board_university, achievements,
                photo_path, id_proof_path, marksheet_path, transfer_certificate_path, additional_docs_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssssssissssdssssss", 
                $studentId, $firstName, $lastName, $email, $phone, $dob, $gender, $address,
                $nationality, $idNumber, $department, $program, $semester, $academicYear,
                $previousSchool, $qualification, $percentage, $board, $achievements,
                $photoPath, $idProofPath, $marksheetPath, $transferCertificatePath, $additionalDocsPath
            );
            
            if ($stmt->execute()) {
                $success = true;
                $studentData = [
                    'student_id' => $studentId,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'department' => $department,
                    'program' => $program
                ];
                
                // Send confirmation email (you can implement this)
                // sendConfirmationEmail($email, $studentId, $firstName . ' ' . $lastName);
            } else {
                $errors[] = "Registration failed. Please try again. Error: " . $conn->error;
            }
            
            $stmt->close();
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        $errors[] = "System error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Result - Woolkite Polytechnic College</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="light-mode">
    <div class="theme-toggle">
        <button id="themeToggle" class="theme-btn">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="container" style="max-width: 700px;">
        <div class="header">
            <div class="college-brand">
                <div class="college-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="college-info">
                    <h1>Woolkite Polytechnic College</h1>
                    <p>Excellence in Technical Education Since 1995</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <?php if ($success): ?>
                <div style="text-align: center; padding: 30px 0;">
                    <div style="font-size: 80px; color: var(--success); margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="color: var(--success); margin-bottom: 15px;">Registration Successful!</h2>
                    <p style="margin-bottom: 10px; color: var(--text-primary); font-size: 1.1rem;">
                        Thank you <strong><?php echo htmlspecialchars($studentData['name']); ?></strong> for registering with Woolkite Polytechnic College.
                    </p>
                    
                    <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--border-radius); margin: 25px 0;">
                        <h3 style="color: var(--primary); margin-bottom: 15px;">Your Registration Details</h3>
                        <div style="text-align: left; max-width: 300px; margin: 0 auto;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong>Student ID:</strong>
                                <span style="color: var(--secondary);"><?php echo $studentData['student_id']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong>Department:</strong>
                                <span><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $studentData['department']))); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong>Program:</strong>
                                <span><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $studentData['program']))); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <p style="margin-bottom: 25px; color: var(--text-secondary);">
                        We have sent a confirmation email to <strong><?php echo htmlspecialchars($studentData['email']); ?></strong><br>
                        Please check your inbox and spam folder.
                    </p>
                    
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="index.php" class="btn" style="display: inline-block; width: auto; padding: 12px 30px;">
                            <i class="fas fa-user-plus"></i> Register Another Student
                        </a>
                        <a href="admin/login.php" class="btn" style="display: inline-block; width: auto; padding: 12px 30px; background: var(--secondary);">
                            <i class="fas fa-lock"></i> Admin Login
                        </a>
                    </div>
                    
                    <div style="margin-top: 30px; padding: 15px; background: var(--bg-tertiary); border-radius: var(--border-radius);">
                        <h4 style="color: var(--primary); margin-bottom: 10px;">Next Steps</h4>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            Your application is under review. You will receive an email within 3-5 working days 
                            regarding the status of your application and further instructions.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 30px 0;">
                    <div style="font-size: 80px; color: var(--danger); margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2 style="color: var(--danger); margin-bottom: 15px;">Registration Failed</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid var(--danger); padding: 20px; margin-bottom: 25px; text-align: left; border-radius: 0 var(--border-radius) var(--border-radius) 0;">
                            <h3 style="color: var(--danger); margin-bottom: 15px;">Please correct the following errors:</h3>
                            <ul style="color: var(--danger); padding-left: 20px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <p style="margin-bottom: 25px; color: var(--text-secondary);">
                        Please review your information and try again. If the problem persists, 
                        contact our admissions office.
                    </p>
                    
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="index.php" class="btn" style="display: inline-block; width: auto; padding: 12px 30px;">
                            <i class="fas fa-arrow-left"></i> Go Back to Registration
                        </a>
                        <a href="mailto:admissions@woolkitepoly.edu" class="btn" style="display: inline-block; width: auto; padding: 12px 30px; background: var(--secondary);">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>