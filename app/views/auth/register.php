<!DOCTYPE html>
<html>
<head>
    <title>Register | Online Classroom Management</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <style>
        .strength-meter {
            height: 10px;
            width: 100%;
            background-color: #ddd;
            margin-top: 5px;
            border-radius: 3px;
        }
        .strength-bar {
            height: 10px;
            width: 0%;
            border-radius: 3px;
        }
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php
    
    function checkPasswordStrength($password) {
        $strength = 0;
        $result = [];
        
        
        if (strlen($password) >= 6) {
            $strength += 1;
        }
        if (strlen($password) >= 8) {
            $strength += 1;
        }
        
        
        if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) {
            $strength += 1;
        }
        
        
        if (preg_match('/\d/', $password)) {
            $strength += 1;
        }
        
        
        if (preg_match('/[^a-zA-Z\d]/', $password)) {
            $strength += 1;
        }
        
        
        switch ($strength) {
            case 0:
                $result = [
                    'width' => '0%',
                    'color' => '#ccc',
                    'text' => '',
                    'text_color' => '#000'
                ];
                break;
            case 1:
                $result = [
                    'width' => '20%',
                    'color' => '#ff0000',
                    'text' => 'Very Weak',
                    'text_color' => '#ff0000'
                ];
                break;
            case 2:
                $result = [
                    'width' => '40%',
                    'color' => '#ff8c00',
                    'text' => 'Weak',
                    'text_color' => '#ff8c00'
                ];
                break;
            case 3:
                $result = [
                    'width' => '60%',
                    'color' => '#ffcc00',
                    'text' => 'Medium',
                    'text_color' => '#ffcc00'
                ];
                break;
            case 4:
                $result = [
                    'width' => '80%',
                    'color' => '#4caf50',
                    'text' => 'Strong',
                    'text_color' => '#4caf50'
                ];
                break;
            case 5:
                $result = [
                    'width' => '100%',
                    'color' => '#008000',
                    'text' => 'Very Strong',
                    'text_color' => '#008000'
                ];
                break;
        }
        
        return $result;
    }
    
    
    $password = '';
    $strengthResult = checkPasswordStrength($password);
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_preview'])) {
        $password = $_POST['password'] ?? '';
        $strengthResult = checkPasswordStrength($password);
    }
    ?>
    
    <div class="container">
        <div class="register-form">
            <h2>Register</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <form id="registerForm" action="../../controllers/authController.php?action=register" method="post">
                <div class="form-group">
                    <label for="firstName"> First Name:</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName"> Last Name:</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
                
                
                <div class="form-group">
                    <label>Your University Email:</label>
                    <div id="email-preview" style="background: #e9f7ef; padding: 12px; border: 1px solid #28a745; border-radius: 5px; color: #155724; font-weight: bold;">
                        <em>Please fill in your details to see your university email</em>
                    </div>
                    <small style="color: #6c757d; font-size: 0.85em;">
                        Your email will be automatically generated based on your role and details
                    </small>
                </div>
                
               
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <div class="strength-meter">
                        <div class="strength-bar" style="width: <?php echo $strengthResult['width']; ?>; background-color: <?php echo $strengthResult['color']; ?>;"></div>
                    </div>
                    <div class="strength-text" style="color: <?php echo $strengthResult['text_color']; ?>;">
                        <?php echo $strengthResult['text']; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <div class="form-group">
                    <label for="userType">User Type:</label>
                    <select name="userType" id="userType">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                
                <!-- Student-specific fields -->
                <div id="studentFields" class="form-group" <?php if(isset($_POST['userType']) && $_POST['userType'] !== 'student') echo 'style="display:none;"'; ?>>
                    <label for="studentId">Student ID:</label>
                    <input type="text" id="studentId" name="studentId">
                </div>
                
                <!-- Teacher-specific fields -->
                <div id="teacherFields" class="form-group" <?php if(!isset($_POST['userType']) || $_POST['userType'] !== 'teacher') echo 'style="display:none;"'; ?>>
                    <label for="subjects">Subjects Taught:</label>
                    <input type="text" id="subjects" name="subjects">
                    <br><br>
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department">
                </div>
                
                <button type="submit" class="btn btn-register">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="../../../public/index.php" class="btn" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Return to Home</a></p>
            
            <?php if (isset($_GET['debug'])): ?>
            <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;">
                <h4>Debug Information</h4>
                <p>PHP Version: <?php echo phpversion(); ?></p>
                <p>Extensions: <?php echo implode(', ', get_loaded_extensions()); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <form id="passwordPreviewForm" method="post" style="display:none;">
        <input type="hidden" name="password_preview" value="1">
        <input type="hidden" id="password_copy" name="password" value="">
    </form>
    
    <script>
    
    document.getElementById('password').addEventListener('input', function() {
        
        var password = this.value;
        
        
        document.getElementById('password_copy').value = password;
        
        
        var xhr = new XMLHttpRequest();
        var formData = new FormData(document.getElementById('passwordPreviewForm'));
        
        xhr.open('POST', window.location.href, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                
                
                var parser = new DOMParser();
                var responseDoc = parser.parseFromString(xhr.responseText, 'text/html');
                
                
                var strengthBar = responseDoc.querySelector('.strength-bar');
                var strengthText = responseDoc.querySelector('.strength-text');
                
                if (strengthBar && document.querySelector('.strength-bar')) {
                    document.querySelector('.strength-bar').style.width = strengthBar.style.width;
                    document.querySelector('.strength-bar').style.backgroundColor = strengthBar.style.backgroundColor;
                }
                
                if (strengthText && document.querySelector('.strength-text')) {
                    document.querySelector('.strength-text').textContent = strengthText.textContent;
                    document.querySelector('.strength-text').style.color = strengthText.style.color;
                }
            }
        };
        xhr.send(formData);
    });
    
    
    function updateEmailPreview() {
        var firstName = document.getElementById('firstName').value.trim().toLowerCase();
        var lastName = document.getElementById('lastName').value.trim().toLowerCase();
        var userType = document.getElementById('userType').value;
        var studentId = document.getElementById('studentId') ? document.getElementById('studentId').value.trim() : '';
        var department = document.getElementById('department') ? document.getElementById('department').value.trim().toLowerCase() : '';
        
        var email = '';
        var emailPreview = document.getElementById('email-preview');
        
        if (userType === 'student' && studentId) {
            email = studentId + '@aiub.edu';
            emailPreview.innerHTML = '📧 ' + email;
            emailPreview.style.background = '#e9f7ef';
            emailPreview.style.borderColor = '#28a745';
            emailPreview.style.color = '#155724';
        } else if (userType === 'teacher' && firstName && lastName) {
            email = firstName + '.' + lastName + '@aiub.edu';
            emailPreview.innerHTML = '📧 ' + email;
            emailPreview.style.background = '#e9f7ef';
            emailPreview.style.borderColor = '#28a745';
            emailPreview.style.color = '#155724';
        } else if (userType === 'admin' && lastName) {
            email = 'admin.' + lastName + '@aiub.edu';
            emailPreview.innerHTML = '📧 ' + email;
            emailPreview.style.background = '#e9f7ef';
            emailPreview.style.borderColor = '#28a745';
            emailPreview.style.color = '#155724';
        } else {
            emailPreview.innerHTML = '<em>Please fill in your details to see your university email</em>';
            emailPreview.style.background = '#f8f9fa';
            emailPreview.style.borderColor = '#dee2e6';
            emailPreview.style.color = '#6c757d';
        }
    }
    
    
    document.getElementById('firstName').addEventListener('input', updateEmailPreview);
    document.getElementById('lastName').addEventListener('input', updateEmailPreview);
    document.getElementById('userType').addEventListener('change', updateEmailPreview);
    
    
    var studentIdField = document.getElementById('studentId');
    if (studentIdField) {
        studentIdField.addEventListener('input', updateEmailPreview);
    }
    
    
    var departmentField = document.getElementById('department');
    if (departmentField) {
        departmentField.addEventListener('input', updateEmailPreview);
    }
    
    
    document.getElementById('userType').addEventListener('change', function() {
        var userType = this.value;
        var studentFields = document.getElementById('studentFields');
        var teacherFields = document.getElementById('teacherFields');
        
        if (userType === 'student') {
            studentFields.style.display = 'block';
            teacherFields.style.display = 'none';
            
            setTimeout(function() {
                var studentIdField = document.getElementById('studentId');
                if (studentIdField) {
                    studentIdField.addEventListener('input', updateEmailPreview);
                }
            }, 100);
        } else if (userType === 'teacher') {
            studentFields.style.display = 'none';
            teacherFields.style.display = 'block';
            
            setTimeout(function() {
                var departmentField = document.getElementById('department');
                if (departmentField) {
                    departmentField.addEventListener('input', updateEmailPreview);
                }
            }, 100);
        } else {
            studentFields.style.display = 'none';
            teacherFields.style.display = 'none';
        }
        
        updateEmailPreview(); 
        
        if (userType === 'student') {
            studentFields.style.display = 'block';
            teacherFields.style.display = 'none';
        } else if (userType === 'teacher') {
            studentFields.style.display = 'none';
            teacherFields.style.display = 'block';
        }
    });
    </script>
</body>
</html>