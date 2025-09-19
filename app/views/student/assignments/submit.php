<?php
session_start();

// Check korlam user login kora ase kina & o student kina
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}
// Database & model file gula include korlam
require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Assignment ID URL theke nicchi
$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentId = $_SESSION['user_id']; // Logged in student er ID


$assignment = getAssignmentWithClass($assignmentId);

// Assignment ta ase kina check korlam
if (!$assignment) {
    header('Location: index.php?error=' . urlencode('Assignment not found'));
    exit;
}

//student ta oi class e enrolled kina
if (!checkStudentEnrollment($studentId, $assignment['class_id'])) {
    header('Location: index.php?error=' . urlencode('Access denied'));
    exit;
}

// Check korlam student agei submit kore dise kina
$isSubmitted = checkExistingSubmission($assignmentId, $studentId);

// Form submit hoile data process korbo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isSubmitted) {
    $submissionText = trim($_POST['submission_text'] ?? '');
    $uploadedFile = ''; //upload kora file rakhbo ekhane
    
    // File upload handle korbo jodi thake
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../../uploads/assignments/';
        //directory thakle check korbo, na thakle create korbo
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        //unique file name er jonno time use korlam
        $fileName = time() . '_' . basename($_FILES['submission_file']['name']);
        $uploadPath = $uploadDir . $fileName;
        //file move kore upload directory te rakhlam
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $uploadPath)) {
            $uploadedFile = $fileName;
        }
    }
    
    //student k text ba file kichu ekta must submit korte hobe
    if (empty($submissionText) && empty($uploadedFile)) {
        $error = 'Please provide either submission text or upload a file.';
    } else {
        //submission save korar jonno function call 
        if (submitAssignment($assignmentId, $studentId, $submissionText, $uploadedFile)) {
            $_SESSION['success'] = 'Assignment submitted successfully!';
            header('Location: index.php');
            exit;
        } else {
            
            $error = 'Failed to submit assignment. Please try again.';
            
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="index.php">Assignments</a></li>
                <li><a href="../attendance/index.php">Attendance</a></li>
                <li><a href="../materials/index.php">Materials</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <div class="page-header">
                <h2>Submit Assignment</h2>
                <a href="index.php" class="btn btn-secondary">Back to Assignments</a>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="assignment-details">
                    <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($assignment['class_name']); ?></p>
                    <p><strong>Due Date:</strong> <?php echo date('M d, Y g:i A', strtotime($assignment['due_date'])); ?></p>
                    
                    <?php if ($assignment['description']): ?>
                        <div class="assignment-description">
                            <h4>Description:</h4>
                            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($isSubmitted): ?>
                    <div class="success-message">
                        <h4>Assignment Already Submitted</h4>
                        <p>You have already submitted this assignment.</p>
                    </div>
                <?php elseif (strtotime($assignment['due_date']) < time()): ?>
                    <div class="error-message">
                        <h4>Assignment Overdue</h4>
                        <p>This assignment is past its due date and can no longer be submitted.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" enctype="multipart/form-data" class="user-form">
                        <div class="form-group">
                            <label for="submission_text">Submission Text:</label>
                            <textarea id="submission_text" name="submission_text" rows="6" 
                                      placeholder="Enter your submission text here..."><?php echo isset($_POST['submission_text']) ? htmlspecialchars($_POST['submission_text']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="submission_file">Upload File (optional):</label>
                            <input type="file" id="submission_file" name="submission_file" 
                                   accept=".pdf,.doc,.docx,.txt,.zip,.jpg,.png">
                            <small style="color: #6c757d;">Supported formats: PDF, Word, Text, ZIP, Images</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Assignment</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>