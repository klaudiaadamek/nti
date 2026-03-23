<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Komentarze na forum — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

// USUWANIE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = (int)($_POST['comment_id'] ?? 0);

    if ($commentId > 0) {
        // Lajki usuną się automatycznie kaskadowo z bazy
        $stmt = $pdo->prepare("DELETE FROM forum_comments WHERE comment_id = ?");
        $stmt->execute([$commentId]);
    }

    header('Location: forum_comments.php');
    exit;
}

// LISTA KOMENTARZY
$stmt = $pdo->query("
  SELECT
    c.comment_id,
    c.content,
    c.created_at,
    c.post_id,
    c.likes,
    u.username,
    f.title AS post_title
  FROM forum_comments c
  JOIN users u ON u.user_id = c.user_id
  JOIN forum_posts f ON f.post_id = c.post_id
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
        <h1>Komentarze z forum</h1>

        <?php if (!$comments): ?>
            <div class="auth-card">Brak komentarzy na forum.</div>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <article class="auth-card" style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div>
                            <strong>#<?= (int)$c['comment_id'] ?></strong>
                            — <strong><?= htmlspecialchars($c['username']) ?></strong>
                            <span style="opacity: 0.7;">
                    w poście: #<?= (int)$c['post_id'] ?> (<?= htmlspecialchars($c['post_title']) ?>)
                </span>
                            — <span style="color: #ff4757;">❤ <?= (int)$c['likes'] ?></span>
                        </div>
                        <div style="opacity:.8;">
                            <?= htmlspecialchars((string)$c['created_at']) ?>
                        </div>
                    </div>

                    <div style="white-space:pre-wrap; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                        <?= htmlspecialchars((string)$c['content']) ?>
                    </div>

                    <form method="post" action="forum_comments.php" onsubmit="return confirm('Na pewno usunąć ten komentarz?');" style="margin-top: 5px;">
                        <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id'] ?>">
                        <button class="auth-btn" type="submit" style="background:#8b1d1d; padding: 8px 16px; display: inline-block; width: auto;">USUŃ</button>
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