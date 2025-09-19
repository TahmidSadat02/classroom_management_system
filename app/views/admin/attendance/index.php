<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$stats = getAttendanceStats();
$records = getAllAttendanceRecords(20);
$classes = getAllClassesForAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Management</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="attendance-container">
        <div class="attendance-header">
            <h1>Attendance Management</h1>
            <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_records']; ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['present_today']; ?></div>
                <div class="stat-label">Present Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['absent_today']; ?></div>
                <div class="stat-label">Absent Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['late_today']; ?></div>
                <div class="stat-label">Late Today</div>
            </div>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="nav-tab active">Records</a>
            <a href="reports.php" class="nav-tab">Reports</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Student</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Marked By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">
                            <h3>No attendance records found</h3>
                            <p>No attendance records match your current filters</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($record['class_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($record['class_code']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($record['student_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($record['student_email']); ?></small>
                            </td>
                            <td><span class="status-badge <?php echo $record['status']; ?>"><?php echo ucfirst($record['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['marked_by_name'] ?? 'System'); ?></td>
                            <td>
                                <button onclick="openOverrideModal(<?php echo $record['id']; ?>, '<?php echo $record['status']; ?>', '<?php echo htmlspecialchars($record['notes']); ?>')" class="btn btn-warning">Override</button>
                                <button onclick="deleteRecord(<?php echo $record['id']; ?>)" class="btn btn-danger" style="margin-left: 5px;">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Override Modal -->
    <div id="overrideModal" class="modal">
        <div class="modal-content">
            <h3>Override Attendance</h3>
            <form method="POST" action="../../../controllers/admin/attendanceController.php">
                <input type="hidden" name="action" value="override">
                <input type="hidden" name="attendanceId" id="attendanceId">
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" id="overrideStatus">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="excused">Excused</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" id="overrideNotes" placeholder="Reason for override..." style="height: 60px;"></textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="closeOverrideModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openOverrideModal(attendanceId, currentStatus, currentNotes) {
        document.getElementById('attendanceId').value = attendanceId;
        document.getElementById('overrideStatus').value = currentStatus;
        document.getElementById('overrideNotes').value = currentNotes;
        document.getElementById('overrideModal').style.display = 'block';
    }
    
    function closeOverrideModal() {
        document.getElementById('overrideModal').style.display = 'none';
    }
    
    function deleteRecord(attendanceId) {
        if (confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')) {
            
            window.location.href = '/classroom-management/app/controllers/admin/attendanceController.php?action=delete&attendance_id=' + attendanceId;
        }
    }
    </script>
</body>
</html>
