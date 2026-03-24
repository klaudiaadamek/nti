<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Edytuj post — Admin';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "Niepoprawne ID.";
    exit;
}

$errors = [];
$success = false;

// Pobierz post z bazy
$stmt = $pdo->prepare("SELECT devlog_id, title, content, media_path, media_type FROM devlog_posts WHERE devlog_id = ? LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo "Nie znaleziono posta.";
    exit;
}

$old = [
        'title' => (string)$post['title'],
        'content' => (string)$post['content']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $removeMedia = isset($_POST['remove_media']) ? true : false;

    $mediaPath = $post['media_path'];
    $mediaType = $post['media_type'];

    $old = ['title' => $title, 'content' => $content];

    if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
    if ($content === '' || mb_strlen($content) < 10) $errors['content'] = 'Treść min. 10 znaków.';

    // Usunięcie obecnego pliku
    if ($removeMedia && $mediaPath) {
        if (file_exists(__DIR__ . '/../' . $mediaPath)) {
            unlink(__DIR__ . '/../' . $mediaPath);
        }
        $mediaPath = null;
        $mediaType = 'none';
    }

    // Wgranie nowego pliku
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileSize = $_FILES['media']['size'];
        $fileMime = mime_content_type($fileTmp);

        $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/webm'];
        $maxSize = 50 * 1024 * 1024;

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
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('devlog_', true) . '.' . $ext;

                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    if ($mediaPath && file_exists(__DIR__ . '/../' . $mediaPath)) {
                        unlink(__DIR__ . '/../' . $mediaPath);
                    }
                    $mediaPath = 'uploads/' . $newFileName;
                    $mediaType = $newMediaType;
                } else {
                    $errors['media'] = 'Błąd zapisu pliku.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
      UPDATE devlog_posts
      SET title = ?, content = ?, media_path = ?, media_type = ?
      WHERE devlog_id = ?
    ");
        $stmt->execute([
                $title,
                $content,
                $mediaPath,
                $mediaType,
                $id
        ]);

        $success = true;

        // odśwież dane widoku posta po zapisie
        $stmt = $pdo->prepare("SELECT devlog_id, title, content, media_path, media_type FROM devlog_posts WHERE devlog_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        .current-media-preview {
            margin-top: 10px;
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            background: #000;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #ff6b6b;
            margin-top: 10px;
            cursor: pointer;
            font-weight: 700;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main>
    <section class="admin">
        <h1>Edytuj post #<?= (int)$id ?></h1>

        <?php if ($success): ?>
            <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
                Zapisano zmiany.
            </div>
        <?php endif; ?>

        <form class="auth-card" method="post" action="devlog_edit.php?id=<?= (int)$id ?>" enctype="multipart/form-data" novalidate>
            <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
                <label class="auth-label" for="title">TYTUŁ</label>
                <div class="auth-inputrow">
                    <input class="auth-input" id="title" name="title" type="text"
                           value="<?= htmlspecialchars($old['title']) ?>" />
                </div>
                <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
            </div>

            <div class="auth-field <?= isset($errors['content']) ? 'has-error' : '' ?>">
                <label class="auth-label" for="content">TREŚĆ</label>
                <div class="auth-inputrow">
            <textarea class="auth-input" id="content" name="content" rows="8"
                      style="width:100%; resize: vertical;"><?= htmlspecialchars($old['content']) ?></textarea>
                </div>
                <div class="auth-error"><?= htmlspecialchars($errors['content'] ?? '') ?></div>
            </div>

            <div class="auth-field <?= isset($errors['media']) ? 'has-error' : '' ?>">
                <label class="auth-label">ZDJĘCIE / WIDEO</label>

                <?php if ($post['media_type'] !== 'none' && $post['media_path']): ?>
                    <div style="margin-bottom: 15px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                        <div style="font-size: 0.8rem; margin-bottom: 5px; opacity: 0.7;">OBECNIE ZAŁĄCZONY PLIK:</div>

                        <?php if ($post['media_type'] === 'image'): ?>
                            <img src="<?= htmlspecialchars('../' . ltrim($post['media_path'], '/')) ?>" class="current-media-preview" alt="Obecne zdjęcie">
                        <?php elseif ($post['media_type'] === 'video'): ?>
                            <video class="current-media-preview" controls>
                                <source src="<?= htmlspecialchars('../' . ltrim($post['media_path'], '/')) ?>" type="video/mp4">
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

            <div class="auth-actions" style="gap:10px;">
                <button class="auth-btn" type="submit">ZAPISZ</button>
                <a class="auth-btn auth-btn--link" href="devlog_manage.php">WRÓĆ</a>
            </div>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>