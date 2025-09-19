<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/StudentModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_GET['action'] ?? '';
$studentId = $_SESSION['user_id'];

switch($action) {
    case 'classes':
        showClasses();
        break;
    case 'assignments':
        showAssignments();
        break;
    case 'attendance':
        showAttendance();
        break;
    case 'submit_assignment':
        submitAssignmentFile();
        break;
    default:
        showDashboard();
        break;
}

function showDashboard() {
    global $studentId;
    
    $stats = getStudentStats($studentId);
    $recentMaterials = getRecentMaterials($studentId, 5);
    $enrolledClasses = getStudentClasses($studentId);
    
    include '../../views/student/dashboard.php';
}

function showClasses() {
    global $studentId;
    
    $classes = getStudentClasses($studentId);
    include '../../views/student/classes/index.php';
}

function showAssignments() {
    global $studentId;
    
    $assignments = array(); 
    include '../../views/student/assignments/index.php';
}

function showAttendance() {
    global $studentId;
    
    $classId = $_GET['class_id'] ?? null;
    $attendance = array(); 
    $classes = getStudentClasses($studentId);
    
    include '../../views/student/attendance/index.php';
}

function submitAssignmentFile() {
    global $studentId;
    
    $assignmentId = $_POST['assignment_id'] ?? '';
    
    if (empty($assignmentId)) {
        $_SESSION['error'] = 'Assignment ID is required.';
        header('Location: ../../views/student/assignments/index.php');
        return;
    }
    
    
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/assignments/';
        
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = $studentId . '_' . $assignmentId . '_' . time() . '_' . basename($_FILES['assignment_file']['name']);
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $uploadPath)) {
            
            require_once __DIR__ . '/../../models/Database.php';
            $checkTable = $conn->query("SHOW TABLES LIKE 'assignment_submissions'");
            
            if ($checkTable && $checkTable->num_rows > 0) {
                $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, file_name, submitted_at) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                if ($stmt && $stmt->bind_param("iis", $assignmentId, $studentId, $fileName) && $stmt->execute()) {
                    $_SESSION['success'] = 'Assignment submitted successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to submit assignment.';
                }
            } else {
                $_SESSION['success'] = 'File uploaded successfully! (Assignment submissions table not configured)';
            }
        } else {
            $_SESSION['error'] = 'Failed to upload file.';
        }
    } else {
        $_SESSION['error'] = 'Please select a file to upload.';
    }
    
    header('Location: ../../views/student/assignments/index.php');
}
?>