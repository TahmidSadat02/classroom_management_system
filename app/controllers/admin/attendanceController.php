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
        overrideAttendance();
        break;
    case 'delete':
        deleteAttendance();
        break;
    case 'update_policy':
        updatePolicy();
        break;
    case 'update_setting':
        updateSetting();
        break;
    case 'generate_report':
        generateReport();
        break;
    default:
        header('Location: ../../views/admin/attendance/index.php');
        break;
}

function overrideAttendance() {
    global $adminId;
    
    $attendanceId = $_POST['attendanceId'] ?? '';
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($attendanceId) || empty($status)) {
        $_SESSION['error'] = 'Attendance ID and status are required.';
        header('Location: ../../views/admin/attendance/index.php');
        return;
    }
    
    if (overrideAttendanceRecord($attendanceId, $status, $notes, $adminId)) {
        $_SESSION['success'] = 'Attendance record updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update attendance record.';
    }
    
    header('Location: ../../views/admin/attendance/index.php');
}

function deleteAttendance() {
    global $adminId;
    
    $attendanceId = $_POST['attendance_id'] ?? $_GET['attendance_id'] ?? '';
    
    
    error_log("Delete function called with ID: " . $attendanceId);
    
    if (empty($attendanceId) || !is_numeric($attendanceId)) {
        $_SESSION['error'] = 'Valid attendance ID is required.';
        header('Location: ../../views/admin/attendance/index.php');
        return;
    }
    
    if (deleteAttendanceRecord($attendanceId, $adminId)) {
        $_SESSION['success'] = 'Attendance record deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete attendance record.';
    }
    
    header('Location: ../../views/admin/attendance/index.php');
}

function updatePolicy() {
    $policyId = $_POST['policyId'] ?? '';
    $data = [
        'policy_name' => $_POST['policy_name'] ?? '',
        'min_attendance_percentage' => $_POST['min_attendance_percentage'] ?? 75,
        'late_threshold_minutes' => $_POST['late_threshold_minutes'] ?? 15,
        'excused_limit' => $_POST['excused_limit'] ?? 3,
        'policy_description' => $_POST['policy_description'] ?? '',
        'is_active' => $_POST['is_active'] ?? 0
    ];
    
    if (empty($policyId)) {
        $_SESSION['error'] = 'Policy ID is required.';
        header('Location: ../../views/admin/attendance/policies.php');
        return;
    }
    
    if (updateAttendancePolicy($policyId, $data)) {
        $_SESSION['success'] = 'Attendance policy updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update attendance policy.';
    }
    
    header('Location: ../../views/admin/attendance/policies.php');
}

function updateSetting() {
    $settingKey = $_POST['setting_key'] ?? '';
    $settingValue = $_POST['setting_value'] ?? '';
    
    if (empty($settingKey)) {
        $_SESSION['error'] = 'Setting key is required.';
        header('Location: ../../views/admin/attendance/settings.php');
        return;
    }
    
    if (updateAttendanceSetting($settingKey, $settingValue)) {
        $_SESSION['success'] = 'Setting updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update setting.';
    }
    
    header('Location: ../../views/admin/attendance/settings.php');
}

function generateReport() {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $classId = $_POST['class_id'] ?? null;
    
    if (empty($startDate) || empty($endDate)) {
        $_SESSION['error'] = 'Start date and end date are required.';
        header('Location: ../../views/admin/attendance/reports.php');
        return;
    }
    
    $reportData = generateAttendanceReportData($startDate, $endDate, $classId);
    $_SESSION['report_data'] = $reportData;
    $_SESSION['report_params'] = ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId];
    
    header('Location: ../../views/admin/attendance/reports.php');
}
?>
