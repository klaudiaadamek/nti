<?php
session_start();
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// POBIERANIE KOMENTARZY (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $postId = (int)($_GET['post_id'] ?? 0);
    $userId = $_SESSION['user_id'] ?? 0;

    if ($postId <= 0) {
        echo json_encode(['error' => 'Brak ID posta']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT c.comment_id, c.content, c.created_at, c.likes, u.username,
               (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id AND cl.user_id = ?) AS user_liked
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$userId, $postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
    exit;
}

// DODAWANIE KOMENTARZA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Musisz być zalogowany, aby dodać komentarz.']);
        exit;
    }

    // Obsługa formatu JSON wysyłanego z nowego devlog.js
    $data = json_decode(file_get_contents('php://input'), true);
    $postId = (int)($data['post_id'] ?? $_POST['post_id'] ?? 0);
    $content = trim($data['content'] ?? $_POST['content'] ?? '');
    $userId = (int)$_SESSION['user_id'];

    if ($postId <= 0 || $content === '') {
        echo json_encode(['error' => 'Treść komentarza nie może być pusta.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    if ($stmt->execute([$postId, $userId, $content])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Wystąpił błąd podczas zapisywania komentarza.']);
    }
    exit;
}