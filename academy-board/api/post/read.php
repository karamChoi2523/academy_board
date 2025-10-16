<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_GET['id'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// posts와 users 테이블을 조인하여 author_nickname 가져오기
$query = "SELECT p.*, u.nickname AS author_nickname 
          FROM posts p
          LEFT JOIN users u ON p.user_id = u.id
          WHERE p.id = :id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);
$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($post);
?>
