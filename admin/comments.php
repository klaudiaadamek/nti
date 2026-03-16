<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Komentarze — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

// USUWANIE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commentId = (int)($_POST['comment_id'] ?? 0);

  if ($commentId > 0) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->execute([$commentId]);
  }

  header('Location: comments.php');
  exit;
}

// LISTA
$stmt = $pdo->query("
  SELECT
    c.comment_id,
    c.content,
    c.created_at,
    c.post_id,
    u.username,
    p.title AS post_title
  FROM comments c
  JOIN users u ON u.user_id = c.user_id
  JOIN devlog_posts p ON p.devlog_id = c.post_id
  ORDER BY c.created_at DESC, c.comment_id DESC
");
$comments = $stmt->fetchAll();
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
      <h1>Komentarze</h1>

      <?php if (!$comments): ?>
        <div class="auth-card">Brak komentarzy.</div>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
          <article class="auth-card" style="display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
              <div>
                <strong>#<?= (int)$c['comment_id'] ?></strong>
                — <?= htmlspecialchars($c['username']) ?>
                — post: <strong>#<?= (int)$c['post_id'] ?></strong> (<?= htmlspecialchars($c['post_title']) ?>)
              </div>
              <div style="opacity:.8;">
                <?= htmlspecialchars((string)$c['created_at']) ?>
              </div>
            </div>

            <div style="white-space:pre-wrap;">
              <?= htmlspecialchars((string)$c['content']) ?>
            </div>

            <form method="post" action="comments.php" onsubmit="return confirm('Usunąć ten komentarz?');">
              <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id'] ?>">
              <button class="auth-btn" type="submit" style="background:#8b1d1d;">USUŃ</button>
            </form>
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