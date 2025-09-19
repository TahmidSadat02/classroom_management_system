<?php
session_start();

require_once __DIR__ . '/../../models/GradeModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'save_grades') {
    saveGradesAction();
} elseif ($action === 'update_grade') {
    updateGradeAction();
} else {
    $_SESSION['error'] = "Invalid action.";
    header("Location: ../../views/teacher/grades/index.php");
    exit;
}


function saveGradesAction()
{
    global $conn;

    $classId   = $_POST['class_id'] ?? null;
    $marks     = $_POST['marks'] ?? [];
    $grades    = $_POST['grade'] ?? [];
    $types     = $_POST['assessment_type'] ?? [];

    if (!$classId || empty($marks)) {
        $_SESSION['error'] = "Missing class or student data.";
        header("Location: ../../views/teacher/grades/index.php");
        return;
    }

    foreach ($marks as $studentId => $mark) {
        $grade = $grades[$studentId] ?? '';
        $assessmentType = $types[$studentId] ?? "assignment";

        saveGrade($studentId, $classId, null, $mark, $grade, $assessmentType);
    }

    $_SESSION['success'] = "Grades saved successfully!";
    header("Location: ../../views/teacher/grades/view.php?class_id=" . $classId);
}


function updateGradeAction()
{
    global $conn;

    $gradeId        = $_POST['grade_id'] ?? null;
    $marks          = $_POST['marks'] ?? null;
    $grade          = $_POST['grade'] ?? '';
    $assessmentType = $_POST['assessment_type'] ?? 'assignment';
    $classId        = $_POST['class_id'] ?? null;

    if (!$gradeId || !$classId) {
        $_SESSION['error'] = "Missing grade or class ID.";
        header("Location: ../../views/teacher/grades/index.php");
        return;
    }

    if (updateGrade($gradeId, $marks, $grade, $assessmentType)) {
        $_SESSION['success'] = "Grade updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update grade.";
    }

    header("Location: ../../views/teacher/grades/view.php?class_id=" . $classId);
}
