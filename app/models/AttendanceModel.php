<?php
require_once __DIR__ . '/Database.php';


function getEnrolledStudents($classId) {
    global $conn;
    $classId = intval($classId);

    $sql = "SELECT u.id, u.first_name, u.last_name, u.email
            FROM users u
            INNER JOIN enrollments e ON u.id = e.student_id
            WHERE e.class_id = $classId AND u.user_type = 'student'";

    $result = $conn->query($sql);
    $students = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    return $students;
}


function saveAttendance($classId, $studentId, $date, $status, $notes, $teacherId) {
    global $conn;

    $status = $conn->real_escape_string($status);
    $notes = $notes ? "'" . $conn->real_escape_string($notes) . "'" : "NULL";

    $sql = "INSERT INTO attendance (class_id, student_id, date, status, notes, marked_by, created_at, updated_at) 
            VALUES ($classId, $studentId, '$date', '$status', $notes, $teacherId, NOW(), NOW())";

    return $conn->query($sql);
}
