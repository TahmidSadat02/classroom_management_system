<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


require_once __DIR__ . '/../../models/Database.php';


function getUserStats() {
    global $conn;
    
    $stats = [
        'total_users' => 0,
        'total_teachers' => 0,
        'total_students' => 0,
        'total_admins' => 0,
        'active_users' => 0
    ];
    
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_users'] = $row['count'];
    }
    
    
    $result = $conn->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            switch ($row['user_type']) {
                case 'teacher':
                    $stats['total_teachers'] = $row['count'];
                    break;
                case 'student':
                    $stats['total_students'] = $row['count'];
                    break;
                case 'admin':
                    $stats['total_admins'] = $row['count'];
                    break;
            }
        }
    }
    
    
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($result && $result->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['active_users'] = $row['count'];
        }
    } else {
        $stats['active_users'] = $stats['total_users']; 
    }
    
    return $stats;
}


function getCourseStats() {
    global $conn;
    
    $stats = [
        'total_courses' => 0,
        'active_courses' => 0
    ];
    
    
    $result = $conn->query("SHOW TABLES LIKE 'courses'");
    if ($result && $result->num_rows > 0) {
        
        $result = $conn->query("SELECT COUNT(*) as count FROM courses");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_courses'] = $row['count'];
        }
        
        
        $result = $conn->query("SELECT COUNT(*) as count FROM courses WHERE status = 'active'");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['active_courses'] = $row['count'];
        }
    }
    
    return $stats;
}


function getClassStats() {
    global $conn;
    
    $stats = [
        'total_classes' => 0,
        'active_classes' => 0
    ];
    
    
    $result = $conn->query("SHOW TABLES LIKE 'classes'");
    if ($result && $result->num_rows > 0) {
        
        $result = $conn->query("SELECT COUNT(*) as count FROM classes");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_classes'] = $row['count'];
        }
        
        
        $result = $conn->query("SELECT COUNT(*) as count FROM classes WHERE status = 'active'");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['active_classes'] = $row['count'];
        }
    }
    
    return $stats;
}


$userStats = getUserStats();
$courseStats = getCourseStats();
$classStats = getClassStats();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="dashboard-container admin-container">
        <nav class="dashboard-sidebar sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">
                    <img src="../../../public/assets/menu.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Dashboard
                </a></li>
                <li><a href="users/index.php">Manage Users</a></li>
                <li><a href="courses/index.php">Manage Courses</a></li>
                <li> <a href="attendance/index.php">Manage Attendance</a></li>
                <li><a href="reports/index.php">View Reports</a></li>
                <li> <a href="classes/index.php">Class Oversight</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="dashboard-content content">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            
            <div class="dashboard-stats stats">
                <div class="dashboard-stat stat">
                    <h3><?php echo $userStats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $userStats['total_teachers']; ?></h3>
                    <p>Teachers</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $userStats['total_students']; ?></h3>
                    <p>Students</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $courseStats['total_courses']; ?></h3>
                    <p>Total Courses</p>
                </div>
            </div>

            <div class="dashboard-stats stats">
                <div class="dashboard-stat stat">
                    <h3><?php echo $userStats['active_users']; ?></h3>
                    <p>Active Users</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $courseStats['active_courses']; ?></h3>
                    <p>Active Courses</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $classStats['total_classes']; ?></h3>
                    <p>Total Classes</p>
                </div>
                <div class="dashboard-stat stat">
                    <h3><?php echo $classStats['active_classes']; ?></h3>
                    <p>Active Classes</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>