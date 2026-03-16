<?php
session_start();

$pageTitle = 'Logowanie — Catnetic Storm';
$active = 'login';

require __DIR__ . '/includes/db.php';

$errors = [];
$old = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $old['username'] = $username;

  if ($username === '') $errors['username'] = 'Wpisz nazwę użytkownika.';
  if ($password === '') $errors['password'] = 'Wpisz hasło.';

  if (!$errors) {
    $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
      $errors['general'] = 'Nieprawidłowa nazwa użytkownika lub hasło.';
    } else {
      // zalogowano
      $_SESSION['user_id'] = (int)$user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

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
    <section class="auth" aria-label="Logowanie">

      <?php if (!empty($errors['general'])): ?>
        <div class="auth-card" style="background: rgba(255,255,255,.85); font-weight:700;">
          <?= htmlspecialchars($errors['general']) ?>
        </div>
      <?php endif; ?>

      <form class="auth-card" action="login.php" method="post" novalidate data-auth-form="true">
        <div class="auth-header">LOGOWANIE</div>

        <div class="auth-field <?= isset($errors['username']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="loginUser">NAZWA UŻYTKOWNIKA</label>
          <div class="auth-inputrow">
            <span class="auth-icon" aria-hidden="true">👤</span>
            <input
              class="auth-input"
              id="loginUser"
              name="username"
              type="text"
              autocomplete="username"
              value="<?= htmlspecialchars($old['username']) ?>"
            />
          </div>
          <div class="auth-error" role="alert" aria-live="polite"><?= htmlspecialchars($errors['username'] ?? '') ?></div>
        </div>

        <div class="auth-field <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label class="auth-label" for="loginPass">HASŁO</label>
          <div class="auth-inputrow">
            <span class="auth-icon" aria-hidden="true">🔒</span>
            <input
              class="auth-input"
              id="loginPass"
              name="password"
              type="password"
              autocomplete="current-password"
            />
          </div>
          <div class="auth-error" role="alert" aria-live="polite"><?= htmlspecialchars($errors['password'] ?? '') ?></div>
        </div>

        <div class="auth-actions">
          <button class="auth-btn" type="submit">ZALOGUJ</button>
        </div>
      </form>

      <div class="auth-card auth-card--small">
        <div class="auth-smalltext">NOWY UŻYTKOWNIK?</div>
        <a class="auth-btn auth-btn--link" href="register.php">ZAŁÓŻ KONTO</a>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
  <script defer src="/nti/js/auth.js"></script>
</body>
</html>