<?php
session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo "Brak dostępu (403).";
  exit;
}