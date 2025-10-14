<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    sendResponse(false, '이메일과 비밀번호를 입력해주세요.');
}

// 사용자 조회
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    sendResponse(false, '이메일 또는 비밀번호가 올바르지 않습니다.');
}

// 세션 설정
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['nickname'] = $user['nickname'];
$_SESSION['role'] = $user['role'];
$_SESSION['profile_image'] = $user['profile_image'];

sendResponse(true, '로그인 성공', [
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'nickname' => $user['nickname'],
        'role' => $user['role'],
        'profile_image' => $user['profile_image']
    ]
]);
?>