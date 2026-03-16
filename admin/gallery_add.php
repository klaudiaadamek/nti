<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Dodaj zdjęcie — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$old = ['title' => '', 'description' => '', 'image_path' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $imagePath = trim($_POST['image_path'] ?? '');

  $old = ['title' => $title, 'description' => $description, 'image_path' => $imagePath];

  if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
  if ($imagePath === '') $errors['image_path'] = 'Podaj ścieżkę do obrazka (np. images/1.jpg).';

  if (!$errors) {
    $stmt = $pdo->prepare("
      INSERT INTO gallery (title, description, image_path, uploaded_by)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
      $title,
      $description !== '' ? $description : null,
      $imagePath,
      (int)$_SESSION['user_id'],
    ]);

    header('Location: ../gallery.php');
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
      <h1>Dodaj zdjęcie do galerii</h1>

      <form class="auth-card" method="post" action="gallery_add.php" novalidate>
        <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="title">TYTUŁ</label>
          <div class="auth-inputrow">
            <input class="auth-input" id="title" name="title" type="text"
              value="<?= htmlspecialchars($old['title']) ?>" />
          </div>
          <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['description']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="description">OPIS (opcjonalnie)</label>
          <div class="auth-inputrow">
            <textarea class="auth-input" id="description" name="description" rows="5"
              style="width:100%; resize: vertical;"><?= htmlspecialchars($old['description']) ?></textarea>
          </div>
          <div class="auth-error"><?= htmlspecialchars($errors['description'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['image_path']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="image_path">ŚCIEŻKA DO OBRAZKA</label>
          <div class="auth-inputrow">
            <input class="auth-input" id="image_path" name="image_path" type="text"
              placeholder="np. images/1.jpg"
              value="<?= htmlspecialchars($old['image_path']) ?>" />
          </div>
          <div class="auth-error"><?= htmlspecialchars($errors['image_path'] ?? '') ?></div>
        </div>

        <div class="auth-actions">
          <button class="auth-btn" type="submit">DODAJ ZDJĘCIE</button>
          <a class="auth-btn auth-btn--link" href="index.php">WRÓĆ</a>
        </div>
      </form>

    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>