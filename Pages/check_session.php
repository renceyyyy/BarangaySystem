<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background: #f5f5f5;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <h1>Session & Notification Status Check</h1>
    
    <div class="info-box">
        <h2>Current Session Info:</h2>
        <?php
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            echo "<p class='success'>✅ Logged in</p>";
            echo "<p><strong>User ID:</strong> " . htmlspecialchars($_SESSION['user_id']) . "</p>";
            echo "<p><strong>Role:</strong> " . htmlspecialchars($_SESSION['role'] ?? 'Not set') . "</p>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($_SESSION['username'] ?? 'Not set') . "</p>";
            echo "<p><strong>Full Name:</strong> " . htmlspecialchars($_SESSION['fullname'] ?? 'Not set') . "</p>";
        } else {
            echo "<p class='error'>❌ Not logged in</p>";
        }
        ?>
    </div>
    
    <div class="info-box">
        <h2>Real-time Notification Check:</h2>
        <p id="status">Testing...</p>
        <pre id="result" style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 300px; overflow: auto;"></pre>
    </div>
    
    <div class="info-box">
        <h2>localStorage Status:</h2>
        <pre id="localStorage" style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 300px; overflow: auto;"></pre>
    </div>
    
    <script>
        // Check localStorage
        const localStorageDiv = document.getElementById('localStorage');
        const allKeys = Object.keys(localStorage);
        const barangayKeys = allKeys.filter(key => key.includes('barangay'));
        
        if (barangayKeys.length > 0) {
            localStorageDiv.innerHTML = '<strong>Found ' + barangayKeys.length + ' barangay keys:</strong>\n\n';
            barangayKeys.forEach(key => {
                const data = localStorage.getItem(key);
                localStorageDiv.innerHTML += key + ':\n' + data.substring(0, 200) + '...\n\n';
            });
        } else {
            localStorageDiv.innerHTML = 'No barangay keys found in localStorage';
        }
        
        // Test the API
        fetch('../Process/check_status_updates.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('status').innerHTML = '<span class="success">✅ API Response received</span>';
                document.getElementById('result').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('status').innerHTML = '<span class="error">❌ API Error</span>';
                document.getElementById('result').textContent = error.message;
            });
    </script>
</body>
</html>
