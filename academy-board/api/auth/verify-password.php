<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

// 로그인 확인
if (!isLoggedIn()) {
    http_response_code(401);
    sendResponse(false, '로그인이 필요합니다.');
}

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

if (empty($password)) {
    sendResponse(false, '비밀번호를 입력해주세요.');
}

$userId = $_SESSION['user_id'];

// 사용자 조회
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    sendResponse(false, '사용자를 찾을 수 없습니다.');
}

// 비밀번호 검증
if (!password_verify($password, $user['password'])) {
    sendResponse(false, '비밀번호가 일치하지 않습니다.');
}

sendResponse(true, '비밀번호 확인 완료');
?>