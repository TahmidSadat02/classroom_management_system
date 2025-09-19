<?php
require_once __DIR__ . '/Database.php';

function saveGrade($studentId, $classId, $assignmentId, $marks, $grade, $assessmentType, $feedback = null) {
    global $conn;

    $marks = intval($marks);
    $grade = $conn->real_escape_string($grade);
    $assessmentType = $conn->real_escape_string($assessmentType);
    $feedback = $feedback ? $conn->real_escape_string($feedback) : null;

    $sql = "INSERT INTO grades 
                (student_id, class_id, assignment_id, marks, grade, assessment_type, feedback, created_at, updated_at)
            VALUES 
                ($studentId, $classId, " . ($assignmentId ?? "NULL") . ", $marks, '$grade', '$assessmentType', " . ($feedback ? "'$feedback'" : "NULL") . ", NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                marks = VALUES(marks),
                grade = VALUES(grade),
                assessment_type = VALUES(assessment_type),
                feedback = VALUES(feedback),
                updated_at = NOW()";

    return $conn->query($sql);
}

function updateGrade($gradeId, $marks, $grade, $assessmentType, $feedback = null) {
    global $conn;

    $gradeId = intval($gradeId);
    $marks = intval($marks);
    $grade = $conn->real_escape_string($grade);
    $assessmentType = $conn->real_escape_string($assessmentType);
    $feedback = $feedback ? $conn->real_escape_string($feedback) : null;

    $sql = "UPDATE grades 
            SET marks = $marks,
                grade = '$grade',
                assessment_type = '$assessmentType',
                feedback = " . ($feedback ? "'$feedback'" : "NULL") . ",
                updated_at = NOW()
            WHERE id = $gradeId";

    return $conn->query($sql);
}

function getGradesByClass($classId) {
    global $conn;
    $classId = intval($classId);

    $result = $conn->query("SELECT * FROM grades WHERE class_id = $classId");
    $grades = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
    }
    return $grades;
}
