<?php
/**
 * Admin Model
 * Handles admin authentication and account management
 */

class Admin {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Authenticate admin user
     */
    public function authenticate($username, $password) {
        try {
            // Check if account is locked
            $lockCheck = "SELECT locked_until FROM admins WHERE username = ? AND locked_until > NOW()";
            $stmt = mysqli_prepare($this->conn, $lockCheck);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                mysqli_stmt_close($stmt);
                return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
            }
            mysqli_stmt_close($stmt);
            
            // Get admin details
            $query = "SELECT id, username, password, full_name, email, failed_attempts FROM admins WHERE username = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) === 0) {
                mysqli_stmt_close($stmt);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
            
            $admin = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Reset failed attempts and update last login
                $updateQuery = "UPDATE admins SET failed_attempts = 0, last_login = NOW() WHERE id = ?";
                $updateStmt = mysqli_prepare($this->conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "i", $admin['id']);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
                
                // Create session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['login_time'] = time();
                
                return ['success' => true, 'admin' => $admin];
            } else {
                // Increment failed attempts
                $failedAttempts = $admin['failed_attempts'] + 1;
                $lockUntil = null;
                
                // Lock account after 3 failed attempts
                if ($failedAttempts >= 3) {
                    $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                }
                
                $updateQuery = "UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE id = ?";
                $updateStmt = mysqli_prepare($this->conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "isi", $failedAttempts, $lockUntil, $admin['id']);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
                
                $message = $failedAttempts >= 3 ? 
                    'Too many failed attempts. Account locked for 15 minutes.' : 
                    'Invalid username or password.';
                
                return ['success' => false, 'message' => $message];
            }
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication system error. Please try again.'];
        }
    }
    
    /**
     * Check if session is valid
     */
    public function validateSession() {
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Check session timeout (30 minutes)
        if (time() - $_SESSION['login_time'] > 1800) {
            session_destroy();
            return false;
        }
        
        // Update login time
        $_SESSION['login_time'] = time();
        return true;
    }
    
    /**
     * Get admin details by ID
     */
    public function getAdminById($adminId) {
        $query = "SELECT id, username, full_name, email, last_login FROM admins WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $admin;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Logout admin
     */
    public function logout() {
        session_destroy();
        return true;
    }
}
?>