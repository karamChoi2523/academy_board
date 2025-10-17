<?php
require_once '../../config/database.php';

// ✅ 로그인 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    sendResponse(false, '로그인이 필요합니다.');
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nickname = trim($_POST['nickname'] ?? '');
    $password = $_POST['password'] ?? null;
    $file = $_FILES['profile_image'] ?? null;

    // ✅ 유효성 검사
    if (empty($nickname)) {
        sendResponse(false, '닉네임을 입력해주세요.');
    }
    if (strlen($nickname) < 2 || strlen($nickname) > 20) {
        sendResponse(false, '닉네임은 2~20자 이내로 입력해주세요.');
    }

    // ✅ 닉네임 중복 확인
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = ? AND id != ?");
    $stmt->execute([$nickname, $userId]);
    if ($stmt->fetch()) {
        sendResponse(false, '이미 사용 중인 닉네임입니다.');
    }

    // ✅ 기존 프로필 이미지 이름 조회
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldImage = $stmt->fetchColumn();

    // ✅ 업로드 폴더 설정
    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $profileImageName = $oldImage; // 기본적으로 기존 이미지 유지

    // ✅ 새 프로필 이미지 업로드
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            sendResponse(false, '이미지 파일만 업로드할 수 있습니다.');
        }

        $newName = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadPath = $uploadDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            sendResponse(false, '프로필 이미지 업로드에 실패했습니다.');
        }

        // ✅ 기존 이미지 삭제 (기본 이미지는 삭제하지 않음)
        if ($oldImage && $oldImage !== 'default-profile.png') {
            $oldPath = $uploadDir . $oldImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $profileImageName = $newName;
    }

    // ✅ 비밀번호 변경 포함 업데이트 쿼리 구성
    $query = "UPDATE users SET nickname = :nickname";
    $params = [':nickname' => $nickname];

    if ($password && strlen($password) >= 6) {
        $query .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($profileImageName) {
        $query .= ", profile_image = :profile_image";
        $params[':profile_image'] = $profileImageName;
    }

    $query .= " WHERE id = :id";
    $params[':id'] = $userId;

    // ✅ DB 업데이트 실행
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // ✅ 세션 닉네임 갱신
    $_SESSION['nickname'] = $nickname;

    sendResponse(true, '회원 정보가 수정되었습니다.', [
        'nickname' => $nickname,
        'profile_image' => $profileImageName
    ]);
}

sendResponse(false, '잘못된 요청 방식입니다.');
?>
