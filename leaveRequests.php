<?php include "./checkSession.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Leave Requests - Employee Panel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="./logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" id="theme" href="./admin/css/<?php getTheme(); ?>" />
    <link rel="stylesheet" href="./mobile.css" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/css/alertify.min.css" />
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/alertify.min.js"></script>
    <style>
        .leave-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); padding:24px; margin-bottom:24px; }
        .leave-card h4 { margin:0 0 18px; font-size:15px; font-weight:700; color:#000066; border-bottom:2px solid #e8eaf6; padding-bottom:10px; }
        .form-row { display:flex; gap:16px; flex-wrap:wrap; }
        .form-row .form-group { flex:1; min-width:180px; }
        label { font-size:12px; font-weight:600; color:#555; display:block; margin-bottom:4px; }
        .form-control { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; box-sizing:border-box; }
        .form-control:focus { outline:none; border-color:#000066; }
        .btn-submit { background:#000066; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
        .btn-submit:hover { background:#3333cc; }
        .badge-pending  { background:#fff3cd; color:#856404; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        .badge-approved { background:#d4edda; color:#155724; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        .badge-rejected { background:#f8d7da; color:#721c24; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th { background:#f8f9ff; color:#555; font-weight:600; padding:10px 12px; text-align:left; border-bottom:2px solid #e8eaf6; }
        td { padding:10px 12px; border-bottom:1px solid #f0f0f0; color:#333; }
        tr:hover td { background:#fafbff; }
    </style>
</head>
<body>
<div class="page-container">
    <?php include "./header.php"; ?>
    <ul class="breadcrumb">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li class="active">Leave Requests</li>
    </ul>
    <div class="page-content-wrap">
        <div class="container" style="max-width:860px;padding-top:20px;">

            <?php
            include "./db.php";
            include "./csrf.php";
            $empId = intval($_SESSION['emp_id']);

            // Handle new request submission
            if (isset($_POST['submit_leave'])) {
                csrf_verify();
                $leaveDate = $conn->real_escape_string($_POST['leave_date']);
                $leaveType = $conn->real_escape_string($_POST['leave_type']);
                $reason    = $conn->real_escape_string($_POST['reason']);

                // Check not a holiday or already requested
                $dup = $conn->prepare("SELECT id FROM leave_requests WHERE emp_id=? AND leave_date=?");
                $dup->bind_param("is", $empId, $leaveDate);
                $dup->execute();
                if ($dup->get_result()->fetch_assoc()) {
                    echo "<script>alertify.error(\"<span style='color:#fff'>You already have a request for that date.</span>\");</script>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO leave_requests(emp_id, leave_date, leave_type, reason) VALUES(?,?,?,?)");
                    $stmt->bind_param("isss", $empId, $leaveDate, $leaveType, $reason);
                    if ($stmt->execute()) {
                        echo "<script>alertify.success(\"<span style='color:#fff'><i class='fa fa-check-circle'></i> Leave request submitted!</span>\");</script>";
                    }
                }
            }

            // Handle cancel
            if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
                $cancelId = intval($_GET['cancel']);
                $conn->prepare("DELETE FROM leave_requests WHERE id=? AND emp_id=? AND status='Pending'")->bind_param("ii", $cancelId, $empId) ?: null;
                $del = $conn->prepare("DELETE FROM leave_requests WHERE id=? AND emp_id=? AND status='Pending'");
                $del->bind_param("ii", $cancelId, $empId);
                $del->execute();
                echo "<script>alertify.success(\"<span style='color:#fff'>Request cancelled.</span>\");</script>";
            }
            ?>

            <!-- Submit Form -->
            <div class="leave-card">
                <h4><i class="fa fa-paper-plane"></i> New Leave Request</h4>
                <form method="post">
                    <?php csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Leave Date</label>
                            <input type="date" name="leave_date" class="form-control"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Leave Type</label>
                            <select name="leave_type" class="form-control" required>
                                <option value="Annual">Annual Leave</option>
                                <option value="Sick">Sick Leave</option>
                                <option value="Emergency">Emergency Leave</option>
                                <option value="Unpaid">Unpaid Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Briefly describe the reason..." required style="resize:vertical;"></textarea>
                    </div>
                    <div style="margin-top:14px;">
                        <button type="submit" name="submit_leave" class="btn-submit">
                            <i class="fa fa-send"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- My Requests Table -->
            <div class="leave-card">
                <h4><i class="fa fa-list"></i> My Leave Requests</h4>
                <?php
                $rows = $conn->prepare("SELECT * FROM leave_requests WHERE emp_id=? ORDER BY created_at DESC");
                $rows->bind_param("i", $empId);
                $rows->execute();
                $result = $rows->get_result();
                if ($result->num_rows === 0):
                ?>
                    <p style="color:#888;font-size:13px;">No leave requests yet.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Reason</th><th>Status</th><th>Admin Note</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['leave_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Approved'): ?>
                                    <span class="badge-approved"><i class="fa fa-check"></i> Approved</span>
                                <?php elseif ($row['status'] === 'Rejected'): ?>
                                    <span class="badge-rejected"><i class="fa fa-times"></i> Rejected</span>
                                <?php else: ?>
                                    <span class="badge-pending"><i class="fa fa-clock-o"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['admin_note'] ? htmlspecialchars($row['admin_note']) : '—'; ?></td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <a href="?cancel=<?php echo $row['id']; ?>"
                                       onclick="return confirm('Cancel this request?')"
                                       style="color:#e74c3c;font-size:12px;"><i class="fa fa-trash"></i> Cancel</a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div>
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
