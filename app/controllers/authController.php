<?php
session_start();
require_once __DIR__ . '/../models/User.php';


$action = isset($_GET['action']) ? $_GET['action'] : '';


if ($action === 'register') {

    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    $userType = isset($_POST['userType']) ? trim($_POST['userType']) : '';


    $studentId = isset($_POST['studentId']) ? trim($_POST['studentId']) : '';
    $subjects = isset($_POST['subjects']) ? trim($_POST['subjects']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';


    $errors = [];

    if (empty($firstName)) {
        $errors[] = "First name is required";
    }

    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }


    if ($userType === 'student' && empty($studentId)) {
        $errors[] = "Student ID is required for students";
    }

    if ($userType === 'teacher' && empty($department)) {
        $errors[] = "Department is required for teachers";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    if (empty($userType) || !in_array($userType, ['student', 'teacher', 'admin'])) {
        $errors[] = "Invalid user type";
    }


    if (empty($errors)) {
        $userData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'password' => $password,
            'userType' => $userType,
            'studentId' => $studentId,
            'subjects' => $subjects,
            'department' => $department
        ];

        $result = registerUser($userData);

        if ($result && isset($result['user_id'])) {

            $message = "Registration successful! Your university email is: " . $result['email'] . " (copy this email for login)";
            header("Location: ../views/auth/login.php?success=" . urlencode($message));
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }


    if (!empty($errors)) {
        $errorMsg = implode(", ", $errors);
        header("Location: ../views/auth/register.php?error=" . urlencode($errorMsg));
        exit();
    }
} elseif ($action === 'login') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $userType = isset($_POST['userType']) ? trim($_POST['userType']) : '';
    $remember = isset($_POST['rememberMe']) ? true : false;


    $user = authenticateUser($email, $password, $userType);

    if ($user) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];


        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        }


        switch ($user['user_type']) {
            case 'admin':
                header("Location: ../views/admin/dashboard.php");
                break;

            case 'teacher':
                header("Location: ../views/teacher/dashboard.php");
                break;

            case 'student':
                header("Location: ../views/student/dashboard.php");
                break;

            default:
                header("Location: ../views/auth/login.php");
        }
        exit();
    } else {

        header("Location: ../views/auth/login.php?error=" . urlencode("Invalid credentials"));
        exit();
    }
} elseif ($action === 'logout') {

    $_SESSION = [];


    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }


    session_destroy();


    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }


    header("Location: ../views/auth/login.php?success=" . urlencode("You have been logged out"));
    exit();
} else {

    header("Location: ../views/auth/login.php");
    exit();
}
