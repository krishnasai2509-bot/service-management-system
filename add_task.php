<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login and prevent workers from creating tasks
requireLogin();
if (hasRole('worker')) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

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
    $userId = getCurrentUserId();

    // Validation
    if (empty($title)) {
        $error = 'Task title is required!';
    } else {
        if ($assigned_to) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, status, priority, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssi", $userId, $title, $description, $status, $priority, $assigned_to);
        } else {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, status, priority) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $userId, $title, $description, $status, $priority);
        }

        if ($stmt->execute()) {
            $success = 'Task added successfully!';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Error adding task: ' . $conn->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Task - Task Manager</title>
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
            <h1>Add New Task</h1>
            <p class="subtitle">Create a new task to stay organized</p>
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

                <form method="POST" action="add_task.php" class="task-form">
                    <div class="form-group">
                        <label for="title">Task Title *</label>
                        <input type="text"
                               id="title"
                               name="title"
                               placeholder="Enter task title"
                               required
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description"
                                  name="description"
                                  rows="5"
                                  placeholder="Enter task description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="assigned_to">Assign to Worker (Optional)</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">-- Not Assigned --</option>
                            <?php while ($worker = $workersResult->fetch_assoc()): ?>
                                <option value="<?php echo $worker['id']; ?>">
                                    <?php echo htmlspecialchars($worker['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Task</button>
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
