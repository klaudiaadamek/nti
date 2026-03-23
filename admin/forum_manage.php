<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Forum — Zarządzanie (Admin)';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);

    if ($postId <= 0) {
        $errors[] = 'Niepoprawne ID posta.';
    } else {
        try {
            // Sprawdzamy, czy post ma przypisany plik, żeby go usunąć z serwera
            $stmt = $pdo->prepare("SELECT media_path FROM forum_posts WHERE post_id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();

            if ($post && $post['media_path'] && file_exists(__DIR__ . '/../' . $post['media_path'])) {
                unlink(__DIR__ . '/../' . $post['media_path']);
            }

            // Usuwamy post (komentarze znikną automatycznie dzięki ON DELETE CASCADE)
            $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE post_id = ?");
            $stmt->execute([$postId]);

            $success = 'Post na forum został usunięty (wraz z komentarzami i plikami).';
        } catch (Throwable $e) {
            $errors[] = 'Błąd usuwania: ' . $e->getMessage();
        }
    }
}

// Lista postów
$stmt = $pdo->query("
  SELECT f.post_id, f.title, f.created_at, u.username,
    (SELECT COUNT(*) FROM forum_comments c WHERE c.post_id = f.post_id) AS comments_count
  FROM forum_posts f
  JOIN users u ON u.user_id = f.user_id
  ORDER BY f.created_at DESC, f.post_id DESC
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
        <h1>Forum — zarządzanie postami</h1>

        <?php foreach ($errors as $e): ?>
            <div class="auth-card" style="background: rgba(255,255,255,.9); font-weight:700; color: #ff6b6b;">
                <?= htmlspecialchars($e) ?>
            </div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700; color: #15803d;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!$posts): ?>
            <div class="auth-card">Brak postów na forum.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <article class="auth-card" style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
                    <div>
                        <strong>#<?= (int)$p['post_id'] ?></strong>
                        <?= htmlspecialchars((string)$p['title']) ?>
                        <div style="opacity:.8; font-size:12px; margin-top:4px;">
                            autor: <?= htmlspecialchars((string)$p['username']) ?>
                            • <?= htmlspecialchars((string)$p['created_at']) ?>
                            • komentarze: <strong><?= (int)$p['comments_count'] ?></strong>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <form method="post" action="forum_manage.php"
                              onsubmit="return confirm('Czy na pewno chcesz usunąć ten post oraz wszystkie jego komentarze?');"
                              style="display:inline; margin: 0;">
                            <input type="hidden" name="post_id" value="<?= (int)$p['post_id'] ?>">
                            <button class="auth-btn" type="submit" style="background:#8b1d1d; padding: 10px 16px;">USUŃ</button>
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