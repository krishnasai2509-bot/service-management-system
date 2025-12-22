<?php
require_once 'config.php';
requireLogin();

// Route to appropriate dashboard based on user type
$userType = getUserType();

switch ($userType) {
    case 'admin':
        include 'admin_dashboard.php';
        break;
    case 'worker':
        include 'worker_dashboard.php';
        break;
    case 'customer':
        include 'customer_dashboard.php';
        break;
    default:
        header('Location: logout.php');
        exit();
}
?>
