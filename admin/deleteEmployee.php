<?php
include "./db.php";
$emp_id = intval($_GET["emp_id"]);
$stmt = $conn->prepare("UPDATE employee SET status='Inactive' WHERE emp_id=?");
$stmt->bind_param("i", $emp_id);
echo $stmt->execute() ? "Success" : "Error";
