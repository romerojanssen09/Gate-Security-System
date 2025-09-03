<?php
/**
 * RFID Card & User Management Page
 * Combined management interface for RFID cards and users
 */

require_once 'models/Admin.php';

// Validate session
$admin = new Admin();
if (!$admin->validateSession()) {
    header('Location: index.php?page=login&msg=session_expired');
    exit;
}

// Get current admin info
$currentAdmin = $admin->getAdminById($_SESSION['admin_id']);

// Get database connection
$conn = getConnection();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_card') {
        $rfidId = trim($_POST['rfid_id'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? '';
        $plateNumber = trim($_POST['plate_number'] ?? '');
        
        if (empty($rfidId) || empty($fullName) || empty($role)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        } else {
            // Check if RFID ID already exists
            $checkQuery = "SELECT id FROM rfid_cards WHERE rfid_id = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "s", $rfidId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                $message = 'RFID ID already exists in the system.';
                $messageType = 'danger';
            } else {
                // Insert new card
                $insertQuery = "INSERT INTO rfid_cards (rfid_id, full_name, role, plate_number, created_by) VALUES (?, ?, ?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($insertStmt, "ssssi", $rfidId, $fullName, $role, $plateNumber, $_SESSION['admin_id']);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $message = 'RFID card added successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding RFID card. Please try again.';
                    $messageType = 'danger';
                }
                mysqli_stmt_close($insertStmt);
            }
            mysqli_stmt_close($checkStmt);
        }
    } elseif ($action === 'edit_card') {
        $cardId = intval($_POST['card_id'] ?? 0);
        $rfidId = trim($_POST['rfid_id'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? '';
        $plateNumber = trim($_POST['plate_number'] ?? '');
        
        if ($cardId > 0 && !empty($rfidId) && !empty($fullName) && !empty($role)) {
            // Check if RFID ID already exists for other cards
            $checkQuery = "SELECT id FROM rfid_cards WHERE rfid_id = ? AND id != ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "si", $rfidId, $cardId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                $message = 'RFID ID already exists in the system.';
                $messageType = 'danger';
            } else {
                // Update card
                $updateQuery = "UPDATE rfid_cards SET rfid_id = ?, full_name = ?, role = ?, plate_number = ? WHERE id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "ssssi", $rfidId, $fullName, $role, $plateNumber, $cardId);
                
                if (mysqli_stmt_execute($updateStmt)) {
                    $message = 'User information updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating user information. Please try again.';
                    $messageType = 'danger';
                }
                mysqli_stmt_close($updateStmt);
            }
            mysqli_stmt_close($checkStmt);
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        }
    } elseif ($action === 'update_status') {
        $cardId = intval($_POST['card_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        
        if ($cardId > 0 && in_array($newStatus, ['active', 'inactive', 'suspended'])) {
            $updateQuery = "UPDATE rfid_cards SET status = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $newStatus, $cardId);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $message = 'Card status updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating card status. Please try again.';
                $messageType = 'danger';
            }
            mysqli_stmt_close($updateStmt);
        }
    } elseif ($action === 'delete_card') {
        $cardId = intval($_POST['card_id'] ?? 0);
        
        if ($cardId > 0) {
            // Soft delete by setting status to inactive
            $deleteQuery = "UPDATE rfid_cards SET status = 'inactive' WHERE id = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $cardId);
            
            if (mysqli_stmt_execute($deleteStmt)) {
                $message = 'RFID card deactivated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deactivating RFID card. Please try again.';
                $messageType = 'danger';
            }
            mysqli_stmt_close($deleteStmt);
        }
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$roleFilter = $_GET['role'] ?? '';

// Build query with filters
$whereClause = "1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (rc.full_name LIKE ? OR rc.rfid_id LIKE ? OR rc.plate_number LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($statusFilter)) {
    $whereClause .= " AND rc.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($roleFilter)) {
    $whereClause .= " AND rc.role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

// Get users with their access statistics
$usersQuery = "
    SELECT 
        rc.*,
        a.username as created_by_username,
        (SELECT COUNT(*) FROM access_logs WHERE card_id = rc.id) as total_access,
        (SELECT COUNT(*) FROM access_logs WHERE card_id = rc.id AND access_result = 'granted') as granted_access,
        (SELECT MAX(access_timestamp) FROM access_logs WHERE card_id = rc.id) as last_access
    FROM rfid_cards rc
    LEFT JOIN admins a ON rc.created_by = a.id
    WHERE $whereClause
    ORDER BY rc.created_at DESC
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $usersQuery);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $usersResult = mysqli_stmt_get_result($stmt);
} else {
    $usersResult = mysqli_query($conn, $usersQuery);
}

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_cards,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_cards,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_cards,
        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_cards
    FROM rfid_cards
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID & User Management - Gate Security System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary-gradient">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['total_cards']) ?></h5>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success-gradient">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['active_cards']) ?></h5>
                                <small class="text-muted">Active Cards</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning-gradient">
                                <i class="fas fa-pause"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['inactive_cards']) ?></h5>
                                <small class="text-muted">Inactive Cards</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger-gradient">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['suspended_cards']) ?></h5>
                                <small class="text-muted">Suspended Cards</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="GET" class="form-inline">
                        <input type="hidden" name="page" value="rfid">
                        
                        <input type="text" class="form-control mr-2" name="search" placeholder="Search by name, RFID ID, or plate" value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                        
                        <select class="form-control mr-2" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                        
                        <select class="form-control mr-2" name="role">
                            <option value="">All Roles</option>
                            <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= $roleFilter === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="staff" <?= $roleFilter === 'staff' ? 'selected' : '' ?>>Staff</option>
                            <option value="visitor" <?= $roleFilter === 'visitor' ? 'selected' : '' ?>>Visitor</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        
                        <a href="index.php?page=rfid" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-users"></i> Users & RFID Cards</h5>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserModal">
                                <i class="fas fa-plus"></i> Add New User
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($message): ?>
                                <div class="alert alert-<?= $messageType ?> m-3">
                                    <?= htmlspecialchars($message) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>User</th>
                                            <th>RFID ID</th>
                                            <th class="d-none d-md-table-cell">Role</th>
                                            <th class="d-none d-lg-table-cell">Plate Number</th>
                                            <th>Status</th>
                                            <th class="d-none d-md-table-cell">Access Stats</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                                    </div>
                                                    <div class="ml-3">
                                                        <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                                        <div class="d-md-none">
                                                            <small class="text-muted">
                                                                <span class="badge badge-secondary badge-sm"><?= ucfirst($user['role']) ?></span>
                                                                <?php if ($user['plate_number']): ?>
                                                                    | <?= htmlspecialchars($user['plate_number']) ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                        <br><small class="text-muted">Created: <?= date('M d, Y', strtotime($user['created_at'])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($user['rfid_id']) ?></code>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="badge badge-secondary">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars($user['plate_number'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $user['status'] ?>">
                                                    <?= ucfirst($user['status']) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <small>
                                                    Total: <?= $user['total_access'] ?><br>
                                                    Granted: <?= $user['granted_access'] ?>
                                                    <?php if ($user['last_access']): ?>
                                                        <br>Last: <?= date('M d', strtotime($user['last_access'])) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" title="Edit User" 
                                                            onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['rfid_id']) ?>', '<?= htmlspecialchars($user['full_name']) ?>', '<?= $user['role'] ?>', '<?= htmlspecialchars($user['plate_number'] ?? '') ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <button class="btn btn-sm btn-outline-warning" title="Change Status" 
                                                            onclick="changeStatus(<?= $user['id'] ?>, '<?= $user['status'] ?>')">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    
                                                    <button class="btn btn-sm btn-outline-danger" title="Deactivate" 
                                                            onclick="deactivateUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        
                                        <?php if (mysqli_num_rows($usersResult) === 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                                No users found
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Information</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" id="editUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_card">
                        <input type="hidden" name="card_id" id="editUserId">
                        
                        <div class="form-group">
                            <label>RFID ID *</label>
                            <input type="text" class="form-control" name="rfid_id" id="editRfidId" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" class="form-control" name="full_name" id="editFullName" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Role *</label>
                            <select class="form-control" name="role" id="editRole" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="staff">Staff</option>
                                <option value="visitor">Visitor</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Plate Number</label>
                            <input type="text" class="form-control" name="plate_number" id="editPlateNumber">
                            <small class="text-muted">Optional - for vehicles</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Card Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="card_id" id="statusCardId">
                        
                        <div class="form-group">
                            <label>New Status</label>
                            <select class="form-control" name="status" id="statusSelect">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" id="addUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_card">
                        
                        <div class="form-group">
                            <label>RFID ID *</label>
                            <input type="text" class="form-control" name="rfid_id" id="addRfidId" required placeholder="e.g., ABC123456">
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" class="form-control" name="full_name" id="addFullName" required placeholder="Enter full name">
                        </div>
                        
                        <div class="form-group">
                            <label>Role *</label>
                            <select class="form-control" name="role" id="addRole" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="staff">Staff</option>
                                <option value="visitor">Visitor</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Plate Number</label>
                            <input type="text" class="form-control" name="plate_number" id="addPlateNumber" placeholder="e.g., ABC-1234">
                            <small class="text-muted">Optional - for vehicles</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        function editUser(id, rfidId, fullName, role, plateNumber) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editRfidId').value = rfidId;
            document.getElementById('editFullName').value = fullName;
            document.getElementById('editRole').value = role;
            document.getElementById('editPlateNumber').value = plateNumber;
            
            $('#editUserModal').modal('show');
        }
        
        function changeStatus(cardId, currentStatus) {
            document.getElementById('statusCardId').value = cardId;
            document.getElementById('statusSelect').value = currentStatus;
            $('#statusModal').modal('show');
        }
        
        function deactivateUser(id, fullName) {
            Swal.fire({
                title: 'Deactivate User',
                text: `Do you want to deactivate the RFID card for "${fullName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c82333',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, deactivate it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction('delete_card', id);
                }
            });
        }
        
        function submitAction(action, cardId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="${action}">
                <input type="hidden" name="card_id" value="${cardId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Show success/error messages with SweetAlert2
        <?php if ($message): ?>
            Swal.fire({
                title: '<?= $messageType === 'success' ? 'Success!' : 'Error!' ?>',
                text: '<?= addslashes(htmlspecialchars($message)) ?>',
                icon: '<?= $messageType === 'success' ? 'success' : 'error' ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                <?php if ($messageType === 'success'): ?>
                    // Reload page to show updated data
                    window.location.href = 'index.php?page=rfid';
                <?php endif; ?>
            });
        <?php endif; ?>
        
        // Form validation for Edit User
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const rfidId = document.getElementById('editRfidId').value.trim();
            const fullName = document.getElementById('editFullName').value.trim();
            const role = document.getElementById('editRole').value;
            
            if (!rfidId || !fullName || !role) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Form validation for Add User
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            const rfidId = document.getElementById('addRfidId').value.trim();
            const fullName = document.getElementById('addFullName').value.trim();
            const role = document.getElementById('addRole').value;
            
            if (!rfidId || !fullName || !role) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Clear form when modal is closed
        $('#addUserModal').on('hidden.bs.modal', function () {
            document.getElementById('addUserForm').reset();
        });
    </script>
</body>
</html>