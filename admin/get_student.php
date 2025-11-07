<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID required']);
    exit;
}

$studentId = intval($_GET['id']);
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $result->fetch_assoc();

// Format department and program names for display
$student['department_display'] = ucfirst(str_replace('-', ' ', $student['department']));
$student['program_display'] = ucfirst(str_replace('-', ' ', $student['program']));

header('Content-Type: application/json');
echo json_encode($student);

$stmt->close();
$conn->close();
?>