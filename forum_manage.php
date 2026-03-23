<?php
session_start();
$pageTitle = 'Zarządzaj postami — Catnetic Storm';
$active = 'forum';

require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Obsługa usuwania posta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $postId = (int)$_POST['post_id'];

    // Upewnij się, że post należy do zalogowanego użytkownika!
    $stmt = $pdo->prepare("SELECT media_path FROM forum_posts WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $post = $stmt->fetch();

    if ($post) {
        // Usunięcie pliku z serwera, jeśli istnieje
        if ($post['media_path'] && file_exists(__DIR__ . '/' . $post['media_path'])) {
            unlink(__DIR__ . '/' . $post['media_path']);
        }
        // Usunięcie z bazy (komentarze i lajki usuną się automatycznie dzięki ON DELETE CASCADE)
        $pdo->prepare("DELETE FROM forum_posts WHERE post_id = ?")->execute([$postId]);
    }
    header('Location: forum_manage.php');
    exit;
}

// Pobranie postów użytkownika
$stmt = $pdo->prepare("SELECT post_id, title, created_at FROM forum_posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$myPosts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="page">
    <div class="manage-list">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Zarządzaj swoimi postami</h1>
            <a href="forum.php" class="auth-btn auth-btn--link" style="width: auto; padding: 10px 20px;">Wróć na forum</a>
        </div>

        <?php if (!$myPosts): ?>
            <div class="auth-card" style="text-align: center;">Nie masz jeszcze żadnych postów na forum.</div>
        <?php else: ?>
            <?php foreach ($myPosts as $post): ?>
                <div class="manage-item">
                    <div>
                        <strong style="font-size: 1.1rem;"><?= htmlspecialchars($post['title']) ?></strong><br>
                        <span style="font-size: 0.85rem; opacity: 0.7;"><?= $post['created_at'] ?></span>
                    </div>
                    <div class="manage-actions">
                        <a href="forum_edit.php?id=<?= $post['post_id'] ?>" class="manage-btn manage-btn--edit">EDYTUJ</a>
                        <form method="POST" action="forum_manage.php" onsubmit="return confirm('Na pewno chcesz bezpowrotnie usunąć ten post?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                            <button type="submit" class="manage-btn manage-btn--delete">USUŃ</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>