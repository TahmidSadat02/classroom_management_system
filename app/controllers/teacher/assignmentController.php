<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/AssignmentModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$teacherId = $_SESSION['user_id'];

switch($action) {
    case 'create':
        createAssignmentAction();
        break;
    case 'update':
        updateAssignment();
        break;
    default:
        header('Location: ../../views/teacher/assignment/index.php');
        break;
}

function createAssignmentAction() {
    global $teacherId;

    if (empty($_POST['title']) || empty($_POST['deadline']) || empty($_POST['max_points'])) {
        $_SESSION['error'] = 'Title, deadline, and max points are required.';
        header('Location: ../../views/teacher/assignment/create.php');
        return;
    }

    $filePath = null;
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/assignments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filePath = $uploadDir . basename($_FILES['attachment']['name']);
        move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath);
    }

    $data = [
        'class_id' => $_POST['class_id'] ?? null,
        'title' => trim($_POST['title']),
        'description' => trim($_POST['instructions']),
        'due_date' => $_POST['deadline'],
        'requirements' => trim($_POST['requirements']),
        'attachment' => $filePath,
        'max_marks' => intval($_POST['max_points']),
        'grading_criteria' => trim($_POST['grading_criteria']),
        'visibility' => $_POST['visibility']
    ];

    $assignmentId = createAssignment(
        $teacherId,
        $data['class_id'],
        $data['title'],
        $data['description'],
        $data['due_date'],
        $data['requirements'],
        $data['attachment'],
        $data['max_marks'],
        $data['grading_criteria'],
        $data['visibility']
    );

    if ($assignmentId) {
        $_SESSION['success'] = "Assignment '{$data['title']}' created successfully!";
        header('Location: ../../views/teacher/assignment/index.php');
    } else {
        $_SESSION['error'] = 'Error creating assignment.';
        header('Location: ../../views/teacher/assignment/create.php');
    }
}

function updateAssignment() {
    $assignmentId = $_POST['assignmentId'] ?? '';

    if (empty($assignmentId)) {
        $_SESSION['error'] = 'Assignment ID is required.';
        header('Location: ../../views/teacher/assignment/index.php');
        exit;
    }

    $filePath = $_POST['existing_attachment'] ?? null;

    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/assignments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filePath = $uploadDir . basename($_FILES['attachment']['name']);
        move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath);
    }

    $data = [
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'due_date' => $_POST['deadline'],
        'requirements' => '', 
        'attachment' => $filePath,
        'max_marks' => intval($_POST['max_points']),
        'grading_criteria' => '', 
        'visibility' => $_POST['status'] 
    ];

    
    $updated = updateAssignmentData($assignmentId, $data);

    if ($updated) {
        $_SESSION['success'] = 'Assignment updated successfully!';
        
        header('Location: ../../views/teacher/assignment/index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Error updating assignment.';
        header('Location: ../../views/teacher/assignment/edit.php?id=' . urlencode($assignmentId));
        exit;
    }
}


