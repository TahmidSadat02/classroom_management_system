<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$currentYear = date('Y');


$teacherClasses = getTeacherClasses($_SESSION['user_id']);


if (empty($teacherClasses)) {
    $_SESSION['error'] = "You need to create at least one class before creating assignments.";
    header("Location: ../classes/create.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['class_id'];
    $title = $_POST['title'];
    $instructions = $_POST['instructions'];
    $dueDate = $_POST['deadline']; 
    $maxMarks = $_POST['max_points'];
    $requirements = $_POST['requirements'];
    $gradingCriteria = $_POST['grading_criteria'];
    $visibility = $_POST['visibility'];

    $filePath = null;
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = __DIR__ . '/../../../uploads/assignments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filePath = $uploadDir . basename($_FILES['attachment']['name']);
        move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath);
    }

    createAssignment($_SESSION['user_id'], $title, $instructions, $dueDate, $requirements, $filePath, $maxMarks, $gradingCriteria, $visibility, $classId);

    $_SESSION['success'] = "Assignment created successfully!";
    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Assignment</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
       
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .form-container { max-width: 800px; margin: 20px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef; }
        .form-header h1 { margin: 0; color: #333; font-size: 1.8rem; }
        .form-section { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e9ecef; }
        .form-section:last-of-type { border-bottom: none; }
        .form-section h3 { color: #333; margin-bottom: 1rem; font-size: 1.25rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 1rem; box-sizing: border-box; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .form-actions { margin-top: 20px; padding-top: 15px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 5px; text-decoration: none; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .required { color: #dc3545; }
        .help-text { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2); }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>Create Assignment</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Assignments</a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-group">
                    <label for="class_id">Select Class <span class="required">*</span></label>
                    <select id="class_id" name="class_id" required>
                        <option value="">Choose a class...</option>
                        <?php foreach ($teacherClasses as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['class_name'] . ' (' . $class['class_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Assignment Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Final Project Report">
                </div>

                <div class="form-group">
                    <label for="instructions">Instructions <span class="required">*</span></label>
                    <textarea id="instructions" name="instructions" required placeholder="Detailed instructions for the assignment..."></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Submission & Requirements</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="deadline">Submission Deadline <span class="required">*</span></label>
                        <input type="datetime-local" id="deadline" name="deadline" required>
                    </div>
                    <div class="form-group">
                        <label for="max_points">Maximum Points <span class="required">*</span></label>
                        <input type="number" id="max_points" name="max_points" value="100" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" placeholder="Special requirements or submission guidelines..."></textarea>
                </div>

                <div class="form-group">
                    <label for="attachment">Attach File/Resource</label>
                    <input type="file" id="attachment" name="attachment">
                </div>
            </div>

            <div class="form-section">
                <h3>Grading & Visibility</h3>
                <div class="form-group">
                    <label for="grading_criteria">Grading Criteria</label>
                    <textarea id="grading_criteria" name="grading_criteria" placeholder="Describe how this assignment will be graded..."></textarea>
                </div>

                <div class="form-group">
                    <label for="visibility">Visibility</label>
                    <select id="visibility" name="visibility">
                        <option value="visible">Visible to Students</option>
                        <option value="hidden">Hidden from Students</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Assignment</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
