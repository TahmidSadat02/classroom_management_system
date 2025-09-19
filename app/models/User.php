<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EmailGenerator.php';

function registerUser($userData) {
    global $conn;
    
    $email = generateUniqueEmail($userData['userType'], $userData);
    if (!$email) return false;
    
    $password = password_hash($userData['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) 
            VALUES ('{$userData['firstName']}', '{$userData['lastName']}', '$email', '$password', '{$userData['userType']}')";
    
    if ($conn->query($sql)) {
        return ['user_id' => $conn->insert_id, 'email' => $email];
    }
    return false;
}

function authenticateUser($email, $password, $userType) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND user_type = '$userType'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) return $user;
    }
    return false;
}

function emailExists($email) {
    global $conn;
    return $conn->query("SELECT id FROM users WHERE email = '$email'")->num_rows > 0;
}

function getUserById($id) {
    global $conn;
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}


function getAllUsers() {
    global $conn;
    $result = $conn->query("SELECT id, first_name, last_name, email, user_type, created_at FROM users ORDER BY created_at DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function createUserByAdmin($userData) {
    global $conn;
    
    $email = generateUniqueEmail($userData['role'], $userData);
    if (!$email) return false;
    
    $defaultPassword = 'password123';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (first_name, last_name, email, password, user_type, created_at) 
            VALUES ('{$userData['firstName']}', '{$userData['lastName']}', '$email', '$hashedPassword', '{$userData['role']}', NOW())";
    
    if ($conn->query($sql)) {
        return ['user_id' => $conn->insert_id, 'email' => $email, 'password' => $defaultPassword];
    }
    return false;
}

function updateUserByAdmin($userId, $userData) {
    global $conn;
    
    $sql = "UPDATE users SET 
            first_name = '{$userData['firstName']}', 
            last_name = '{$userData['lastName']}', 
            user_type = '{$userData['role']}',
            updated_at = NOW()
            WHERE id = $userId";
    
    return $conn->query($sql);
}

function deleteUserByAdmin($userId) {
    global $conn;
    
    $result = $conn->query("SELECT id FROM users WHERE id = $userId");
    if ($result->num_rows === 0) return false;
    
    return $conn->query("DELETE FROM users WHERE id = $userId");
}

function generateNextStudentId($semester = null) {
    global $conn;
    
    $currentYear = date('y'); // Get last 2 digits of year (e.g., 25 for 2025)
    
    // Auto-detect semester if not provided
    if (!$semester) {
        $month = (int)date('n'); // 1-12
        if ($month >= 1 && $month <= 4) {
            $semester = 1; // Spring
        } elseif ($month >= 5 && $month <= 8) {
            $semester = 2; // Summer  
        } else {
            $semester = 3; // Fall
        }
    }
    
    // Find highest student number for this year and semester
    $pattern = $currentYear . '-%-' . $semester . '@aiub.edu';
    $result = $conn->query("SELECT email FROM users WHERE user_type = 'student' AND email LIKE '$pattern' ORDER BY email DESC LIMIT 1");
    
    $nextNumber = 1;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        // Extract number from email like "25-0001-1@aiub.edu"
        if (preg_match('/(\d{2})-(\d+)-(\d)@/', $email, $matches)) {
            $nextNumber = (int)$matches[2] + 1;
        }
    }
    
    // Format: XX-XXXX-X (pad number to 4 digits)
    return sprintf('%02d-%04d-%d', $currentYear, $nextNumber, $semester);
}
?>