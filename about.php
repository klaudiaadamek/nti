<?php
  $pageTitle = 'O grze — Catnetic Storm';
  $active = 'about';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="about">
      <div class="about-left">
        <h1 class="about-title">O GRZE</h1>

        <div class="about-text">
          <p><strong>TEKST AKAPITU</strong></p>
          <p>opis gry (fabuła, mechaniki, cel).</p>
          <p>...</p>
          <p>...</p>
        </div>
      </div>

      <aside class="about-right" aria-label="Grafika o grze">
        <img
          class="about-photo-img"
          src="images/game.png"
          alt="Grafika przedstawiająca grę Catnetic Storm"
        />
      </aside>
    </section>
  </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>