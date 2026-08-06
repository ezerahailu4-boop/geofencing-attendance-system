<?php include "./checkSession.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shift Settings - Admin Panel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="./logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" id="theme" href="css/<?php getTheme(); ?>" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/css/alertify.min.css" />
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/alertify.min.js"></script>
    <style>
        .form-control { background:#fff !important; height:38px; font-size:13px; }
        label { color:#111; font-size:13px; font-weight:650; margin-top:6px; display:block; }
    </style>
</head>
<body>
<div class="page-container">
    <?php include "./header.php"; ?>
    <ul class="breadcrumb">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li class="active">Shift Settings</li>
    </ul>
    <div class="page-content-wrap container">
        <span style="padding:10px;background:#000066;color:#fff;font-weight:600;font-size:17px;width:100%;margin-bottom:20px;display:block;">
            <i class="fa fa-clock-o"></i> Shift Settings
        </span>

        <?php
        include "./db.php";
        $settings = $conn->query("SELECT * FROM shift_settings WHERE id=1")->fetch_assoc();
        ?>

        <form action="" method="post" style="max-width:400px;">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label>Shift Start Time:</label>
                <input type="time" name="shift_start" class="form-control"
                       value="<?php echo $settings['shift_start'] ?? '09:00'; ?>" required>
            </div>
            <div class="form-group">
                <label>Grace Period (minutes):</label>
                <input type="number" name="grace_minutes" class="form-control" min="0" max="60"
                       value="<?php echo $settings['grace_minutes'] ?? 10; ?>" required>
                <small style="color:#666;">Employees checking in within this many minutes after shift start are not marked late.</small>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="save" style="background:#000066;color:#fff;padding:9px 20px;border:none;border-radius:4px;font-size:14px;">
                    <i class="fa fa-save"></i> Save Settings
                </button>
            </div>
        </form>

        <?php
        if (isset($_POST['save'])) {
            csrf_verify();
            $shift_start = $_POST['shift_start'] . ':00';
            $grace = intval($_POST['grace_minutes']);
            $stmt = $conn->prepare("INSERT INTO shift_settings (id, shift_start, grace_minutes) VALUES (1,?,?) ON DUPLICATE KEY UPDATE shift_start=?, grace_minutes=?");
            $stmt->bind_param("sisi", $shift_start, $grace, $shift_start, $grace);
            if ($stmt->execute()) {
        ?>
                <script>alertify.success("<span style='color:#fff;font-size:15px;'><i class='fa fa-check-circle'></i> Shift settings saved!</span>");</script>
        <?php } else { ?>
                <script>alertify.error("<span style='color:#fff;font-size:15px;'><i class='fa fa-warning'></i> Error saving settings.</span>");</script>
        <?php } }  ?>
    </div>
</div>

<div class="message-box animated fadeIn" data-sound="alert" id="mb-signout">
    <div class="mb-container"><div class="mb-middle">
        <div class="mb-title"><span class="fa fa-sign-out"></span> Log <strong>Out</strong>?</div>
        <div class="mb-content"><p>Are you sure you want to log out?</p></div>
        <div class="mb-footer"><div class="pull-right">
            <a href="./logout.php" class="btn btn-success btn-lg">Yes</a>
            <button class="btn btn-default btn-lg mb-control-close">No</button>
        </div></div>
    </div></div>
</div>

<?php include "./preload.php"; ?>
<?php include "./mainScripts.php"; ?>
</body>
</html>
