<?php
  $active = $active ?? '';

  function navClass(string $key, string $active): string {
    return $key === $active ? 'is-active' : '';
  }

  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  $BASE = '/nti/';
?>
<header>
  <nav class="menu" aria-label="Menu główne">
    <a class="<?= navClass('home', $active) ?>" href="<?= $BASE ?>index.php">STRONA GŁÓWNA</a>
    <a class="<?= navClass('about', $active) ?>" href="<?= $BASE ?>about.php">O GRZE</a>
    <a class="<?= navClass('gallery', $active) ?>" href="<?= $BASE ?>gallery.php">GALERIA</a>
      <a class="<?= navClass('forum', $active) ?>" href="<?= $BASE ?>forum.php">FORUM</a>
    <a class="<?= navClass('devlog', $active) ?>" href="<?= $BASE ?>devlog.php">DEVLOG</a>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <a class="<?= navClass('profile', $active) ?>" href="<?= $BASE ?>profile.php">PROFIL</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'admin')): ?>
      <a class="<?= navClass('admin', $active) ?>" href="<?= $BASE ?>admin/index.php">ADMIN</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="<?= $BASE ?>logout.php">WYLOGUJ (<?= htmlspecialchars($_SESSION['username'] ?? 'konto') ?>)</a>
    <?php else: ?>
      <a class="<?= navClass('login', $active) ?>" href="<?= $BASE ?>login.php">LOGOWANIE</a>
    <?php endif; ?>
  </nav>
</header>