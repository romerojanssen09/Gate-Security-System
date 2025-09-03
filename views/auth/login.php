<?php
/**
 * Admin Login Page
 */

require_once 'models/Admin.php';

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $admin = new Admin();
        $result = $admin->authenticate($username, $password);
        
        if ($result['success']) {
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Handle messages from URL
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'logged_out':
            $success = 'You have been logged out successfully.';
            break;
        case 'session_expired':
            $error = 'Your session has expired. Please log in again.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Gate Security System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #3A434C;
            --success-green: #9EA580;
            --text-dark: #272929;
            --panel-bg: #C5BBB7;
            --page-bg: #DAD5CC;
            --white: #ffffff;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d3640 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(58, 67, 76, 0.2);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
            border: 1px solid rgba(197, 187, 183, 0.3);
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d3640 100%);
            color: var(--white);
            padding: 40px 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px 30px;
            background-color: var(--white);
        }
        .form-control {
            border-radius: 12px;
            padding: 15px 20px;
            border: 2px solid rgba(197, 187, 183, 0.5);
            background-color: var(--white);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(58, 67, 76, 0.25);
            background-color: var(--white);
        }
        .form-control::placeholder {
            color: rgba(39, 41, 41, 0.6);
        }
        .btn-login {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d3640 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: var(--white);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(58, 67, 76, 0.3);
            color: var(--white);
        }
        .input-group-text {
            background: var(--panel-bg);
            border: 2px solid rgba(197, 187, 183, 0.5);
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--text-dark);
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--text-dark);
            border-left: 4px solid #dc3545;
        }
        .alert-success {
            background-color: rgba(158, 165, 128, 0.1);
            color: var(--text-dark);
            border-left: 4px solid var(--success-green);
        }
        .text-muted {
            color: rgba(39, 41, 41, 0.6) !important;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-shield-alt fa-3x mb-3"></i>
            <h3>Gate Security System</h3>
            <p class="mb-0">Holy Family High School</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Default: admin / admin123
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>