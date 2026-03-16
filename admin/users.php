<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Użytkownicy — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';

// USUWANIE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId = (int)($_POST['user_id'] ?? 0);

  if ($userId <= 0) {
    $errors[] = 'Niepoprawny user_id.';
  } elseif ($userId === (int)$_SESSION['user_id']) {
    $errors[] = 'Nie możesz usunąć samej siebie.';
  } else {
    try {
      $pdo->beginTransaction();

      // usuń komentarze usera
      $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
      $stmt->execute([$userId]);

      // usuń usera
      $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
      $stmt->execute([$userId]);

      $pdo->commit();
      $success = 'Użytkownik został usunięty (razem z komentarzami).';
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[] = 'Błąd usuwania: ' . $e->getMessage();
    }
  }
}

// LISTA
$stmt = $pdo->query("
  SELECT u.user_id, u.username, u.role,
    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.user_id) AS comments_count
  FROM users u
  ORDER BY u.role DESC, u.user_id ASC
");
$users = $stmt->fetchAll();
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
      <h1>Użytkownicy</h1>

      <?php foreach ($errors as $e): ?>
        <div class="auth-card" style="background: rgba(255,255,255,.9); font-weight:700;">
          <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <?php if ($success): ?>
        <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!$users): ?>
        <div class="auth-card">Brak użytkowników.</div>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <article class="auth-card" style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
            <div>
              <strong>#<?= (int)$u['user_id'] ?></strong>
              <?= htmlspecialchars($u['username']) ?>
              <span style="opacity:.8;">(<?= htmlspecialchars($u['role']) ?>)</span>
              — komentarze: <strong><?= (int)$u['comments_count'] ?></strong>
            </div>

            <div>
              <?php if ((int)$u['user_id'] === (int)$_SESSION['user_id']): ?>
                <button class="auth-btn" type="button" disabled>TO TY</button>
              <?php elseif (($u['role'] ?? '') === 'admin'): ?>
                <button class="auth-btn" type="button" disabled>ADMIN</button>
              <?php else: ?>
                <form method="post" action="users.php" style="display:inline;"
                      onsubmit="return confirm('Usunąć użytkownika i wszystkie jego komentarze?');">
                  <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                  <button class="auth-btn" type="submit" style="background:#8b1d1d;">USUŃ</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="auth-card auth-card--small">
        <a class="auth-btn auth-btn--link" href="index.php">WRÓĆ DO PANELU</a>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>