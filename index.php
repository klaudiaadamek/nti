<?php
  $pageTitle = 'Catnetic Storm';
  $active = 'home';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
      <div class="game-container">
          <img src="images/screen.png" alt="Catnetic Storm" />

          <div class="game-credits">

              <div class="credits-group">
                  <div class="credits-label"><strong>Gra Stworzona Przez</strong></div>
                  <div class="credits-main-title">CATERING</div>
                  <div class="credits-divider">------------------------------------------------------</div>
              </div>

              <div class="credits-group">
                  <div class="credits-heading"><strong>PROGRAMOWANIE I ROZWÓJ</strong></div>
                  <div class="credits-names">
                      Amelia Garnys<br>
                      Aleksandra Jakobik<br>
                      Oskar Konecki<br>
                      Adrian Matczak
                  </div>
              </div>
                <br>
              <div class="credits-group">
                  <div class="credits-heading"><strong>SZTUKA I GRAFIKA</strong></div>
                  <div class="credits-names">
                      Emilia Szczerba<br>
                      Aleksandra Jakobik
                  </div>
                  <div class="credits-divider">------------------------------------------------------</div>
              </div>

              <div class="credits-group">
                  <div class="credits-heading"><strong>NARZĘDZIA I TECHNOLOGIA</strong></div>
                  <div class="credits-names">
                      Silnik Gry: <a href="https://unity.com/" target="_blank" rel="noopener noreferrer" class="credits-link">Unity</a><br>
                      Narzędzia Pixel Art: <a href="https://www.aseprite.org/" target="_blank" rel="noopener noreferrer" class="credits-link">Pixquare</a>, <a href="https://www.pixquare.art/" target="_blank" rel="noopener noreferrer" class="credits-link">Aseprite</a>
                  </div>
              </div>

              <div class="credits-group" style="margin-top: 50px;">
                  <div class="credits-heading"><strong>AUDIO</strong></div>
                  <div class="credits-names">
                      <strong>Muzyka i Efekty Dźwiękowe</strong><br>
                      <a href="https://kenney.nl/" target="_blank" rel="noopener noreferrer" class="credits-link">kenney.nl</a>,  <a href="https://freesound.org/" target="_blank" rel="noopener noreferrer" class="credits-link">freesound.org</a>
                  </div>
              </div>

              <div class="credits-group">
                  <div class="credits-divider">------------------------------------------------------</div>
                  <div class="credits-names"><strong>Strona Internetowa</strong></div>
                  <br>
                  <div class="credits-names">
                      Klaudia Adamek<br>
                      Emilia Szczerba <br>
                      Hanna Sauchuk
                  </div>
              </div>

          </div>
      </div>
  </main>
  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>