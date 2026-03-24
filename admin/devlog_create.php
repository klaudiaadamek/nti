<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Dodaj post — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$old = ['title' => '', 'content' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $old = ['title' => $title, 'content' => $content];

    if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
    if ($content === '' || mb_strlen($content) < 10) $errors['content'] = 'Treść min. 10 znaków.';

    $mediaPath = null;
    $mediaType = 'none';

    // Obsługa wgrywania zdjęcia lub wideo
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileSize = $_FILES['media']['size'];
        $fileMime = mime_content_type($fileTmp);

        $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/webm'];
        $maxSize = 50 * 1024 * 1024; // Limit 50 MB

        if ($fileSize > $maxSize) {
            $errors['media'] = 'Plik jest za duży (maks. 50MB).';
        } else {
            if (in_array($fileMime, $allowedImages)) {
                $mediaType = 'image';
            } elseif (in_array($fileMime, $allowedVideos)) {
                $mediaType = 'video';
            } else {
                $errors['media'] = 'Niedozwolony format. Akceptowane: JPG, PNG, GIF, WEBP, MP4, WEBM.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('devlog_', true) . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    $mediaPath = 'uploads/' . $newFileName;
                } else {
                    $errors['media'] = 'Błąd zapisu pliku.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
      INSERT INTO devlog_posts (title, content, media_path, media_type, author_id)
      VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->execute([
                $title,
                $content,
                $mediaPath,
                $mediaType,
                (int)$_SESSION['user_id'],
        ]);

        header('Location: ../devlog.php');
        exit;
    }
}
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
        <h1>Dodaj post do devloga</h1>

        <form class="auth-card" method="post" action="devlog_create.php" enctype="multipart/form-data" novalidate>
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
                <label class="auth-label" for="media">ZDJĘCIE / WIDEO (opcjonalnie)</label>
                <input type="file" id="media" name="media" accept="image/*,video/mp4,video/webm" style="color: #fff; margin-top: 5px; font-family: inherit; width: 100%; font-size: 0.8rem;" />
                <div class="auth-error"><?= htmlspecialchars($errors['media'] ?? '') ?></div>
            </div>

            <div class="auth-actions">
                <button class="auth-btn" type="submit">DODAJ POST</button>
                <a class="auth-btn auth-btn--link" href="index.php">WRÓĆ</a>
            </div>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>