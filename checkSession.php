<?php
session_start();
const SESSION_TIMEOUT = 1800; // 30 minutes

if (isset($_SESSION["emp_id"], $_SESSION["emp_token"])) {
    // Timeout check
    if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header("Location: index.php?timeout=1");
        exit;
    }
    // Concurrent login check — token must match DB
    include "./db.php";
    $emp_id = intval($_SESSION["emp_id"]);
    $chk = $conn->prepare("SELECT session_token FROM employee WHERE emp_id=?");
    $chk->bind_param("i", $emp_id);
    $chk->execute();
    $row = $chk->get_result()->fetch_assoc();
    if (!$row || !hash_equals($row["session_token"] ?? '', $_SESSION["emp_token"])) {
        // Another device logged in — force logout
        session_unset();
        session_destroy();
        header("Location: index.php?kicked=1");
        exit;
    }
    $_SESSION["last_activity"] = time();
    include "./functions.php";
    include "./csrf.php";
} else {
    header("Location: index.php");
    exit;
}
