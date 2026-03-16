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

// pobierz post
$stmt = $pdo->prepare("SELECT devlog_id, title, content, image_path FROM devlog_posts WHERE devlog_id = ? LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
  http_response_code(404);
  echo "Nie znaleziono posta.";
  exit;
}

$old = [
  'title' => (string)$post['title'],
  'content' => (string)$post['content'],
  'image_path' => (string)($post['image_path'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $imagePath = trim($_POST['image_path'] ?? '');

  $old = ['title' => $title, 'content' => $content, 'image_path' => $imagePath];

  if ($title === '' || mb_strlen($title) < 3) $errors['title'] = 'Tytuł min. 3 znaki.';
  if ($content === '' || mb_strlen($content) < 10) $errors['content'] = 'Treść min. 10 znaków.';

  if (!$errors) {
    $stmt = $pdo->prepare("
      UPDATE devlog_posts
      SET title = ?, content = ?, image_path = ?
      WHERE devlog_id = ?
    ");
    $stmt->execute([
      $title,
      $content,
      $imagePath !== '' ? $imagePath : null,
      $id
    ]);

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
      <h1>Edytuj post #<?= (int)$id ?></h1>

      <?php if ($success): ?>
        <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
          Zapisano zmiany.
        </div>
      <?php endif; ?>

      <form class="auth-card" method="post" action="devlog_edit.php?id=<?= (int)$id ?>" novalidate>
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
              value="<?= htmlspecialchars($old['image_path']) ?>" />
          </div>
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