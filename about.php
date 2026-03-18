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
          <p><strong>CATNETIC STROM</strong></p>
            <p>To powszechnie znana prawda: koty absolutnie szaleją na punkcie kłębka włóczki. W tym niekonwencjonalnym roguelike'u zamienisz tę obsesję w swoją najpotężniejszą broń.</p>

            <p>Przejmij dowodzenie nad armią uroczych, puszystych kulek, kierując nimi za pomocą jedynej rzeczy, której nie potrafią się oprzeć. Twoja misja? Pokonać inwazję ich nikczemnych, złych kosmicznych sobowtórów!</p>

              <p>Eksploruj niebezpieczne strefy, wymanewruj wroga i podejmij wyzwanie, aby zebrać wszystkie brakujące części statku kosmicznego. Czas odbudować statek i odesłać tych kosmicznych oszustów z powrotem do gwiazd – tam, gdzie ich miejsce.</p>

            <br>
            <p><strong>Kluczowe cechy:</strong></p>

            <p><strong>Włóczkowa taktyka:</strong> Używaj kłębków włóczki, aby sterować i dowodzić swoją kocią armią.</p>

            <p><strong>Słodycz kontra zło:</strong> Wystaw swoje urocze kociaki do walki z bezlitosnymi kosmicznymi najeźdźcami.</p>

            <p><strong>Ostateczny cel:</strong> Znajdź części, napraw statek, ocal planetę.</p>

            <br>
            <p><strong><a href="https://raspberrycola.itch.io/catnetic-storm" target="_blank" rel="noopener noreferrer" class="about-game-link">Zapraszamy do gry!</a></strong></p>
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