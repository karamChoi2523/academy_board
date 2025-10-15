<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_POST['id'] ?? null;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => '게시물 ID가 필요합니다.']);
    exit;
}

$query = "DELETE FROM posts WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '게시물이 삭제되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 삭제에 실패했습니다.']);
}
?>
