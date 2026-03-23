<?php
session_start();
$pageTitle = 'Edytuj post — Catnetic Storm';
$active = 'forum';

require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$postId = (int)($_GET['id'] ?? 0);
$errors = [];

// Pobranie obecnych danych posta (teraz wliczając ścieżkę do pliku i jego typ)
$stmt = $pdo->prepare("SELECT title, content, media_path, media_type FROM forum_posts WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: forum_manage.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $removeMedia = isset($_POST['remove_media']) ? true : false;

    // Zmienne przechowujące obecny stan pliku
    $mediaPath = $post['media_path'];
    $mediaType = $post['media_type'];

    if ($title === '') $errors['title'] = 'Podaj tytuł posta.';
    if ($content === '') $errors['content'] = 'Treść posta nie może być pusta.';

    // 1. Zaznaczono chęć usunięcia obecnego zdjęcia/wideo
    if ($removeMedia && $mediaPath) {
        if (file_exists(__DIR__ . '/' . $mediaPath)) {
            unlink(__DIR__ . '/' . $mediaPath); // Usuwanie z serwera
        }
        $mediaPath = null;
        $mediaType = 'none';
    }

    // 2. Obsługa przesłania nowego pliku (podmienia stary)
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileSize = $_FILES['media']['size'];
        $fileMime = mime_content_type($fileTmp);

        $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/webm'];
        $maxSize = 50 * 1024 * 1024; // 50 MB

        if ($fileSize > $maxSize) {
            $errors['media'] = 'Plik jest za duży (maks. 50MB).';
        } else {
            if (in_array($fileMime, $allowedImages)) {
                $newMediaType = 'image';
            } elseif (in_array($fileMime, $allowedVideos)) {
                $newMediaType = 'video';
            } else {
                $errors['media'] = 'Niedozwolony format. Akceptowane: JPG, PNG, GIF, WEBP, MP4, WEBM.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('forum_', true) . '.' . $ext;

                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    // Jeśli był wcześniej jakiś stary plik, to go usuwamy
                    if ($mediaPath && file_exists(__DIR__ . '/' . $mediaPath)) {
                        unlink(__DIR__ . '/' . $mediaPath);
                    }
                    $mediaPath = 'uploads/' . $newFileName;
                    $mediaType = $newMediaType;
                } else {
                    $errors['media'] = 'Błąd zapisu pliku.';
                }
            }
        }
    }

    // 3. Zapis do bazy danych
    if (empty($errors)) {
        $updateStmt = $pdo->prepare("UPDATE forum_posts SET title = ?, content = ?, media_path = ?, media_type = ? WHERE post_id = ? AND user_id = ?");
        if ($updateStmt->execute([$title, $content, $mediaPath, $mediaType, $postId, $userId])) {
            header('Location: forum_manage.php');
            exit;
        } else {
            $errors['general'] = 'Błąd podczas aktualizacji posta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="page">
    <form class="auth-card" method="POST" enctype="multipart/form-data" style="max-width: 800px; margin: 40px auto;">
        <div class="auth-header">EDYTUJ POST</div>

        <?php if (!empty($errors['general'])): ?>
            <div class="auth-error" style="display:block; margin-bottom: 15px;"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
            <label class="auth-label">TYTUŁ POSTA</label>
            <input class="auth-input" name="title" type="text" value="<?= htmlspecialchars($_POST['title'] ?? $post['title']) ?>" required />
            <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['content']) ? 'has-error' : '' ?>">
            <label class="auth-label">TREŚĆ POSTA</label>
            <textarea class="auth-input" name="content" rows="8" style="resize:vertical; min-height: 150px;" required><?= htmlspecialchars($_POST['content'] ?? $post['content']) ?></textarea>
            <div class="auth-error"><?= htmlspecialchars($errors['content'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['media']) ? 'has-error' : '' ?>">
            <label class="auth-label">ZDJĘCIE / WIDEO</label>

            <?php if ($post['media_type'] !== 'none' && $post['media_path']): ?>
                <div style="margin-bottom: 15px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                    <div style="font-size: 0.8rem; margin-bottom: 5px; opacity: 0.7;">OBECNIE ZAŁĄCZONY PLIK:</div>

                    <?php if ($post['media_type'] === 'image'): ?>
                        <img src="<?= htmlspecialchars($post['media_path']) ?>" class="current-media-preview" alt="Obecne zdjęcie">
                    <?php elseif ($post['media_type'] === 'video'): ?>
                        <video class="current-media-preview" controls>
                            <source src="<?= htmlspecialchars($post['media_path']) ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>

                    <label class="checkbox-label">
                        <input type="checkbox" name="remove_media" value="1" style="width: auto; margin: 0;">
                        Usuń obecny załącznik (zaznacz to, jeśli nie chcesz już żadnego zdjęcia/wideo)
                    </label>
                </div>
            <?php endif; ?>

            <div style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 5px; margin-top: 10px;">
                <?= ($post['media_type'] !== 'none' && $post['media_path']) ? 'LUB WGRAJ NOWY, ABY ZAMIENIĆ STARY:' : 'WGRAJ PLIK (OPCJONALNIE):' ?>
            </div>

            <input type="file" name="media" accept="image/*,video/mp4,video/webm" style="color: #fff; font-family: inherit; width: 100%; font-size: 0.8rem;" />
            <div class="auth-error"><?= htmlspecialchars($errors['media'] ?? '') ?></div>
        </div>

        <div class="auth-actions" style="display: flex; gap: 15px; margin-top: 20px;">
            <button class="auth-btn" type="submit" style="flex: 1;">ZAPISZ ZMIANY</button>
            <a href="forum_manage.php" class="auth-btn" style="flex: 1; background: rgba(255,255,255,0.2); text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">ANULUJ</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>