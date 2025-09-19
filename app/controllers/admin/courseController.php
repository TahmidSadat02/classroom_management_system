<?php

session_start();


require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/Course.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        createCourse();
        break;
    case 'update':
        updateCourse();
        break;
    case 'delete':
        deleteCourse();
        break;
    case 'archive':
        archiveCourse();
        break;
    default:
        header('Location: ../../views/admin/courses/index.php');
        break;
}

function createCourse() {
    
    if (empty($_POST['courseCode']) || empty($_POST['courseName']) || empty($_POST['department']) || empty($_POST['semester']) || empty($_POST['academicYear'])) {
        $_SESSION['error'] = 'Course code, name, department, semester, and academic year are required.';
        header('Location: ../../views/admin/courses/create.php');
        return;
    }
    
    $courseData = [
        'courseCode' => strtoupper(trim($_POST['courseCode'])),
        'courseName' => trim($_POST['courseName']),
        'courseDescription' => trim($_POST['courseDescription']),
        'credits' => !empty($_POST['credits']) ? intval($_POST['credits']) : 3,
        'department' => trim($_POST['department']),
        'semester' => trim($_POST['semester']),
        'academicYear' => trim($_POST['academicYear']),
        'teacherId' => !empty($_POST['teacherId']) ? intval($_POST['teacherId']) : null
    ];
    
    $courseId = createNewCourse($courseData);
    
    if ($courseId) {
        $_SESSION['success'] = "Course '{$courseData['courseCode']} - {$courseData['courseName']}' created successfully!";
        header('Location: ../../views/admin/courses/index.php');
    } else {
        $_SESSION['error'] = 'Error creating course. Course code might already exist.';
        header('Location: ../../views/admin/courses/create.php');
    }
}

function updateCourse() {
    
    $_SESSION['info'] = 'Update course feature will be implemented next.';
    header('Location: ../../views/admin/courses/index.php');
}

function deleteCourse() {
    
    $_SESSION['info'] = 'Delete course feature will be implemented next.';
    header('Location: ../../views/admin/courses/index.php');
}

function archiveCourse() {
    
    $_SESSION['info'] = 'Archive course feature will be implemented next.';
    header('Location: ../../views/admin/courses/index.php');
}
?>
