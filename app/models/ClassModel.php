<?php
require_once __DIR__ . '/Database.php';


function getTeacherClasses($teacherId) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM classes WHERE teacher_id = $teacherId ORDER BY created_at DESC");
    
    $classes = [];
    if ($result) {
        while ($class = $result->fetch_assoc()) {
            if ($class['course_id']) {
                $courseResult = $conn->query("SELECT course_name, course_code FROM courses WHERE id = " . $class['course_id']);
                if ($courseResult && $course = $courseResult->fetch_assoc()) {
                    $class['course_name'] = $course['course_name'];
                    $class['course_code_ref'] = $course['course_code'];
                }
            }
            
            $enrollResult = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE class_id = " . $class['id'] . " AND status = 'enrolled'");
            if ($enrollResult && $enrollRow = $enrollResult->fetch_assoc()) {
                $class['enrolled_count'] = $enrollRow['count'];
            } else {
                $class['enrolled_count'] = 0;
            }
            
            $classes[] = $class;
        }
    }
    
    return $classes;
}

function createNewClass($classData) {
    global $conn;
    
    $className = $classData['className'];
    $classCode = $classData['classCode'];
    $description = $classData['description'];
    $teacherId = $classData['teacherId'];
    $semester = $classData['semester'];
    $academicYear = $classData['academicYear'];
    $maxStudents = $classData['maxStudents'];
    $courseId = !empty($classData['courseId']) ? $classData['courseId'] : 'NULL';
    $schedule = !empty($classData['schedule']) ? "'" . json_encode($classData['schedule']) . "'" : "'{}'";
    
    $result = $conn->query("INSERT INTO classes (class_name, class_code, description, teacher_id, course_id, semester, academic_year, schedule, max_students) VALUES ('$className', '$classCode', '$description', $teacherId, $courseId, '$semester', '$academicYear', $schedule, $maxStudents)");
    
    return $result ? $conn->insert_id : false;
}

function getClassById($classId) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM classes WHERE id = $classId");
    
    if ($result && $result->num_rows > 0) {
        $class = $result->fetch_assoc();
        
        // Initialize course_name to null by default
        $class['course_name'] = null;
        $class['course_code_ref'] = null;
        
        if (!empty($class['course_id'])) {
            $courseResult = $conn->query("SELECT course_name, course_code FROM courses WHERE id = " . $class['course_id']);
            if ($courseResult && $course = $courseResult->fetch_assoc()) {
                $class['course_name'] = $course['course_name'];
                $class['course_code_ref'] = $course['course_code'];
            }
        }
        
        $enrollResult = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE class_id = " . $class['id'] . " AND status = 'enrolled'");
        if ($enrollResult && $enrollRow = $enrollResult->fetch_assoc()) {
            $class['enrolled_count'] = $enrollRow['count'];
        } else {
            $class['enrolled_count'] = 0;
        }
        
        return $class;
    }
    
    return null;
}

function getAvailableStudents($classId) {
    global $conn;
    
    $result = $conn->query("SELECT id, first_name, last_name, email FROM users WHERE user_type = 'student'");
    $availableStudents = [];
    
    if ($result) {
        $enrolledResult = $conn->query("SELECT student_id FROM enrollments WHERE class_id = $classId AND status = 'enrolled'");
        $enrolledIds = [];
        while ($row = $enrolledResult->fetch_assoc()) {
            $enrolledIds[] = $row['student_id'];
        }
        
        while ($student = $result->fetch_assoc()) {
            if (!in_array($student['id'], $enrolledIds)) {
                $availableStudents[] = $student;
            }
        }
    }
    
    return $availableStudents;
}

function getEnrolledStudents($classId) {
    global $conn;
    
    $result = $conn->query("SELECT u.id, u.first_name, u.last_name, u.email, e.enrollment_date FROM users u JOIN enrollments e ON u.id = e.student_id WHERE e.class_id = $classId AND e.status = 'enrolled'");
    
    $enrolledStudents = [];
    if ($result) {
        while ($student = $result->fetch_assoc()) {
            $student['status'] = 'enrolled';
            $enrolledStudents[] = $student;
        }
    }
    
    return $enrolledStudents;
}

function enrollStudentInClass($classId, $studentId) {
    global $conn;
    
    $class = getClassById($classId);
    if ($class && $class['enrolled_count'] >= $class['max_students']) {
        return false;
    }
    
    return $conn->query("INSERT INTO enrollments (class_id, student_id) VALUES ($classId, $studentId) ON DUPLICATE KEY UPDATE status = 'enrolled', enrollment_date = CURRENT_TIMESTAMP");
}

function removeStudentFromClass($classId, $studentId) {
    global $conn;
    
    return $conn->query("UPDATE enrollments SET status = 'dropped' WHERE class_id = $classId AND student_id = $studentId");
}

function getAvailableCourses() {
    global $conn;
    
    $result = $conn->query("SELECT id, course_code, course_name FROM courses WHERE status = 'active' ORDER BY course_code");
    
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function updateClassData($classId, $classData) {
    global $conn;
    
    $className = $classData['className'];
    $description = $classData['description'];
    $semester = $classData['semester'];
    $academicYear = $classData['academicYear'];
    $maxStudents = $classData['maxStudents'];
    $status = $classData['status'];
    $courseId = !empty($classData['courseId']) ? $classData['courseId'] : 'NULL';
    $schedule = !empty($classData['schedule']) ? "'" . json_encode($classData['schedule']) . "'" : "'{}'";
    
    return $conn->query("UPDATE classes SET class_name = '$className', description = '$description', course_id = $courseId, semester = '$semester', academic_year = '$academicYear', schedule = $schedule, max_students = $maxStudents, status = '$status', updated_at = CURRENT_TIMESTAMP WHERE id = $classId");
}

function getAllClassesForAdmin() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM classes ORDER BY status ASC, created_at DESC");
    $classes = [];
    
    if ($result) {
        while ($class = $result->fetch_assoc()) {
            if ($class['teacher_id']) {
                $teacherResult = $conn->query("SELECT first_name, last_name FROM users WHERE id = " . $class['teacher_id']);
                if ($teacherResult && $teacher = $teacherResult->fetch_assoc()) {
                    $class['teacher_first_name'] = $teacher['first_name'];
                    $class['teacher_last_name'] = $teacher['last_name'];
                } else {
                    $class['teacher_first_name'] = 'Unknown';
                    $class['teacher_last_name'] = '';
                }
            }
            
            if ($class['course_id']) {
                $courseResult = $conn->query("SELECT course_name FROM courses WHERE id = " . $class['course_id']);
                $class['course_name'] = ($courseResult && $course = $courseResult->fetch_assoc()) ? $course['course_name'] : null;
            } else {
                $class['course_name'] = null;
            }
            
            $enrollResult = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE class_id = " . $class['id'] . " AND status = 'enrolled'");
            $class['enrolled_count'] = ($enrollResult && $enrollRow = $enrollResult->fetch_assoc()) ? $enrollRow['count'] : 0;
            
            $activityResult = $conn->query("SELECT COUNT(*) as count FROM class_activities WHERE class_id = " . $class['id'] . " AND DATE(created_at) = CURDATE()");
            $class['today_activities'] = ($activityResult && $activityRow = $activityResult->fetch_assoc()) ? $activityRow['count'] : 0;
            
            $classes[] = $class;
        }
    }
    
    return $classes;
}

function getClassActivities($classId, $limit = 10) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM class_activities WHERE class_id = $classId ORDER BY created_at DESC LIMIT $limit");
    $activities = [];
    
    if ($result) {
        while ($activity = $result->fetch_assoc()) {
            $userResult = $conn->query("SELECT first_name, last_name, user_type FROM users WHERE id = " . $activity['user_id']);
            if ($userResult && $user = $userResult->fetch_assoc()) {
                $activity['first_name'] = $user['first_name'];
                $activity['last_name'] = $user['last_name'];
                $activity['user_type'] = $user['user_type'];
            }
            $activities[] = $activity;
        }
    }
    
    return $activities;
}

function getClassDiscussions($classId, $limit = 10) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM class_discussions WHERE class_id = $classId ORDER BY created_at DESC LIMIT $limit");
    $discussions = [];
    
    if ($result) {
        while ($discussion = $result->fetch_assoc()) {
            $userResult = $conn->query("SELECT first_name, last_name, user_type FROM users WHERE id = " . $discussion['user_id']);
            if ($userResult && $user = $userResult->fetch_assoc()) {
                $discussion['first_name'] = $user['first_name'];
                $discussion['last_name'] = $user['last_name'];
                $discussion['user_type'] = $user['user_type'];
            }
            $discussions[] = $discussion;
        }
    }
    
    return $discussions;
}


function adminUpdateClassSettings($classId, $settings) {
    global $conn;
    
    if (empty($settings) || empty($classId)) {
        return false;
    }
    
    $updateFields = [];
    $updateValues = [];
    
    if (isset($settings['status'])) {
        $updateFields[] = "status = ?";
        $updateValues[] = $settings['status'];
    }
    
    if (isset($settings['max_students'])) {
        $updateFields[] = "max_students = ?";
        $updateValues[] = (int)$settings['max_students'];
    }
    
    if (empty($updateFields)) {
        return false;
    }
    
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    $updateValues[] = $classId;
    
    $sql = "UPDATE classes SET " . implode(', ', $updateFields) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    // Create type string for bind_param
    $types = str_repeat('s', count($updateValues) - 1) . 'i'; // All strings except classId which is int
    
    $stmt->bind_param($types, ...$updateValues);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

function logAdminActivity($classId, $adminId, $description) {
    global $conn;
    
    return $conn->query("INSERT INTO class_activities (class_id, user_id, activity_type, activity_description) VALUES ($classId, $adminId, 'admin_action', '$description')");
}

function getAllAttendanceRecords($limit = 50, $classId = null, $date = null) {
    global $conn;
    
    $whereClause = "WHERE 1=1";
    if ($classId) $whereClause .= " AND a.class_id = $classId";
    if ($date) $whereClause .= " AND a.date = '$date'";
    
    $result = $conn->query("SELECT a.*, c.class_name, c.class_code FROM attendance a JOIN classes c ON a.class_id = c.id $whereClause ORDER BY a.date DESC, c.class_name LIMIT $limit");
    
    $records = [];
    if ($result) {
        while ($record = $result->fetch_assoc()) {
            $studentResult = $conn->query("SELECT first_name, last_name, email FROM users WHERE id = " . $record['student_id']);
            if ($studentResult && $student = $studentResult->fetch_assoc()) {
                $record['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $record['student_email'] = $student['email'];
            } else {
                $record['student_name'] = 'Unknown Student';
                $record['student_email'] = 'N/A';
            }
            
            $teacherResult = $conn->query("SELECT first_name, last_name FROM users WHERE id = " . $record['marked_by']);
            if ($teacherResult && $teacher = $teacherResult->fetch_assoc()) {
                $record['marked_by_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
            } else {
                $record['marked_by_name'] = 'Unknown Teacher';
            }
            
            $records[] = $record;
        }
    }
    
    return $records;
}

function getAttendanceStats() {
    global $conn;
    
    $stats = ['total_records' => 0, 'present_today' => 0, 'absent_today' => 0, 'late_today' => 0, 'classes_with_attendance' => 0];
    $today = date('Y-m-d');
    
    $result = $conn->query("SELECT COUNT(*) as count FROM attendance");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_records'] = $row['count'];
    }
    
    $statuses = ['present', 'absent', 'late'];
    foreach ($statuses as $status) {
        $result = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = '$status'");
        if ($result && $row = $result->fetch_assoc()) {
            $stats[$status . '_today'] = $row['count'];
        }
    }
    
    $result = $conn->query("SELECT COUNT(DISTINCT class_id) as count FROM attendance WHERE date = '$today'");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['classes_with_attendance'] = $row['count'];
    }
    
    return $stats;
}

function getClassAttendanceReport($classId, $startDate = null, $endDate = null) {
    global $conn;
    
    $whereClause = "WHERE a.class_id = $classId";
    if ($startDate) $whereClause .= " AND a.date >= '$startDate'";
    if ($endDate) $whereClause .= " AND a.date <= '$endDate'";
    
    $result = $conn->query("SELECT a.*, u.first_name, u.last_name, u.email FROM attendance a JOIN users u ON a.student_id = u.id $whereClause ORDER BY a.date DESC, u.last_name");
    
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function overrideAttendanceRecord($attendanceId, $newStatus, $notes, $adminId) {
    global $conn;
    
    return $conn->query("UPDATE attendance SET status = '$newStatus', notes = '$notes', marked_by = $adminId, updated_at = CURRENT_TIMESTAMP WHERE id = $attendanceId");
}

function deleteAttendanceRecord($attendanceId, $adminId) {
    global $conn;
    
    $conn->query("INSERT INTO class_activities (class_id, activity_type, activity_description, created_by) SELECT class_id, 'attendance_deleted', CONCAT('Deleted attendance record ID: ', id, ' for student ID: ', student_id, ' on date: ', date), $adminId FROM attendance WHERE id = $attendanceId");
    
    return $conn->query("DELETE FROM attendance WHERE id = $attendanceId");
}


function getAttendancePolicies() {
    global $conn;
    
    $sql = "SELECT * FROM attendance_policies ORDER BY is_active DESC, policy_name";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


function updateAttendancePolicy($policyId, $data) {
    global $conn;
    
    $sql = "UPDATE attendance_policies SET 
            policy_name = '{$data['policy_name']}',
            min_attendance_percentage = {$data['min_attendance_percentage']},
            late_threshold_minutes = {$data['late_threshold_minutes']},
            excused_limit = {$data['excused_limit']},
            policy_description = '{$data['policy_description']}',
            is_active = {$data['is_active']},
            updated_at = CURRENT_TIMESTAMP
            WHERE id = $policyId";
    
    return $conn->query($sql);
}


function getAttendanceSettings() {
    global $conn;
    
    $sql = "SELECT * FROM attendance_settings ORDER BY setting_key";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


function updateAttendanceSetting($settingKey, $settingValue) {
    global $conn;
    
    $sql = "UPDATE attendance_settings SET 
            setting_value = '$settingValue',
            updated_at = CURRENT_TIMESTAMP
            WHERE setting_key = '$settingKey'";
    
    return $conn->query($sql);
}


function generateAttendanceReportData($startDate, $endDate, $classId = null) {
    global $conn;
    
    $whereClause = "WHERE a.date BETWEEN '$startDate' AND '$endDate'";
    if ($classId) {
        $whereClause .= " AND a.class_id = $classId";
    }
    
    $sql = "SELECT 
                c.class_name,
                c.class_code,
                u.first_name,
                u.last_name,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_days,
                ROUND((SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
            FROM attendance a
            JOIN classes c ON a.class_id = c.id
            JOIN users u ON a.student_id = u.id
            $whereClause
            GROUP BY a.class_id, a.student_id
            ORDER BY c.class_name, u.last_name";
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getTeacherContent($teacherId, $classId = null) {
    global $conn;
    
    $whereClass = $classId ? " AND class_id = $classId" : "";
    $result = $conn->query("SELECT * FROM content_materials WHERE teacher_id = $teacherId $whereClass ORDER BY created_at DESC");
    
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getContentById($contentId) {
    global $conn;
    
    $result = $conn->query("SELECT * FROM content_materials WHERE id = $contentId");
    
    return $result ? $result->fetch_assoc() : null;
}

function createContentData($teacherId, $data) {
    global $conn;
    
    $classId = (int)($data['class_id'] ?? 0);
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description'] ?? '');
    $topic = $conn->real_escape_string($data['topic'] ?? '');
    $visibility = $data['visibility'] ?? 'public';
    $fileType = $data['file_type'] ?? 'document';
    $fileName = $data['file_name'] ?? '';
    $filePath = $data['file_path'] ?? '';
    $fileSize = (int)($data['file_size'] ?? 0);
    $availableFrom = !empty($data['available_from']) ? "'" . $data['available_from'] . "'" : "NULL";
    $availableUntil = !empty($data['available_until']) ? "'" . $data['available_until'] . "'" : "NULL";
    
    return $conn->query("INSERT INTO content_materials (teacher_id, class_id, title, description, topic, visibility, file_type, file_name, file_path, file_size, available_from, available_until) VALUES ($teacherId, $classId, '$title', '$description', '$topic', '$visibility', '$fileType', '$fileName', '$filePath', $fileSize, $availableFrom, $availableUntil)");
}

function updateContentData($contentId, $data) {
    global $conn;
    
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description'] ?? '');
    $topic = $conn->real_escape_string($data['topic'] ?? '');
    $visibility = $data['visibility'] ?? 'public';
    $availableFrom = !empty($data['available_from']) ? "'" . $data['available_from'] . "'" : "NULL";
    $availableUntil = !empty($data['available_until']) ? "'" . $data['available_until'] . "'" : "NULL";
    
    return $conn->query("UPDATE content_materials SET title = '$title', description = '$description', topic = '$topic', visibility = '$visibility', available_from = $availableFrom, available_until = $availableUntil, updated_at = CURRENT_TIMESTAMP WHERE id = $contentId");
}

function deleteContent($contentId) {
    global $conn;
    
    $content = getContentById($contentId);
    $result = $conn->query("DELETE FROM content_materials WHERE id = $contentId");
    
    if ($result && $content && !empty($content['file_path']) && file_exists($content['file_path'])) {
        unlink($content['file_path']);
    }
    
    return $result;
}

function getContentTopics($teacherId, $classId = null) {
    global $conn;
    
    $whereClass = $classId ? " AND class_id = $classId" : "";
    $result = $conn->query("SELECT * FROM content_topics WHERE teacher_id = $teacherId $whereClass ORDER BY sort_order, topic_name");
    
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function createTopic($teacherId, $data) {
    global $conn;
    
    $classId = (int)($data['class_id'] ?? 0);
    $topicName = $conn->real_escape_string($data['topic_name']);
    $description = $conn->real_escape_string($data['description'] ?? '');
    $sortOrder = (int)($data['sort_order'] ?? 0);
    
    return $conn->query("INSERT INTO content_topics (teacher_id, class_id, topic_name, description, sort_order) VALUES ($teacherId, $classId, '$topicName', '$description', $sortOrder)");
}

function getContentStats($teacherId) {
    global $conn;
    
    $stats = ['total_materials' => 0, 'public_materials' => 0, 'private_materials' => 0, 'scheduled_materials' => 0, 'total_topics' => 0];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM content_materials WHERE teacher_id = $teacherId");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_materials'] = $row['count'];
    }
    
    $result = $conn->query("SELECT visibility, COUNT(*) as count FROM content_materials WHERE teacher_id = $teacherId GROUP BY visibility");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['visibility'] . '_materials'] = $row['count'];
        }
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM content_topics WHERE teacher_id = $teacherId");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_topics'] = $row['count'];
    }
    
    return $stats;
}
?>
