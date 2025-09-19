<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/AttendanceModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$teacherId = $_SESSION['user_id'];

switch ($action) {
    case 'fetch_students':
        fetchEnrolledStudentsAction();
        break;

    case 'save_attendance':
        saveAttendanceAction();
        break;

    case 'update_attendance':   // <-- Add this
        updateAttendanceAction();
        break;

    default:
        header('Location: ../../views/teacher/attendance/index.php');
        break;
}


function fetchEnrolledStudentsAction()
{
    $classId = $_GET['class_id'] ?? null;

    if (!$classId) {
        $_SESSION['error'] = "Class ID missing.";
        header('Location: ../../views/teacher/attendance/index.php');
        exit;
    }

    $students = getEnrolledStudents($classId);

    $_SESSION['students'] = $students;
    header("Location: ../../views/teacher/attendance/mark.php?class_id=" . urlencode($classId));
    exit;
}


function saveAttendanceAction()
{
    global $teacherId;

    $classId = $_POST['class_id'] ?? null;
    $statuses = $_POST['status'] ?? [];
    $notes = $_POST['notes'] ?? [];
    $date = date('Y-m-d');

    if (!$classId || empty($statuses)) {
        $_SESSION['error'] = "Missing class or student data.";
        header('Location: ../../views/teacher/attendance/index.php');
        return;
    }

    foreach ($statuses as $studentId => $status) {
        $note = $notes[$studentId] ?? null;
        saveAttendance($classId, $studentId, $date, $status, $note, $teacherId);
    }

    $_SESSION['success'] = "Attendance recorded successfully!";
    header("Location: ../../views/teacher/attendance/index.php");
    exit;
}

function updateAttendanceAction()
{
    global $conn, $teacherId;

    $attendanceId = intval($_POST['attendance_id'] ?? 0);
    $attendanceDate = $_POST['attendance_date'] ?? null; // expects YYYY-MM-DD
    $status = $_POST['status'] ?? null;
    $notes = $_POST['notes'] ?? '';
    $classId = intval($_POST['class_id'] ?? 0);

    if (!$attendanceId || !$attendanceDate || !$status) {
        $_SESSION['error'] = "Missing required fields for updating attendance.";
        header('Location: ../../views/teacher/attendance/view.php?class_id=' . urlencode($classId));
        exit;
    }

    $checkSql = "SELECT c.teacher_id
                 FROM attendance a
                 JOIN classes c ON a.class_id = c.id
                 WHERE a.id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header('Location: ../../views/teacher/attendance/view.php?class_id=' . urlencode($classId));
        exit;
    }
    $checkStmt->bind_param("i", $attendanceId);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    $row = $checkRes->fetch_assoc();
    $checkStmt->close();

    if (!$row || intval($row['teacher_id']) !== intval($teacherId)) {
        $_SESSION['error'] = "You do not have permission to update this attendance record.";
        header('Location: ../../views/teacher/attendance/view.php?class_id=' . urlencode($classId));
        exit;
    }

    $sql = "UPDATE attendance SET `date` = ?, `status` = ?, `notes` = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Failed to prepare update: " . $conn->error;
        header('Location: ../../views/teacher/attendance/view.php?class_id=' . urlencode($classId));
        exit;
    }

    $stmt->bind_param("sssi", $attendanceDate, $status, $notes, $attendanceId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $_SESSION['success'] = "Attendance updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update attendance.";
    }

    header('Location: ../../views/teacher/attendance/view.php?class_id=' . urlencode($classId));
    exit;
}
