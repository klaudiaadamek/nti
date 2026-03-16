<?php
session_start();

$pageTitle = 'Rejestracja — Catnetic Storm';
$active = 'login';

require __DIR__ . '/includes/db.php';

$errors = [];
$old = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';

  $old['username'] = $username;

  if ($username === '' || strlen($username) < 3) $errors['username'] = 'Nazwa użytkownika min. 3 znaki.';
  if ($password === '' || strlen($password) < 8) $errors['password'] = 'Hasło min. 8 znaków.';
  if ($password2 === '' || $password !== $password2) $errors['password2'] = 'Hasła nie są takie same.';

  if (!$errors) {
    // czy istnieje username
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $exists = $stmt->fetch();

    if ($exists) {
      $errors['general'] = 'Taka nazwa użytkownika jest już zajęta.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      // email = NULL (bo nie używamy)
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->execute([$username, $hash]);

      $_SESSION['user_id'] = (int)$pdo->lastInsertId();
      $_SESSION['username'] = $username;
      $_SESSION['role'] = 'user';

      header('Location: index.php');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="auth" aria-label="Rejestracja">

      <?php if (!empty($errors['general'])): ?>
        <div class="auth-card" style="background: rgba(255,255,255,.85); font-weight:700;">
          <?= htmlspecialchars($errors['general']) ?>
        </div>
      <?php endif; ?>

      <form class="auth-card" action="register.php" method="post" novalidate data-auth-form="true">
        <div class="auth-header">REJESTRACJA</div>

        <div class="auth-field <?= isset($errors['username']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="regUser">NAZWA UŻYTKOWNIKA</label>
          <div class="auth-inputrow">
            <span class="auth-icon" aria-hidden="true">👤</span>
            <input class="auth-input" id="regUser" name="username" type="text" autocomplete="username"
              value="<?= htmlspecialchars($old['username']) ?>" />
          </div>
          <div class="auth-error" role="alert" aria-live="polite"><?= htmlspecialchars($errors['username'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="regPass">HASŁO</label>
          <div class="auth-inputrow">
            <span class="auth-icon" aria-hidden="true">🔒</span>
            <input class="auth-input" id="regPass" name="password" type="password" autocomplete="new-password" />
          </div>
          <div class="auth-error" role="alert" aria-live="polite"><?= htmlspecialchars($errors['password'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['password2']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="regPass2">POWTÓRZ HASŁO</label>
          <div class="auth-inputrow">
            <span class="auth-icon" aria-hidden="true">🔒</span>
            <input class="auth-input" id="regPass2" name="password2" type="password" autocomplete="new-password" />
          </div>
          <div class="auth-error" role="alert" aria-live="polite"><?= htmlspecialchars($errors['password2'] ?? '') ?></div>
        </div>

        <div class="auth-actions">
          <button class="auth-btn" type="submit">ZAŁÓŻ KONTO</button>
        </div>
      </form>

      <div class="auth-card auth-card--small">
        <div class="auth-smalltext">MASZ JUŻ KONTO?</div>
        <a class="auth-btn auth-btn--link" href="login.php">ZALOGUJ SIĘ</a>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
  <script defer src="/nti/js/auth.js"></script>
</body>
</html>