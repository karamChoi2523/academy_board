<?php
// 데이터베이스 연결
require_once '../../config/database.php';

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => '게시물 ID가 필요합니다.']);
    exit;
}

// 댓글 목록 가져오기
$query = "SELECT c.*, u.nickname AS author_nickname
          FROM comments c
          LEFT JOIN users u ON c.user_id = u.id
          WHERE c.post_id = :post_id
          ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->execute();

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 로그인된 사용자 ID 확인
$userId = $_SESSION['user_id'] ?? null;

foreach ($comments as &$comment) {
    // 댓글 작성자와 로그인한 사용자가 일치하면 삭제 버튼을 표시할 수 있도록 'isAuthor' 추가
    $comment['isAuthor'] = ($comment['user_id'] == $userId);
}

echo json_encode($comments);
?>
