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
            background: var(--text-dark);
            color: var(--success-green);
            font-family: 'Courier New', monospace;
            font-size: 24px;
            padding: 25px;
            text-align: center;
            border-radius: 12px;
            margin: 25px 0;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(197, 187, 183, 0.3);
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
        .status-ready { background-color: var(--success-green); }
        .status-scanning { background-color: #ffc107; animation: pulse 1s infinite; }
        .status-error { background-color: #dc3545; }
        
        .card-body {
            padding: 30px;
            background-color: var(--white);
            color: var(--text-dark);
        }
        
        .text-muted {
            color: rgba(39, 41, 41, 0.6) !important;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                Waiting for RFID card...
            </div>
            
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" id="manualRFID" placeholder="Enter RFID ID manually">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-primary btn-block" onclick="scanManualRFID()">
                        <i class="fas fa-keyboard"></i> Manual
                    </button>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <button class="btn btn-scan btn-block" onclick="simulateRFIDScan()">
                    <i class="fas fa-wifi"></i> Simulate RFID Scan
                </button>
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
        
        function simulateRFIDScan() {
            const randomRFID = sampleRFIDs[Math.floor(Math.random() * sampleRFIDs.length)];
            scanRFID(randomRFID);
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
            document.getElementById('rfidDisplay').textContent = 'Scanning: ' + rfidId;
            
            // Simulate scanning delay
            setTimeout(() => {
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
                    
                    if (data.success) {
                        const resultText = data.access_result.toUpperCase();
                        const displayText = `${rfidId}\n${resultText}\n${data.full_name || 'Unknown'}`;
                        
                        document.getElementById('rfidDisplay').innerHTML = displayText.replace(/\n/g, '<br>');
                        
                        if (data.access_result === 'granted') {
                            updateStatus('ready', 'Access Granted');
                            document.getElementById('rfidDisplay').style.color = '#00ff00';
                        } else {
                            updateStatus('error', 'Access Denied');
                            document.getElementById('rfidDisplay').style.color = '#ff0000';
                        }
                    } else {
                        updateStatus('error', 'Scan Error');
                        document.getElementById('rfidDisplay').textContent = 'Error: ' + data.message;
                        document.getElementById('rfidDisplay').style.color = '#ff0000';
                    }
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        updateStatus('ready', 'Ready to scan');
                        document.getElementById('rfidDisplay').textContent = 'Waiting for RFID card...';
                        document.getElementById('rfidDisplay').style.color = '#00ff00';
                    }, 3000);
                })
                .catch(error => {
                    console.error('Scan error:', error);
                    updateStatus('error', 'Connection Error');
                    document.getElementById('rfidDisplay').textContent = 'Connection Error';
                    document.getElementById('rfidDisplay').style.color = '#ff0000';
                    
                    setTimeout(() => {
                        updateStatus('ready', 'Ready to scan');
                        document.getElementById('rfidDisplay').textContent = 'Waiting for RFID card...';
                        document.getElementById('rfidDisplay').style.color = '#00ff00';
                    }, 3000);
                });
            }, 1000);
        }
        
        // Allow Enter key to trigger manual scan
        document.getElementById('manualRFID').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                scanManualRFID();
            }
        });
        
        // Auto-focus on manual input
        document.getElementById('manualRFID').focus();
    </script>
</body>
</html>