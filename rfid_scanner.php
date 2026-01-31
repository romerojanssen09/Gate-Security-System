<?php
// Get database connection
require_once 'storage/database.php';

// Set timezone to PHT
date_default_timezone_set('Asia/Manila');

// Get today's access logs (limit 5)
$conn = getConnection();
$todayLogsQuery = "
    SELECT al.*, rc.full_name, rc.role, rc.plate_number
    FROM access_logs al
    LEFT JOIN rfid_cards rc ON al.card_id = rc.id
    WHERE DATE(al.access_timestamp) = CURDATE()
    ORDER BY al.access_timestamp DESC
    LIMIT 5
";
$todayLogsResult = mysqli_query($conn, $todayLogsQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Scanner - Holy Family High School Gate Security</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/enhanced-app.css" rel="stylesheet">
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
            background: linear-gradient(135deg, var(--page-bg) 0%, #f0ebe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
        }
        
        .scanner-container {
            padding: 20px;
            margin-top: 20px;
        }

        .scanner-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .main-scanner-panel {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 31, 77, 0.1);
            border: 1px solid rgba(0, 31, 77, 0.1);
            min-height: 600px;
        }

        .card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 31, 77, 0.1);
            border: 1px solid rgba(0, 31, 77, 0.1);
        }

        .scanner-header {
            background: linear-gradient(135deg, #001F4D 0%, #003366 100%);
            color: var(--white);
            padding: 30px 20px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }

        .sidebar-header {
            background: var(--panel-bg);
            color: var(--text-dark);
            padding: 20px;
            border-radius: 15px 15px 0 0;
            font-weight: 600;
        }

        .rfid-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 30px;
            border-radius: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin: 30px 0;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .status-icon-large {
            font-size: 40px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .status-text {
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .checking {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            animation: pulse 1.5s infinite;
        }
        
        .checking .status-icon-large {
            animation: spin 2s linear infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .timeout {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            animation: timeoutPulse 1.5s infinite;
        }
        
        .timeout .status-icon-large {
            animation: timeoutShake 0.5s ease-in-out;
        }
        
        @keyframes timeoutPulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
            }
            50% { 
                transform: scale(1.02); 
                box-shadow: 0 12px 35px rgba(255, 152, 0, 0.5);
            }
        }
        
        @keyframes timeoutShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .input-section {
            background: var(--page-bg);
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
        }

        .rfid-input {
            font-size: 18px;
            padding: 15px 20px;
            border: 3px solid var(--primary-dark);
            border-radius: 10px;
            background: var(--white);
            color: var(--text-dark);
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 15px rgba(58, 67, 76, 0.1);
            transition: all 0.3s ease;
        }

        .rfid-input:focus {
            border-color: var(--success-green);
            box-shadow: 0 0 0 0.2rem rgba(158, 165, 128, 0.25);
            background: var(--white);
            color: var(--text-dark);
            outline: none;
        }

        .rfid-input::placeholder {
            color: rgba(39, 41, 41, 0.6);
            font-weight: 500;
        }

        .btn-scan {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            color: var(--white);
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-scan:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: var(--white) !important;
        }

        .today-logs {
            padding: 0;
        }

        .log-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 8px 0;
            background: #F8F9FA;
            border-radius: 10px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .log-item:hover {
            background: rgba(0, 31, 77, 0.05);
            border-left-color: #001F4D;
            box-shadow: 0 2px 10px rgba(0, 31, 77, 0.1);
        }

        .log-rfid {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #001F4D;
            font-size: 13px;
        }

        .log-name {
            font-size: 12px;
            color: #003366;
            font-weight: 500;
            margin-top: 2px;
        }

        .log-time {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        .log-status {
            text-align: right;
        }

        .log-result {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }

        .result-granted {
            background: #28a745;
            color: white;
        }

        .result-timeout {
            background: #ff9800;
            color: white;
        }
        
        .log-item.timeout-entry {
            border-left-color: #ff9800 !important;
            background: rgba(255, 152, 0, 0.1) !important;
        }
        
        .log-item.timeout-entry:hover {
            background: rgba(255, 152, 0, 0.15) !important;
        }

        .log-role {
            font-size: 9px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .no-logs {
            padding: 20px;
        }

        .today-logs {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Real-time animations */
        .animate-new-log {
            animation: slideInLeft 0.5s ease-out;
            background: rgba(0, 31, 77, 0.1) !important;
            border-left-color: #001F4D !important;
        }
        
        @keyframes slideInLeft {
            0% {
                transform: translateX(-20px);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInRight {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        /* Real-time notification pulse effect */
        .real-time-notification {
            animation: slideInRight 0.3s ease, pulse 2s infinite 1s;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.02);
            }
        }
        


        .btn-outline-secondary {
            border: 2px solid #001F4D;
            color: #001F4D;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #001F4D;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 31, 77, 0.3);
            border-color: #001F4D;
        }
        
        .btn-outline-primary {
            border: 2px solid #001F4D;
            color: #001F4D;
            background: white;
        }
        
        .btn-outline-primary:hover {
            background: #001F4D;
            color: white !important;
            border-color: #001F4D;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #001F4D 0%, #003366 100%);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #003366 0%, #001F4D 100%);
            color: white !important;
        }
        
        .btn-outline-info {
            border: 2px solid #17a2b8;
            color: #17a2b8;
            background: white;
        }
        
        .btn-outline-info:hover {
            background: #17a2b8;
            color: white !important;
            border-color: #17a2b8;
        }

        .btn-success, .btn-danger {
            border-radius: 10px;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Additional Utility Classes */
        .d-grid {
            display: grid;
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
        
        .d-grid.gap-2 > * {
            margin-bottom: 0.5rem;
        }
        
        .d-grid.gap-2 > *:last-child {
            margin-bottom: 0;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .scanner-container {
                padding: 10px;
            }
            
            .rfid-display {
                min-height: 150px;
                padding: 30px 20px;
            }
            
            .status-icon-large {
                font-size: 32px;
            }
            
            .status-text {
                font-size: 16px;
            }
            
            .rfid-input {
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .col-lg-4 {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="scanner-container">
        
        <div class="row">
            <!-- Main Scanner Panel -->
            <div class="col-lg-8">
                <div class="main-scanner-panel">
                    <div class="scanner-header">
                        <i class="fas fa-wifi fa-3x mb-3"></i>
                        <h3>RFID Scanner Interface</h3>
                        <p class="mb-0">Real-time Gate Control System</p>
                    </div>

                    <div class="p-4">
                        <!-- RFID Display -->
                        <div class="rfid-display" id="rfidDisplay">
                            <div id="statusIcon" class="status-icon-large">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div id="statusText" class="status-text">
                                Ready to scan card
                            </div>
                        </div>

                        <!-- Input Section -->
                        <div class="input-section">
                            <label class="font-weight-bold text-dark mb-3">
                                <i class="fas fa-keyboard"></i> Manual RFID Entry
                            </label>
                            <div class="row">
                                <div class="col-8">
                                    <input type="text" class="form-control rfid-input" id="manualRFID" 
                                           placeholder="Enter RFID ID...">
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-scan" onclick="scanManualRFID()">
                                        <i class="fas fa-search"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle"></i> 
                                Type an RFID ID or click sample cards on the right
                            </small>
                        </div>

                        <!-- Manual Gate Control Section -->
                        <div class="input-section">
                            <label class="font-weight-bold text-dark mb-3">
                                <i class="fas fa-door-open"></i> Manual Gate Control
                            </label>
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn btn-success btn-block" onclick="sendManualCommand('manualopen')" id="openBtn">
                                        <i class="fas fa-unlock"></i> Open Gate
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-danger btn-block" onclick="sendManualCommand('manualclose')" id="closeBtn">
                                        <i class="fas fa-lock"></i> Close Gate
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Manual gate control - use with caution
                            </small>
                        </div>

                    </div>
                </div>
            </div> 
            <!-- Today's Access Logs -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header" style="background: #001F4D; color: #001F4D; border-bottom: 2px solid #003366;">
                        <h5><i class="fas fa-history"></i> Today's Access Logs</h5>
                        <small class="text-muted"><?= date('F d, Y') ?></small>
                    </div>
                    <div class="card-body p-0">
                        <div class="today-logs">
                            <?php if (mysqli_num_rows($todayLogsResult) > 0): ?>
                                <?php while ($log = mysqli_fetch_assoc($todayLogsResult)): ?>
                                <div class="log-item">
                                    <div class="log-info">
                                        <div class="log-rfid"><?= htmlspecialchars($log['rfid_id']) ?></div>
                                        <div class="log-name"><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></div>
                                        <div class="log-time"><?= date('g:i A', strtotime($log['access_timestamp'])) ?></div>
                                    </div>
                                    <div class="log-status">
                                        <span class="log-result <?= $log['access_result'] === 'granted' ? 'result-granted' : 'result-denied' ?>">
                                            <i class="fas fa-<?= $log['access_result'] === 'granted' ? 'check' : 'times' ?>"></i>
                                            <?= ucfirst($log['access_result']) ?>
                                        </span>
                                        <?php if ($log['role']): ?>
                                            <small class="log-role"><?= ucfirst($log['role']) ?></small>
                                        <?php endif; ?>
                                        <?php if ($log['denial_reason']): ?>
                                            <small class="text-danger d-block mt-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($log['denial_reason']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-logs">
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No access logs for today</p>
                                        <small class="text-muted">Logs will appear here when cards are scanned</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header" style="background: #001F4D; color: #001F4D; border-bottom: 2px solid #003366;">
                        <h5 class="text-white"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Landing Page
                            </a>
                            <a href="index.php?page=dashboard" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="index.php?page=rfid" class="btn btn-outline-secondary">
                                <i class="fas fa-users"></i> User Management
                            </a>
                            <a href="index.php?page=reports" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </div>
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
            console.log('✅ Pusher connected successfully (Scanner)');
        });
        
        pusher.connection.bind('error', function(err) {
            console.error('❌ Pusher connection error (Scanner):', err);
        });
        
        pusher.connection.bind('disconnected', function() {
            console.log('⚠️ Pusher disconnected (Scanner)');
        });
        
        // Subscribe to RFID access channel
        const rfidChannel = pusher.subscribe('rfid-access-channel');
        
        rfidChannel.bind('pusher:subscription_succeeded', function() {
            console.log('✅ Successfully subscribed to rfid-access-channel (Scanner)');
        });
        
        // Listen for new RFID scans to update the logs
        rfidChannel.bind('rfid-scanned', function(data) {
            console.log('🔔 New RFID scan received:', data);
            addNewLogToSidebar(data);
        });

        function scanManualRFID() {
            const manualRFID = document.getElementById('manualRFID').value.trim();
            if (manualRFID) {
                scanRFID(manualRFID);
                document.getElementById('manualRFID').value = '';
            } else {
                alert('Please enter an RFID ID');
            }
        }

        function sendManualCommand(command) {
            const openBtn = document.getElementById('openBtn');
            const closeBtn = document.getElementById('closeBtn');
            
            // Disable buttons during request
            openBtn.disabled = true;
            closeBtn.disabled = true;
            
            // Show status
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.add('checking');
            icon.innerHTML = '<i class="fas fa-cog"></i>';
            text.textContent = command === 'manualopen' ? 'Opening gate...' : 'Closing gate...';
            
            // Send command via PHP proxy to avoid CORS issues
            fetch('api/manual_control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    command: command
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Manual command result:', data);
                
                if (data.success) {
                    // Show success state
                    display.classList.remove('checking');
                    icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    text.innerHTML = `<strong>MANUAL OVERRIDE</strong><br>${command === 'manualopen' ? 'Gate opened' : 'Gate closed'}`;
                    display.style.background = command === 'manualopen' ? 
                        'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)' : 
                        'linear-gradient(135deg, #2196F3 0%, #1976D2 100%)';
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        showWaitingState();
                        display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                    }, 3000);
                } else {
                    // Show error from server
                    display.classList.remove('checking');
                    icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    text.textContent = 'Error: ' + (data.message || 'Operation failed');
                    display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        showWaitingState();
                        display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Manual command error:', error);
                
                // Show error state
                display.classList.remove('checking');
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                text.textContent = 'Connection error. Please try again.';
                display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
                
                // Reset after 3 seconds
                setTimeout(() => {
                    showWaitingState();
                    display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                }, 3000);
            })
            .finally(() => {
                // Re-enable buttons
                openBtn.disabled = false;
                closeBtn.disabled = false;
            });
        }

        function scanRFID(rfidId) {
            // Show checking state
            showCheckingState();

            // Send RFID data to API
            fetch('api/rfid_scan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rfid_id: rfidId,
                    gate_location: 'main_gate'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Server returned invalid JSON response');
                }
            })
            .then(data => {
                console.log('Scan result:', data);
                
                // Handle timeout errors silently
                if (data.is_timeout || !data.success) {
                    showTimeoutState(data.message || 'Please wait before scanning again');
                    return;
                }
                
                displayResult(data);
            })
            .catch(error => {
                console.error('Scan error:', error);
                showErrorState();
            });
        }
        
        function showCheckingState() {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.add('checking');
            icon.innerHTML = '<i class="fas fa-spinner"></i>';
            text.textContent = 'Verifying card...';
        }
        
        function showWaitingState() {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            // Clear any existing countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            display.classList.remove('checking', 'timeout');
            icon.innerHTML = '<i class="fas fa-id-card"></i>';
            text.textContent = 'Ready to scan card';
        }
        
        function showErrorState() {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.remove('checking');
            icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            text.textContent = 'Connection error. Please try again.';
            
            setTimeout(() => {
                showWaitingState();
            }, 3000);
        }
        
        let countdownInterval = null;
        
        function showTimeoutState(message) {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            // Clear any existing countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            display.classList.remove('checking');
            display.classList.add('timeout');
            icon.innerHTML = '<i class="fas fa-clock"></i>';
            
            // Extract seconds from message
            const secondsMatch = message.match(/(\d+)\s+seconds?/);
            if (secondsMatch) {
                let remainingSeconds = parseInt(secondsMatch[1]);
                
                // Update display with countdown
                const updateCountdown = () => {
                    if (remainingSeconds > 0) {
                        text.innerHTML = `<strong>GATE COOLDOWN</strong><br>Please wait <span style="font-size: 1.5em; font-weight: bold;">${remainingSeconds}</span> second${remainingSeconds !== 1 ? 's' : ''}`;
                        remainingSeconds--;
                    } else {
                        // Countdown finished
                        if (countdownInterval) {
                            clearInterval(countdownInterval);
                            countdownInterval = null;
                        }
                        text.innerHTML = `<strong>READY</strong><br>You can scan now`;
                        setTimeout(() => {
                            showWaitingState();
                            display.style.background = '';
                        }, 1000);
                    }
                };
                
                // Initial display
                updateCountdown();
                
                // Update every second
                countdownInterval = setInterval(updateCountdown, 1000);
            } else {
                text.innerHTML = `<strong>GATE COOLDOWN</strong><br>${message}`;
            }
            
            display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
            
            // Extended display time (5 seconds or until countdown finishes)
            setTimeout(() => {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                showWaitingState();
                display.style.background = '';
            }, 5000);
        }
        
        function displayResult(data) {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            // Clear any existing countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            display.classList.remove('checking');
            
            if (data.success) {
                if (data.access_result === 'granted') {
                    // Show ENTRY or EXIT based on access_type
                    const actionText = data.access_type === 'time_in' ? 'ENTRY GRANTED' : 'EXIT GRANTED';
                    const actionIcon = data.access_type === 'time_in' ? 'sign-in-alt' : 'sign-out-alt';
                    
                    icon.innerHTML = `<i class="fas fa-${actionIcon}"></i>`;
                    text.innerHTML = `<strong>${actionText}</strong><br>${data.full_name || 'Unknown'}<br><small>${data.plate_number || ''}</small>`;
                    display.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                } else if (data.is_timeout) {
                    // Special handling for timeout with countdown
                    display.classList.add('timeout');
                    icon.innerHTML = '<i class="fas fa-clock"></i>';
                    
                    // Extract seconds from denial_reason
                    const secondsMatch = data.denial_reason.match(/(\d+)\s+seconds?/);
                    if (secondsMatch) {
                        let remainingSeconds = parseInt(secondsMatch[1]);
                        
                        // Update display with countdown
                        const updateCountdown = () => {
                            if (remainingSeconds > 0) {
                                text.innerHTML = `<strong>GATE COOLDOWN</strong><br>Please wait <span style="font-size: 1.5em; font-weight: bold;">${remainingSeconds}</span> second${remainingSeconds !== 1 ? 's' : ''}`;
                                remainingSeconds--;
                            } else {
                                // Countdown finished
                                if (countdownInterval) {
                                    clearInterval(countdownInterval);
                                    countdownInterval = null;
                                }
                                text.innerHTML = `<strong>READY</strong><br>You can scan now`;
                                setTimeout(() => {
                                    showWaitingState();
                                    display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                                }, 1000);
                            }
                        };
                        
                        // Initial display
                        updateCountdown();
                        
                        // Update every second
                        countdownInterval = setInterval(updateCountdown, 1000);
                        
                        display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
                        
                        // Extended display time (5 seconds or until countdown finishes)
                        setTimeout(() => {
                            if (countdownInterval) {
                                clearInterval(countdownInterval);
                                countdownInterval = null;
                            }
                            display.classList.remove('timeout');
                            showWaitingState();
                            display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                        }, 5000);
                        
                        return; // Exit early to prevent the normal 3-second reset
                    } else {
                        text.innerHTML = `<strong>GATE COOLDOWN</strong><br>${data.denial_reason}`;
                    }
                    
                    display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
                    
                    // Remove timeout class after animation
                    setTimeout(() => {
                        display.classList.remove('timeout');
                    }, 2000);
                } else {
                    icon.innerHTML = '<i class="fas fa-times-circle"></i>';
                    text.innerHTML = `<strong>ACCESS DENIED</strong><br>${data.denial_reason || 'Card not authorized'}`;
                    display.style.background = 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
                }
            } else {
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                text.textContent = 'Error: ' + data.message;
                display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
            }

            // Reset after 3 seconds
            setTimeout(() => {
                showWaitingState();
                display.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }, 3000);
        }

        // Allow Enter key to trigger manual scan
        document.getElementById('manualRFID').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                scanManualRFID();
            }
        });

        // Add new log entry to sidebar
        function addNewLogToSidebar(data) {
            const logsContainer = document.querySelector('.today-logs');
            const noLogsElement = document.querySelector('.no-logs');
            
            // Remove "no logs" message if it exists
            if (noLogsElement) {
                noLogsElement.remove();
            }
            
            // Create new log item
            const logItem = document.createElement('div');
            let logClass = 'log-item animate-new-log';
            let resultClass = '';
            let icon = '';
            
            if (data.is_timeout) {
                logClass += ' timeout-entry';
                resultClass = 'result-timeout';
                icon = 'clock';
            } else if (data.access_result === 'granted') {
                resultClass = 'result-granted';
                icon = 'check';
            } else {
                resultClass = 'result-denied';
                icon = 'times';
            }
            
            logItem.className = logClass;
            
            logItem.innerHTML = `
                <div class="log-info">
                    <div class="log-rfid">${data.rfid_id}</div>
                    <div class="log-name">${data.full_name || 'Unknown'}</div>
                    <div class="log-time">${new Date().toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    })}</div>
                </div>
                <div class="log-status">
                    <span class="log-result ${resultClass}">
                        <i class="fas fa-${icon}"></i>
                        ${data.is_timeout ? 'Timeout' : data.access_result.charAt(0).toUpperCase() + data.access_result.slice(1)}
                    </span>
                    ${data.role ? `<small class="log-role">${data.role.charAt(0).toUpperCase() + data.role.slice(1)}</small>` : ''}
                    ${data.denial_reason ? `<small class="text-danger d-block mt-1" style="font-size: 0.75rem;"><i class="fas fa-exclamation-circle"></i> ${data.denial_reason}</small>` : ''}
                </div>
            `;
            
            // Insert at the top
            logsContainer.insertBefore(logItem, logsContainer.firstChild);
            
            // Remove animation class after animation completes
            setTimeout(() => {
                logItem.classList.remove('animate-new-log');
            }, 1000);
            
            // Keep only 5 most recent logs
            const logItems = logsContainer.querySelectorAll('.log-item');
            if (logItems.length > 5) {
                logItems[logItems.length - 1].remove();
            }
        }
        


        // Initialize the waiting state on page load
        document.addEventListener('DOMContentLoaded', function() {
            showWaitingState();
            // Focus on input after a short delay to avoid autofocus warning
            setTimeout(() => {
                document.getElementById('manualRFID').focus();
            }, 500);
        });
    </script>
</body>

</html>