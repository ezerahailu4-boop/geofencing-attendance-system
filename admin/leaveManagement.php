<?php include "./checkSession.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Leave Management - Admin Panel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="./logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" id="theme" href="css/<?php getTheme(); ?>" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/css/alertify.min.css" />
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/alertify.min.js"></script>
    <style>
        .leave-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); padding:24px; margin-bottom:24px; }
        .leave-card h4 { margin:0 0 18px; font-size:15px; font-weight:700; color:#000066; border-bottom:2px solid #e8eaf6; padding-bottom:10px; }
        label { font-size:12px; font-weight:600; color:#555; display:block; margin-bottom:4px; }
        .form-control { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; box-sizing:border-box; }
        .badge-pending  { background:#fff3cd; color:#856404; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        .badge-approved { background:#d4edda; color:#155724; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        .badge-rejected { background:#f8d7da; color:#721c24; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th { background:#f8f9ff; color:#555; font-weight:600; padding:10px 12px; text-align:left; border-bottom:2px solid #e8eaf6; }
        td { padding:10px 12px; border-bottom:1px solid #f0f0f0; color:#333; vertical-align:middle; }
        tr:hover td { background:#fafbff; }
        .btn-approve { background:#27ae60; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:12px; cursor:pointer; }
        .btn-reject  { background:#e74c3c; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:12px; cursor:pointer; margin-left:4px; }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; align-items:flex-end; }
        .filter-bar .form-group { margin:0; }
        .filter-bar select, .filter-bar input { padding:7px 10px; border:1px solid #ddd; border-radius:8px; font-size:13px; }
    </style>
</head>
<body>
<div class="page-container">
    <?php include "./header.php"; ?>
    <ul class="breadcrumb">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li class="active">Leave Management</li>
    </ul>
    <div class="page-content-wrap">
        <div class="container" style="max-width:1000px;padding-top:20px;">

            <?php
            include "./db.php";
            include "./csrf.php";

            // Handle approve/reject
            if (isset($_POST['action']) && in_array($_POST['action'], ['Approved', 'Rejected'])) {
                csrf_verify();
                $reqId     = intval($_POST['req_id']);
                $action    = $_POST['action'];
                $adminNote = $conn->real_escape_string($_POST['admin_note'] ?? '');
                $stmt = $conn->prepare("UPDATE leave_requests SET status=?, admin_note=? WHERE id=?");
                $stmt->bind_param("ssi", $action, $adminNote, $reqId);
                if ($stmt->execute()) {
                    $msg = $action === 'Approved' ? 'approved' : 'rejected';
                    echo "<script>alertify.success(\"<span style='color:#fff'><i class='fa fa-check-circle'></i> Request $msg.</span>\");</script>";
                }
            }

            // Filters
            $filterStatus = $_GET['status'] ?? 'Pending';
            $filterEmp    = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;
            $where = "WHERE 1=1";
            $params = [];
            $types  = "";
            if ($filterStatus !== 'All') { $where .= " AND lr.status=?"; $params[] = $filterStatus; $types .= "s"; }
            if ($filterEmp > 0)          { $where .= " AND lr.emp_id=?"; $params[] = $filterEmp;    $types .= "i"; }
            ?>

            <div class="leave-card">
                <h4><i class="fa fa-calendar-check-o"></i> Leave Requests</h4>

                <!-- Filter bar -->
                <form method="get" class="filter-bar">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <?php foreach (['Pending','Approved','Rejected','All'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $filterStatus===$s?'selected':''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="emp_id">
                            <option value="0">All Employees</option>
                            <?php
                            $emps = $conn->query("SELECT emp_id, emp_name FROM employee WHERE status='Active' ORDER BY emp_name");
                            while ($e = $emps->fetch_assoc()):
                            ?>
                            <option value="<?php echo $e['emp_id']; ?>" <?php echo $filterEmp==$e['emp_id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($e['emp_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" style="background:#000066;color:#fff;border:none;padding:7px 16px;border-radius:8px;font-size:13px;cursor:pointer;">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </form>

                <?php
                $sql = "SELECT lr.*, e.emp_name FROM leave_requests lr LEFT JOIN employee e ON lr.emp_id=e.emp_id $where ORDER BY lr.created_at DESC";
                $stmt = $conn->prepare($sql);
                if ($types) $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <?php if ($result->num_rows === 0): ?>
                    <p style="color:#888;font-size:13px;">No requests found.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Employee</th><th>Date</th><th>Type</th><th>Reason</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
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
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                <form method="post" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                    <input type="text" name="admin_note" class="form-control"
                                           placeholder="Note (optional)" style="width:130px;display:inline;padding:4px 8px;margin-right:4px;">
                                    <button type="submit" name="action" value="Approved" class="btn-approve">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="Rejected" class="btn-reject">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span style="color:#888;font-size:12px;"><?php echo htmlspecialchars($row['admin_note'] ?? '—'); ?></span>
                                <?php endif; ?>
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
