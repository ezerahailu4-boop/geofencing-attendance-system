<?php
session_start();

// 1. Must be logged in
if (!isset($_SESSION["emp_id"])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

include "db.php";

$userId             = intval($_SESSION["emp_id"]); // always use session
$userLat            = floatval($_POST["userLat"]);
$userLng            = floatval($_POST["userLng"]);
$address            = $conn->real_escape_string($_POST["address"]);
$lastInsertCheckInId = intval($_POST["lastInsertCheckInId"]);
$distance           = floatval($_POST["distance"]);

// 2. Server-side geofence validation
$geo = $conn->prepare("SELECT master.lat, master.longi, assign_location.distance_limit FROM assign_location LEFT JOIN master ON assign_location.location_id = master.id WHERE assign_location.emp_id = ?");
$geo->bind_param("i", $userId);
$geo->execute();
$geoRow = $geo->get_result()->fetch_assoc();

if (!$geoRow) {
    echo "No location assigned";
    exit;
}

$earthRadius = 6371000;
$dLat = deg2rad($userLat - $geoRow["lat"]);
$dLng = deg2rad($userLng - $geoRow["longi"]);
$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($geoRow["lat"])) * cos(deg2rad($userLat)) * sin($dLng/2) * sin($dLng/2);
$serverDistance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1-$a));

if ($serverDistance > $geoRow["distance_limit"]) {
    echo "Outside geofence";
    exit;
}

// 3. Verify the check-in record belongs to this user
$verify = $conn->prepare("SELECT id FROM checkin WHERE id = ? AND userId = ?");
$verify->bind_param("ii", $lastInsertCheckInId, $userId);
$verify->execute();
if (!$verify->get_result()->fetch_assoc()) {
    echo "Invalid check-in record";
    exit;
}

$stmt = $conn->prepare("INSERT INTO checkout(userId, lat, longi, address, check_in_id, distance) VALUES(?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssis", $userId, $userLat, $userLng, $address, $lastInsertCheckInId, $distance);
echo $stmt->execute() ? "Success Checkout" : "error Check out";
