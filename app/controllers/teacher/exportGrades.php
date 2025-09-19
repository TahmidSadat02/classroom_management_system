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

$sql = "SELECT g.id, g.student_id, g.class_id, g.marks, g.grade, g.assessment_type, g.updated_at,
               CONCAT(u.first_name, ' ', u.last_name) AS student_name,
               c.class_name
        FROM grades g
        JOIN users u ON g.student_id = u.id AND u.user_type = 'student'
        JOIN classes c ON g.class_id = c.id
        WHERE g.class_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error'] = "SQL Error: " . $conn->error;
    header("Location: index.php");
    exit;
}

$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($grades)) {
    $_SESSION['error'] = "No grades found for this class.";
    header("Location: index.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="grades_export_' . $classId . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Student Name', 'Marks', 'Grade', 'Assessment Type', 'Last Updated', 'Class Name']);

// Write CSV data
foreach ($grades as $grade) {
    fputcsv($output, [
        $grade['student_name'],
        $grade['marks'],
        $grade['grade'],
        $grade['assessment_type'],
        $grade['updated_at'],
        $grade['class_name']
    ]);
}

fclose($output);
exit;