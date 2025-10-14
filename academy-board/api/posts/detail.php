<?php
require_once '../../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$postId = intval($_GET['id'] ?? 0);

if ($postId <= 0) {
    sendResponse(false, '유효하지 않은 게시글 ID입니다.');
}

try {
    // 조회수 증가
    $stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$postId]);
    
    // 게시글 조회
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.nickname as author,
            u.role as author_role,
            u.profile_image as author_profile,
            bt.name as board_name,
            bt.code as board_code
        FROM posts p
        JOIN users u ON p.user_id = u.id
        JOIN board_types bt ON p.board_type_id = bt.id
        WHERE p.id = ?
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        sendResponse(false, '게시글을 찾을 수 없습니다.');
    }
    
    // 첨부파일 조회
    $stmt = $pdo->prepare("SELECT * FROM attachments WHERE post_id = ?");
    $stmt->execute([$postId]);
    $attachments = $stmt->fetchAll();
    
    // 댓글 조회
    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            u.nickname as author,
            u.role as author_role,
            u.profile_image as author_profile
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll();
    
    sendResponse(true, '게시글 조회 성공', [
        'post' => $post,
        'attachments' => $attachments,
        'comments' => $comments
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    sendResponse(false, '게시글 조회 중 오류가 발생했습니다.');
}
?>