<?php
session_start();
const SESSION_TIMEOUT = 1800; // 30 minutes

if (isset($_SESSION["admin_id"])) {
    if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header("Location: index.php?timeout=1");
        exit;
    }
    $_SESSION["last_activity"] = time();
    include "./adminFunctions.php";
    include "./csrf.php";
} else {
    header("Location: index.php");
    exit;
}
