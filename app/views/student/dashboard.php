<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../models/StudentModel.php';

$studentId = $_SESSION['user_id'];
$stats = getStudentStats($studentId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .action-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .action-card h3 { margin: 0 0 15px 0; color: #333; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <div class="dashboard-container student-container">
        <nav class="dashboard-sidebar sidebar">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="classes/index.php">My Classes</a></li>
                <li><a href="assignments/index.php">Assignments</a></li>
                <li><a href="attendance/index.php">Attendance</a></li>
                <li><a href="materials/index.php">Materials</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="dashboard-content content">
            <h1>Student Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            
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
            
            <div class="dashboard-stats stats">
                <div class="dashboard-stat stat">
                    <h3><?php echo $stats['total_classes']; ?></h3>
                    <p>Enrolled Classes</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $stats['pending_assignments']; ?></h3>
                    <p>Pending Assignments</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $stats['submitted_assignments']; ?></h3>
                    <p>Submitted Assignments</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $stats['available_materials']; ?></h3>
                    <p>Available Materials</p>
                </div>
            </div>
            
            <?php if (!empty($recentMaterials)): ?>
            <div style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 15px 0; color: #495057;">📚 Recent Materials</h3>
                <div style="display: grid; gap: 10px;">
                    <?php foreach ($recentMaterials as $material): ?>
                        <div style="padding: 12px; border: 1px solid #e9ecef; border-radius: 5px; background: #f8f9fa;">
                            <div style="display: flex; justify-content: between; align-items: start;">
                                <div style="flex: 1;">
                                    <strong><?php echo htmlspecialchars($material['title']); ?></strong>
                                    <br><small style="color: #6c757d;">
                                        <?php echo htmlspecialchars($material['class_name']); ?> • 
                                        <?php echo date('M d, Y', strtotime($material['created_at'])); ?>
                                    </small>
                                </div>
                                <a href="materials/download.php?id=<?php echo $material['id']; ?>" 
                                   style="font-size: 0.8em; color: #667eea; text-decoration: none;">Download</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="materials/index.php" class="btn btn-secondary">View All Materials</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="quick-actions">
                <div class="action-card">
                    <h3>My Classes</h3>
                    <p>View your enrolled classes and access course materials.</p>
                    <a href="classes/index.php" class="btn btn-primary">View Classes</a>
                </div>
                
                <div class="action-card">
                    <h3>Assignments</h3>
                    <p>Check pending assignments and submit your work.</p>
                    <a href="assignments/index.php" class="btn btn-primary">View Assignments</a>
                </div>
                
                <div class="action-card">
                    <h3>Course Materials</h3>
                    <p>Access and download course materials from your classes.</p>
                    <a href="materials/index.php" class="btn btn-primary">Browse Materials</a>
                </div>
                
                <div class="action-card">
                    <h3>Attendance</h3>
                    <p>View your attendance records and statistics.</p>
                    <a href="attendance/index.php" class="btn btn-primary">View Attendance</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
