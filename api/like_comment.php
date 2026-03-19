<?php
session_start();
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Nieprawidłowa metoda.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$comment_id = $input['comment_id'] ?? null;
$action = $input['action'] ?? 'like';

if (!$comment_id) {
    echo json_encode(['ok' => false, 'error' => 'Brak ID komentarza.']);
    exit;
}

try {
    if ($action === 'unlike') {
        $stmt = $pdo->prepare("UPDATE comments SET likes = GREATEST(0, likes - 1) WHERE comment_id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE comments SET likes = likes + 1 WHERE comment_id = ?");
    }
    $stmt->execute([$comment_id]);

    $stmt2 = $pdo->prepare("SELECT likes FROM comments WHERE comment_id = ?");
    $stmt2->execute([$comment_id]);
    $new_likes = $stmt2->fetchColumn();

    echo json_encode(['ok' => true, 'likes' => (int)$new_likes]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Błąd bazy danych.']);
}