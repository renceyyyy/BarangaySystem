<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cookie Check</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #5CB25D; }
        h2 { color: #333; margin: 0 0 10px 0; }
        pre { background: #eee; padding: 10px; overflow-x: auto; }
        .warning { border-left-color: #e74c3c; }
    </style>
</head>
<body>
    <h1>🍪 Session & Cookie Diagnostics</h1>
    
    <div class="box">
        <h2>PHP Cookies Received ($_COOKIE)</h2>
        <pre><?php print_r($_COOKIE); ?></pre>
    </div>
    
    <div class="box">
        <h2>All Session Names in Cookies</h2>
        <pre><?php
        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, 'Barangay') !== false) {
                echo "$name = $value\n";
            }
        }
        ?></pre>
    </div>
    
    <div class="box">
        <h2>Current Session Status</h2>
        <pre><?php
        echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "NOT ACTIVE") . "\n";
        echo "Session Name: " . session_name() . "\n";
        echo "Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : "N/A") . "\n";
        ?></pre>
    </div>
    
    <div class="box warning">
        <h2>⚠️ IMPORTANT</h2>
        <p><strong>To test properly, you MUST use DIFFERENT BROWSERS or INCOGNITO mode!</strong></p>
        <ul>
            <li><strong>Same Browser, Different Tabs:</strong> Shares ALL cookies - sessions will interfere</li>
            <li><strong>Different Browsers:</strong> Separate cookies - sessions are isolated ✅</li>
            <li><strong>Incognito/Private Mode:</strong> Separate cookies - sessions are isolated ✅</li>
        </ul>
    </div>
    
    <div class="box">
        <h2>Testing Instructions</h2>
        <ol>
            <li><strong>Browser 1 (Chrome Normal):</strong> Login as resident, keep tab open</li>
            <li><strong>Browser 2 (Chrome Incognito OR Firefox):</strong> Login as admin</li>
            <li>Both should stay logged in because they have separate cookies</li>
        </ol>
    </div>
    
    <div class="box">
        <h2>Clear Cookies</h2>
        <button onclick="clearAllCookies()" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Clear All Barangay Cookies
        </button>
        <p id="result"></p>
    </div>
    
    <script>
        function clearAllCookies() {
            const cookies = document.cookie.split(";");
            let count = 0;
            
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i];
                const eqPos = cookie.indexOf("=");
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                
                if (name.indexOf('Barangay') !== -1) {
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                    count++;
                }
            }
            
            document.getElementById('result').innerHTML = `<strong>Cleared ${count} cookies. Refresh page to verify.</strong>`;
            setTimeout(() => location.reload(), 1000);
        }
    </script>
</body>
</html>
