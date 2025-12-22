<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login
requireLogin();

$error = '';
$success = '';
$task = null;

// Get task ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$taskId = intval($_GET['id']);
$userId = getCurrentUserId();
$userType = getCurrentUserType();

// Get database connection
$conn = getDBConnection();

// Get list of workers for assignment (admin and customer can assign)
$workersQuery = "SELECT id, full_name FROM users WHERE user_type = 'worker'";
$workersResult = $conn->query($workersQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : NULL;

    // Validation
    if (empty($title)) {
        $error = 'Task title is required!';
    } else {
        // Workers can only update status
        if ($userType == 'worker') {
            $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
            $stmt->bind_param("sii", $status, $taskId, $userId);
        } else {
            // Admin and customers can update everything
            if ($assigned_to) {
                $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, assigned_to = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $title, $description, $status, $priority, $assigned_to, $taskId);
            } else {
                $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, assigned_to = NULL WHERE id = ?");
                $stmt->bind_param("ssssi", $title, $description, $status, $priority, $taskId);
            }
        }

        if ($stmt->execute()) {
            $success = 'Task updated successfully!';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Error updating task: ' . $conn->error;
        }

        $stmt->close();
    }
}

// Fetch task details
$stmt = $conn->prepare("SELECT t.*, u.full_name as creator_name FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->bind_param("i", $taskId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$task = $result->fetch_assoc();

// Check permissions
if ($userType == 'worker' && $task['assigned_to'] != $userId) {
    header("Location: dashboard.php");
    exit();
} elseif ($userType == 'customer' && $task['user_id'] != $userId) {
    header("Location: dashboard.php");
    exit();
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="nav-brand">
                <h2>Task Manager</h2>
            </div>
            <div class="nav-user">
                <span class="user-badge user-badge-<?php echo getCurrentUserType(); ?>">
                    <?php echo ucfirst(getCurrentUserType()); ?>
                </span>
                <span class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </nav>

        <header>
            <h1><?php echo $userType == 'worker' ? 'Update Task Status' : 'Edit Task'; ?></h1>
            <p class="subtitle"><?php echo $userType == 'worker' ? 'Update the status of your assigned task' : 'Update your task details'; ?></p>
        </header>

        <div class="main-content">
            <div class="form-container">
                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="edit_task.php?id=<?php echo $taskId; ?>" class="task-form">
                    <?php if ($userType != 'worker'): ?>
                        <div class="form-group">
                            <label for="title">Task Title *</label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   placeholder="Enter task title"
                                   required
                                   value="<?php echo htmlspecialchars($task['title']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description"
                                      name="description"
                                      rows="5"
                                      placeholder="Enter task description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Task Title</label>
                            <p class="readonly-field"><?php echo htmlspecialchars($task['title']); ?></p>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <p class="readonly-field"><?php echo htmlspecialchars($task['description']); ?></p>
                        </div>

                        <div class="form-group">
                            <label>Created by</label>
                            <p class="readonly-field"><?php echo htmlspecialchars($task['creator_name']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status <?php echo $userType == 'worker' ? '*' : ''; ?></label>
                            <select id="status" name="status">
                                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <?php if ($userType != 'worker'): ?>
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority">
                                    <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label>Priority</label>
                                <p class="readonly-field"><?php echo ucfirst($task['priority']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($userType != 'worker'): ?>
                        <div class="form-group">
                            <label for="assigned_to">Assign to Worker (Optional)</label>
                            <select id="assigned_to" name="assigned_to">
                                <option value="">-- Not Assigned --</option>
                                <?php
                                $workersResult->data_seek(0);
                                while ($worker = $workersResult->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $worker['id']; ?>"
                                            <?php echo $task['assigned_to'] == $worker['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($worker['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $userType == 'worker' ? 'Update Status' : 'Update Task'; ?>
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
closeDBConnection($conn);
?>
