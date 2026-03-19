<?php
  session_start();
  $pageTitle = 'Devlog — Catnetic Storm';
  $active = 'devlog';

  require __DIR__ . '/includes/db.php';

  // pobierz posty devloga (najnowsze na górze)
  $stmt = $pdo->query("
    SELECT devlog_id, title, content, image_path, created_at
    FROM devlog_posts
    ORDER BY created_at DESC, devlog_id DESC
  ");
  $posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
    <!-- WIDOK 1: lista postów -->
    <section id="devlog-list" class="devlog">

      <?php if (!$posts): ?>
        <article class="post-card" tabindex="0">
          <div class="post-left">
            <h2 class="post-title">BRAK POSTÓW</h2>
            <p class="post-body">Dodaj pierwszy post do tabeli <strong>devlog_posts</strong>.</p>
          </div>
          <div class="post-right"></div>
        </article>
      <?php endif; ?>

      <?php foreach ($posts as $p): ?>
        <?php
          $id = (int)$p['devlog_id'];
          $title = (string)$p['title'];
          $body = (string)$p['content'];      // w DB może być z \n
          $image = (string)$p['image_path'];  // np. images/1.jpg
        ?>
        <article class="post-card" tabindex="0"
          data-post-id="<?= $id ?>"
          data-title="<?= htmlspecialchars($title) ?>"
          data-body="<?= htmlspecialchars($body) ?>"
          data-image="<?= htmlspecialchars($image) ?>"
        >
          <div class="post-left">
            <h2 class="post-title"><?= htmlspecialchars($title) ?></h2>
            <p class="post-body">
              <?= nl2br(htmlspecialchars($body)) ?>
            </p>
          </div>

          <div class="post-right">
            <?php if (trim($image) !== ''): ?>
<img
  class="post-img"
  src="<?= htmlspecialchars($image) ?>"
  alt="<?= htmlspecialchars('Grafika do wpisu: ' . $title) ?>"
  loading="lazy"
/>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>

    </section>

    <!-- WIDOK 2: szczegóły posta + komentarze -->
    <section id="devlog-post" class="devlog is-hidden" aria-label="Szczegóły posta">
      <button class="back-btn" type="button" id="backToList"> ← WRÓĆ </button>

      <article class="post-card post-card--details">
        <div class="post-left">
          <h2 class="post-title" id="detailsTitle">NOWY POST</h2>
          <p class="post-body" id="detailsBody">...</p>
        </div>

        <div class="post-right">
          <img class="post-img" id="detailsImg" src="" alt="Grafika do wpisu devloga" hidden />
        </div>
      </article>

      <section class="comments">
        <div class="comments-head">KOMENTARZE</div>

        <div class="comments-list" id="commentsList" aria-label="Lista komentarzy"></div>

        <form class="comment-form" id="commentForm">
          <input class="comment-input comment-input--wide" id="commentText" name="text" type="text" placeholder="DODAJ KOMENTARZ..." required />
          <button class="comment-btn" type="submit">DODAJ</button>
        </form>
      </section>
    </section>
  </main>

  <script src="/nti/js/devlog.js"></script>
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <div class="lightbox" id="devlog-lightbox">
      <button class="lightbox-close" id="dlb-close">&times;</button>
      <div class="lightbox-content">
          <img class="lightbox-img" id="dlb-img" src="" alt="">
      </div>
  </div>

  <script>
      document.addEventListener('DOMContentLoaded', () => {
          const lightbox = document.getElementById('devlog-lightbox');
          const lbImg = document.getElementById('dlb-img');
          const btnClose = document.getElementById('dlb-close');

          document.body.addEventListener('click', (e) => {
              if (e.target.classList.contains('post-img')) {
                  e.stopPropagation();

                  lbImg.src = e.target.src;
                  lbImg.alt = e.target.alt;
                  lightbox.classList.add('active');
              }
          });

          function closeLightbox() {
              lightbox.classList.remove('active');
          }

          btnClose.addEventListener('click', closeLightbox);

          lightbox.addEventListener('click', (e) => {
              if (e.target === lightbox || e.target.classList.contains('lightbox-content')) {
                  closeLightbox();
              }
          });

          document.addEventListener('keydown', (e) => {
              if (e.key === 'Escape' && lightbox.classList.contains('active')) {
                  closeLightbox();
              }
          });
      });
  </script>
</body>
</html>