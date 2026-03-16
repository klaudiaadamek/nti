<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../includes/db.php';

function jsonOut(array $data, int $code = 200): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $postId = (int)($_GET['post_id'] ?? 0);
  if ($postId <= 0) jsonOut(['ok' => false, 'error' => 'Brak post_id'], 400);

  $stmt = $pdo->prepare("
    SELECT c.comment_id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON u.user_id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC, c.comment_id ASC
  ");
  $stmt->execute([$postId]);

  jsonOut(['ok' => true, 'comments' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
  if (empty($_SESSION['user_id'])) {
    jsonOut(['ok' => false, 'error' => 'Musisz być zalogowana, aby dodać komentarz.'], 401);
  }

  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) $data = [];

  $postId = (int)($data['post_id'] ?? 0);
  $content = trim((string)($data['content'] ?? ''));

  if ($postId <= 0) jsonOut(['ok' => false, 'error' => 'Brak post_id'], 400);
  if ($content === '') jsonOut(['ok' => false, 'error' => 'Treść komentarza jest pusta.'], 400);

  try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, (int)$_SESSION['user_id'], $content]);
  } catch (Throwable $e) {
    jsonOut(['ok' => false, 'error' => 'Błąd zapisu do bazy.'], 500);
  }

  jsonOut(['ok' => true]);
}

jsonOut(['ok' => false, 'error' => 'Method not allowed'], 405);