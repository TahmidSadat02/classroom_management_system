<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classes = getAllClassesForAdmin();
$reportData = $_SESSION['report_data'] ?? [];
$reportParams = $_SESSION['report_params'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Reports</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="reports-container">
        <div class="reports-header">
            <h1>Attendance Reports</h1>
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

        <div class="nav-tabs">
            <a href="index.php" class="nav-tab">Records</a>
            <a href="reports.php" class="nav-tab active">Reports</a>
        </div>

        <div class="report-form">
            <h3>Generate Attendance Report</h3>
            <form method="POST" action="../../../controllers/admin/attendanceController.php">
                <input type="hidden" name="action" value="generate_report">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" value="<?php echo $reportParams['start_date'] ?? date('Y-m-01'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" value="<?php echo $reportParams['end_date'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Class (Optional):</label>
                        <select name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($reportParams['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($reportData)): ?>
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Report Results</h3>
                    <button onclick="window.print()" class="btn btn-success">Print Report</button>
                </div>
                
                <p><strong>Period:</strong> <?php echo date('M d, Y', strtotime($reportParams['start_date'])); ?> - <?php echo date('M d, Y', strtotime($reportParams['end_date'])); ?></p>
                
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Student</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Excused</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['class_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['class_code']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo $row['total_days']; ?></td>
                                <td><?php echo $row['present_days']; ?></td>
                                <td><?php echo $row['late_days']; ?></td>
                                <td><?php echo $row['absent_days']; ?></td>
                                <td><?php echo $row['excused_days']; ?></td>
                                <td>
                                    <span class="attendance-percent <?php 
                                        echo $row['attendance_percentage'] >= 75 ? 'good' : 
                                             ($row['attendance_percentage'] >= 60 ? 'warning' : 'poor'); 
                                    ?>">
                                        <?php echo $row['attendance_percentage']; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?php unset($_SESSION['report_data'], $_SESSION['report_params']); ?>
    </div>
</body>
</html>
