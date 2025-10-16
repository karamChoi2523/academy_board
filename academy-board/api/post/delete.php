<?php
require_once '../../config/database.php';

// JSON 데이터 받기
$data = json_decode(file_get_contents("php://input"), true);
$postId = $data['id'] ?? null;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => '게시물 ID가 필요합니다.']);
    exit;
}

try {
    // 트랜잭션 시작
    $pdo->beginTransaction();

    // 1️⃣ 첨부파일 정보 가져오기
    $stmt = $pdo->prepare("SELECT stored_name FROM attachments WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2️⃣ 실제 파일 삭제
    $uploadDir = __DIR__ . '/../../uploads/';
    foreach ($attachments as $file) {
        $filePath = $uploadDir . $file['stored_name'];
        if (file_exists($filePath)) {
            unlink($filePath); // 실제 파일 삭제
        }
    }

    // 3️⃣ 게시글 삭제 (CASCADE로 attachments 데이터 자동 삭제)
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $postId);
    $stmt->execute();

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => '게시물과 첨부파일이 삭제되었습니다.']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => '삭제 중 오류 발생: ' . $e->getMessage()]);
}
?>
