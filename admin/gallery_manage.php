<?php
require __DIR__ . '/guard.php';

$pageTitle = 'Galeria — Zarządzanie (Admin)';
$active = 'admin';

require __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $galleryId = (int)($_POST['gallery_id'] ?? 0);

  if ($galleryId <= 0) {
    $errors[] = 'Niepoprawny gallery_id.';
  } else {
    try {
      $stmt = $pdo->prepare("DELETE FROM gallery WHERE gallery_id = ?");
      $stmt->execute([$galleryId]);
      $success = 'Zdjęcie zostało usunięte z galerii.';
    } catch (Throwable $e) {
      $errors[] = 'Błąd usuwania: ' . $e->getMessage();
    }
  }
}

$stmt = $pdo->query("
  SELECT g.gallery_id, g.title, g.image_path, g.uploaded_at, u.username
  FROM gallery g
  JOIN users u ON u.user_id = g.uploaded_by
  ORDER BY g.uploaded_at DESC, g.gallery_id DESC
");
$items = $stmt->fetchAll();
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
      <h1>Galeria — zarządzanie</h1>

      <?php foreach ($errors as $e): ?>
        <div class="auth-card" style="background: rgba(255,255,255,.9); font-weight:700;">
          <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <?php if ($success): ?>
        <div class="auth-card" style="background: rgba(200,255,200,.9); font-weight:700;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <div class="auth-card auth-card--small">
        <a class="auth-btn" href="gallery_add.php">+ DODAJ NOWE ZDJĘCIE</a>
      </div>

      <?php if (!$items): ?>
        <div class="auth-card">Brak zdjęć.</div>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <article class="auth-card" style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <img
  src="<?= htmlspecialchars('../' . ltrim((string)$it['image_path'], '/')) ?>"
  alt=""
  style="width:120px; height:80px; object-fit:cover; border-radius:12px; border:2px solid rgba(0,0,0,.15); background:#000;"
  loading="lazy"
/>
              <div>
                <strong>#<?= (int)$it['gallery_id'] ?></strong>
                <?= htmlspecialchars((string)$it['title']) ?>
                <div style="opacity:.8; font-size:12px; margin-top:4px;">
                  dodał(a): <?= htmlspecialchars((string)$it['username']) ?>
                  • <?= htmlspecialchars((string)$it['uploaded_at']) ?>
                </div>
                <div style="opacity:.75; font-size:12px; margin-top:4px;">
                  <?= htmlspecialchars((string)$it['image_path']) ?>
                </div>
              </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
              <a class="auth-btn auth-btn--link" href="gallery_edit.php?id=<?= (int)$it['gallery_id'] ?>">EDYTUJ</a>

              <form method="post" action="gallery_manage.php"
                    onsubmit="return confirm('Usunąć to zdjęcie z galerii?');"
                    style="display:inline;">
                <input type="hidden" name="gallery_id" value="<?= (int)$it['gallery_id'] ?>">
                <button class="auth-btn" type="submit" style="background:#8b1d1d;">USUŃ</button>
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