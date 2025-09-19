<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$courses = getAvailableCourses();
$currentYear = date('Y');
$nextYear = $currentYear + 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Class</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Create class form styles */
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
        .checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .checkbox-group label { display: flex; align-items: center; font-weight: normal; }
        .checkbox-group input[type="checkbox"] { width: auto; margin-right: 8px; }
        .required { color: #dc3545; }
        .help-text { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2); }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>Create New Class</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Classes</a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/teacher/classController.php">
            <input type="hidden" name="action" value="create">

            <div class="form-section">
                <h3>Basic Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="className">Class Name <span class="required">*</span></label>
                        <input type="text" id="className" name="className" required 
                               placeholder="e.g., Web Development Fundamentals" maxlength="100"
                               value="<?php echo isset($_POST['className']) ? htmlspecialchars($_POST['className']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="classCode">Class Code <span class="required">*</span></label>
                        <input type="text" id="classCode" name="classCode" required 
                               placeholder="e.g., CSE101-01" maxlength="20"
                               value="<?php echo isset($_POST['classCode']) ? htmlspecialchars($_POST['classCode']) : ''; ?>">
                        <div class="help-text">Unique identifier for this class section</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" 
                              placeholder="Brief description of the class..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Course & Term Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="courseId">Course (Optional)</label>
                        <select id="courseId" name="courseId">
                            <option value="">No Course Assigned</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"
                                    <?php echo (isset($_POST['courseId']) && $_POST['courseId'] == $course['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="maxStudents">Max Students</label>
                        <input type="number" id="maxStudents" name="maxStudents" min="1" max="100" value="30">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="semester">Semester <span class="required">*</span></label>
                        <select id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="Spring">Spring</option>
                            <option value="Summer">Summer</option>
                            <option value="Fall">Fall</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="academicYear">Academic Year <span class="required">*</span></label>
                        <select id="academicYear" name="academicYear" required>
                            <option value="">Select Academic Year</option>
                            <option value="<?php echo $currentYear; ?>"><?php echo $currentYear; ?></option>
                            <option value="<?php echo $nextYear; ?>" selected><?php echo $nextYear; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Schedule</h3>
                
                <div class="form-group">
                    <label>Class Days</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="days[]" value="Monday"> Monday</label>
                        <label><input type="checkbox" name="days[]" value="Tuesday"> Tuesday</label>
                        <label><input type="checkbox" name="days[]" value="Wednesday"> Wednesday</label>
                        <label><input type="checkbox" name="days[]" value="Thursday"> Thursday</label>
                        <label><input type="checkbox" name="days[]" value="Friday"> Friday</label>
                        <label><input type="checkbox" name="days[]" value="Saturday"> Saturday</label>
                        <label><input type="checkbox" name="days[]" value="Sunday"> Sunday</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time">
                    </div>
                </div>

                <div class="form-group">
                    <label for="room">Room/Location</label>
                    <input type="text" id="room" name="room" placeholder="e.g., Room 101, Online">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Class</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
