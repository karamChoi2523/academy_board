<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

// 로그인 확인
requireLogin();

$data = json_decode(file_get_contents('php://input'), true);

$nickname = trim($data['nickname'] ?? '');
$name = trim($data['name'] ?? '');
$password = $data['password'] ?? null;

// 유효성 검사
if (empty($nickname)) {
    sendResponse(false, '닉네임을 입력해주세요.');
}

if (empty($name)) {
    sendResponse(false, '이름을 입력해주세요.');
}

if (strlen($nickname) < 2) {
    sendResponse(false, '닉네임은 최소 2자 이상이어야 합니다.');
}

if (strlen($nickname) > 20) {
    sendResponse(false, '닉네임은 20자 이하여야 합니다.');
}

// 닉네임 중복 확인 (자신의 닉네임은 제외)
$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = ? AND id != ?");
$stmt->execute([$nickname, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    sendResponse(false, '이미 사용 중인 닉네임입니다.');
}

$userId = $_SESSION['user_id'];

// 업데이트할 필드 준비
if ($password) {
    // 비밀번호도 변경
    if (strlen($password) < 6) {
        sendResponse(false, '비밀번호는 최소 6자 이상이어야 합니다.');
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET nickname = ?, name = ?, password = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$nickname, $name, $hashedPassword, $userId]);
} else {
    // 비밀번호 없이 닉네임, 이름만 변경
    $stmt = $pdo->prepare("
        UPDATE users 
        SET nickname = ?, name = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$nickname, $name, $userId]);
}

// 세션 업데이트
$_SESSION['nickname'] = $nickname;

sendResponse(true, '회원 정보가 수정되었습니다.', [
    'user' => [
        'nickname' => $nickname,
        'name' => $name
    ]
]);
?>