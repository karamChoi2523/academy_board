<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// 댓글 데이터 받기 (JSON 형식)
$data = json_decode(file_get_contents("php://input"), true);

$postId = $data['post_id'] ?? null;
$userId = $data['user_id'] ?? null;
$content = $data['content'] ?? '';

if (!$postId || !$userId || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

// 댓글 저장 쿼리
$query = "INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':content', $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '댓글이 추가되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '댓글 추가에 실패했습니다.']);
}
?>
