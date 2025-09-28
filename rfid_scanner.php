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
            background-color: var(--page-bg);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .scanner-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .main-scanner-panel {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(58, 67, 76, 0.1);
            border: 1px solid rgba(197, 187, 183, 0.3);
            min-height: 600px;
        }

        .sidebar-panel {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(58, 67, 76, 0.1);
            border: 1px solid rgba(197, 187, 183, 0.3);
            height: fit-content;
        }

        .scanner-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d3640 100%);
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
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
            background: linear-gradient(135deg, var(--success-green), #8a9470);
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
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(158, 165, 128, 0.4);
            color: var(--white);
        }

        .sample-rfids {
            padding: 20px;
        }

        .sample-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            margin: 8px 0;
            background: var(--page-bg);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .sample-item:hover {
            background: rgba(158, 165, 128, 0.1);
            border-color: var(--success-green);
            transform: translateX(5px);
        }

        .sample-rfid {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary-dark);
        }

        .sample-name {
            font-size: 12px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .sample-status {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #28a745;
            color: white;
        }

        .status-inactive {
            background: #dc3545;
            color: white;
        }

        .status-unknown {
            background: #6c757d;
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid var(--primary-dark);
            color: var(--primary-dark);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--primary-dark);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(58, 67, 76, 0.3);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .sidebar-panel {
                margin-bottom: 20px;
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
        }
    </style>
</head>

<body>
    <div class="scanner-container">
        <div class="row">
            <!-- Left Main Scanner -->
            <div class="col-lg-12 col-md-7">
                <div class="main-scanner-panel">
                    <div class="scanner-header">
                        <i class="fas fa-wifi fa-3x mb-3"></i>
                        <h3>RFID Scanner Simulator</h3>
                        <p class="mb-0">Holy Family High School Gate</p>
                    </div>

                    <div class="p-4">
                        <!-- RFID Display -->
                        <div class="rfid-display" id="rfidDisplay">
                            <div id="statusIcon" class="status-icon-large">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div id="statusText" class="status-text">
                                Waiting for RFID card...
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

                        <!-- Navigation -->
                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
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
        
        // Subscribe to RFID access channel
        const rfidChannel = pusher.subscribe('rfid-access-channel');
        
        rfidChannel.bind('pusher:subscription_succeeded', function() {
            console.log('✅ Successfully subscribed to rfid-access-channel (Scanner)');
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
                    icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    text.innerHTML = `<strong>ACCESS GRANTED</strong><br>${data.full_name || 'Unknown'}`;
                    display.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                } else {
                    icon.innerHTML = '<i class="fas fa-times-circle"></i>';
                    text.innerHTML = `<strong>ACCESS DENIED</strong><br>${data.denial_reason || 'Unknown reason'}`;
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