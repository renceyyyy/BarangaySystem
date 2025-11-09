<?php
session_start();
// Check if registration data exists
if (!isset($_SESSION['registration_data'])) {
    header("Location: Signup.php"); 
    exit();
}

$data = $_SESSION['registration_data'];
unset($_SESSION['registration_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Successful - Barangay Sampaguita</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #5CB25D;
      --text-dark: #333;
      --text-light: #fff;
    }
    
    body {
      font-family: 'Archivo', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f5f5f5;
      margin: 0;
    }
    
    .success-container {
      width: 90%;
      max-width: 600px;
      padding: 2rem;
      background: white;
      border-radius: 0.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    
    .success-header h1 {
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .account-details {
      text-align: left;
      margin: 2rem 0;
      padding: 1rem;
      background: #f9f9f9;
      border-radius: 0.3rem;
    }
    
    .btn-login {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background-color: var(--primary);
      color: white;
      text-decoration: none;
      border-radius: 0.3rem;
      transition: all 0.3s ease;
    }
    
    .btn-login:hover {
      background-color: #4CAF50;
      transform: translateY(-2px);
    }
    
    .warning {
      color: #d9534f;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="success-container">
    <div class="success-header">
      <h1>Registration Successful!</h1>
      <p>Thank you for registering with Barangay Sampaguita</p>
    </div>
    
    <div class="account-details">
      <h3>Your Account Details</h3>
      <p><strong>Name:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email']); ?></p>
      <p><strong>Temporary Password:</strong> <?php echo htmlspecialchars($data['temp_password']); ?></p>
      <p class="warning">Please change your password after logging in.</p>
    </div>
    
    <a href="login.php" class="btn-login">Proceed to Login</a>
  </div>
</body>
</html>
