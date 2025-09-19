<?php
require_once __DIR__ . '/Database.php';


function generateStudentEmail($studentId, $department = null)
{
    $domain = "aiub.edu";


    return $studentId . "@" . $domain;
}

function generateTeacherEmail($firstName, $lastName, $department = null)
{
    $domain = "aiub.edu";
    $firstName = strtolower(trim($firstName));
    $lastName = strtolower(trim($lastName));


    return $firstName . "." . $lastName . "@" . $domain;
}

function generateAdminEmail($role, $name)
{
    $domain = "aiub.edu";
    $role = strtolower(trim($role));
    $name = strtolower(trim($name));

    return $role . "." . $name . "@" . $domain;
}

function generateUniqueEmail($userType, $userData)
{
    global $conn;

    switch ($userType) {
        case 'student':
            $baseEmail = generateStudentEmail($userData['studentId'], $userData['department']);
            break;
        case 'teacher':
            $baseEmail = generateTeacherEmail($userData['firstName'], $userData['lastName'], $userData['department']);
            break;
        case 'admin':
            $baseEmail = generateAdminEmail('admin', $userData['lastName']);
            break;
        default:
            return false;
    }


    $email = $baseEmail;
    $counter = 1;

    while (emailExistsByEmail($email)) {
        $emailParts = explode('@', $baseEmail);
        $email = $emailParts[0] . $counter . '@' . $emailParts[1];
        $counter++;
    }

    return $email;
}

function emailExistsByEmail($email)
{
    global $conn;
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}
