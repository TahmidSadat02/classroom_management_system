<?php
session_start();
require_once __DIR__ . '/../../models/User.php';


if (isset($_SESSION["user_id"])) {
    $userType = $_SESSION["user_type"];
    
    switch ($userType) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: ../teacher/dashboard.php");
            break;
        case 'student':
            header("Location: ../student/dashboard.php");
            break;
        default:
            
    }
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        Login | Online Classroom Management
    </title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>

<body>
    <div class="container">
        <div class="login-form">
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <form id="loginForm" action="../../controllers/authController.php?action=login" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <select name="userType" id="userType">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <label for="rememberMe">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>


</body>

</html>