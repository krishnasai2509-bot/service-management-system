<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login and prevent workers from deleting
requireLogin();
if (hasRole('worker')) {
    header("Location: dashboard.php");
    exit();
}

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

// Check permissions - customers can only delete their own tasks
if ($userType == 'customer') {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $taskId, $userId);
} else {
    // Admin can delete any task
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
}

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    header("Location: dashboard.php");
    exit();
} else {
    $error = 'Error deleting task: ' . $conn->error;
    $stmt->close();
    closeDBConnection($conn);
    echo "Error: " . $error;
}
?>
