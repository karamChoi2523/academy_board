<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$newPassword = trim($data['new_password'] ?? '');

if (empty($email) || empty($newPassword)) {
    sendResponse(false, '이메일과 새 비밀번호를 입력해주세요.');
}

if (strlen($newPassword) < 6) {
    sendResponse(false, '비밀번호는 6자 이상이어야 합니다.');
}

try {
    // 이메일 확인
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        sendResponse(false, '해당 이메일로 가입된 사용자가 없습니다.');
    }

    // 비밀번호 해싱 후 업데이트
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$hashed, $user['id']]);

    sendResponse(true, '비밀번호가 성공적으로 변경되었습니다.');
} catch (PDOException $e) {
    sendResponse(false, '서버 오류가 발생했습니다.');
}
?>
