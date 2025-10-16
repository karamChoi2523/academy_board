<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// 데이터 가져오기
$data = json_decode(file_get_contents("php://input"), true);
$commentId = $data['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;  // 로그인된 사용자 ID

if (!$commentId || !$userId) {
    echo json_encode(['success' => false, 'message' => '댓글 ID 또는 사용자 정보가 없습니다.']);
    exit;
}

// 댓글 정보 가져오기
$query = "SELECT * FROM comments WHERE id = :commentId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':commentId', $commentId);
$stmt->execute();
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    echo json_encode(['success' => false, 'message' => '댓글을 찾을 수 없습니다.']);
    exit;
}

// 댓글 작성자가 로그인한 사용자와 동일한지 확인
if ($comment['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => '댓글 삭제 권한이 없습니다.']);
    exit;
}

// 댓글 삭제
$query = "DELETE FROM comments WHERE id = :commentId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':commentId', $commentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '댓글이 삭제되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '댓글 삭제에 실패했습니다.']);
}
?>
