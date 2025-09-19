<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-form-container">
        <div class="admin-form-header">
            <h1>Create User</h1>
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/admin/userController.php">
            <input type="hidden" name="action" value="create">
            
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstName" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
                </div>
                <div class="admin-form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastName" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
                </div>
            </div>

            <div class="admin-form-group">
                <label>Role *</label>
                <select name="role" required id="roleSelect">
                    <option value="">Select Role</option>
                    <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="admin-form-group" id="studentIdGroup" style="display:none;">
                <label>Student ID * <small style="font-weight:normal;color:#666;">Format: YY-XXXXX-X</small></label>
                <div class="admin-form-row">
                    <div class="admin-form-group" style="flex: 1;">
                        <input type="text" name="studentId" id="studentIdInput" placeholder="Leave blank for auto-generation" pattern="^[0-9]{2}-[0-9]{4,5}-[0-9]$" value="<?php echo isset($_POST['studentId']) ? htmlspecialchars($_POST['studentId']) : ''; ?>">
                        <small style="color:#6c757d;">Will be used to generate email (e.g. 25-0001-1@aiub.edu)</small>
                    </div>
                    <div>
                        <label style="margin-bottom: 5px; display: block; font-size: 0.9em;">Semester</label>
                        <select name="semester" id="semesterSelect" style="width: 100px;">
                            <option value="1" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '1') ? 'selected' : ''; ?>>Spring</option>
                            <option value="2" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '2') ? 'selected' : ''; ?>>Summer</option>
                            <option value="3" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '3') ? 'selected' : ''; ?>>Fall</option>
                        </select>
                    </div>
                    <button type="button" onclick="generateStudentId()" class="btn btn-secondary" style="white-space: nowrap;">Auto Generate</button>
                </div>
            </div>

            <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #667eea;">
                <strong>Note:</strong> Email will be automatically generated based on role and name.<br>
                Default password will be set to: <strong>password123</strong><br>
                User can change password after first login.
            </div>
                </div>
                <div class="admin-form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirmPassword" required>
                </div>
            <div class="admin-form-actions">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        </div>
<script>
const roleSelect = document.getElementById('roleSelect');
const studentGroup = document.getElementById('studentIdGroup');

function toggleStudent(){
    if(roleSelect.value === 'student'){ 
        studentGroup.style.display='block'; 
        // Don't require field since we can auto-generate
    } else { 
        studentGroup.style.display='none'; 
        studentGroup.querySelector('input').removeAttribute('required'); 
    }
}

async function generateStudentId() {
    const semester = document.getElementById('semesterSelect').value;
    const input = document.getElementById('studentIdInput');
    
    try {
        const response = await fetch('../../../controllers/admin/generateStudentId.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'semester=' + semester
        });
        const data = await response.json();
        
        if (data.success) {
            input.value = data.studentId;
            input.style.borderColor = '#28a745';
        } else {
            alert('Error generating ID: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error generating student ID');
    }
}

roleSelect.addEventListener('change', toggleStudent);
toggleStudent();
</script>
</body>
</html>
