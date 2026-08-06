<?php
include "./db.php";
$conn->query("ALTER TABLE employee ADD COLUMN IF NOT EXISTS session_token VARCHAR(64) DEFAULT NULL");
echo "Done. You can delete this file now.";
?>
