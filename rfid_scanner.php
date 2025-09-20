<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Scanner Simulator</title>
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

        .scanner-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(58, 67, 76, 0.2);
            max-width: 520px;
            width: 100%;
            border: 1px solid rgba(197, 187, 183, 0.3);
        }

        .scanner-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d3640 100%);
            color: var(--white);
            padding: 40px 30px;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }

        .rfid-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 40px 0;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .status-icon-large {
            font-size: 48px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .status-text {
            font-size: 24px;
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
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .btn-scan {
            background: linear-gradient(135deg, var(--success-green), #8a9470);
            border: none;
            border-radius: 12px;
            padding: 18px 35px;
            font-weight: 600;
            color: var(--white);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .btn-scan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(158, 165, 128, 0.4);
            color: var(--white);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid rgba(197, 187, 183, 0.5);
            background-color: var(--white);
            color: var(--text-dark);
        }

        .form-control:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(58, 67, 76, 0.25);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-dark);
            color: var(--primary-dark);
            border-radius: 10px;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-dark);
            color: var(--white);
        }

        .btn-outline-secondary {
            border: 2px solid var(--panel-bg);
            color: var(--text-dark);
            border-radius: 10px;
        }

        .btn-outline-secondary:hover {
            background-color: var(--panel-bg);
            color: var(--text-dark);
        }

        .status-indicator {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 12px;
        }

        .status-ready {
            background-color: var(--success-green);
        }

        .status-scanning {
            background-color: #ffc107;
            animation: pulse 1s infinite;
        }

        .status-error {
            background-color: #dc3545;
        }

        .card-body {
            padding: 30px;
            background-color: var(--white);
            color: var(--text-dark);
        }

        .text-muted {
            color: rgba(39, 41, 41, 0.6) !important;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="scanner-card">
        <div class="scanner-header">
            <i class="fas fa-wifi fa-3x mb-3"></i>
            <h3>RFID Scanner Simulator</h3>
            <p class="mb-0">Holy Family High School Gate</p>
        </div>

        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="status-indicator status-ready" id="statusIndicator"></div>
                <span id="statusText">Ready to scan</span>
            </div>

            <div class="rfid-display" id="rfidDisplay">
                <div id="statusIcon" class="status-icon-large">
                    <i class="fas fa-id-card"></i>
                </div>
                <div id="statusText" class="status-text">
                    Waiting for RFID card...
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" id="manualRFID" placeholder="Enter RFID ID manually" Auto-focus>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-primary btn-block" onclick="scanManualRFID()">
                        <i class="fas fa-keyboard"></i> Manual
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Sample RFIDs:</small>
                    <ul class="list-unstyled small">
                        <li><code>RFID001</code> - John Doe</li>
                        <li><code>RFID002</code> - Jane Smith</li>
                        <li><code>RFID003</code> - Mike Johnson</li>
                    </ul>
                </div>
                <div class="col-6">
                    <small class="text-muted">Test Cases:</small>
                    <ul class="list-unstyled small">
                        <li><code>RFID004</code> - Active</li>
                        <li><code>RFID005</code> - Visitor</li>
                        <li><code>UNKNOWN</code> - Not found</li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sampleRFIDs = ['RFID001', 'RFID002', 'RFID003', 'RFID004', 'RFID005', 'UNKNOWN', 'EXPIRED001'];

        function updateStatus(status, text) {
            const indicator = document.getElementById('statusIndicator');
            const statusText = document.getElementById('statusText');

            indicator.className = 'status-indicator status-' + status;
            statusText.textContent = text;
        }

        function scanManualRFID() {
            const manualRFID = document.getElementById('manualRFID').value.trim();
            if (manualRFID) {
                scanRFID(manualRFID);
                document.getElementById('manualRFID').value = '';
            } else {
                alert('Please enter an RFID ID');
            }
        }

        function scanRFID(rfidId) {
            updateStatus('scanning', 'Scanning...');
            
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
            .then(response => response.json())
            .then(data => {
                console.log('Scan result:', data);
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
            text.textContent = 'Checking card data...';
        }
        
        function showWaitingState() {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.remove('checking');
            icon.innerHTML = '<i class="fas fa-id-card"></i>';
            text.textContent = 'Waiting for RFID card...';
        }
        
        function showErrorState() {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.remove('checking');
            icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            text.textContent = 'Error occurred. Please try again.';
            
            setTimeout(() => {
                showWaitingState();
            }, 3000);
        }
        
        function displayResult(data) {
            const display = document.getElementById('rfidDisplay');
            const icon = document.getElementById('statusIcon');
            const text = document.getElementById('statusText');
            
            display.classList.remove('checking');
            
            if (data.success) {
                if (data.access_result === 'granted') {
                    updateStatus('ready', 'Access Granted');
                    icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    text.innerHTML = `<strong>ACCESS GRANTED</strong><br>${data.full_name || 'Unknown'}`;
                    display.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                } else {
                    updateStatus('error', 'Access Denied');
                    icon.innerHTML = '<i class="fas fa-times-circle"></i>';
                    text.innerHTML = `<strong>ACCESS DENIED</strong><br>${data.denial_reason || 'Unknown reason'}`;
                    display.style.background = 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
                }
            } else {
                updateStatus('error', 'Scan Error');
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                text.textContent = 'Error: ' + data.message;
                display.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
            }

            // Reset after 3 seconds
            setTimeout(() => {
                updateStatus('ready', 'Ready to scan');
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

        // Initialize the waiting state on page load
        document.addEventListener('DOMContentLoaded', function() {
            showWaitingState();
        });

        const input = document.getElementById('manualRFID');
        setInterval(() => {
            if (document.activeElement !== input) {
                input.focus();
            }
        }, 2000); // Focus every 2 seconds if not already focused

        // Auto-focus on manual input
        input.focus();
    </script>
</body>

</html>