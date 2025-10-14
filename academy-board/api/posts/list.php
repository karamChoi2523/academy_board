<?php
require_once '../../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$boardType = $_GET['board_type'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

if (empty($boardType)) {
    sendResponse(false, '게시판 타입을 지정해주세요.');
}

// 게시판 타입 확인
$stmt = $pdo->prepare("SELECT id FROM board_types WHERE code = ?");
$stmt->execute([$boardType]);
$boardTypeData = $stmt->fetch();

if (!$boardTypeData) {
    sendResponse(false, '유효하지 않은 게시판입니다.');
}

$boardTypeId = $boardTypeData['id'];

// 전체 게시글 수 조회
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE board_type_id = ?");
$stmt->execute([$boardTypeId]);
$totalPosts = $stmt->fetch()['total'];

// 게시글 목록 조회
$stmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.title, 
        p.content,
        p.category,
        p.views, 
        p.created_at,
        u.nickname as author,
        u.role as author_role,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) as comment_count,
        (SELECT COUNT(*) FROM attachments a WHERE a.post_id = p.id) as attachment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.board_type_id = ?
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$boardTypeId, $limit, $offset]);
$posts = $stmt->fetchAll();

sendResponse(true, '게시글 목록 조회 성공', [
    'posts' => $posts,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => ceil($totalPosts / $limit),
        'total_posts' => $totalPosts,
        'per_page' => $limit
    ]
]);
?>