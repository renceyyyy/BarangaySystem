<?php
/**
 * System Validation Test Page
 * Use this page to quickly test if all services are accessible
 * Access: http://localhost/BarangaySystem/BarangaySystem/test_system.php
 */

require_once __DIR__ . '/config/session_resident.php';
require_once './Process/db_connection.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['Firstname'] ?? 'Guest';
$userId = $_SESSION['user_id'] ?? 'N/A';
$sessionName = session_name();

// Test database connection
$dbStatus = 'Disconnected';
$dbError = '';
try {
    $conn = getDBConnection();
    if ($conn && $conn->ping()) {
        $dbStatus = 'Connected';
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// Test all service pages
$services = [
    'Government Documents' => '../NewRequests/NewGovernmentDocs.php',
    'Business Request' => '../NewRequests/NewBusinessRequest.php',
    'Complain' => '../NewRequests/NewComplain.php',
    'Scholarship' => '../NewRequests/NewScholar.php',
    'No Fixed Income' => '../NewRequests/NewNoFixIncome.php',
    'Guardianship' => '../NewRequests/NewGuardianshipForm.php',
    'Cohabitation' => '../NewRequests/CohabilitationForm.php'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Validation Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4CAF50, #2c5f2d);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        
        .status-card h2 {
            color: #2c5f2d;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .info-item strong {
            display: block;
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .info-item span {
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .service-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .service-card:hover {
            border-color: #4CAF50;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.3);
        }
        
        .service-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .service-card a {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #4CAF50, #2c5f2d);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .service-card a:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .test-result {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .test-result.pass {
            background: #d4edda;
            color: #155724;
        }
        
        .test-result.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert.info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 System Validation Test</h1>
            <p>Barangay Sampaguita Management System</p>
        </div>
        
        <div class="content">
            <?php if (!$isLoggedIn): ?>
                <div class="alert warning">
                    <strong>⚠️ Not Logged In</strong><br>
                    Please login first to test all features. Some tests may be limited.
                    <br><br>
                    <a href="Login/login.php" style="color: #856404; font-weight: bold; text-decoration: underline;">Click here to login</a>
                </div>
            <?php else: ?>
                <div class="alert info">
                    <strong>✅ Logged In Successfully</strong><br>
                    You can now test all services and features.
                </div>
            <?php endif; ?>
            
            <!-- System Status -->
            <div class="status-card">
                <h2>📊 System Status</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Login Status</strong>
                        <span>
                            <?php if ($isLoggedIn): ?>
                                <span class="status-badge success">✓ Logged In</span>
                            <?php else: ?>
                                <span class="status-badge error">✗ Not Logged In</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <strong>Database</strong>
                        <span>
                            <?php if ($dbStatus === 'Connected'): ?>
                                <span class="status-badge success">✓ Connected</span>
                            <?php else: ?>
                                <span class="status-badge error">✗ Disconnected</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <strong>Session Name</strong>
                        <span><?php echo htmlspecialchars($sessionName); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <strong>User</strong>
                        <span><?php echo htmlspecialchars($userName); ?></span>
                    </div>
                </div>
                
                <?php if ($dbError): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <strong>Database Error:</strong> <?php echo htmlspecialchars($dbError); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Services Test -->
            <div class="status-card">
                <h2>🛠️ Service Pages Test</h2>
                <p style="color: #666; margin-bottom: 15px;">
                    Click on each service to test if it loads correctly without logging you out.
                </p>
                
                <div class="service-grid">
                    <?php foreach ($services as $name => $path): ?>
                        <div class="service-card">
                            <h3><?php echo htmlspecialchars($name); ?></h3>
                            <span class="test-result pending">Ready to Test</span>
                            <br><br>
                            <a href="<?php echo htmlspecialchars($path); ?>" target="_blank">
                                Open Service →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Notification Test -->
            <div class="status-card">
                <h2>🔔 Real-Time Notification Test</h2>
                <p style="color: #666; margin-bottom: 15px;">
                    The notification system checks every 30 seconds for new approvals/declines.
                </p>
                <div id="notificationTest" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">
                    <p><strong>Status:</strong> <span id="notifStatus">Waiting for check...</span></p>
                    <p><strong>Last Check:</strong> <span id="lastCheck">N/A</span></p>
                    <button onclick="testNotifications()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                        Test Notification System
                    </button>
                </div>
            </div>
            
            <!-- Navigation -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="Pages/landingpage.php" class="btn-back">
                    ← Back to Landing Page
                </a>
                <?php if (!$isLoggedIn): ?>
                    <a href="Login/login.php" class="btn-back" style="background: #4CAF50;">
                        Login →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function testNotifications() {
            const statusEl = document.getElementById('notifStatus');
            const lastCheckEl = document.getElementById('lastCheck');
            
            statusEl.textContent = 'Checking...';
            statusEl.style.color = '#ffc107';
            
            fetch('Process/check_new_updates.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'check_updates=1&user_id=<?php echo $userId; ?>'
            })
            .then(response => response.json())
            .then(data => {
                statusEl.textContent = '✓ System Working';
                statusEl.style.color = '#28a745';
                lastCheckEl.textContent = new Date().toLocaleTimeString();
                
                if (data.hasNewApprovals) {
                    alert('✅ You have new approvals!');
                }
                if (data.hasNewDeclines) {
                    alert('⚠️ You have new declines!');
                }
                if (!data.hasNewApprovals && !data.hasNewDeclines) {
                    alert('ℹ️ No new updates. System is working correctly!');
                }
            })
            .catch(error => {
                statusEl.textContent = '✗ Error: ' + error.message;
                statusEl.style.color = '#dc3545';
            });
        }
        
        // Auto-test notification system on page load (if logged in)
        <?php if ($isLoggedIn): ?>
        window.addEventListener('load', function() {
            setTimeout(testNotifications, 2000);
        });
        <?php endif; ?>
    </script>
</body>
</html>
