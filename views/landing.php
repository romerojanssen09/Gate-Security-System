<?php
/**
 * Interactive Landing Page with Gate Visualization
 * Holy Family High School - Gate Security System
 */

require_once 'models/Admin.php';

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
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
    <title>Holy Family High School - Gate Security System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/landing.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="container">
            <div class="nav-brand">
                <img src="assets/images/holy_logo_no_bg.png" alt="Holy Family High School Logo" class="school-logo">
                <div class="brand-text">
                    <h1 class="school-name">Holy Family High School</h1>
                    <span class="system-name">Gate Security System</span>
                </div>
            </div>
            <ul class="nav-menu">
                <li><a href="#features">Features</a></li>
                <li><a href="rfid_scanner.php">RFID Scanner</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><button class="btn-login-modal" id="loginButton">Admin Login</button></li>
            </ul>
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span><span></span><span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="school-branding">
                    <h1 class="hero-title">
                        <span class="school-highlight">Holy Family High School</span>
                        <span class="system-subtitle">Advanced Gate Security System</span>
                    </h1>
                    <p class="hero-description">
                        Protecting our campus with cutting-edge RFID technology and real-time access control
                    </p>
                </div>
                
                <!-- Gate Animation Container -->
                <div class="gate-animation-container">
                    <div class="gate-structure">
                        <div class="gate-post left"></div>
                        <div class="gate-barrier" id="gateBarrier"></div>
                        <div class="gate-post right">
                            <div class="rfid-scanner">
                                <div class="scanner-display" id="scannerDisplay">Waiting...</div>
                                <div class="scanner-leds" id="scannerLeds">
                                    <div class="led red"></div>
                                    <div class="led green"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="road-surface"></div>
                    <div class="vehicle" id="animatedVehicle">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="rfid-card" id="animatedCard">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="animation-label" id="animationStatus">Car approaching gate...</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">System Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Real-time Access Control</h3>
                        <p class="feature-description">Instant RFID card verification with automated gate control for secure campus access.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Comprehensive Logging</h3>
                        <p class="feature-description">Detailed access logs with timestamps, user information, and security reports.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Dashboard</h3>
                        <p class="feature-description">Monitor gate activities and manage access permissions from any device.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="section-title text-left">About Our Security System</h2>
                    <p class="about-description">
                        Holy Family High School's Gate Security System represents the latest in RFID technology, 
                        providing seamless and secure access control for our campus community. Our system ensures 
                        that only authorized personnel can enter the premises while maintaining detailed logs for 
                        security and administrative purposes.
                    </p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime Reliability</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Monitoring</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Active Cards</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="about-image">
                        <div class="security-showcase">
                            <div class="showcase-item">
                                <i class="fas fa-id-card-alt"></i>
                                <h4>RFID Technology</h4>
                                <p>Advanced card readers with instant verification</p>
                            </div>
                            <div class="showcase-item">
                                <i class="fas fa-database"></i>
                                <h4>Secure Database</h4>
                                <p>Encrypted storage of all access records</p>
                            </div>
                            <div class="showcase-item">
                                <i class="fas fa-clock"></i>
                                <h4>Real-time Logging</h4>
                                <p>Instant recording of all gate activities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal" onclick="closeLoginModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3><i class="fas fa-shield-alt"></i> Admin Login</h3>
                <button class="modal-close" onclick="closeLoginModal()">&times;</button>
            </div>
            <div class="modal-body">
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
                    
                    <button type="submit" name="login_submit" class="btn-login">
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
    </div>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h4 class="school-name-footer">Holy Family High School</h4>
                    <p>Advanced Gate Security System</p>
                </div>
                <div class="footer-info" id="contact">
                    <p><i class="fas fa-envelope"></i> info@holyfamilyhighschool.edu</p>
                    <p><i class="fas fa-phone"></i> +63 (02) 123-4567</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/landing.js"></script>

    <?php if ($error || $success): ?>
    <script>
        // Auto-open modal if there are login messages
        document.addEventListener('DOMContentLoaded', function() {
            openLoginModal();
        });
    </script>
    <?php endif; ?>
</body>
</html>