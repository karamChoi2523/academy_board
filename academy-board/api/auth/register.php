<?php
require_once '../../config/database.php';

// ✅ 공통 응답 함수
function sendResponse($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

// ✅ form-data로 받은 값 처리
$data = $_POST;

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$nickname = trim($data['nickname'] ?? '');
$role = $data['role'] ?? '';
$file = $_FILES['profile_image'] ?? null;

// ✅ 유효성 검사
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

// ✅ 이메일 중복 확인
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendResponse(false, '이미 사용 중인 이메일입니다.');
}

// ✅ 비밀번호 해싱
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ 프로필 이미지 처리
$uploadDir = '../../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$profileImageName = 'default-profile.png'; // 기본 이미지

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowedExt)) {
        sendResponse(false, '이미지 파일만 업로드 가능합니다.');
    }

    // 중복 방지된 파일명 생성
    $storedName = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadPath = $uploadDir . $storedName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $profileImageName = $storedName;
    } else {
        sendResponse(false, '프로필 이미지 업로드에 실패했습니다.');
    }
}

// ✅ 사용자 등록 (프로필 이미지 포함)
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, nickname, role, profile_image)
        VALUES (?, ?, ?, ?, ?)
        RETURNING id
    ");
    $stmt->execute([$email, $hashedPassword, $nickname, $role, $profileImageName]);
    $userId = $stmt->fetch()['id'];
    
    sendResponse(true, '회원가입이 완료되었습니다.', ['user_id' => $userId]);
} catch (PDOException $e) {
    http_response_code(500);
    sendResponse(false, '회원가입 중 오류가 발생했습니다: ' . $e->getMessage());
}
?>
