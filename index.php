<?php
/**
 * Holy Family High School - Automatic Gate Security System
 * Main Entry Point with Routing
 */

session_start();
require_once 'storage/database.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Simple routing
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? '';

// Check if user is logged in for protected pages
$protectedPages = ['dashboard', 'rfid', 'reports', 'settings'];
if (in_array($page, $protectedPages) && !isset($_SESSION['admin_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Handle logout
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php?page=login&msg=logged_out');
    exit;
}

// Route to appropriate page
switch ($page) {
    case 'login':
        include 'views/auth/login.php';
        break;
    case 'dashboard':
        include 'views/dashboard/index.php';
        break;
    case 'rfid':
        include 'views/rfid/manage.php';
        break;
    case 'reports':
        include 'views/reports/logs.php';
        break;
    default:
        if (isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=dashboard');
        } else {
            header('Location: index.php?page=login');
        }
        exit;
}
?>