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
          grid-template-columns: repeat(5, 1fr);
          gap:16px;
          padding:16px 0;
          width: 100%;
      }

      @media (max-width: 1200px) {
          .gallery-grid { grid-template-columns: repeat(4, 1fr); }
      }
      @media (max-width: 900px) {
          .gallery-grid { grid-template-columns: repeat(3, 1fr); }
      }
      @media (max-width: 600px) {
          .gallery-grid { grid-template-columns: repeat(2, 1fr); }
      }
      @media (max-width: 400px) {
          .gallery-grid { grid-template-columns: 1fr; }
      }
    .gallery-card{
      background: rgba(255,255,255,.10);
      border-radius: 22px;
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

  <div class="lightbox" id="lightbox">
      <button class="lightbox-close" id="lb-close">&times;</button>
      <div class="lightbox-controls">
          <button class="lightbox-btn" id="lb-prev">&#10094;</button>
          <button class="lightbox-btn" id="lb-next">&#10095;</button>
      </div>
      <div class="lightbox-content">
          <img class="lightbox-img" id="lb-img" src="" alt="">
          <div class="lightbox-caption" id="lb-caption"></div>
      </div>
  </div>

  <script>
      document.addEventListener('DOMContentLoaded', () => {
          const lightbox = document.getElementById('lightbox');
          const lbImg = document.getElementById('lb-img');
          const lbCaption = document.getElementById('lb-caption');
          const btnClose = document.getElementById('lb-close');
          const btnPrev = document.getElementById('lb-prev');
          const btnNext = document.getElementById('lb-next');

          const images = Array.from(document.querySelectorAll('.gallery-img'));
          let currentIndex = 0;

          if (images.length === 0) return;

          images.forEach((img, index) => {
              img.style.cursor = 'pointer';
              img.addEventListener('click', () => {
                  currentIndex = index;
                  updateLightbox();
                  lightbox.classList.add('active');
              });
          });

          function updateLightbox() {
              const imgElement = images[currentIndex];
              lbImg.src = imgElement.src;
              lbImg.alt = imgElement.alt;
              lbCaption.textContent = imgElement.alt;
          }

          function closeLightbox() {
              lightbox.classList.remove('active');
          }

          function showNext() {
              currentIndex = (currentIndex + 1) % images.length;
              updateLightbox();
          }

          function showPrev() {
              currentIndex = (currentIndex - 1 + images.length) % images.length;
              updateLightbox();
          }

          btnClose.addEventListener('click', closeLightbox);

          btnNext.addEventListener('click', (e) => {
              e.stopPropagation();
              showNext();
          });

          btnPrev.addEventListener('click', (e) => {
              e.stopPropagation();
              showPrev();
          });

          lightbox.addEventListener('click', (e) => {
              if (e.target === lightbox || e.target.classList.contains('lightbox-content')) {
                  closeLightbox();
              }
          });

          document.addEventListener('keydown', (e) => {
              if (!lightbox.classList.contains('active')) return;

              if (e.key === 'Escape') closeLightbox();
              if (e.key === 'ArrowRight') showNext();
              if (e.key === 'ArrowLeft') showPrev();
          });
      });
  </script>
</body>
</html>