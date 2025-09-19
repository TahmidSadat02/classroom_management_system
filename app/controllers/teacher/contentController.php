<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/ClassModel.php';

$teacherId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        createContent();
        break;
    case 'update':
        updateContent();
        break;
    case 'delete':
        handleDeleteContent();
        break;
    case 'upload':
        uploadFile();
        break;
    case 'create_topic':
        handleCreateTopic();
        break;
    default:
        header('Location: ../../views/teacher/content/index.php');
        break;
}

function createContent() {
    global $teacherId;
    
    $data = [
        'class_id' => $_POST['class_id'] ?? '',
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'topic' => $_POST['topic'] ?? '',
        'visibility' => $_POST['visibility'] ?? 'public',
        'available_from' => $_POST['available_from'] ?? '',
        'available_until' => $_POST['available_until'] ?? '',
        'file_type' => 'document'
    ];
    
    if (empty($data['title'])) {
        $_SESSION['error'] = 'Title is required.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    if (createContentData($teacherId, $data)) {
        $_SESSION['success'] = 'Content created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create content.';
    }
    
    header('Location: ../../views/teacher/content/index.php');
}

function updateContent() {
    global $teacherId;
    
    $contentId = $_POST['content_id'] ?? '';
    $data = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'topic' => $_POST['topic'] ?? '',
        'visibility' => $_POST['visibility'] ?? 'public',
        'available_from' => $_POST['available_from'] ?? '',
        'available_until' => $_POST['available_until'] ?? ''
    ];
    
    if (empty($contentId) || empty($data['title'])) {
        $_SESSION['error'] = 'Content ID and title are required.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    // Check if content belongs to teacher
    $content = getContentById($contentId);
    if (!$content || $content['teacher_id'] != $teacherId) {
        $_SESSION['error'] = 'Content not found or access denied.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    if (updateContentData($contentId, $data)) {
        $_SESSION['success'] = 'Content updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update content.';
    }
    
    header('Location: ../../views/teacher/content/index.php');
}

function handleDeleteContent() {
    global $teacherId;
    
    $contentId = $_POST['content_id'] ?? $_GET['content_id'] ?? '';
    
    if (empty($contentId)) {
        $_SESSION['error'] = 'Content ID is required.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    // Check if content belongs to teacher
    $content = getContentById($contentId);
    if (!$content || $content['teacher_id'] != $teacherId) {
        $_SESSION['error'] = 'Content not found or access denied.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    if (deleteContent($contentId)) {
        $_SESSION['success'] = 'Content deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete content.';
    }
    
    header('Location: ../../views/teacher/content/index.php');
}

function uploadFile() {
    global $teacherId;
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'File upload failed.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    $file = $_FILES['file'];
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        $_SESSION['error'] = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, PPT, PPTX';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    // Create upload directory if not exists
    $uploadDir = '../../../uploads/content/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileName = time() . '_' . $file['name'];
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $data = [
            'class_id' => $_POST['class_id'] ?? '',
            'title' => $_POST['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
            'description' => $_POST['description'] ?? '',
            'topic' => $_POST['topic'] ?? '',
            'visibility' => $_POST['visibility'] ?? 'public',
            'file_name' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $fileExt,
            'available_from' => $_POST['available_from'] ?? '',
            'available_until' => $_POST['available_until'] ?? ''
        ];
        
        if (createContentData($teacherId, $data)) {
            $_SESSION['success'] = 'File uploaded and content created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save content information.';
            unlink($filePath); // Delete file if database save failed
        }
    } else {
        $_SESSION['error'] = 'Failed to save uploaded file.';
    }
    
    header('Location: ../../views/teacher/content/index.php');
}

function handleCreateTopic() {
    global $teacherId;
    
    $data = [
        'class_id' => $_POST['class_id'] ?? '',
        'topic_name' => $_POST['topic_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'sort_order' => $_POST['sort_order'] ?? 0
    ];
    
    if (empty($data['topic_name'])) {
        $_SESSION['error'] = 'Topic name is required.';
        header('Location: ../../views/teacher/content/index.php');
        return;
    }
    
    if (createTopic($teacherId, $data)) {
        $_SESSION['success'] = 'Topic created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create topic.';
    }
    
    header('Location: ../../views/teacher/content/index.php');
}
?>