<?php
/**
 * Reports Page - Access Logs with Excel Export
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

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    
    // Build query with date filters
    $whereClause = "1=1";
    $params = [];
    $types = "";
    
    if (!empty($startDate)) {
        $whereClause .= " AND DATE(al.access_timestamp) >= ?";
        $params[] = $startDate;
        $types .= "s";
    }
    
    if (!empty($endDate)) {
        $whereClause .= " AND DATE(al.access_timestamp) <= ?";
        $params[] = $endDate;
        $types .= "s";
    }
    
    $exportQuery = "
        SELECT 
            al.access_timestamp,
            al.rfid_id,
            COALESCE(rc.full_name, 'Unknown') as full_name,
            COALESCE(rc.role, 'N/A') as role,
            COALESCE(rc.plate_number, 'N/A') as plate_number,
            al.access_result,
            COALESCE(al.denial_reason, 'N/A') as denial_reason,
            al.gate_location
        FROM access_logs al
        LEFT JOIN rfid_cards rc ON al.card_id = rc.id
        WHERE $whereClause
        ORDER BY al.access_timestamp DESC
    ";
    
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $exportQuery);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $exportResult = mysqli_stmt_get_result($stmt);
    } else {
        $exportResult = mysqli_query($conn, $exportQuery);
    }
    
    // Generate Excel file
    $filename = "access_logs_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, [
        'Timestamp',
        'RFID ID',
        'Full Name',
        'Role',
        'Plate Number',
        'Access Result',
        'Denial Reason',
        'Gate Location'
    ]);
    
    // CSV Data
    while ($row = mysqli_fetch_assoc($exportResult)) {
        fputcsv($output, [
            $row['access_timestamp'],
            $row['rfid_id'],
            $row['full_name'],
            $row['role'],
            $row['plate_number'],
            $row['access_result'],
            $row['denial_reason'],
            $row['gate_location']
        ]);
    }
    
    fclose($output);
    exit;
}

// Get filter parameters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$result = $_GET['result'] ?? '';
$page = max(1, intval($_GET['p'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

// Build query with filters
$whereClause = "1=1";
$params = [];
$types = "";

if (!empty($startDate)) {
    $whereClause .= " AND DATE(al.access_timestamp) >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (!empty($endDate)) {
    $whereClause .= " AND DATE(al.access_timestamp) <= ?";
    $params[] = $endDate;
    $types .= "s";
}

if (!empty($result)) {
    $whereClause .= " AND al.access_result = ?";
    $params[] = $result;
    $types .= "s";
}

// Get total count
$countQuery = "
    SELECT COUNT(*) as total
    FROM access_logs al
    LEFT JOIN rfid_cards rc ON al.card_id = rc.id
    WHERE $whereClause
";

if (!empty($params)) {
    $countStmt = mysqli_prepare($conn, $countQuery);
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
} else {
    $countResult = mysqli_query($conn, $countQuery);
}

$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get logs with pagination
$logsQuery = "
    SELECT al.*, rc.full_name, rc.role, rc.plate_number, rc.status
    FROM access_logs al
    LEFT JOIN rfid_cards rc ON al.card_id = rc.id
    WHERE $whereClause
    ORDER BY al.access_timestamp DESC
    LIMIT ? OFFSET ?
";

// Create separate arrays for pagination query
$paginationParams = $params;
$paginationTypes = $types;
$paginationParams[] = $limit;
$paginationParams[] = $offset;
$paginationTypes .= "ii";

if (!empty($paginationParams)) {
    $stmt = mysqli_prepare($conn, $logsQuery);
    mysqli_stmt_bind_param($stmt, $paginationTypes, ...$paginationParams);
    mysqli_stmt_execute($stmt);
    $logsResult = mysqli_stmt_get_result($stmt);
} else {
    $logsResult = mysqli_query($conn, $logsQuery);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Gate Security System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
        <!-- Filters and Export -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Filters & Export</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <input type="hidden" name="page" value="reports">
                    
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label>Result</label>
                        <select class="form-control" name="result">
                            <option value="">All</option>
                            <option value="granted" <?= $result === 'granted' ? 'selected' : '' ?>>Granted</option>
                            <option value="denied" <?= $result === 'denied' ? 'selected' : '' ?>>Denied</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-success btn-block" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Access Logs Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-list"></i> Access Logs (<span id="totalRecords"><?= number_format($totalRecords) ?></span> records)</h5>
                    <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
                </div>
            </div>
            <div class="card-body p-0">
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
                        <tbody id="reportsTableBody">
                            <?php while ($log = mysqli_fetch_assoc($logsResult)): ?>
                            <tr>
                                <td>
                                    <small><?= date('M d, Y H:i:s', strtotime($log['access_timestamp'])) ?></small>
                                </td>
                                <td><code><?= htmlspecialchars($log['rfid_id']) ?></code></td>
                                <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
                                <td>
                                    <?php if ($log['role']): ?>
                                        <span class="badge badge-secondary"><?= ucfirst($log['role']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['plate_number'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-<?= $log['access_result'] === 'granted' ? 'granted' : 'denied' ?>">
                                        <i class="fas fa-<?= $log['access_result'] === 'granted' ? 'check' : 'times' ?>"></i>
                                        <?= ucfirst($log['access_result']) ?>
                                    </span>
                                    <?php if ($log['denial_reason']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($log['denial_reason']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($log['gate_location']) ?></small></td>
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
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=reports&p=<?= $page - 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&result=<?= $result ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=reports&p=<?= $i ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&result=<?= $result ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=reports&p=<?= $page + 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&result=<?= $result ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher for real-time updates
        const pusher = new Pusher('5b24b867b55f7decb7a5', {
            cluster: 'ap1',
            useTLS: true
        });
        
        // Debug Pusher connection
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher connected successfully (Reports)');
        });
        
        pusher.connection.bind('error', function(err) {
            console.error('❌ Pusher connection error (Reports):', err);
        });
        
        // Subscribe to RFID access channel for real-time updates
        const rfidChannel = pusher.subscribe('rfid-access-channel');
        
        rfidChannel.bind('pusher:subscription_succeeded', function() {
            console.log('✅ Successfully subscribed to rfid-access-channel (Reports)');
        });
        
        rfidChannel.bind('pusher:subscription_error', function(err) {
            console.error('❌ Subscription error (Reports):', err);
        });
        
        rfidChannel.bind('rfid-scanned', function(data) {
            console.log('🔔 RFID Scanned Event Received (Reports):', data);
            addNewLogToReports(data);
        });
        
        function addNewLogToReports(data) {
            const tbody = document.querySelector('#reportsTableBody');
            
            // Only add to first page and if no filters are applied
            const currentPage = <?= $page ?>;
            const hasFilters = '<?= $startDate . $endDate . $result ?>' !== '';
            
            if (currentPage !== 1 || hasFilters) {
                // Just update the record count for other pages or filtered views
                updateRecordCount();
                return;
            }
            
            // Check if tbody has any rows with "No access logs found" message
            const noDataRow = tbody.querySelector('td[colspan="7"]');
            if (noDataRow) {
                tbody.innerHTML = ''; // Clear the "no data" message
            }
            
            const newRow = document.createElement('tr');
            newRow.className = 'table-warning animate-new-row'; // Highlight ONLY new entry
            
            const badgeClass = data.access_result === 'granted' ? 'badge-granted' : 'badge-denied';
            const icon = data.access_result === 'granted' ? 'check' : 'times';
            
            newRow.innerHTML = `
                <td><small>${new Date(data.timestamp).toLocaleString()}</small></td>
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
            
            // Remove highlight after 3 seconds (shorter duration)
            setTimeout(() => {
                newRow.classList.remove('table-warning', 'animate-new-row');
            }, 3000);
            
            // Remove last row if we exceed the page limit
            const maxRows = 50;
            if (tbody.children.length > maxRows) {
                tbody.removeChild(tbody.lastChild);
            }
            
            // Update record count
            updateRecordCount();
        }
        
        function updateRecordCount() {
            // Increment the total records count
            const recordsElement = document.getElementById('totalRecords');
            const currentCount = parseInt(recordsElement.textContent.replace(/,/g, ''));
            recordsElement.textContent = new Intl.NumberFormat().format(currentCount + 1);
        }
        

        
        // No polling - only Pusher real-time updates
        
        function exportToExcel() {
            // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split('T')[0];
            
            Swal.fire({
                title: 'Export to Excel',
                html: `
                    <div class="form-group text-left">
                        <label for="export-start-date">Start Date:</label>
                        <input type="date" id="export-start-date" class="form-control" value="">
                    </div>
                    <div class="form-group text-left">
                        <label for="export-end-date">End Date:</label>
                        <input type="date" id="export-end-date" class="form-control" value="${today}">
                    </div>
                    <small class="text-muted">Leave start date empty to export from beginning. End date defaults to today.</small>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download"></i> Export',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                preConfirm: () => {
                    const startDate = document.getElementById('export-start-date').value;
                    const endDate = document.getElementById('export-end-date').value;
                    
                    // Validate date range
                    if (startDate && endDate && startDate > endDate) {
                        Swal.showValidationMessage('Start date cannot be after end date');
                        return false;
                    }
                    
                    return { startDate, endDate };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { startDate, endDate } = result.value;
                    
                    // Get current filter values
                    const currentResult = document.querySelector('select[name="result"]').value;
                    
                    let exportUrl = '?page=reports&export=excel';
                    if (startDate) exportUrl += '&start_date=' + startDate;
                    if (endDate) exportUrl += '&end_date=' + endDate;
                    if (currentResult) exportUrl += '&result=' + currentResult;
                    
                    // Show loading
                    Swal.fire({
                        title: 'Exporting...',
                        text: 'Please wait while we prepare your Excel file',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Trigger download
                    window.location.href = exportUrl;
                    
                    // Close loading after a short delay
                    setTimeout(() => {
                        Swal.close();
                    }, 2000);
                }
            });
        }
    </script>
</body>
</html>