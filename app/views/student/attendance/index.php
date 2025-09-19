<?php
session_start();

// Check korlam user login kora ase kina & o student kina
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}
// Database lagailam
require_once __DIR__ . '/../../../models/Database.php';

// Attendance related function gula ekhane likhbo
function getStudentAttendance($studentId) {
    global $conn;
    // Attendance records rakhbo ekhane
    $attendance = [];
    
    //student ta kon kon class e enrolled ase seta ber korbo
    $sql = "SELECT class_id FROM enrollments WHERE student_id = $studentId AND status = 'enrolled'";
    $result = $conn->query($sql);

    if ($result) {
        // prottekta enrolled class er jonno attendance records ber korbo
        while ($enrollment = $result->fetch_assoc()) {
            $classId = $enrollment['class_id'];
            
        // class er attendance records gula ber korbo
            $attendanceSql = "SELECT a.*, c.class_name FROM attendance a 
                             JOIN classes c ON a.class_id = c.id 
                             WHERE a.class_id = $classId AND a.student_id = $studentId 
                             ORDER BY a.date DESC";
            $attendanceResult = $conn->query($attendanceSql);
            
            // prottekta record ke attendance array te add korbo
            if ($attendanceResult) {
                while ($record = $attendanceResult->fetch_assoc()) {
                    $attendance[] = $record;
                }
            }
        }
    }
    
    return $attendance;
}

// Attendance summary ber korar jonno function
function getAttendanceSummary($studentId) {
    global $conn;
    
    $summary = [];
    
    //student ta kon kon class e enrolled ase seta ber korbo
    $sql = "SELECT e.class_id, c.class_name FROM enrollments e 
            JOIN classes c ON e.class_id = c.id 
            WHERE e.student_id = $studentId AND e.status = 'enrolled'";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($class = $result->fetch_assoc()) {
            $classId = $class['class_id'];
            
            // oi class er total attendance
            $totalSql = "SELECT COUNT(*) as total FROM attendance WHERE class_id = $classId AND student_id = $studentId";
            $totalResult = $conn->query($totalSql);
            $total = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
            // present count
            $presentSql = "SELECT COUNT(*) as present FROM attendance WHERE class_id = $classId AND student_id = $studentId AND status = 'present'";
            $presentResult = $conn->query($presentSql);
            $present = $presentResult ? $presentResult->fetch_assoc()['present'] : 0;
            //
            $summary[] = [
                'class_name' => $class['class_name'],
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0
            ];
        }
    }
    
    return $summary;
}

$studentId = $_SESSION['user_id'];
$attendance = getStudentAttendance($studentId);
$summary = getAttendanceSummary($studentId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="../assignments/index.php">Assignments</a></li>
                <li><a href="index.php">Attendance</a></li>
                <li><a href="../materials/index.php">Materials</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <div class="page-header">
                <h2>My Attendance</h2>
            </div>

            <!-- Attendance Summary -->
            <div class="dashboard-stats">
                <h3>Attendance Summary</h3>
                <div class="stats-grid">
                    <?php if (empty($summary)): ?>
                        <div class="stat-card">
                            <div class="stat-info">
                                <h4>No Data</h4>
                                <p>No attendance records found</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($summary as $classSummary): ?>
                            <div class="stat-card">
                                <div class="stat-info">
                                    <h4><?php echo htmlspecialchars($classSummary['class_name']); ?></h4>
                                    <p><strong><?php echo $classSummary['percentage']; ?>%</strong> attendance</p>
                                    <small><?php echo $classSummary['present']; ?> present, <?php echo $classSummary['absent']; ?> absent</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detailed Attendance Records -->
            <div class="users-table-container">
                <h3>Attendance Records</h3>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #6c757d;">
                                    No attendance records found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attendance as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                    <td>
                                        <?php if ($record['status'] === 'present'): ?>
                                            <span class="role admin">Present</span>
                                        <?php else: ?>
                                            <span class="role teacher">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($record['remarks']) && $record['remarks'] ? htmlspecialchars($record['remarks']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>