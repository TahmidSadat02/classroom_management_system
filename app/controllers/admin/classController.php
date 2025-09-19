<?php
session_start();

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/ClassModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../views/auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$adminId = $_SESSION['user_id'];

switch($action) {
    case 'override':
        overrideClassSettings();
        break;
    case 'access':
        accessClass();
        break;
    case 'suspend':
        suspendClass();
        break;
    case 'activate':
        activateClass();
        break;
    default:
        header('Location: ../../views/admin/classes/index.php');
        break;
}

function overrideClassSettings() {
    global $adminId;
    
    $classId = $_POST['classId'] ?? '';
    $status = $_POST['status'] ?? '';
    $maxStudents = $_POST['max_students'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/admin/classes/index.php');
        return;
    }
    
    // Only include non-empty values in settings
    $settings = [];
    if (!empty($status)) {
        $settings['status'] = $status;
    }
    if (!empty($maxStudents)) {
        $settings['max_students'] = (int)$maxStudents;
    }
    
    // Check if at least one setting was provided
    if (empty($settings)) {
        $_SESSION['error'] = 'Please provide at least one setting to update.';
        header('Location: ../../views/admin/classes/index.php');
        return;
    }
    
    if (adminUpdateClassSettings($classId, $settings)) {
        $changes = [];
        if (isset($settings['status'])) $changes[] = "Status=" . $settings['status'];
        if (isset($settings['max_students'])) $changes[] = "Max Students=" . $settings['max_students'];
        
        logAdminActivity($classId, $adminId, "Admin updated class settings: " . implode(', ', $changes));
        $_SESSION['success'] = 'Class settings updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update class settings.';
    }
    
    header('Location: ../../views/admin/classes/index.php');
}

function accessClass() {
    $classId = $_GET['id'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/admin/classes/index.php');
        return;
    }
    
    header('Location: ../../views/admin/classes/view.php?id=' . $classId);
}

function suspendClass() {
    global $adminId;
    
    $classId = $_POST['classId'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/admin/classes/index.php');
        return;
    }
    
    $settings = ['status' => 'inactive'];
    
    if (adminUpdateClassSettings($classId, $settings)) {
        logAdminActivity($classId, $adminId, "Admin suspended class");
        $_SESSION['success'] = 'Class suspended successfully!';
    } else {
        $_SESSION['error'] = 'Failed to suspend class.';
    }
    
    header('Location: ../../views/admin/classes/index.php');
}

function activateClass() {
    global $adminId;
    
    $classId = $_POST['classId'] ?? '';
    
    if (empty($classId)) {
        $_SESSION['error'] = 'Class ID is required.';
        header('Location: ../../views/admin/classes/index.php');
        return;
    }
    
    $settings = ['status' => 'active'];
    
    if (adminUpdateClassSettings($classId, $settings)) {
        logAdminActivity($classId, $adminId, "Admin activated class");
        $_SESSION['success'] = 'Class activated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to activate class.';
    }
    
    header('Location: ../../views/admin/classes/index.php');
}
?>
