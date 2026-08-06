<?php
ob_start();
session_start();
include "./csrf.php";

if (isset($_POST["go"])) {
    csrf_verify();
    include "./db.php";
    $email = $conn->real_escape_string($_POST["email"]);
    $password = $_POST["password"];
    $sql = $conn->prepare("SELECT id, password FROM admin WHERE email=?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"]) || $password === $row["password"]) {
            $_SESSION["admin_id"] = $row["id"];
            header("Location: ./dashboard.php");
            exit;
        }
    }
    $sql->close();
    $loginFailed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Geo Attendance</title>
  <link rel="shortcut icon" href="../logo.jpg" type="image/x-icon">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://use.fontawesome.com/4c38b3bc58.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0a0a1a;
      background: linear-gradient(135deg, #0a0a1a, #1a1a3e, #0d0d2b);
      overflow: hidden;
    }

    body::before, body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.3;
      animation: float 8s ease-in-out infinite;
      z-index: 0;
    }
    body::before {
      width: 500px; height: 500px;
      background: #e74c3c;
      top: -150px; left: -100px;
    }
    body::after {
      width: 400px; height: 400px;
      background: #f39c12;
      bottom: -100px; right: -100px;
      animation-delay: 4s;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(30px) scale(1.05); }
    }

    .login-wrapper {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
      padding: 16px;
    }

    .login-card {
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 24px;
      padding: 48px 40px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }

    .login-logo {
      text-align: center;
      margin-bottom: 32px;
    }
    .logo-icon {
      width: 64px; height: 64px;
      background: linear-gradient(135deg, #e74c3c, #f39c12);
      border-radius: 18px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 8px 24px rgba(231,76,60,0.4);
    }
    .logo-icon i { font-size: 28px; color: #fff; }
    .login-logo h2 { color: #fff; font-size: 22px; font-weight: 700; }
    .login-logo p { color: rgba(255,255,255,0.45); font-size: 13px; margin-top: 4px; }

    .form-group { margin-bottom: 20px; }
    .form-group label {
      display: block;
      color: rgba(255,255,255,0.65);
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 8px;
    }

    .input-wrap { position: relative; }
    .input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255,255,255,0.3);
      font-size: 15px;
      pointer-events: none;
    }
    .input-wrap input {
      width: 100%;
      padding: 12px 14px 12px 40px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      color: #fff;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color 0.2s, background 0.2s;
    }
    .input-wrap input::placeholder { color: rgba(255,255,255,0.25); }
    .input-wrap input:focus {
      border-color: #e74c3c;
      background: rgba(231,76,60,0.1);
    }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #e74c3c, #f39c12);
      border: none;
      border-radius: 12px;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.15s;
      box-shadow: 0 8px 24px rgba(231,76,60,0.35);
      margin-top: 8px;
      font-family: 'Inter', sans-serif;
    }
    .btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    .login-footer {
      text-align: center;
      margin-top: 24px;
      color: rgba(255,255,255,0.3);
      font-size: 12px;
    }
    .login-footer a { color: #f39c12; text-decoration: none; }

    /* error modal */
    .modal-content { border-radius: 16px; border: none; overflow: hidden; }
    .modal-error-header {
      background: linear-gradient(135deg, #e74c3c, #f39c12);
      padding: 32px;
      text-align: center;
    }
    .modal-error-header i { font-size: 48px; color: #fff; }
    .modal-body h2 { color: #2d3436; font-weight: 700; text-align: center; margin: 16px 0 8px; }
    .modal-body p { color: #636e72; text-align: center; font-size: 14px; }
    .modal-footer { border: none; justify-content: center; padding-bottom: 24px; }
    .btn-modal-ok {
      padding: 10px 40px;
      background: linear-gradient(135deg, #e74c3c, #f39c12);
      border: none;
      border-radius: 50px;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
    }

    @media (max-width: 480px) {
      .login-card { padding: 36px 24px; }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-logo">
        <div class="logo-icon"><i class="fa fa-user-secret"></i></div>
        <h2>Admin Portal</h2>
        <p>Geo Attendance System &mdash; Admin Access</p>
      </div>

      <?php if (isset($_GET['timeout'])): ?>
        <div style="background:rgba(231,76,60,0.2);border:1px solid rgba(231,76,60,0.4);color:#fff;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;text-align:center;">
          <i class="fa fa-clock-o"></i> Session expired due to inactivity. Please log in again.
        </div>
      <?php endif; ?>
      <form action="" method="post">
        <?php csrf_field(); ?>
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrap">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="admin@company.com" required autofocus>
          </div>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="input-wrap">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Enter your password" required>
          </div>
        </div>
        <button type="submit" name="go" class="btn-login">
          <i class="fa fa-sign-in"></i> Sign In as Admin
        </button>
      </form>

      <div class="login-footer">
        Employee? <a href="../index.php">Login here</a>
      </div>
    </div>
  </div>

  <!-- Error Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-error-header"><i class="fa fa-times-circle"></i></div>
        <div class="modal-body">
          <h2>Login Failed</h2>
          <p>Incorrect email or password. Please try again.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-ok" data-dismiss="modal">Try Again</button>
        </div>
      </div>
    </div>
  </div>

  <?php if (isset($loginFailed)): ?>
    <script>$('#myModal').modal('show');</script>
  <?php endif; ?>

</body>
</html>
