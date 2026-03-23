<?php
session_start();
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Nieprawidłowe żądanie']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Musisz być zalogowany, aby lajkować.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$commentId = (int)($data['comment_id'] ?? 0);

if ($commentId <= 0) {
    echo json_encode(['error' => 'Brak ID komentarza']);
    exit;
}

// Sprawdzamy, czy użytkownik już polajkował ten komentarz
$stmt = $pdo->prepare("SELECT * FROM forum_comment_likes WHERE user_id = ? AND comment_id = ?");
$stmt->execute([$userId, $commentId]);
$liked = $stmt->fetch();

if ($liked) {
    // Cofnięcie lajka (Unlike)
    $pdo->prepare("DELETE FROM forum_comment_likes WHERE user_id = ? AND comment_id = ?")->execute([$userId, $commentId]);
    $pdo->prepare("UPDATE forum_comments SET likes = likes - 1 WHERE comment_id = ?")->execute([$commentId]);
    $action = 'unliked';
} else {
    // Dodanie lajka (Like)
    $pdo->prepare("INSERT INTO forum_comment_likes (user_id, comment_id) VALUES (?, ?)")->execute([$userId, $commentId]);
    $pdo->prepare("UPDATE forum_comments SET likes = likes + 1 WHERE comment_id = ?")->execute([$commentId]);
    $action = 'liked';
}

// Pobranie nowej liczby lajków do wyświetlenia
$stmt = $pdo->prepare("SELECT likes FROM forum_comments WHERE comment_id = ?");
$stmt->execute([$commentId]);
$newLikes = $stmt->fetchColumn();

echo json_encode(['success' => true, 'action' => $action, 'likes' => $newLikes]);