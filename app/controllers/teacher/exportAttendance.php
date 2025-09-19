<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


if (!isset($conn) || !$conn) {
    $_SESSION['error'] = "Database connection failed.";
    header("Location: index.php");
    exit;
}

$classId = $_GET['class_id'] ?? null;
if (!$classId) {
    $_SESSION['error'] = "No class selected.";
    header("Location: index.php");
    exit;
}


$sql = "SELECT a.id, a.student_id, a.class_id, a.date, a.status, a.notes, a.updated_at,
               CONCAT(u.first_name, ' ', u.last_name) AS student_name,
               u.email,
               c.class_name
        FROM attendance a
        JOIN users u ON a.student_id = u.id AND u.user_type = 'student'
        JOIN classes c ON a.class_id = c.id
        WHERE a.class_id = ?
        ORDER BY a.date DESC, a.updated_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error'] = "SQL Error: " . $conn->error;
    header("Location: index.php");
    exit;
}

$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($attendance)) {
    $_SESSION['error'] = "No attendance records found for this class.";
    header("Location: index.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="attendance_export_' . $classId . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fputcsv($output, ['Student Name', 'Email', 'Date', 'Status', 'Notes', 'Last Updated', 'Class Name']);

foreach ($attendance as $a) {
    fputcsv($output, [
        $a['student_name'],
        $a['email'],
        $a['date'],
        ucfirst($a['status']),
        $a['notes'],
        $a['updated_at'],
        $a['class_name']
    ]);
}

fclose($output);
exit;
