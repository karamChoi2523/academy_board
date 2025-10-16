<?php
require_once '../../config/database.php';

// POST 데이터 (multipart/form-data)
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$board_type = $_POST['board_type'] ?? 'notice';
$user_id = $_POST['user_id'] ?? null;
$category = $_POST['category'] ?? null;

// 파일 업로드 처리
$uploadDir = '../../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'] ?? null;
$storedName = null;
$originalName = null;
$fileType = null;
$fileSize = null;

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($file['name']);
    $storedName = time() . '_' . $originalName; // 중복 방지
    $fileType = $file['type'] ?? '';
    $fileSize = $file['size'] ?? 0;

    $filePath = $uploadDir . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => '파일 업로드에 실패했습니다.']);
        exit;
    }
}

// 필수값 확인
if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '제목과 내용을 입력해주세요.']);
    exit;
}

// ✅ posts 테이블에 저장
$query = "INSERT INTO posts (category, title, content, board_type, user_id, created_at)
          VALUES (:category, :title, :content, :board_type, :user_id, NOW())";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':content', $content);
$stmt->bindParam(':board_type', $board_type);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':category', $category);

if ($stmt->execute()) {
    $postId = $pdo->lastInsertId(); // 방금 저장된 게시물 ID 가져오기

    // ✅ 첨부파일이 있으면 attachments 테이블에도 저장
    if ($storedName) {
        $fileInsert = "INSERT INTO attachments (post_id, original_name, stored_name, file_size, file_type)
                       VALUES (:post_id, :original_name, :stored_name, :file_size, :file_type)";
        $stmtFile = $pdo->prepare($fileInsert);
        $stmtFile->bindParam(':post_id', $postId);
        $stmtFile->bindParam(':original_name', $originalName);
        $stmtFile->bindParam(':stored_name', $storedName);
        $stmtFile->bindParam(':file_size', $fileSize);
        $stmtFile->bindParam(':file_type', $fileType);
        $stmtFile->execute();
    }

    echo json_encode(['success' => true, 'message' => '게시물이 생성되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 생성에 실패했습니다.']);
}
?>
