<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../../models/Database.php';

// student er materials gula ber korar jonno function
function getStudentMaterials($studentId, $search = '', $sortBy = 'date', $sortOrder = 'DESC', $filterType = '', $filterClass = '') {
    global $conn;
    
    $materials = [];
    
    $classesSql = "SELECT class_id FROM enrollments WHERE student_id = $studentId AND status = 'enrolled'";
    $classesResult = $conn->query($classesSql);

    // student er enrolled class gula ber korlam
    if ($classesResult && $classesResult->num_rows > 0) {
        $classIds = [];
        while ($row = $classesResult->fetch_assoc()) {
            $classIds[] = $row['class_id'];
        }
        
        if (!empty($classIds)) {
            $classIdsStr = implode(',', $classIds);
            
            $sql = "SELECT cm.*, c.class_name, c.class_code, u.first_name, u.last_name,
                           CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                    FROM content_materials cm 
                    JOIN classes c ON cm.class_id = c.id 
                    JOIN users u ON cm.teacher_id = u.id 
                    WHERE cm.class_id IN ($classIdsStr) AND cm.visibility = 'public'";
            
            if (!empty($search)) {
                $searchTerm = $conn->real_escape_string($search);
                $sql .= " AND (cm.title LIKE '%$searchTerm%' OR cm.description LIKE '%$searchTerm%' OR cm.topic LIKE '%$searchTerm%')";
            }
            
            if (!empty($filterType)) {
                $filterType = $conn->real_escape_string($filterType);
                $sql .= " AND cm.file_type = '$filterType'";
            }
            
            if (!empty($filterClass)) {
                $filterClass = (int)$filterClass;
                $sql .= " AND cm.class_id = $filterClass";
            }
            
            switch($sortBy) {
                case 'title':
                    $sql .= " ORDER BY cm.title $sortOrder";
                    break;
                case 'type':
                    $sql .= " ORDER BY cm.file_type $sortOrder, cm.title ASC";
                    break;
                case 'topic':
                    $sql .= " ORDER BY cm.topic $sortOrder, cm.title ASC";
                    break;
                case 'class':
                    $sql .= " ORDER BY c.class_name $sortOrder, cm.title ASC";
                    break;
                case 'date':
                default:
                    $sql .= " ORDER BY cm.created_at $sortOrder";
                    break;
            }
            
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $materials[] = $row;
                }
            }
        }
    }
    
    return $materials;
}


function getAvailableFileTypes($studentId) {
    global $conn;
    
    $types = [];
    
    $classesSql = "SELECT class_id FROM enrollments WHERE student_id = $studentId AND status = 'enrolled'";
    $classesResult = $conn->query($classesSql);
    
    if ($classesResult && $classesResult->num_rows > 0) {
        $classIds = [];
        while ($row = $classesResult->fetch_assoc()) {
            $classIds[] = $row['class_id'];
        }
        
        if (!empty($classIds)) {
            $classIdsStr = implode(',', $classIds);
            $sql = "SELECT DISTINCT file_type FROM content_materials 
                    WHERE class_id IN ($classIdsStr) AND visibility = 'public' 
                    AND file_type IS NOT NULL AND file_type != ''
                    ORDER BY file_type";
            
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $types[] = $row['file_type'];
                }
            }
        }
    }
    
    return $types;
}


function getStudentClassesForFilter($studentId) {
    global $conn;
    
    $classes = [];
    $sql = "SELECT c.id, c.class_name, c.class_code 
            FROM classes c 
            JOIN enrollments e ON c.id = e.class_id 
            WHERE e.student_id = $studentId AND e.status = 'enrolled'
            ORDER BY c.class_name";
    
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }
    
    return $classes;
}

$studentId = $_SESSION['user_id'];

$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'date';
$sortOrder = $_GET['order'] ?? 'DESC';
$filterType = $_GET['type'] ?? '';
$filterClass = $_GET['class'] ?? '';

$materials = getStudentMaterials($studentId, $search, $sortBy, $sortOrder, $filterType, $filterClass);
$fileTypes = getAvailableFileTypes($studentId);
$studentClasses = getStudentClassesForFilter($studentId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Course Materials</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .materials-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.9em;
        }
        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .material-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .material-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .material-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
        }
        .material-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        .material-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .material-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .file-type-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
        }
        .topic-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #d1ecf1;
            border-radius: 4px;
            font-size: 0.8em;
            color: #0c5460;
        }
        .btn-download {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-download:hover {
            background: #218838;
            color: white;
        }
        .no-materials {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .no-materials h3 {
            margin-bottom: 10px;
            color: #495057;
        }
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            .material-meta {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="../assignments/index.php">Assignments</a></li>
                <li><a href="../attendance/index.php">Attendance</a></li>
                <li><a href="index.php">Materials</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <div class="page-header">
                <h2>Course Materials</h2>
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

            <!-- Filters and Search -->
            <div class="materials-filters">
                <form method="GET" action="" id="filterForm">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search Materials:</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title, description, or topic..." style="width: 300px;">
                        </div>
                        
                        <div class="filter-group">
                            <label for="class">Class:</label>
                            <select id="class" name="class">
                                <option value="">All Classes</option>
                                <?php foreach ($studentClasses as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $filterClass == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="type">File Type:</label>
                            <select id="type" name="type">
                                <option value="">All Types</option>
                                <?php foreach ($fileTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $filterType === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(strtoupper($type)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="sort">Sort By:</label>
                            <select id="sort" name="sort">
                                <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Date Added</option>
                                <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Title</option>
                                <option value="type" <?php echo $sortBy === 'type' ? 'selected' : ''; ?>>File Type</option>
                                <option value="topic" <?php echo $sortBy === 'topic' ? 'selected' : ''; ?>>Topic</option>
                                <option value="class" <?php echo $sortBy === 'class' ? 'selected' : ''; ?>>Class</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="order">Order:</label>
                            <select id="order" name="order">
                                <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                        
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <a href="index.php" class="btn btn-secondary">Clear All</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Materials List -->
            <div class="materials-container">
                <?php if (empty($materials)): ?>
                    <div class="no-materials">
                        <h3>No Materials Found</h3>
                        <p>No course materials are available with the current filters.</p>
                        <?php if (!empty($search) || !empty($filterType) || !empty($filterClass)): ?>
                            <p><a href="index.php">Clear filters</a> to see all available materials.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="margin-bottom: 20px; color: #666;">
                        Found <?php echo count($materials); ?> material<?php echo count($materials) !== 1 ? 's' : ''; ?>
                    </p>
                    
                    <?php foreach ($materials as $material): ?>
                        <div class="material-card">
                            <div class="material-header">
                                <div style="flex: 1;">
                                    <h3 class="material-title"><?php echo htmlspecialchars($material['title']); ?></h3>
                                    <div class="material-meta">
                                        <span><strong>Class:</strong> <?php echo htmlspecialchars($material['class_name']); ?></span>
                                        <span><strong>Teacher:</strong> <?php echo htmlspecialchars($material['teacher_name']); ?></span>
                                        <span><strong>Added:</strong> <?php echo date('M d, Y', strtotime($material['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <?php if ($material['file_type']): ?>
                                        <span class="file-type-badge"><?php echo htmlspecialchars(strtoupper($material['file_type'])); ?></span>
                                    <?php endif; ?>
                                    <?php if ($material['topic']): ?>
                                        <span class="topic-badge"><?php echo htmlspecialchars($material['topic']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($material['description']): ?>
                                <div class="material-description">
                                    <?php echo nl2br(htmlspecialchars($material['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="material-actions">
                                <?php if ($material['file_path']): ?>
                                    <a href="download.php?id=<?php echo $material['id']; ?>" class="btn-download">
                                        ⬇ Download
                                        <?php if ($material['file_size']): ?>
                                            (<?php echo formatFileSize($material['file_size']); ?>)
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500);
        });
        
        ['class', 'type', 'sort', 'order'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>

<?php

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>