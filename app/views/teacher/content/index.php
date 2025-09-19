<?php
session_start();

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

$teacherId = $_SESSION['user_id'];
$classFilter = $_GET['class_id'] ?? '';

// Get teacher's classes for filter
$classes = getTeacherClasses($teacherId);

// Get content materials
$content = getTeacherContent($teacherId, $classFilter ?: null);

// Get topics for organization
$topics = getContentTopics($teacherId, $classFilter ?: null);

// Get content statistics
$stats = getContentStats($teacherId);

// Handle success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Content Creation - Teacher</title>
    <style>
        /* Basic styles following the existing pattern */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            margin: 0 0 20px 0;
            text-align: center;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }
        
        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 10px;
        }
        
        .content {
            flex: 1;
            padding: 30px;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        
        .stat h3 {
            font-size: 1.5rem;
            margin: 0 0 5px 0;
        }
        
        .stat p {
            margin: 0;
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .content-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .badge-public {
            background: #28a745;
            color: white;
        }
        
        .badge-private {
            background: #6c757d;
            color: white;
        }
        
        .badge-scheduled {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="../content/index.php">Upload Content</a></li>
                <li><a href="../assignment/index.php">Assignments</a></li>
                <li><a href="../grades/index.php">Submit Grades</a></li>
                <li><a href="../attendance/index.php">Take Attendance</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <h1>Content Creation</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats">
                <div class="stat">
                    <h3><?php echo $stats['total_materials']; ?></h3>
                    <p>Total Materials</p>
                </div>
                <div class="stat">
                    <h3><?php echo $stats['public_materials']; ?></h3>
                    <p>Public Materials</p>
                </div>
                <div class="stat">
                    <h3><?php echo $stats['private_materials']; ?></h3>
                    <p>Private Materials</p>
                </div>
                <div class="stat">
                    <h3><?php echo $stats['total_topics']; ?></h3>
                    <p>Topics</p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="actions">
                <button onclick="openCreateModal()" class="btn">Create Content</button>
                <button onclick="openUploadModal()" class="btn">Upload File</button>
                <button onclick="openTopicModal()" class="btn">Create Topic</button>
                <a href="topics.php" class="btn" style="background: #17a2b8;">Manage Topics</a>
            </div>
            
            <!-- Filter -->
            <div class="filter-section">
                <h3>Filter Content</h3>
                <form method="GET">
                    <div class="form-group" style="display: inline-block; margin-right: 20px;">
                        <label for="class_id">Filter by Class:</label>
                        <select name="class_id" id="class_id" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            
            <!-- Content Table -->
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Topic</th>
                            <th>Class</th>
                            <th>Visibility</th>
                            <th>File</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($content)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No content materials found. Click "Create Content" to get started!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($content as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        <?php if ($item['description']): ?>
                                            <br><small><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['topic'] ?: 'No Topic'); ?></td>
                                    <td>
                                        <?php 
                                        $className = 'All Classes';
                                        foreach ($classes as $class) {
                                            if ($class['id'] == $item['class_id']) {
                                                $className = $class['class_name'];
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($className);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $item['visibility']; ?>">
                                            <?php echo ucfirst($item['visibility']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['file_name']): ?>
                                            <strong><?php echo htmlspecialchars($item['file_name']); ?></strong>
                                            <br><small><?php echo number_format($item['file_size'] / 1024, 1); ?> KB</small>
                                        <?php else: ?>
                                            <em>No file</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                    <td>
                                        <button onclick="editContent(<?php echo $item['id']; ?>)" class="btn btn-warning" style="margin-right: 5px;">Edit</button>
                                        <button onclick="deleteContent(<?php echo $item['id']; ?>)" class="btn btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Create Content Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('createModal')">&times;</span>
            <h2>Create Content</h2>
            <form action="../../../controllers/teacher/contentController.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="create_title">Title *</label>
                    <input type="text" id="create_title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="create_description">Description</label>
                    <textarea id="create_description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="create_class">Class</label>
                    <select id="create_class" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="create_topic">Topic</label>
                    <input type="text" id="create_topic" name="topic" list="topics">
                    <datalist id="topics">
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo htmlspecialchars($topic['topic_name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="create_visibility">Visibility</label>
                    <select id="create_visibility" name="visibility">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="create_available_from">Available From</label>
                    <input type="datetime-local" id="create_available_from" name="available_from">
                </div>
                
                <div class="form-group">
                    <label for="create_available_until">Available Until</label>
                    <input type="datetime-local" id="create_available_until" name="available_until">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Create Content</button>
                    <button type="button" onclick="closeModal('createModal')" class="btn" style="background: #6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Upload File Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('uploadModal')">&times;</span>
            <h2>Upload File</h2>
            <form action="../../../controllers/teacher/contentController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="form-group">
                    <label for="upload_file">File * (PDF, DOC, DOCX, TXT, PPT, PPTX)</label>
                    <input type="file" id="upload_file" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" required>
                </div>
                
                <div class="form-group">
                    <label for="upload_title">Title (leave empty to use filename)</label>
                    <input type="text" id="upload_title" name="title">
                </div>
                
                <div class="form-group">
                    <label for="upload_description">Description</label>
                    <textarea id="upload_description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="upload_class">Class</label>
                    <select id="upload_class" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="upload_topic">Topic</label>
                    <input type="text" id="upload_topic" name="topic" list="topics">
                </div>
                
                <div class="form-group">
                    <label for="upload_visibility">Visibility</label>
                    <select id="upload_visibility" name="visibility">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Upload File</button>
                    <button type="button" onclick="closeModal('uploadModal')" class="btn" style="background: #6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Create Topic Modal -->
    <div id="topicModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('topicModal')">&times;</span>
            <h2>Create Topic</h2>
            <form action="../../../controllers/teacher/contentController.php" method="POST">
                <input type="hidden" name="action" value="create_topic">
                
                <div class="form-group">
                    <label for="topic_name">Topic Name *</label>
                    <input type="text" id="topic_name" name="topic_name" required>
                </div>
                
                <div class="form-group">
                    <label for="topic_description">Description</label>
                    <textarea id="topic_description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="topic_class">Class</label>
                    <select id="topic_class" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="0">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Create Topic</button>
                    <button type="button" onclick="closeModal('topicModal')" class="btn" style="background: #6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'block';
    }
    
    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'block';
    }
    
    function openTopicModal() {
        document.getElementById('topicModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    function editContent(contentId) {
        // For simplicity, redirect to edit page with content ID
        window.location.href = 'edit.php?id=' + contentId;
    }
    
    function deleteContent(contentId) {
        if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
            window.location.href = '../../../controllers/teacher/contentController.php?action=delete&content_id=' + contentId;
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        let modals = document.getElementsByClassName('modal');
        for (let i = 0; i < modals.length; i++) {
            if (event.target == modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>