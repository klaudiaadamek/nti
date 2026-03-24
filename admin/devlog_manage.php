<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Devlog — Zarządzanie (Admin)';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $devlogId = (int)($_POST['devlog_id'] ?? 0);

    if ($devlogId <= 0) {
        $errors[] = 'Niepoprawny devlog_id.';
    } else {
        try {
            $pdo->beginTransaction();

            // Sprawdź, czy ma załączony plik i usuń z dysku
            $stmt = $pdo->prepare("SELECT media_path FROM devlog_posts WHERE devlog_id = ?");
            $stmt->execute([$devlogId]);
            $post = $stmt->fetch();

            if ($post && $post['media_path'] && file_exists(__DIR__ . '/../' . $post['media_path'])) {
                unlink(__DIR__ . '/../' . $post['media_path']);
            }

            // usuń komentarze do posta
            $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
            $stmt->execute([$devlogId]);

            // usuń post
            $stmt = $pdo->prepare("DELETE FROM devlog_posts WHERE devlog_id = ?");
            $stmt->execute([$devlogId]);

            $pdo->commit();
            $success = 'Post został usunięty (razem z komentarzami i plikiem z dysku).';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Błąd usuwania: ' . $e->getMessage();
        }
    }
}

// lista postów
$stmt = $pdo->query("
  SELECT p.devlog_id, p.title, p.created_at, u.username,
    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.devlog_id) AS comments_count
  FROM devlog_posts p
  JOIN users u ON u.user_id = p.author_id
  ORDER BY p.created_at DESC, p.devlog_id DESC
");
$posts = $stmt->fetchAll();
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
        <h1>Devlog — zarządzanie</h1>

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

        <div class="auth-card auth-card--small">
            <a class="auth-btn" href="devlog_create.php">+ DODAJ NOWY POST</a>
        </div>

        <?php if (!$posts): ?>
            <div class="auth-card">Brak postów.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <article class="auth-card" style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
                    <div>
                        <strong>#<?= (int)$p['devlog_id'] ?></strong>
                        <?= htmlspecialchars((string)$p['title']) ?>
                        <div style="opacity:.8; font-size:12px; margin-top:4px;">
                            autor: <?= htmlspecialchars((string)$p['username']) ?>
                            • <?= htmlspecialchars((string)$p['created_at']) ?>
                            • komentarze: <strong><?= (int)$p['comments_count'] ?></strong>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <a class="auth-btn auth-btn--link" href="devlog_edit.php?id=<?= (int)$p['devlog_id'] ?>">EDYTUJ</a>

                        <form method="post" action="devlog_manage.php"
                              onsubmit="return confirm('Usunąć post i wszystkie komentarze do niego?');"
                              style="display:inline;">
                            <input type="hidden" name="devlog_id" value="<?= (int)$p['devlog_id'] ?>">
                            <button class="auth-btn" type="submit" style="background:#8b1d1d;">USUŃ</button>
                        </form>
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