<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_GET['id'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

$query = "SELECT * FROM posts WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);
$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($post);
?>
