<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Edytuj galerię — Admin';
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

$stmt = $pdo->prepare("SELECT gallery_id, title, description, media_path, media_type FROM gallery WHERE gallery_id = ? LIMIT 1");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo "Nie znaleziono pliku.";
    exit;
}

$old = [
        'title' => (string)$item['title'],
        'description' => (string)$item['description']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $removeMedia = isset($_POST['remove_media']) ? true : false;

    $mediaPath = $item['media_path'];
    $mediaType = $item['media_type'];

    $old = ['title' => $title, 'description' => $description];

    if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';

    if ($removeMedia && $mediaPath) {
        if (file_exists(__DIR__ . '/../' . $mediaPath)) unlink(__DIR__ . '/../' . $mediaPath);
        $mediaPath = null;
        $mediaType = 'none';
    }

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
                $newFileName = uniqid('gallery_', true) . '.' . $ext;

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
      UPDATE gallery
      SET title = ?, description = ?, media_path = ?, media_type = ?
      WHERE gallery_id = ?
    ");
        $stmt->execute([$title, $description !== '' ? $description : null, $mediaPath, $mediaType, $id]);

        $success = true;

        // Odświeżenie danych do podglądu po zapisie
        $stmt->execute([$title, $description, $mediaPath, $mediaType, $id]); // workaround update
        $stmt = $pdo->prepare("SELECT gallery_id, title, description, media_path, media_type FROM gallery WHERE gallery_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        .current-media-preview { margin-top: 10px; max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: #000; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #ff6b6b; margin-top: 10px; cursor: pointer; font-weight: 700; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main>
    <section class="admin">
        <h1>Edytuj galerię #<?= (int)$id ?></h1>

        <?php if ($success): ?>
            <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
                Zapisano zmiany.
            </div>
        <?php endif; ?>

        <form class="auth-card" method="post" action="gallery_edit.php?id=<?= (int)$id ?>" enctype="multipart/form-data" novalidate>
            <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
                <label class="auth-label" for="title">TYTUŁ</label>
                <div class="auth-inputrow">
                    <input class="auth-input" id="title" name="title" type="text" value="<?= htmlspecialchars($old['title']) ?>" />
                </div>
                <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
            </div>

            <div class="auth-field <?= isset($errors['description']) ? 'has-error' : '' ?>">
                <label class="auth-label" for="description">OPIS (opcjonalnie)</label>
                <div class="auth-inputrow">
                    <textarea class="auth-input" id="description" name="description" rows="5" style="width:100%; resize: vertical;"><?= htmlspecialchars($old['description']) ?></textarea>
                </div>
                <div class="auth-error"><?= htmlspecialchars($errors['description'] ?? '') ?></div>
            </div>

            <div class="auth-field <?= isset($errors['media']) ? 'has-error' : '' ?>">
                <label class="auth-label">ZDJĘCIE / WIDEO</label>

                <?php if ($item['media_type'] !== 'none' && $item['media_path']): ?>
                    <div style="margin-bottom: 15px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                        <div style="font-size: 0.8rem; margin-bottom: 5px; opacity: 0.7;">OBECNIE ZAŁĄCZONY PLIK:</div>

                        <?php if ($item['media_type'] === 'image'): ?>
                            <img src="<?= htmlspecialchars('../' . ltrim($item['media_path'], '/')) ?>" class="current-media-preview" alt="Obecne zdjęcie">
                        <?php elseif ($item['media_type'] === 'video'): ?>
                            <video class="current-media-preview" controls>
                                <source src="<?= htmlspecialchars('../' . ltrim($item['media_path'], '/')) ?>" type="video/mp4">
                            </video>
                        <?php endif; ?>

                        <label class="checkbox-label">
                            <input type="checkbox" name="remove_media" value="1" style="width: auto; margin: 0;">
                            Usuń obecny załącznik (w galerii spowoduje to zepsucie kafelka, więc sugerujemy wgranie nowego pliku!)
                        </label>
                    </div>
                <?php endif; ?>

                <div style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 5px; margin-top: 10px;">
                    <?= ($item['media_type'] !== 'none' && $item['media_path']) ? 'LUB WGRAJ NOWY, ABY ZAMIENIĆ STARY:' : 'WGRAJ PLIK:' ?>
                </div>

                <input type="file" name="media" accept="image/*,video/mp4,video/webm" style="color: #fff; font-family: inherit; width: 100%; font-size: 0.8rem;" />
                <div class="auth-error"><?= htmlspecialchars($errors['media'] ?? '') ?></div>
            </div>

            <div class="auth-actions" style="gap:10px;">
                <button class="auth-btn" type="submit">ZAPISZ</button>
                <a class="auth-btn auth-btn--link" href="gallery_manage.php">WRÓĆ</a>
            </div>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>