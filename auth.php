<?php
// Authentication helper functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require login - redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Check if user has specific role
function hasRole($role) {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == $role;
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: dashboard.php");
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user type
function getCurrentUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Get current user full name
function getCurrentUserName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
}
?>
