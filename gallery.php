<?php
session_start();

$pageTitle = 'Galeria — Catnetic Storm';
$active = 'gallery';

require __DIR__ . '/includes/db.php';

$stmt = $pdo->query("
  SELECT g.gallery_id, g.title, g.description, g.media_path, g.media_type, g.uploaded_at,
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
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="page">
        <h1>Galeria</h1>

        <?php if (!$items): ?>
            <div class="auth-card">Brak elementów w galerii.</div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($items as $it): ?>
                    <article class="gallery-card">

                        <?php if ($it['media_type'] === 'video'): ?>
                            <video class="gallery-img gallery-trigger" data-type="video" data-src="<?= htmlspecialchars((string)$it['media_path']) ?>" data-alt="<?= htmlspecialchars((string)$it['title']) ?>" loading="lazy">
                                <source src="<?= htmlspecialchars((string)$it['media_path']) ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <img class="gallery-img gallery-trigger" data-type="image" data-src="<?= htmlspecialchars((string)$it['media_path']) ?>" data-alt="<?= htmlspecialchars((string)$it['title']) ?>" src="<?= htmlspecialchars((string)$it['media_path']) ?>" alt="<?= htmlspecialchars((string)$it['title']) ?>" loading="lazy" />
                        <?php endif; ?>

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

<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lb-close">&times;</button>
    <div class="lightbox-controls">
        <button class="lightbox-btn" id="lb-prev">&#10094;</button>
        <button class="lightbox-btn" id="lb-next">&#10095;</button>
    </div>
    <div class="lightbox-content">
        <img class="lightbox-img" id="lb-img" src="" alt="" hidden>
        <video class="lightbox-img" id="lb-video" controls hidden></video>
        <div class="lightbox-caption" id="lb-caption"></div>
    </div>
</div>

<script defer src="js/gallery.js"></script>
</body>
</html>