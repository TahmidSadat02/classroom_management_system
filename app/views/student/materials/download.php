<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../../models/Database.php';


$materialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentId = $_SESSION['user_id']; 


if ($materialId <= 0) {
    $_SESSION['error'] = 'Invalid material ID.';
    header('Location: index.php');
    exit;
}


function canStudentAccessMaterial($studentId, $materialId) {
    global $conn;
    
    
    $sql = "SELECT cm.*, c.class_name 
            FROM content_materials cm 
            JOIN classes c ON cm.class_id = c.id 
            JOIN enrollments e ON c.id = e.class_id 
            WHERE cm.id = $materialId 
            AND e.student_id = $studentId 
            AND e.status = 'enrolled'
            AND cm.visibility = 'public'
            AND (cm.available_from IS NULL OR cm.available_from <= NOW())
            AND (cm.available_until IS NULL OR cm.available_until >= NOW())";
    
    
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : false; 
}

$material = canStudentAccessMaterial($studentId, $materialId);


if (!$material) {
    $_SESSION['error'] = 'Material not found or access denied.'; 
    header('Location: index.php'); 
    exit;
}


$filePath = __DIR__ . '/../../../../uploads/content/' . $material['file_path'];


if (!file_exists($filePath) || !$material['file_path']) {
    $_SESSION['error'] = 'File not found.';
    header('Location: index.php');
    exit;
}


function logDownloadActivity($studentId, $materialId) {
    global $conn;
    
    $sql = "INSERT INTO download_logs (student_id, material_id, downloaded_at) 
            VALUES ($studentId, $materialId, NOW())";
    $conn->query($sql); 
}


logDownloadActivity($studentId, $materialId);


$fileName = $material['file_name'] ?: 'download_' . $materialId;
$fileSize = filesize($filePath);
$fileType = $material['file_type'] ?: 'application/octet-stream';


$fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

header('Content-Type: ' . $fileType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: private');
header('Pragma: private');
header('Expires: 0');


if (ob_get_level()) {
    ob_end_clean();
}


$handle = fopen($filePath, 'rb');
if ($handle) {
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
} else {
    $_SESSION['error'] = 'Unable to read file.';
    header('Location: index.php');
}

exit;
?>