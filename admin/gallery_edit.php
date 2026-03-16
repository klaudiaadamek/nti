<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Edytuj zdjęcie — Admin';
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

$stmt = $pdo->prepare("SELECT gallery_id, title, image_path FROM gallery WHERE gallery_id = ? LIMIT 1");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
  http_response_code(404);
  echo "Nie znaleziono zdjęcia.";
  exit;
}

$old = [
  'title' => (string)$item['title'],
  'image_path' => (string)$item['image_path'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $imagePath = trim($_POST['image_path'] ?? '');

  $old = ['title' => $title, 'image_path' => $imagePath];

  if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
  if ($imagePath === '') $errors['image_path'] = 'Podaj ścieżkę do obrazka.';

  if (!$errors) {
    $stmt = $pdo->prepare("
      UPDATE gallery
      SET title = ?, image_path = ?
      WHERE gallery_id = ?
    ");
    $stmt->execute([$title, $imagePath, $id]);
    $success = true;
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
      <h1>Edytuj zdjęcie #<?= (int)$id ?></h1>

      <?php if ($success): ?>
        <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
          Zapisano zmiany.
        </div>
      <?php endif; ?>

      <form class="auth-card" method="post" action="gallery_edit.php?id=<?= (int)$id ?>" novalidate>
        <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="title">TYTUŁ</label>
          <div class="auth-inputrow">
            <input class="auth-input" id="title" name="title" type="text"
              value="<?= htmlspecialchars($old['title']) ?>" />
          </div>
          <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['image_path']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="image_path">ŚCIEŻKA DO OBRAZKA</label>
          <div class="auth-inputrow">
            <input class="auth-input" id="image_path" name="image_path" type="text"
              value="<?= htmlspecialchars($old['image_path']) ?>" />
          </div>
          <div class="auth-error"><?= htmlspecialchars($errors['image_path'] ?? '') ?></div>
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