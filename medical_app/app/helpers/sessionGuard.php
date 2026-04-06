<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$page = $_GET['page'] ?? '';
$excludedPages = ['login', 'register', 'reset_password'];

if (!in_array($page, $excludedPages)) {
  $timeout = 300; // 5 menit
  $now = time();
  $lastActivity = $_SESSION['last_activity'] ?? $now;

  if (($now - $lastActivity) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: index.php?page=login&timeout=true");
    exit;
  }

  $_SESSION['last_activity'] = $now;
}
?>
