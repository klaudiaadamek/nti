<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Panel administratora — Catnetic Storm';
$active = 'admin';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <section class="admin">
      <h1>Panel administratora</h1>

      <ul class="admin-menu">
        <li><a href="devlog_manage.php">Zarządzaj devlogiem</a></li>
        <li><a href="gallery_manage.php">Zarządzaj galerią</a></li>
        <li><a href="devlog_create.php">Dodaj post do devloga</a></li>
        <li><a href="gallery_add.php">Dodaj zdjęcie do galerii</a></li>
        <li><a href="users.php">Zarządzaj użytkownikami</a></li>
        <li><a href="comments.php">Zarządzaj komentarzami</a></li>
      </ul>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>