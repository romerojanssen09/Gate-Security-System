<?php
/**
 * Admin Dashboard - Main page showing RFID access logs
 */

require_once __DIR__ . "/../../models/Admin.php";

// Set timezone to PHT
date_default_timezone_set('Asia/Manila');

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

// Get recent access logs (last 50 entries)
$logsQuery = "
    SELECT al.*, rc.full_name, rc.role, rc.plate_number, rc.status
    FROM access_logs al
    LEFT JOIN rfid_cards rc ON al.card_id = rc.id
    ORDER BY al.access_timestamp DESC
    LIMIT 50
";
$logsResult = mysqli_query($conn, $logsQuery);

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_logs,
        SUM(CASE WHEN access_result = 'granted' THEN 1 ELSE 0 END) as granted_count,
        SUM(CASE WHEN access_result = 'denied' THEN 1 ELSE 0 END) as denied_count,
        SUM(CASE WHEN DATE(access_timestamp) = CURDATE() THEN 1 ELSE 0 END) as today_count
    FROM access_logs
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

// Get active RFID cards count
$cardsQuery = "SELECT COUNT(*) as active_cards FROM rfid_cards WHERE status = 'active'";
$cardsResult = mysqli_query($conn, $cardsQuery);
$cardsStats = mysqli_fetch_assoc($cardsResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Holy Family High School Gate Security</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/enhanced-app.css" rel="stylesheet">
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
                        <div class="stat-icon bg-success-gradient">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="ml-3">
                            <h5 class="mb-0" id="grantedCount"><?= number_format($stats['granted_count']) ?></h5>
                            <small class="text-muted">Access Granted</small>
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
                            <h5 class="mb-0" id="deniedCount"><?= number_format($stats['denied_count']) ?></h5>
                            <small class="text-muted">Access Denied</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info-gradient">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="ml-3">
                            <h5 class="mb-0" id="todayCount"><?= number_format($stats['today_count']) ?></h5>
                            <small class="text-muted">Today's Access</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning-gradient">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="ml-3">
                            <h5 class="mb-0" id="activeCards"><?= number_format($cardsStats['active_cards']) ?></h5>
                            <small class="text-muted">Active Cards</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Access Logs -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history"></i> Recent Access Logs (Last 50)
                            </h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary text-white" onclick="refreshLogs()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>

                                <a href="index.php?page=reports" class="btn btn-sm btn-outline-secondary ml-2 text-white">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>RFID ID</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Plate Number</th>
                                    <th>Result</th>
                                    <th>Gate</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                                <?php while ($log = mysqli_fetch_assoc($logsResult)): ?>
                                <tr>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($log['access_timestamp'])) ?></small>
                                        <br/>
                                        <small class="text-muted"><?= date('g:i:s A', strtotime($log['access_timestamp'])) ?></small>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($log['rfid_id']) ?></code>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?>
                                    </td>
                                    <td>
                                        <?php if ($log['role']): ?>
                                            <span class="badge badge-secondary">
                                                <?= ucfirst($log['role']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($log['plate_number'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $log['access_result'] === 'granted' ? 'granted' : 'denied' ?>">
                                            <i class="fas fa-<?= $log['access_result'] === 'granted' ? 'check' : 'times' ?>"></i>
                                            <?= ucfirst($log['access_result']) ?>
                                        </span>
                                        <?php if ($log['denial_reason']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($log['denial_reason']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($log['gate_location']) ?></small>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if (mysqli_num_rows($logsResult) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No access logs found
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation to Reports -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="index.php?page=reports" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-chart-bar"></i> View Detailed Reports
                </a>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher for real-time updates
        const pusher = new Pusher('5b24b867b55f7decb7a5', {
            cluster: 'ap1',
            useTLS: true
        });
        
        // Debug Pusher connection
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher connected successfully');
        });
        
        pusher.connection.bind('error', function(err) {
            console.error('❌ Pusher connection error:', err);
        });
        
        pusher.connection.bind('disconnected', function() {
            console.log('⚠️ Pusher disconnected');
        });
        
        // Subscribe to RFID access channel
        const rfidChannel = pusher.subscribe('rfid-access-channel');
        
        rfidChannel.bind('pusher:subscription_succeeded', function() {
            console.log('✅ Successfully subscribed to rfid-access-channel');
        });
        
        rfidChannel.bind('pusher:subscription_error', function(err) {
            console.error('❌ Subscription error:', err);
        });
        
        rfidChannel.bind('rfid-scanned', function(data) {
            console.log('🔔 RFID Scanned Event Received:', data);
            addNewLogEntry(data);
            updateStatistics();
        });
        
        // Subscribe to gate status channel
        const gateChannel = pusher.subscribe('gate-status-channel');
        gateChannel.bind('gate-status-changed', function(data) {
            console.log('Gate Status:', data);
            showGateStatus(data);
        });
        
        function addNewLogEntry(data) {
            const tbody = document.querySelector('#logsTableBody');
            
            // Check if tbody has any rows with "No access logs found" message
            const noDataRow = tbody.querySelector('td[colspan="7"]');
            if (noDataRow) {
                tbody.innerHTML = ''; // Clear the "no data" message
            }
            
            const newRow = document.createElement('tr');
            newRow.className = 'table-success'; // Highlight new entry
            
            const badgeClass = data.access_result === 'granted' ? 'badge-granted' : 'badge-denied';
            const icon = data.access_result === 'granted' ? 'check' : 'times';
            
            newRow.innerHTML = `
                <td><small>${new Date(data.timestamp).toLocaleString('en-US', {
                    timeZone: 'Asia/Manila',
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                })}</small></td>
                <td><code>${data.rfid_id}</code></td>
                <td>${data.full_name || 'Unknown'}</td>
                <td>${data.role ? '<span class="badge badge-secondary">' + data.role.charAt(0).toUpperCase() + data.role.slice(1) + '</span>' : '<span class="text-muted">-</span>'}</td>
                <td>${data.plate_number || '-'}</td>
                <td>
                    <span class="badge ${badgeClass}">
                        <i class="fas fa-${icon}"></i> ${data.access_result.charAt(0).toUpperCase() + data.access_result.slice(1)}
                    </span>
                    ${data.denial_reason ? '<br><small class="text-muted">' + data.denial_reason + '</small>' : ''}
                </td>
                <td><small>${data.gate_location}</small></td>
            `;
            
            // Insert at the top
            tbody.insertBefore(newRow, tbody.firstChild);
            
            // Remove highlight after 3 seconds
            setTimeout(() => {
                newRow.classList.remove('table-success');
            }, 3000);
            
            // Maintain only 50 rows - remove from bottom when exceeding limit
            while (tbody.children.length > 50) {
                tbody.removeChild(tbody.lastChild);
            }
        }
        
        function updateStatistics() {
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update statistics with animation
                        animateCounterUpdate('grantedCount', data.stats.granted_count);
                        animateCounterUpdate('deniedCount', data.stats.denied_count);
                        animateCounterUpdate('todayCount', data.stats.today_count);
                        animateCounterUpdate('activeCards', data.stats.active_cards);
                    }
                })
                .catch(error => {
                    console.error('Error updating statistics:', error);
                });
        }
        
        function animateCounterUpdate(elementId, newValue) {
            const element = document.getElementById(elementId);
            const currentValue = parseInt(element.textContent.replace(/,/g, ''));
            
            if (currentValue !== newValue) {
                // Add pulse animation
                element.parentElement.parentElement.parentElement.classList.add('stat-updated');
                
                // Update the value with formatting
                element.textContent = new Intl.NumberFormat().format(newValue);
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    element.parentElement.parentElement.parentElement.classList.remove('stat-updated');
                }, 1000);
            }
        }
        

        
        function showGateStatus(data) {
            const statusBadge = $('#gateStatus');
            if (statusBadge.length === 0) {
                // Add gate status indicator if it doesn't exist
                $('.navbar-brand').after(`
                    <span id="gateStatus" class="badge badge-secondary ml-2">
                        <i class="fas fa-door-closed"></i> Gate Closed
                    </span>
                `);
            }
            
            const badge = $('#gateStatus');
            if (data.status === 'opening') {
                badge.removeClass().addClass('badge badge-warning ml-2')
                     .html('<i class="fas fa-door-open"></i> Gate Opening');
            } else if (data.status === 'closing') {
                badge.removeClass().addClass('badge badge-secondary ml-2')
                     .html('<i class="fas fa-door-closed"></i> Gate Closed');
            }
        }
        
        // No polling - only Pusher real-time updates
        
        // Manual refresh function
        function refreshLogs() {
            location.reload();
        }
        

        

    </script>
</body>
</html>