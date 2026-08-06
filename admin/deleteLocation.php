<?php
include "./db.php";
$location_id = intval($_GET["location_id"]);
$stmt = $conn->prepare("UPDATE master SET status='Inactive' WHERE id=?");
$stmt->bind_param("i", $location_id);
echo $stmt->execute() ? "Success" : "Error";
