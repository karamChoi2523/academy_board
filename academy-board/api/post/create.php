<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// POST 데이터가 JSON 형식으로 들어올 경우 php://input을 사용하여 파싱
$data = json_decode(file_get_contents('php://input'), true);

// 폼 데이터 처리 (제목과 내용)
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$board_type = $data['board_type'] ?? 'notice';  // 기본값을 'notice'로 설정
$user_id = $data['user_id'] ?? null; // 게시글 작성자 (로그인된 사용자 ID)

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
