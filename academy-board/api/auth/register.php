<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

//$data = json_decode(file_get_contents('php://input'), true);
$data = $_POST;

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$nickname = trim($data['nickname'] ?? '');
$role = $data['role'] ?? '';

// 유효성 검사
if (empty($email) || empty($password) || empty($nickname) || empty($role)) {
    sendResponse(false, '모든 필드를 입력해주세요.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, '유효한 이메일 주소를 입력해주세요.');
}

if (strlen($password) < 6) {
    sendResponse(false, '비밀번호는 6자 이상이어야 합니다.');
}

if (!in_array($role, ['student', 'teacher'])) {
    sendResponse(false, '유효한 역할을 선택해주세요.');
}

// 이메일 중복 확인
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendResponse(false, '이미 사용 중인 이메일입니다.');
}

// 비밀번호 해싱
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 사용자 등록
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, nickname, role) 
        VALUES (?, ?, ?, ?)
        RETURNING id
    ");
    $stmt->execute([$email, $hashedPassword, $nickname, $role]);
    $userId = $stmt->fetch()['id'];
    
    sendResponse(true, '회원가입이 완료되었습니다.', ['user_id' => $userId]);
} catch (PDOException $e) {
    http_response_code(500);
    sendResponse(false, '회원가입 중 오류가 발생했습니다.');
}
?>
