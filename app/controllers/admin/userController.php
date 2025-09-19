<?php
session_start();


require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/User.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        createUser();
        break;
    case 'update':
        updateUser();
        break;
    case 'delete':
        deleteUser();
        break;
    case 'view':
        viewUser();
        break;
    default:
        header('Location: ../../views/admin/users/index.php');
        break;
}

function createUser() {
    
    if (empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['role'])) {
        $_SESSION['error'] = 'First name, last name and role are required.';
        header('Location: ../../views/admin/users/create.php');
        return;
    }
    
    $role = $_POST['role'];
    $studentId = null;
    if ($role === 'student') {
        $studentId = $_POST['studentId'] ?? '';
        
        // If studentId is empty, auto-generate it
        if (empty($studentId)) {
            $semester = $_POST['semester'] ?? 0;
            if ($semester < 1 || $semester > 3) {
                $_SESSION['error'] = 'Please select a valid semester.';
                header('Location: ../../views/admin/users/create.php');
                return;
            }
            
            try {
                $studentId = generateNextStudentId($semester);
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error generating student ID: ' . $e->getMessage();
                header('Location: ../../views/admin/users/create.php');
                return;
            }
        } else {
            // Basic format check for manually entered ID: YY-XXXX-X
            if (!preg_match('/^[0-9]{2}-[0-9]{4}-[0-9]$/', $studentId)) {
                $_SESSION['error'] = 'Valid Student ID required (format: YY-XXXX-X).';
                header('Location: ../../views/admin/users/create.php');
                return;
            }
        }
    }

    $userData = [
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'role' => $role,
        'studentId' => $studentId
    ];
    
    $result = createUserByAdmin($userData);
    
    if ($result) {
        $_SESSION['success'] = "User created successfully! Email: {$result['email']}, Default Password: {$result['password']}";
        header('Location: ../../views/admin/users/index.php');
    } else {
        $_SESSION['error'] = 'Error creating user. Please try again.';
        header('Location: ../../views/admin/users/create.php');
    }
}

function updateUser() {
    $userId = $_POST['userId'] ?? '';
    
    if (empty($userId)) {
        $_SESSION['error'] = 'User ID is required.';
        header('Location: ../../views/admin/users/index.php');
        return;
    }
    
    
    if (empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['role'])) {
        $_SESSION['error'] = 'First name, last name and role are required.';
        header('Location: ../../views/admin/users/edit.php?id=' . $userId);
        return;
    }
    
    $userData = [
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'role' => $_POST['role']
    ];
    
    if (updateUserByAdmin($userId, $userData)) {
        $_SESSION['success'] = 'User updated successfully!';
        header('Location: ../../views/admin/users/index.php');
    } else {
        $_SESSION['error'] = 'Error updating user. Please try again.';
        header('Location: ../../views/admin/users/edit.php?id=' . $userId);
    }
}

function deleteUser() {
    $userId = $_POST['userId'] ?? '';
    
    if (empty($userId)) {
        $_SESSION['error'] = 'User ID is required.';
        header('Location: ../../views/admin/users/index.php');
        return;
    }
    
    if (deleteUserByAdmin($userId)) {
        $_SESSION['success'] = 'User deleted successfully!';
    } else {
        $_SESSION['error'] = 'Error deleting user or user not found.';
    }
    
    header('Location: ../../views/admin/users/index.php');
}

function viewUser() {
    $userId = $_GET['id'] ?? '';
    
    if (empty($userId)) {
        $_SESSION['error'] = 'User ID is required.';
        header('Location: ../../views/admin/users/index.php');
        return;
    }
    
    $user = getUserById($userId);
    
    if ($user) {
        $_SESSION['view_user'] = $user;
        header('Location: ../../views/admin/users/view.php');
    } else {
        $_SESSION['error'] = 'User not found.';
        header('Location: ../../views/admin/users/index.php');
    }
}
?>
