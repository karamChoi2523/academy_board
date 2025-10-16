<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    echo json_encode(['error' => '게시물 ID가 필요합니다.']);
    exit;
}

// 해당 게시물의 댓글 조회
$query = "SELECT c.id, c.content, c.created_at, u.nickname AS author_nickname
          FROM comments c
          JOIN users u ON c.user_id = u.id
          WHERE c.post_id = :post_id
          ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->execute();

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($comments ?: []);
?>
