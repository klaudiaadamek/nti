<?php
session_start();

$pageTitle = 'Galeria — Catnetic Storm';
$active = 'gallery';

require __DIR__ . '/includes/db.php';

$stmt = $pdo->query("
  SELECT g.gallery_id, g.title, g.description, g.image_path, g.uploaded_at,
         u.username
  FROM gallery g
  JOIN users u ON u.user_id = g.uploaded_by
  ORDER BY g.uploaded_at DESC, g.gallery_id DESC
");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>

  <style>
    .gallery-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap:16px;
      padding:16px 0;
    }
    .gallery-card{
      background: rgba(255,255,255,.10);
      border-radius: 18px;
      padding: 12px;
      border: 2px solid rgba(255,255,255,.18);
    }
    .gallery-img{
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 14px;
      display:block;
      background: rgba(0,0,0,.15);
    }
    .gallery-title{ font-weight: 800; margin: 10px 0 6px; }
    .gallery-desc{ opacity: .9; margin: 0 0 8px; }
    .gallery-meta{ opacity: .7; font-size: .9rem; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="page">
      <h1>Galeria</h1>

      <?php if (!$items): ?>
        <div class="auth-card">Brak zdjęć w galerii.</div>
      <?php else: ?>
        <div class="gallery-grid">
          <?php foreach ($items as $it): ?>
            <article class="gallery-card">
              <img
                class="gallery-img"
                src="<?= htmlspecialchars((string)$it['image_path']) ?>"
                alt="<?= htmlspecialchars((string)$it['title']) ?>"
                loading="lazy"
              />

              <div class="gallery-title"><?= htmlspecialchars((string)$it['title']) ?></div>

              <div class="gallery-meta">
                dodał(a): <?= htmlspecialchars((string)$it['username']) ?>
                • <?= htmlspecialchars((string)$it['uploaded_at']) ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>