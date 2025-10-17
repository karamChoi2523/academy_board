<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_GET['id'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// posts와 users 테이블을 조인하여 author_nickname 가져오기
// 첨부파일 정보도 가져오기 위해 attachments 테이블과 조인
$query = "
    SELECT 
        p.*, 
        u.nickname AS author_nickname, 
        a.id AS attachment_id, 
        a.original_name AS attachment_name, 
        a.stored_name AS attachment_path
    FROM posts p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN attachments a ON p.id = a.post_id
    WHERE p.id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);
$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($post);
?>
