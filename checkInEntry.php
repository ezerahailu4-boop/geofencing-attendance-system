<?php
session_start();

// 1. Must be logged in
if (!isset($_SESSION["emp_id"])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

include "db.php";

$userId = intval($_SESSION["emp_id"]); // always use session, never trust POST for userId
$userLat = floatval($_POST["userLat"]);
$userLng = floatval($_POST["userLng"]);
$address  = $conn->real_escape_string($_POST["address"]);
$distance = floatval($_POST["distance"]);

// 2. Server-side geofence validation
$geo = $conn->prepare("SELECT master.lat, master.longi, assign_location.distance_limit FROM assign_location LEFT JOIN master ON assign_location.location_id = master.id WHERE assign_location.emp_id = ?");
$geo->bind_param("i", $userId);
$geo->execute();
$geoRow = $geo->get_result()->fetch_assoc();

if (!$geoRow) {
    echo "No location assigned";
    exit;
}

// Haversine formula in PHP
$earthRadius = 6371000; // meters
$dLat = deg2rad($userLat - $geoRow["lat"]);
$dLng = deg2rad($userLng - $geoRow["longi"]);
$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($geoRow["lat"])) * cos(deg2rad($userLat)) * sin($dLng/2) * sin($dLng/2);
$serverDistance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1-$a));

if ($serverDistance > $geoRow["distance_limit"]) {
    echo "Outside geofence";
    exit;
}

// 3. Prevent duplicate check-in today
$today = date("Y-m-d");
$dup = $conn->prepare("SELECT id FROM checkin WHERE userId = ? AND DATE(time) = ?");
$dup->bind_param("is", $userId, $today);
$dup->execute();
if ($dup->get_result()->fetch_assoc()) {
    echo "Already checked in today";
    exit;
}

// 4. Determine if late
include "functions.php";
$shift = getShiftSettings();
$deadline = strtotime(date('Y-m-d') . ' ' . $shift['shift_start']) + ($shift['grace_minutes'] * 60);
$isLate = (time() > $deadline) ? 1 : 0;

$stmt = $conn->prepare("INSERT INTO checkin(userId, lat, longi, address, distance, is_late) VALUES(?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssi", $userId, $userLat, $userLng, $address, $distance, $isLate);
echo $stmt->execute() ? ($isLate ? "Late" : "Success") : "error";
