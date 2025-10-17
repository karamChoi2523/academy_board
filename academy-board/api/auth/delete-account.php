<?php
require_once '../../config/database.php';

// ✅ 로그인 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    sendResponse(false, '로그인이 필요합니다.');
}

$userId = $_SESSION['user_id'];

try {
    // ✅ 트랜잭션 시작
    $pdo->beginTransaction();

    // ✅ 1️⃣ 프로필 이미지 조회
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $profileImage = $stmt->fetchColumn();

    // ✅ 2️⃣ 사용자가 쓴 게시글의 첨부파일 경로 모두 가져오기
    $fileStmt = $pdo->prepare("SELECT file_path FROM posts WHERE user_id = ? AND file_path IS NOT NULL");
    $fileStmt->execute([$userId]);
    $files = $fileStmt->fetchAll(PDO::FETCH_COLUMN);

    // ✅ 3️⃣ 실제 파일 삭제 (존재할 경우만)
    foreach ($files as $filePath) {
        $fullPath = __DIR__ . '/../../uploads/' . basename($filePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // ✅ 4️⃣ 사용자가 쓴 게시글 삭제
    $deletePosts = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
    $deletePosts->execute([$userId]);

    // ✅ 5️⃣ 프로필 이미지 삭제 (기본 이미지 제외)
    if ($profileImage && $profileImage !== 'default-profile.png') {
        $profilePath = __DIR__ . '/../../uploads/' . $profileImage;
        if (file_exists($profilePath)) {
            unlink($profilePath);
        }
    }

    // ✅ 6️⃣ 회원 삭제
    $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->execute([$userId]);

    // ✅ 7️⃣ 커밋 및 세션 종료
    $pdo->commit();
    session_unset();
    session_destroy();

    sendResponse(true, '회원 탈퇴가 완료되었습니다.');
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("회원 탈퇴 오류: " . $e->getMessage());
    sendResponse(false, '회원 탈퇴 중 오류가 발생했습니다.');
}
?>
