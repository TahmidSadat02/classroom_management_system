<?php
require_once __DIR__ . '/../../models/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
        
        if ($semester < 1 || $semester > 3) {
            throw new Exception('Invalid semester selection');
        }
        
        $studentId = generateNextStudentId($semester);
        
        echo json_encode([
            'success' => true,
            'studentId' => $studentId
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>