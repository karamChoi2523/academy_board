<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (!$postId || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

$query = "UPDATE posts SET title = :title, content = :content, updated_at = NOW() WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':content', $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '게시물이 수정되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 수정에 실패했습니다.']);
}
?>
