<?php
session_start();


require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/Course.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$teachers = getAllTeachers();
$departments = getAllDepartments();
$currentYear = date('Y');
$nextYear = $currentYear + 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Course</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

        .admin-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .admin-form-header h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section:last-of-type {
            border-bottom: none;
        }
        .form-section h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #495057;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .form-actions {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .required { color: #dc3545; }
        .help-text { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2); }
    </style>
</head>
<body>
    <div class="admin-form-container">
        <div class="admin-form-header">
            <h1>Create New Course</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Courses</a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/admin/courseController.php">
            <input type="hidden" name="action" value="create">

            <div class="form-section">
                <h3>Basic Course Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="courseCode">Course Code <span class="required">*</span></label>
                        <input type="text" id="courseCode" name="courseCode" required 
                               placeholder="e.g., CSE101" maxlength="20"
                               value="<?php echo isset($_POST['courseCode']) ? htmlspecialchars($_POST['courseCode']) : ''; ?>">
                        <div class="help-text">Unique identifier for the course (e.g., CSE101, MATH201)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="credits">Credits <span class="required">*</span></label>
                        <select id="credits" name="credits" required>
                            <option value="1">1 Credit</option>
                            <option value="2">2 Credits</option>
                            <option value="3" selected>3 Credits</option>
                            <option value="4">4 Credits</option>
                            <option value="5">5 Credits</option>
                            <option value="6">6 Credits</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="courseName">Course Name <span class="required">*</span></label>
                    <input type="text" id="courseName" name="courseName" required 
                           placeholder="e.g., Introduction to Computer Science" maxlength="100"
                           value="<?php echo isset($_POST['courseName']) ? htmlspecialchars($_POST['courseName']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="courseDescription">Course Description</label>
                    <textarea id="courseDescription" name="courseDescription" 
                              placeholder="Brief description of the course content, objectives, and learning outcomes..."><?php echo isset($_POST['courseDescription']) ? htmlspecialchars($_POST['courseDescription']) : ''; ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Course Classification</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department <span class="required">*</span></label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"
                                    <?php echo (isset($_POST['department']) && $_POST['department'] === $dept) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="semester">Semester <span class="required">*</span></label>
                        <select id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="Spring">Spring</option>
                            <option value="Summer">Summer</option>
                            <option value="Fall">Fall</option>
                        </select>
                    </div>
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

            <div class="form-section">
                <h3>Teacher Assignment (Optional)</h3>
                
                <div class="form-group">
                    <label for="teacherId">Assign Teacher</label>
                    <select id="teacherId" name="teacherId">
                        <option value="">No Teacher Assigned</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>"
                                <?php echo (isset($_POST['teacherId']) && $_POST['teacherId'] == $teacher['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($teacher['full_name']) . ' (' . htmlspecialchars($teacher['email']) . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">You can assign a teacher now or later from the courses list</div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Course</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        
        document.getElementById('department').addEventListener('change', function() {
            const courseCodeInput = document.getElementById('courseCode');
            if (courseCodeInput.value === '') {
                const dept = this.value;
                let prefix = '';
                
                switch(dept) {
                    case 'Computer Science': prefix = 'CSE'; break;
                    case 'Business Administration': prefix = 'BBA'; break;
                    case 'Engineering': prefix = 'ENG'; break;
                    case 'Mathematics': prefix = 'MATH'; break;
                    case 'Physics': prefix = 'PHY'; break;
                    case 'Chemistry': prefix = 'CHEM'; break;
                    default: prefix = dept.substring(0, 3).toUpperCase();
                }
                
                if (prefix) {
                    courseCodeInput.placeholder = `e.g., ${prefix}101, ${prefix}201`;
                }
            }
        });
    </script>
</body>
</html>
