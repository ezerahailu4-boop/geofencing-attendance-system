<?php
include "./checkSession.php";
include "./db.php";

$type  = isset($_GET["type"])   ? $_GET["type"]                          : "today";
$emp_id = isset($_GET["emp_id"]) ? intval($_GET["emp_id"])               : 0;
$month  = isset($_GET["month"])  ? $conn->real_escape_string($_GET["month"]) : date("Y-m");
$from   = isset($_GET["from"])   ? $conn->real_escape_string($_GET["from"])  : date("Y-m-01");
$to     = isset($_GET["to"])     ? $conn->real_escape_string($_GET["to"])    : date("Y-m-d");

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=attendance_{$type}_".date("Y-m-d").".csv");

$out = fopen("php://output", "w");

if ($type === "today") {
    fputcsv($out, ["Employee","Email","Mobile","Check-In","Check-In Address","CI Distance","Check-Out","Check-Out Address","CO Distance","Hours Worked"]);
    $today = date("Y-m-d");
    $sql = "SELECT DISTINCT employee.emp_name,employee.emp_email,employee.emp_mobile,
                checkin.time AS ci_time,checkin.address AS ci_addr,checkin.distance AS ci_dist,
                checkout.time AS co_time,checkout.address AS co_addr,checkout.distance AS co_dist,
                ROUND(TIMESTAMPDIFF(minute,checkin.time,checkout.time)/60,2) AS hours
            FROM checkin
            INNER JOIN employee ON checkin.userId=employee.emp_id
            LEFT JOIN checkout ON checkin.id=checkout.check_in_id
            WHERE employee.status='Active' AND DATE(checkin.time)='$today'
            ORDER BY checkin.time DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row["emp_name"], $row["emp_email"], $row["emp_mobile"],
            $row["ci_time"] ? date("h:i:s A", strtotime($row["ci_time"])) : "-",
            $row["ci_addr"], $row["ci_dist"],
            $row["co_time"] ? date("h:i:s A", strtotime($row["co_time"])) : "-",
            $row["co_addr"] ?? "-", $row["co_dist"] ?? "-",
            $row["hours"] ? $row["hours"]." hrs" : "-"
        ]);
    }

} elseif ($type === "employee" && $emp_id > 0) {
    fputcsv($out, ["Date","Check-In","Check-In Address","CI Distance","Check-Out","Check-Out Address","CO Distance","Hours Worked"]);
    $sql = "SELECT checkin.time AS ci_time,checkin.address AS ci_addr,checkin.distance AS ci_dist,
                checkout.time AS co_time,checkout.address AS co_addr,checkout.distance AS co_dist,
                ROUND(TIMESTAMPDIFF(minute,checkin.time,checkout.time)/60,2) AS hours
            FROM checkin
            LEFT JOIN checkout ON checkin.id=checkout.check_in_id
            WHERE checkin.userId='$emp_id'
            ORDER BY checkin.time DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            date("d-m-Y", strtotime($row["ci_time"])),
            date("h:i:s A", strtotime($row["ci_time"])),
            $row["ci_addr"], $row["ci_dist"],
            $row["co_time"] ? date("h:i:s A", strtotime($row["co_time"])) : "-",
            $row["co_addr"] ?? "-", $row["co_dist"] ?? "-",
            $row["hours"] ? $row["hours"]." hrs" : "-"
        ]);
    }

} elseif ($type === "monthly") {
    fputcsv($out, ["Employee","Email","Days Present","Total Hours"]);
    $sql = "SELECT employee.emp_name,employee.emp_email,
                COUNT(DISTINCT DATE(checkin.time)) AS days_present,
                ROUND(SUM(TIMESTAMPDIFF(minute,checkin.time,checkout.time))/60,2) AS total_hours
            FROM checkin
            INNER JOIN employee ON checkin.userId=employee.emp_id
            LEFT JOIN checkout ON checkin.id=checkout.check_in_id
            WHERE employee.status='Active' AND DATE_FORMAT(checkin.time,'%Y-%m')='$month'
            GROUP BY checkin.userId
            ORDER BY employee.emp_name";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row["emp_name"], $row["emp_email"],
            $row["days_present"]." days",
            ($row["total_hours"] ?? 0)." hrs"
        ]);
    }

} elseif ($type === "period") {
    fputcsv($out, ["Employee","Email","Days Present","Total Hours"]);
    $sql = "SELECT employee.emp_name,employee.emp_email,
                COUNT(DISTINCT DATE(checkin.time)) AS days_present,
                ROUND(SUM(TIMESTAMPDIFF(minute,checkin.time,checkout.time))/60,2) AS total_hours
            FROM checkin
            INNER JOIN employee ON checkin.userId=employee.emp_id
            LEFT JOIN checkout ON checkin.id=checkout.check_in_id
            WHERE employee.status='Active' AND DATE(checkin.time) BETWEEN '$from' AND '$to'
            GROUP BY checkin.userId
            ORDER BY employee.emp_name";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row["emp_name"], $row["emp_email"],
            $row["days_present"]." days",
            ($row["total_hours"] ?? 0)." hrs"
        ]);
    }
}

fclose($out);
exit;
