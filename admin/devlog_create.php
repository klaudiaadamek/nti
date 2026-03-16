<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Dodaj post — Panel admina';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$old = ['title' => '', 'content' => '', 'image_path' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $imagePath = trim($_POST['image_path'] ?? '');

  $old = ['title' => $title, 'content' => $content, 'image_path' => $imagePath];

  if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
  if ($content === '' || mb_strlen($content) < 10) $errors['content'] = 'Treść min. 10 znaków.';

  // image_path opcjonalne (może być puste)
  if (!$errors) {
    $stmt = $pdo->prepare("
      INSERT INTO devlog_posts (title, content, image_path, author_id)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
      $title,
      $content,
      $imagePath !== '' ? $imagePath : null,
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

      <form class="auth-card" method="post" action="devlog_create.php" novalidate>
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

        <div class="auth-field">
          <label class="auth-label" for="image_path">ŚCIEŻKA DO OBRAZKA (opcjonalnie)</label>
          <div class="auth-inputrow">
            <input class="auth-input" id="image_path" name="image_path" type="text"
              placeholder="np. images/1.jpg"
              value="<?= htmlspecialchars($old['image_path']) ?>" />
          </div>
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