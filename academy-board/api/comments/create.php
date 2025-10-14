<?php
require_once '../../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$data = json_decode(file_get_contents('php://input'), true);

$postId = intval($data['post_id'] ?? 0);
$content = trim($data['content'] ?? '');

if ($postId <= 0) {
    sendResponse(false, '유효하지 않은 게시글 ID입니다.');
}

if (empty($content)) {
    sendResponse(false, '댓글 내용을 입력해주세요.');
}

// 게시글 존재 확인
$stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    sendResponse(false, '게시글을 찾을 수 없습니다.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO comments (post_id, user_id, content) 
        VALUES (?, ?, ?)
        RETURNING id
    ");
    $stmt->execute([$postId, $_SESSION['user_id'], $content]);
    $commentId = $stmt->fetch()['id'];
    
    sendResponse(true, '댓글이 작성되었습니다.', ['comment_id' => $commentId]);
    
} catch (PDOException $e) {
    http_response_code(500);
    sendResponse(false, '댓글 작성 중 오류가 발생했습니다.');
}
?>