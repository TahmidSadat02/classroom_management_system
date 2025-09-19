<?php
require_once __DIR__ . '/Database.php';

function getAllCourses() {
    global $conn;
    
    $sql = "SELECT * FROM courses WHERE status = 'active' ORDER BY course_code ASC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    $sql = "SELECT * FROM courses ORDER BY course_code ASC";
    $result = $conn->query($sql);
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}

function getTeacherName($teacherId) {
    global $conn;
    
    if (!$teacherId) {
        return null;
    }
    
    $sql = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = $teacherId";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['full_name'];
    }
    
    return null;
}

function createNewCourse($courseData) {
    global $conn;
    
    $courseCode = $courseData['courseCode'];
    $courseName = $courseData['courseName'];
    $courseDescription = $courseData['courseDescription'];
    $credits = $courseData['credits'];
    $department = $courseData['department'];
    $semester = $courseData['semester'];
    $academicYear = $courseData['academicYear'];
    $teacherId = !empty($courseData['teacherId']) ? $courseData['teacherId'] : NULL;
    
    $sql = "INSERT INTO courses (course_code, course_name, course_description, credits, department, semester, academic_year, teacher_id, status) 
            VALUES ('$courseCode', '$courseName', '$courseDescription', $credits, '$department', '$semester', '$academicYear', " . ($teacherId ? $teacherId : 'NULL') . ", 'active')";
    
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    
    return false;
}

function getCourseById($courseId) {
    global $conn;
    
    $sql = "SELECT * FROM courses WHERE id = $courseId";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

function getAllTeachers() {
    global $conn;
    
  
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        return [];
    }
    
    $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name, email 
            FROM users 
            WHERE user_type = 'teacher'
            ORDER BY first_name ASC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}


function getAllDepartments() {
    global $conn;
    
    $sql = "SELECT DISTINCT department FROM courses WHERE department IS NOT NULL AND department != '' ORDER BY department ASC";
    
    $result = $conn->query($sql);
    $departments = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row['department'];
        }
    }
    

    if (empty($departments)) {
        $departments = ['Computer Science', 'Business Administration', 'Engineering', 'Mathematics', 'Physics', 'Chemistry'];
    }
    
    return $departments;
}

?>
