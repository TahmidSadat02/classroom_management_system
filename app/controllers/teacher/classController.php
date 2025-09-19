<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/ClassModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$teacherId = $_SESSION['user_id'];

switch($action) {
    case 'create':
        createClass();
        break;
    case 'update':
        updateClass();
        break;
    case 'enroll':
        enrollStudent();
        break;
    case 'remove':
        removeStudent();
        break;
    case 'view':
        viewClass();
        break;
    default:
        header('Location: ../../views/teacher/classes/index.php');
        break;
}

function createClass() {
    global $teacherId;
    
    if (empty($_POST['className']) || empty($_POST['classCode']) || empty($_POST['semester']) || empty($_POST['academicYear'])) {
        $_SESSION['error'] = 'Class name, class code, semester, and academic year are required.';
        header('Location: ../../views/teacher/classes/create.php');
        return;
    }
    
    $schedule = [
        'days' => $_POST['days'] ?? [],
        'start_time' => $_POST['start_time'] ?? '',
        'end_time' => $_POST['end_time'] ?? '',
        'room' => $_POST['room'] ?? ''
    ];
    
    $classData = [
        'className' => trim($_POST['className']),
        'classCode' => strtoupper(trim($_POST['classCode'])),
        'description' => trim($_POST['description']),
        'teacherId' => $teacherId,
        'courseId' => $_POST['courseId'],
        'semester' => $_POST['semester'],
        'academicYear' => $_POST['academicYear'],
        'maxStudents' => intval($_POST['maxStudents'] ?? 30),
        'schedule' => $schedule
    ];
    
    $classId = createNewClass($classData);
    
    if ($classId) {
        $_SESSION['success'] = "Class '{$classData['className']}' created successfully!";
        header('Location: ../../views/teacher/classes/index.php');
    } else {
        $_SESSION['error'] = 'Error creating class. Class code might already exist.';
        header('Location: ../../views/teacher/classes/create.php');
    }
}

function updateClass() {
    $classId = $_POST['classId'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/teacher/classes/index.php');
        return;
    }
    
    $schedule = [
        'days' => $_POST['days'] ?? [],
        'start_time' => $_POST['start_time'] ?? '',
        'end_time' => $_POST['end_time'] ?? '',
        'room' => $_POST['room'] ?? ''
    ];
    
    $classData = [
        'className' => trim($_POST['className']),
        'description' => trim($_POST['description']),
        'courseId' => $_POST['courseId'],
        'semester' => $_POST['semester'],
        'academicYear' => $_POST['academicYear'],
        'maxStudents' => intval($_POST['maxStudents'] ?? 30),
        'schedule' => $schedule,
        'status' => $_POST['status']
    ];
    
    if (updateClassData($classId, $classData)) {
        $_SESSION['success'] = 'Class updated successfully!';
        header('Location: ../../views/teacher/classes/index.php');
    } else {
        $_SESSION['error'] = 'Error updating class.';
        header('Location: ../../views/teacher/classes/edit.php?id=' . $classId);
    }
}

function enrollStudent() {
    $classId = $_POST['classId'] ?? '';
    $studentId = $_POST['studentId'] ?? '';
    
    if (empty($classId) || empty($studentId)) {
        $_SESSION['error'] = 'Class ID and Student ID are required.';
        header('Location: ../../views/teacher/classes/index.php');
        return;
    }
    
    if (enrollStudentInClass($classId, $studentId)) {
        $_SESSION['success'] = 'Student enrolled successfully!';
    } else {
        $_SESSION['error'] = 'Error enrolling student. Class might be full.';
    }
    
    header('Location: ../../views/teacher/classes/manage.php?id=' . $classId);
}

function removeStudent() {
    $classId = $_POST['classId'] ?? '';
    $studentId = $_POST['studentId'] ?? '';
    
    if (empty($classId) || empty($studentId)) {
        $_SESSION['error'] = 'Class ID and Student ID are required.';
        header('Location: ../../views/teacher/classes/index.php');
        return;
    }
    
    if (removeStudentFromClass($classId, $studentId)) {
        $_SESSION['success'] = 'Student removed successfully!';
    } else {
        $_SESSION['error'] = 'Error removing student.';
    }
    
    header('Location: ../../views/teacher/classes/manage.php?id=' . $classId);
}

function viewClass() {
    $classId = $_GET['id'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/teacher/classes/index.php');
        return;
    }
    
    $class = getClassById($classId);
    
    if ($class) {
        $_SESSION['view_class'] = $class;
        header('Location: ../../views/teacher/classes/view.php');
    } else {
        $_SESSION['error'] = 'Class not found.';
        header('Location: ../../views/teacher/classes/index.php');
    }
}
?>
