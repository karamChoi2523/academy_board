<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// POST 데이터 처리 (폼 데이터)
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$board_type = $_POST['board_type'] ?? 'notice';
$user_id = $_POST['user_id'] ?? null; // 게시글 작성자 (로그인된 사용자 ID)

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '제목과 내용을 입력해주세요.']);
    exit;
}

$query = "INSERT INTO posts (title, content, board_type, user_id, created_at) VALUES (:title, :content, :board_type, :user_id, NOW())";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':content', $content);
$stmt->bindParam(':board_type', $board_type);
$stmt->bindParam(':user_id', $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '게시물이 생성되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 생성에 실패했습니다.']);
}
?>
