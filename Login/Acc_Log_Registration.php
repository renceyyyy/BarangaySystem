<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Sampaguita - Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #5CB25D;
      --text-dark: #333;
      --text-light: #fff;
      --background: #5CB25D;
      --error: #e74c3c;
      --success: #27ae60;
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
      padding: 40px 0;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('../Assets/BaranggayHall.jpeg') no-repeat center center;
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

    .register-container {
      width: 90%;
      max-width: 500px;
      padding: 1.5rem 2.5rem;
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(2px);
      border-radius: 0.5rem;
      box-shadow: 0 4px 15px rgba(0.5, 0.5, 0.7, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.3);
      margin: 20px auto;
    }

    .register-header {
      text-align: center;
      margin-bottom: 1.25rem;
      color: var(--text-dark);
    }

    .register-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }

    .register-header p {
      font-size: 1.125rem;
      font-weight: 500;
      color: #555;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .name-row {
      display: flex;
      gap: 1rem;
    }

    .name-row .form-group {
      flex: 1;
      margin-bottom: 1rem;
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

    .btn-register {
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

    .btn-register:hover {
      background-color: #4CAF50;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #555;
    }

    .login-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .login-link a:hover {
      text-decoration: underline;
      color: #4CAF50;
    }

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

    .success-message {
      background-color: #e8f5e8;
      color: var(--success);
      padding: 12px 20px;
      border-radius: 4px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      border-left: 4px solid var(--success);
    }

    .error-message i,
    .success-message i {
      margin-right: 10px;
    }

    .password-requirements {
      font-size: 0.85rem;
      color: #666;
      margin-top: 0.5rem;
      padding-left: 0.5rem;
    }

    .password-requirements ul {
      margin: 0;
      padding-left: 1rem;
    }

    .password-requirements li {
      margin: 0.25rem 0;
    }
  </style>
</head>

<body>
  <div class="register-container">
    <div class="register-header">
      <h1>Barangay Sampaguita</h1>
      <p>Create New Account</p>
    </div>

    <?php if (isset($_SESSION['register_error'])): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <?php 
          echo htmlspecialchars($_SESSION['register_error']); 
          unset($_SESSION['register_error']);
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['register_success'])): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        <?php 
          echo htmlspecialchars($_SESSION['register_success']); 
          unset($_SESSION['register_success']);
        ?>
      </div>
    <?php endif; ?>

    <form name="registerForm" action="Acc_Log_Process.php" method="POST" onsubmit="return validateForm()">
      <div class="name-row">
        <div class="form-group">
          <label for="firstname" class="form-label">First Name *</label>
          <div class="input-with-icon">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter first name" required>
          </div>
        </div>

        <div class="form-group">
          <label for="lastname" class="form-label">Last Name *</label>
          <div class="input-with-icon">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter last name" required>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Email Address *</label>
        <div class="input-with-icon">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
        </div>
      </div>

      <div class="form-group">
        <label for="username" class="form-label">Username *</label>
        <div class="input-with-icon">
          <i class="fas fa-user-circle input-icon"></i>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password *</label>
        <div class="input-with-icon">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>
        <div class="password-requirements">
          <ul>
            <li>At least 8 characters long</li>
            <li>Include uppercase and lowercase letters</li>
            <li>Include at least one number</li>
          </ul>
        </div>
      </div>

      <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm Password *</label>
        <div class="input-with-icon">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
        </div>
      </div>

      <input type="hidden" name="role" value="resident">

      <button type="submit" class="btn-register" name="register">
        <i class="fas fa-user-plus"></i> Create Account
      </button>

      <div class="login-link">
        <p>Already have an account? <a href="login.php">Log In</a></p>
      </div>
    </form>
  </div>

  <script>
    function validateForm() {
      const firstname = document.getElementById('firstname').value;
      const lastname = document.getElementById('lastname').value;
      const email = document.getElementById('email').value;
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (firstname.length < 2) {
        alert('First name must be at least 2 characters long');
        return false;
      }
      
      if (lastname.length < 2) {
        alert('Last name must be at least 2 characters long');
        return false;
      }
      
      // Basic email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return false;
      }
      
      if (username.length < 3) {
        alert('Username must be at least 3 characters long');
        return false;
      }
      
      if (password.length < 8) {
        alert('Password must be at least 8 characters long');
        return false;
      }
      
      const hasUpper = /[A-Z]/.test(password);
      const hasLower = /[a-z]/.test(password);
      const hasNumber = /\d/.test(password);
      
      if (!hasUpper || !hasLower || !hasNumber) {
        alert('Password must contain at least one uppercase letter, one lowercase letter, and one number');
        return false;
      }
      
      if (password !== confirmPassword) {
        alert('Passwords do not match');
        return false;
      }
      
      return true;
    }

    document.getElementById('confirm_password').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && password !== confirmPassword) {
        this.style.borderColor = '#e74c3c';
      } else {
        this.style.borderColor = '#ddd';
      }
    });
  </script>
</body>
</html>
