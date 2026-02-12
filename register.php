<?php
/**
 * Public RFID Card Registration Page
 * Allows users to request RFID cards without login
 */

require_once 'storage/database.php';

// Handle form submission
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $plateNumber = trim($_POST['plate_number'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getConnection();
        
        // Check if email already has a pending or approved request
        $checkQuery = "SELECT status FROM card_registration_requests WHERE email = ? AND status IN ('pending', 'approved') ORDER BY submitted_at DESC LIMIT 1";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if ($existingRequest = mysqli_fetch_assoc($checkResult)) {
            if ($existingRequest['status'] === 'pending') {
                $error = 'You already have a pending registration request. Please wait for admin approval.';
            } else {
                $error = 'You already have an approved RFID card. Please contact admin if you need assistance.';
            }
            mysqli_stmt_close($checkStmt);
        } else {
            mysqli_stmt_close($checkStmt);
            
            // Insert registration request
            $insertQuery = "INSERT INTO card_registration_requests (full_name, email, role, plate_number, status) VALUES (?, ?, ?, ?, 'pending')";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "ssss", $fullName, $email, $role, $plateNumber);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $success = true;
            } else {
                $error = 'Failed to submit registration request. Please try again.';
            }
            
            mysqli_stmt_close($insertStmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Card Registration - Holy Family High School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .registration-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        
        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #001F4D 0%, #003366 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .card-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 45px;
            cursor: pointer;
        }
        
        select.form-control:hover {
            border-color: #667eea;
        }
        
        select.form-control option {
            padding: 12px;
            font-size: 15px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .success-message {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .success-message i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header">
                <i class="fas fa-id-card fa-3x mb-3"></i>
                <h2>RFID Card Registration</h2>
                <p>Request your gate access card</p>
            </div>
            
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle text-success"></i>
                        <h4>Registration Submitted!</h4>
                        <p class="mb-0">
                            Your RFID card request has been submitted successfully. 
                            You will receive an email notification at <strong><?= htmlspecialchars($email) ?></strong> 
                            once your card is ready for pickup.
                        </p>
                    </div>
                    <div class="text-center mt-4">
                        <a href="register.php" class="btn btn-outline-primary">Submit Another Request</a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="full_name" 
                                   placeholder="Enter your full name" required 
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="your.email@example.com" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <small class="form-text text-muted">
                                You'll receive a notification when your card is ready
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label>Role <span class="required">*</span></label>
                            <select class="form-control" name="role" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>👨‍🎓 Student</option>
                                <option value="teacher" <?= ($_POST['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>👨‍🏫 Teacher</option>
                                <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>👔 Staff</option>
                                <option value="visitor" <?= ($_POST['role'] ?? '') === 'visitor' ? 'selected' : '' ?>>👤 Visitor</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Plate Number</label>
                            <input type="text" class="form-control" name="plate_number" 
                                   placeholder="ABC 1234 (Optional)"
                                   value="<?= htmlspecialchars($_POST['plate_number'] ?? '') ?>">
                            <small class="form-text text-muted">
                                Enter your vehicle plate number if applicable
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-paper-plane"></i> Submit Registration
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
