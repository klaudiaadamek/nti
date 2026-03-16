<?php
session_start();

$pageTitle = 'Profil — Catnetic Storm';
$active = 'profile';

require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT user_id, username, role, created_at FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
  session_destroy();
  header('Location: login.php');
  exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
$stmt->execute([$userId]);
$commentsCount = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="auth">
      <h1>Profil użytkownika</h1>

      <div class="auth-card">
        <div class="auth-header">Twoje dane</div>

        <p class="auth-smalltext" style="margin: 0 0 10px;">
          Login: <strong style="color:#fff; text-transform:none;"><?= htmlspecialchars((string)$user['username']) ?></strong>
        </p>

        <p class="auth-smalltext" style="margin: 0 0 10px;">
          Rola: <strong style="color:#fff;"><?= htmlspecialchars((string)$user['role']) ?></strong>
        </p>

        <p class="auth-smalltext" style="margin: 0 0 10px;">
          Dołączył(a): <strong style="color:#fff; text-transform:none;"><?= htmlspecialchars((string)$user['created_at']) ?></strong>
        </p>

        <p class="auth-smalltext" style="margin: 0;">
          Liczba komentarzy: <strong style="color:#fff;"><?= $commentsCount ?></strong>
        </p>
      </div>

      <div class="auth-card auth-card--small">
        <a class="auth-btn auth-btn--link" href="logout.php">WYLOGUJ</a>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>