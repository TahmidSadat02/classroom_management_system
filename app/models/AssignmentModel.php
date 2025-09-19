<?php
require_once __DIR__ . '/Database.php';
function getTeacherAssignments($teacherId, $classId = null) {
    global $conn;
    
    $whereClass = $classId ? " AND class_id = $classId" : "";
    $sql = "SELECT * FROM assignments WHERE teacher_id = $teacherId $whereClass ORDER BY due_date DESC, created_at DESC";
    $result = $conn->query($sql);

    $assignments = [];
    if ($result) {
        while ($assignment = $result->fetch_assoc()) {
            //class er name ar code gulo nibe
            if ($assignment['class_id']) {
                $classSql = "SELECT class_name, class_code FROM classes WHERE id = " . $assignment['class_id'];
                $classResult = $conn->query($classSql);
                if ($classResult && $class = $classResult->fetch_assoc()) {
                    $assignment['class_name'] = $class['class_name'];
                    $assignment['class_code'] = $class['class_code'];
                } else {
                    $assignment['class_name'] = null;
                    $assignment['class_code'] = null;
                }
            } else {
                $assignment['class_name'] = null;
                $assignment['class_code'] = null;
            }

            //submission count nibe
            $subSql = "SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = " . $assignment['id'];
            $subResult = $conn->query($subSql);
            if ($subResult && $subRow = $subResult->fetch_assoc()) {
                $assignment['submission_count'] = $subRow['count'];
            } else {
                $assignment['submission_count'] = 0;
            }

            $assignments[] = $assignment;
        }
    }

    return $assignments;
}
//notun assignment create korar jonno, eta teacher korbe..
function createAssignment($teacherId, $title, $instructions, $deadline, $requirements, $filePath, $maxPoints, $gradingCriteria, $visibility, $classId = null) {
    global $conn;

    $title = $conn->real_escape_string($title);
    $instructions = $conn->real_escape_string($instructions ?? '');
    $requirements = $conn->real_escape_string($requirements ?? '');
    $gradingCriteria = $conn->real_escape_string($gradingCriteria ?? '');
    $visibility = $conn->real_escape_string($visibility ?? 'visible');
    $filePath = $filePath ? $conn->real_escape_string(basename($filePath)) : null;

    $dueDate = !empty($deadline) ? "'" . $conn->real_escape_string($deadline) . "'" : "NULL";
    $classId = $classId ? (int)$classId : "NULL";
    $maxPoints = (int)$maxPoints;

    $sql = "INSERT INTO assignments 
            (teacher_id, class_id, title, description, due_date, max_marks, attachment, grading_criteria, visibility) 
            VALUES 
            ($teacherId, $classId, '$title', '$instructions', $dueDate, $maxPoints, " . 
            ($filePath ? "'$filePath'" : "NULL") . ", 
            '$gradingCriteria', '$visibility')";

    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    return false;
}

function updateAssignmentData($assignmentId, $data) {
    global $conn;

    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description']);
    $dueDate = !empty($data['due_date']) ? "'" . $conn->real_escape_string($data['due_date']) . "'" : "NULL";
    $maxMarks = intval($data['max_marks']);
    $visibility = $conn->real_escape_string($data['visibility']);
    $attachment = !empty($data['attachment']) ? "'" . $conn->real_escape_string($data['attachment']) . "'" : "NULL";

    $sql = "UPDATE assignments
            SET title='$title',
                description='$description',
                due_date=$dueDate,
                max_marks=$maxMarks,
                visibility='$visibility',
                attachment=$attachment,
                updated_at=NOW()
            WHERE id=$assignmentId";

    return $conn->query($sql);
}

function getAssignmentById($id) {
    global $conn;
    $id = intval($id);
    $result = $conn->query("SELECT * FROM assignments WHERE id = $id");
    return $result->fetch_assoc();
}


function getAssignmentWithClass($assignmentId) {
    global $conn;
    $assignmentId = intval($assignmentId);
    
    $sql = "SELECT a.*, c.class_name FROM assignments a 
            JOIN classes c ON a.class_id = c.id 
            WHERE a.id = $assignmentId";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

function checkStudentEnrollment($studentId, $classId) {
    global $conn;
    $studentId = intval($studentId);
    $classId = intval($classId);
    
    $sql = "SELECT * FROM enrollments WHERE student_id = $studentId AND class_id = $classId AND status = 'enrolled'";
    $result = $conn->query($sql);
    
    return $result && $result->num_rows > 0;
}

function checkExistingSubmission($assignmentId, $studentId) {
    global $conn;
    $assignmentId = intval($assignmentId);
    $studentId = intval($studentId);
    
    $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = $assignmentId AND student_id = $studentId";
    $result = $conn->query($sql);
    
    return $result && $result->num_rows > 0;
}

function submitAssignment($assignmentId, $studentId, $submissionText, $filePath) {
    global $conn;
    
    $assignmentId = intval($assignmentId);
    $studentId = intval($studentId);
    $submissionText = $conn->real_escape_string($submissionText);
    $filePath = $conn->real_escape_string($filePath);
    
    $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, file_path, submitted_at) 
            VALUES ($assignmentId, $studentId, '$submissionText', '$filePath', NOW())";
    
    return $conn->query($sql);
}

function getStudentAssignments($studentId) {
    global $conn;
    $studentId = intval($studentId);
    
    $assignments = [];
    
    
    $classesSql = "SELECT class_id FROM enrollments WHERE student_id = $studentId AND status = 'enrolled'";
    $classesResult = $conn->query($classesSql);
    
    if ($classesResult && $classesResult->num_rows > 0) {
        while ($enrollment = $classesResult->fetch_assoc()) {
            $classId = $enrollment['class_id'];
            
            
            $assignmentsSql = "SELECT * FROM assignments WHERE class_id = $classId AND visibility = 'visible' ORDER BY due_date ASC";
            $assignmentsResult = $conn->query($assignmentsSql);
            
            if ($assignmentsResult && $assignmentsResult->num_rows > 0) {
                while ($assignment = $assignmentsResult->fetch_assoc()) {
                    
                    $classSql = "SELECT class_name FROM classes WHERE id = $classId";
                    $classResult = $conn->query($classSql);
                    
                    if ($classResult && $class = $classResult->fetch_assoc()) {
                        $assignment['class_name'] = $class['class_name'];
                    } else {
                        $assignment['class_name'] = 'Unknown Class';
                    }
                    
                    
                    $assignment['is_submitted'] = checkExistingSubmission($assignment['id'], $studentId);
                    
                    $assignments[] = $assignment;
                }
            }
        }
    }
    
    return $assignments;
}

function getAssignmentSubmissions($assignmentId) {
    global $conn;
    $assignmentId = intval($assignmentId);
    
    $sql = "SELECT 
                s.*,
                CONCAT(u.first_name, ' ', u.last_name) as student_name,
                u.email as student_email,
                u.student_id as student_number
            FROM assignment_submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.assignment_id = $assignmentId
            ORDER BY s.submitted_at DESC";
    
    $result = $conn->query($sql);
    $submissions = [];
    
    if ($result) {
        while ($submission = $result->fetch_assoc()) {
            $submissions[] = $submission;
        }
    } else {
        $simpleSql = "SELECT * FROM assignment_submissions WHERE assignment_id = $assignmentId ORDER BY submitted_at DESC";
        $simpleResult = $conn->query($simpleSql);
        
        if ($simpleResult) {
            while ($submission = $simpleResult->fetch_assoc()) {
                $submission['student_name'] = 'Student ID: ' . $submission['student_id'];
                $submission['student_email'] = 'Unknown';
                $submission['student_number'] = $submission['student_id'];
                $submissions[] = $submission;
            }
        }
    }
    
    return $submissions;
}

function getAssignmentSubmissionStats($assignmentId) {
    global $conn;
    $assignmentId = intval($assignmentId);
    
    
    $assignmentSql = "SELECT a.*, c.class_name, c.id as class_id 
                      FROM assignments a 
                      LEFT JOIN classes c ON a.class_id = c.id 
                      WHERE a.id = $assignmentId";
    $assignmentResult = $conn->query($assignmentSql);
    
    if (!$assignmentResult || $assignmentResult->num_rows === 0) {
        return null;
    }
    
    $assignment = $assignmentResult->fetch_assoc();
    
    
    $totalStudentsSql = "SELECT COUNT(*) as total 
                         FROM enrollments 
                         WHERE class_id = " . $assignment['class_id'] . " AND status = 'enrolled'";
    $totalResult = $conn->query($totalStudentsSql);
    $totalStudents = 0;
    
    if ($totalResult && $row = $totalResult->fetch_assoc()) {
        $totalStudents = $row['total'];
    }
    
    
    $submissionCountSql = "SELECT COUNT(*) as submitted 
                           FROM assignment_submissions 
                           WHERE assignment_id = $assignmentId";
    $submissionResult = $conn->query($submissionCountSql);
    $submittedCount = 0;
    
    if ($submissionResult && $row = $submissionResult->fetch_assoc()) {
        $submittedCount = $row['submitted'];
    }
    
    $assignment['total_students'] = $totalStudents;
    $assignment['submitted_count'] = $submittedCount;
    $assignment['pending_count'] = $totalStudents - $submittedCount;
    
    return $assignment;
}


?>
