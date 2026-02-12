<?php
/**
 * Admin Page - Pending Card Registration Requests
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

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = intval($_POST['request_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($action === 'approve' && !empty($_POST['rfid_id'])) {
        $rfidId = trim($_POST['rfid_id']);
        
        // Get request details
        $requestQuery = "SELECT * FROM card_registration_requests WHERE id = ? AND status = 'pending'";
        $requestStmt = mysqli_prepare($conn, $requestQuery);
        mysqli_stmt_bind_param($requestStmt, "i", $requestId);
        mysqli_stmt_execute($requestStmt);
        $requestResult = mysqli_stmt_get_result($requestStmt);
        $request = mysqli_fetch_assoc($requestResult);
        mysqli_stmt_close($requestStmt);
        
        if ($request) {
            // Check if RFID ID already exists
            $checkRfidQuery = "SELECT id FROM rfid_cards WHERE rfid_id = ?";
            $checkStmt = mysqli_prepare($conn, $checkRfidQuery);
            mysqli_stmt_bind_param($checkStmt, "s", $rfidId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                $error = "RFID ID already exists in the system.";
            } else {
                mysqli_stmt_close($checkStmt);
                
                // Create RFID card (email is not stored in rfid_cards table)
                $insertCardQuery = "INSERT INTO rfid_cards (rfid_id, full_name, role, plate_number, status, created_by) VALUES (?, ?, ?, ?, 'active', ?)";
                $insertStmt = mysqli_prepare($conn, $insertCardQuery);
                mysqli_stmt_bind_param($insertStmt, "ssssi", $rfidId, $request['full_name'], $request['role'], $request['plate_number'], $_SESSION['admin_id']);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $cardId = mysqli_insert_id($conn);
                    mysqli_stmt_close($insertStmt);
                    
                    // Update registration request (using reviewed_by and reviewed_at columns)
                    $updateQuery = "UPDATE card_registration_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "ii", $_SESSION['admin_id'], $requestId);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                    
                    // Send email notification
                    require_once 'includes/EmailHelper.php';
                    $emailHelper = new EmailHelper();
                    $emailHelper->sendCardReadyNotification($request['email'], $request['full_name'], $rfidId);
                    
                    $success = "Registration approved and card created successfully. Email notification sent.";
                } else {
                    $error = "Failed to create RFID card.";
                    mysqli_stmt_close($insertStmt);
                }
            }
        }
    } elseif ($action === 'reject') {
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        
        if (empty($rejectionReason)) {
            $error = "Please provide a rejection reason.";
        } else {
            // Get request details for email
            $requestQuery = "SELECT * FROM card_registration_requests WHERE id = ? AND status = 'pending'";
            $requestStmt = mysqli_prepare($conn, $requestQuery);
            mysqli_stmt_bind_param($requestStmt, "i", $requestId);
            mysqli_stmt_execute($requestStmt);
            $requestResult = mysqli_stmt_get_result($requestStmt);
            $request = mysqli_fetch_assoc($requestResult);
            mysqli_stmt_close($requestStmt);
            
            if ($request) {
                // Update registration request (using reviewed_by and reviewed_at columns)
                $updateQuery = "UPDATE card_registration_requests SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "sii", $rejectionReason, $_SESSION['admin_id'], $requestId);
                
                if (mysqli_stmt_execute($updateStmt)) {
                    mysqli_stmt_close($updateStmt);
                    
                    // Send rejection email
                    require_once 'includes/EmailHelper.php';
                    $emailHelper = new EmailHelper();
                    $emailHelper->sendCardRejectionNotification($request['email'], $request['full_name'], $rejectionReason);
                    
                    $success = "Registration rejected. Email notification sent.";
                } else {
                    $error = "Failed to reject registration.";
                    mysqli_stmt_close($updateStmt);
                }
            }
        }
    }
}

// Get pending requests
$pendingQuery = "SELECT * FROM card_registration_requests WHERE status = 'pending' ORDER BY submitted_at DESC";
$pendingResult = mysqli_query($conn, $pendingQuery);

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM card_registration_requests
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Registration Requests - Holy Family High School Gate Security</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/enhanced-app.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning-gradient">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['pending_count']) ?></h5>
                                <small class="text-muted">Pending</small>
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
                                <h5 class="mb-0"><?= number_format($stats['approved_count']) ?></h5>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger-gradient">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['rejected_count']) ?></h5>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info-gradient">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0"><?= number_format($stats['total_requests']) ?></h5>
                                <small class="text-muted">Total Requests</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-clock"></i> Pending Registration Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Plate Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($request = mysqli_fetch_assoc($pendingResult)): ?>
                                <tr>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($request['submitted_at'])) ?></small>
                                        <br>
                                        <small class="text-muted"><?= date('g:i A', strtotime($request['submitted_at'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($request['full_name']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= ucfirst($request['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($request['plate_number'] ?: '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $request['id'] ?>, '<?= htmlspecialchars($request['full_name']) ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?= $request['id'] ?>, '<?= htmlspecialchars($request['full_name']) ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if (mysqli_num_rows($pendingResult) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No pending registration requests
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function approveRequest(requestId, fullName) {
            Swal.fire({
                title: 'Approve Registration',
                html: `
                    <p>Approve registration for <strong>${fullName}</strong></p>
                    <div class="form-group text-left mt-3">
                        <label for="rfid-input">RFID Card ID <span class="text-danger">*</span></label>
                        <input type="text" id="rfid-input" class="form-control" placeholder="Enter RFID ID" required>
                        <small class="form-text text-muted">Scan or enter the RFID card number</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Approve',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                preConfirm: () => {
                    const rfidId = document.getElementById('rfid-input').value.trim();
                    if (!rfidId) {
                        Swal.showValidationMessage('Please enter an RFID ID');
                        return false;
                    }
                    return { rfidId };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="request_id" value="${requestId}">
                        <input type="hidden" name="rfid_id" value="${result.value.rfidId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        function rejectRequest(requestId, fullName) {
            Swal.fire({
                title: 'Reject Registration',
                html: `
                    <p>Reject registration for <strong>${fullName}</strong></p>
                    <div class="form-group text-left mt-3">
                        <label for="rejection-reason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea id="rejection-reason" class="form-control" rows="3" placeholder="Enter reason for rejection" required></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-times"></i> Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                preConfirm: () => {
                    const reason = document.getElementById('rejection-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Please enter a rejection reason');
                        return false;
                    }
                    return { reason };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="request_id" value="${requestId}">
                        <input type="hidden" name="rejection_reason" value="${result.value.reason}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
