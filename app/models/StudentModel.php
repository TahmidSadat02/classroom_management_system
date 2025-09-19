<?php
require_once __DIR__ . '/Database.php';

function getStudentClasses($studentId) {
    global $conn;
    
    $result = $conn->query("SELECT c.*, e.enrollment_date, e.status as enrollment_status, u.first_name as teacher_first_name, u.last_name as teacher_last_name FROM enrollments e JOIN classes c ON e.class_id = c.id LEFT JOIN users u ON c.teacher_id = u.id WHERE e.student_id = '$studentId' AND e.status = 'enrolled'");
    
    $classes = array();
    if ($result->num_rows > 0) {
        while ($class = $result->fetch_assoc()) {
            if (!isset($class['teacher_first_name'])) $class['teacher_first_name'] = '';
            if (!isset($class['teacher_last_name'])) $class['teacher_last_name'] = '';
            $classes[] = $class;
        }
    }
    return $classes;
}

function getStudentStats($studentId) {
    global $conn;
    
    $stats = array();
    
    
    $result = $conn->query("SELECT COUNT(*) as total FROM enrollments WHERE student_id = '$studentId' AND status = 'enrolled'");
    if ($result->num_rows > 0) {
        $stats['total_classes'] = $result->fetch_assoc()['total'];
    } else {
        $stats['total_classes'] = 0;
    }
    
    
    $stats['total_assignments'] = 0;
    $stats['pending_assignments'] = 0;
    $stats['submitted_assignments'] = 0;
    $stats['total_materials'] = 0;
    $stats['available_materials'] = 0;
    
    return $stats;
}

function getStudentMaterials($studentId) {
    global $conn;
    
    $result = $conn->query("SELECT cm.*, c.class_name FROM content_materials cm JOIN classes c ON cm.class_id = c.id JOIN enrollments e ON c.id = e.class_id WHERE e.student_id = '$studentId' AND e.status = 'enrolled' AND cm.visibility = 'public'");
    
    $materials = array();
    if ($result->num_rows > 0) {
        while ($material = $result->fetch_assoc()) {
            $materials[] = $material;
        }
    }
    return $materials;
}

function getRecentMaterials($studentId, $limit = 5)
{
    $materials = getStudentMaterials($studentId);
    return array_slice($materials, 0, $limit);
}
