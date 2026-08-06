<?php
ob_start();
session_start();
include "./csrf.php";

if (isset($_POST["go"])) {
    csrf_verify();
    include "db.php";
    $emp_email = $conn->real_escape_string($_POST["emp_email"]);
    $emp_password = $_POST["emp_password"];
    $sql = $conn->prepare("SELECT emp_id, emp_password FROM employee WHERE emp_email=? AND status='Active'");
    $sql->bind_param("s", $emp_email);
    $sql->execute();
    $result = $sql->get_result();
    $valid = false;
    if ($row = $result->fetch_assoc()) {
        $valid = password_verify($emp_password, $row["emp_password"]) || $emp_password === $row["emp_password"];
        if ($valid) {
            // Prevent session fixation
            session_regenerate_id(true);
            // Store unique token to prevent concurrent logins
            $token = bin2hex(random_bytes(32));
            $emp_id = $row["emp_id"];
            $conn->prepare("UPDATE employee SET session_token=? WHERE emp_id=?")
                 ->bind_param("si", $token, $emp_id) ?: null;
            $upd = $conn->prepare("UPDATE employee SET session_token=? WHERE emp_id=?");
            $upd->bind_param("si", $token, $emp_id);
            $upd->execute();
            $_SESSION["emp_id"] = $emp_id;
            $_SESSION["emp_token"] = $token;
            header("Location: ./dashboard.php");
            exit;
        }
    }
    $loginFailed = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Geofencing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://use.fontawesome.com/4c38b3bc58.js"></script>
    
    
</head>

<body>
    <div id="overlay"><div class="spinner"></div></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon"><i class="fa fa-map-marker"></i></div>
                <h2>Geo Attendance</h2>
                <p>Employee Portal — Sign in to continue</p>
            </div>
            <?php if (isset($_GET['kicked'])): ?>
            <div style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#e74c3c;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;text-align:center;">
                <i class="fa fa-warning"></i> Your account was logged in from another device. You have been signed out.
            </div>
            <?php endif; ?>
            <form action="" method="post">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="emp_email" id="emp_email" placeholder="you@company.com" autocomplete="off" autofocus required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="emp_password" id="emp_password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" name="go" class="btn-login">
                    <i class="fa fa-sign-in"></i> Sign In
                </button>
            </form>
            <div class="login-footer">Admin? <a href="./admin/index.php" style="color:#a78bfa;">Login here</a></div>
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
  

</body>
<script>
    window.addEventListener('load', function() {
        document.getElementById("overlay").style.display = 'none';
    });
</script>

</html>
<?php if (isset($loginFailed)): ?>
    <script>$('#myModal').modal('show');</script>
<?php endif; ?>