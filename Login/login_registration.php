<?php
session_start();
// Retrieve user ID from session if coming from signup
$user_id = $_SESSION['new_user_id'] ?? null;
if (!$user_id) {
  header("Location: ../Login/Signup.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Sampaguita - Login Credentials</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #5CB25D;
      --text-dark: #333;
      --text-light: #fff;
      --background: #5CB25D;
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

    .btn-login:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .message {
      text-align: center;
      margin-bottom: 1rem;
      padding: 0.75rem;
      border-radius: 0.3rem;
    }

    .error {
      background-color: #ffebee;
      color: #c62828;
    }

    .success {
      background-color: #e8f5e9;
      color: #2e7d32;
    }

    .terms-container {
      margin: 1.5rem 0;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 0.3rem;
      border: 1px solid #ddd;
      max-height: 150px;
      overflow-y: auto;
    }

    .terms-text {
      font-size: 0.8rem;
      color: #555;
      margin-bottom: 0.5rem;
    }

    .terms-checkbox {
      display: flex;
      align-items: center;
      margin-top: 0.5rem;
    }

    .terms-checkbox input {
      margin-right: 0.5rem;
    }

    .terms-checkbox label {
      font-size: 0.85rem;
      color: #444;
    }

    .admin-note {
      background-color: #fff3e0;
      padding: 0.5rem;
      border-radius: 0.3rem;
      margin-top: 0.5rem;
      font-size: 0.8rem;
      color: #e65100;
      border-left: 3px solid #e65100;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-header">
      <h1>Barangay Sampaguita</h1>
      <p>Create Login Credentials</p>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="<?php echo $_SESSION['message_type']; ?> message">
        <?php echo $_SESSION['message']; ?>
      </div>
      <?php unset($_SESSION['message']);
      unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <form action="../Process/process_login_credentials.php" method="POST" id="credentialsForm">
      <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

      <div class="form-group">
        <label for="firstname" class="form-label">First Name</label>
        <div class="input-with-icon">
          <i class="fas fa-user input-icon"></i>
          <input type="text" class="form-control" id="firstname" name="firstname" 
                 placeholder="Enter your first name" required
                 value="<?php echo isset($_SESSION['form_data']['firstname']) ? htmlspecialchars($_SESSION['form_data']['firstname']) : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="lastname" class="form-label">Last Name</label>
        <div class="input-with-icon">
          <i class="fas fa-user input-icon"></i>
          <input type="text" class="form-control" id="lastname" name="lastname" 
                 placeholder="Enter your last name" required
                 value="<?php echo isset($_SESSION['form_data']['lastname']) ? htmlspecialchars($_SESSION['form_data']['lastname']) : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-with-icon">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" class="form-control" id="email" name="email" 
                 placeholder="Enter your email address" required
                 value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <div class="input-with-icon">
          <i class="fas fa-user-circle input-icon"></i>
          <input type="text" class="form-control" id="username" name="username" 
                 placeholder="Choose a username" required
                 value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
        </div>
        <div class="admin-note">
          <strong>Note:</strong> Usernames starting with "BRGYADMIN" are reserved for administrators.
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
            <br>You can create admin accounts by using this prefix.
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" class="form-control" id="password" name="password" 
                 placeholder="Choose a password (min 6 characters)" required>
        </div>
      </div>

      <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                 placeholder="Confirm your password" required>
        </div>
      </div>

      <div class="terms-container">
        <p class="terms-text"><strong>Terms and Conditions</strong></p>
        <p class="terms-text">By checking the box below, you agree to provide your personal information to Barangay
          Sampaguita for the purpose of creating and managing your account. The information you provide will be used in
          accordance with our Privacy Policy and Data Protection Act.</p>
        <p class="terms-text">You acknowledge that the information you provide is accurate and complete. Barangay
          Sampaguita reserves the right to verify the information provided and may suspend or terminate accounts with
          false information.</p>

        <div class="terms-checkbox">
          <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
          <label for="agreeTerms">I agree to the terms and conditions and consent to the processing of my personal
            data</label>
        </div>
      </div>

      <button type="submit" class="btn-login" id="submitBtn">Create Account</button>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const agreeCheckbox = document.getElementById('agreeTerms');
      const submitBtn = document.getElementById('submitBtn');

      // Initialize button state
      submitBtn.disabled = !agreeCheckbox.checked;

      // Update button state when checkbox changes
      agreeCheckbox.addEventListener('change', function () {
        submitBtn.disabled = !this.checked;
      });

      // Form validation before submission
      document.getElementById('credentialsForm').addEventListener('submit', function (e) {
        if (!agreeCheckbox.checked) {
          e.preventDefault();
          alert('You must agree to the terms and conditions to proceed.');
        }
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
          e.preventDefault();
          alert('Passwords do not match!');
        }
      });
    });
  </script>
</body>
</html>
<?php unset($_SESSION['form_data']); ?>
