<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// JSON 데이터 받기
$data = json_decode(file_get_contents("php://input"), true);

// 게시물 ID 가져오기
$postId = $data['id'] ?? null;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => '게시물 ID가 필요합니다.']);
    exit;
}

// 게시물 삭제 쿼리
$query = "DELETE FROM posts WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $postId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '게시물이 삭제되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 삭제에 실패했습니다.']);
}
?>
