<?php
// Start default session for login page (before role is known)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Sampaguita - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #5CB25D;
      --text-dark: #333;
      --text-light: #fff;
      --background: #5CB25D;
      --error: #e74c3c;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Archivo', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('../Assets/Loginbg.jpg') no-repeat center center;
      background-size: cover;
      filter: brightness(1.5) blur(8px);
      z-index: -2;
    }

    body::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.26);
      z-index: -1;
    }

    .login-container {
      width: 90%;
      max-width: 450px;
      padding: 2.5rem;
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(2px);
      border-radius: 0.5rem;
      box-shadow: 0 4px 15px rgba(0.5, 0.5, 0.7, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .login-header {
      text-align: center;
      margin-bottom: 2rem;
      color: var(--text-dark);
    }

    .login-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .login-header p {
      font-size: 1.125rem;
      font-weight: 500;
      color: #555;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      color: #444;
      font-weight: 500;
    }

    .input-with-icon {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #777;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 2.5rem;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid #ddd;
      border-radius: 0.3rem;
      color: #333;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-control::placeholder {
      color: #999;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(92, 178, 93, 0.3);
    }

    .btn-login {
      width: 100%;
      padding: 0.75rem;
      background-color: var(--primary);
      color: var(--text-light);
      border: none;
      border-radius: 0.3rem;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      margin-top: 1rem;
      transition: all 0.3s ease;
    }

    .btn-login:hover {
      background-color: #4CAF50;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .register-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #555;
    }

    .register-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .register-link a:hover {
      text-decoration: underline;
      color: #4CAF50;
    }

    /* Error message styles */
    .error-message {
      background-color: #fdecea;
      color: #d32f2f;
      padding: 12px 20px;
      border-radius: 4px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      border-left: 4px solid #d32f2f;
    }

    .error-message i {
      margin-right: 10px;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-header">
      <h1>Barangay Sampaguita</h1>

    </div>

    <?php if (isset($_SESSION['login_error'])): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <?php 
          echo htmlspecialchars($_SESSION['login_error']); 
          unset($_SESSION['login_error']);
        ?>
      </div>
    <?php endif; ?>

    <form name="form1" action="../Process/process_login.php" method="POST">
      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <div class="input-with-icon">
          <i class="fas fa-user input-icon"></i>
          <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        </div>
      </div>

      <button type="submit" class="btn-login" id="login" name="submit">
        Log In
      </button>

      <div class="register-link" id="register">
        <p>Don't have an account? <a href="Acc_Log_Registration.php">Register</a></p>
      </div>
    </form>
  </div>
</body>
</html>
