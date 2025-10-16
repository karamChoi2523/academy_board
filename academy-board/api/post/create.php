<?php
require_once '../../config/database.php';

// POST 데이터
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$board_type = $_POST['board_type'] ?? 'notice';
$user_id = $_POST['user_id'] ?? null;
$category = $_POST['category'] ?? null;

// 업로드 폴더 경로
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'] ?? null;
$storedName = null;
$originalName = null;
$fileType = null;
$fileSize = null;

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($file['name']);        // 사용자가 업로드한 원본 이름
    $fileType = $file['type'];                      // MIME 타입
    $fileSize = $file['size'];                      // 파일 크기
    $storedName = time() . '_' . $originalName;     // 서버 저장용 이름 (중복 방지)

    // ✅ 실제 저장 경로는 stored_name 기준으로 생성
    $targetPath = $uploadDir . $storedName;

    // ✅ 실제 uploads 폴더에 stored_name 이름으로 저장
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => false, 'message' => '파일 업로드에 실패했습니다.']);
        exit;
    }
}

// DB에 게시글 저장
$query = "INSERT INTO posts (category, title, content, board_type, user_id, created_at)
          VALUES (:category, :title, :content, :board_type, :user_id, NOW())";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':category', $category);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':content', $content);
$stmt->bindParam(':board_type', $board_type);
$stmt->bindParam(':user_id', $user_id);

if ($stmt->execute()) {
    $postId = $pdo->lastInsertId();

    // ✅ attachments 테이블에도 저장
    if ($storedName) {
        $insertAttachment = "INSERT INTO attachments (post_id, original_name, stored_name, file_size, file_type)
                             VALUES (:post_id, :original_name, :stored_name, :file_size, :file_type)";
        $stmt2 = $pdo->prepare($insertAttachment);
        $stmt2->bindParam(':post_id', $postId);
        $stmt2->bindParam(':original_name', $originalName);
        $stmt2->bindParam(':stored_name', $storedName);
        $stmt2->bindParam(':file_size', $fileSize);
        $stmt2->bindParam(':file_type', $fileType);
        $stmt2->execute();
    }

    echo json_encode(['success' => true, 'message' => '게시물이 작성되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 생성에 실패했습니다.']);
}
?>
