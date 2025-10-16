<?php
require_once '../../config/database.php';

$postId = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$category = $_POST['category'] ?? null;
$existingFile = $_POST['existingFileName'] ?? ''; // 기존 파일명
$uploadDir = '../../uploads/';

if (!$postId || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

// 파일 처리
$newFilePath = $existingFile; // 기본값: 기존 파일 유지
$file = $_FILES['file'] ?? null;

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    // 새로운 파일 업로드 시
    $fileName = basename($file['name']);
    $storedName = time() . '_' . $fileName;
    $filePath = $uploadDir . $storedName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $newFilePath = $storedName; // 새 파일 경로
        // 기존 파일 삭제
        if (!empty($existingFile) && file_exists($uploadDir . $existingFile)) {
            unlink($uploadDir . $existingFile);
        }

        // attachments 테이블 갱신
        $checkQuery = "SELECT id FROM attachments WHERE post_id = :post_id";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['post_id' => $postId]);
        if ($checkStmt->fetch()) {
            // 이미 첨부가 있으면 UPDATE
            $updateAttach = "UPDATE attachments 
                             SET original_name = :original_name, stored_name = :stored_name, 
                                 file_size = :file_size, file_type = :file_type, created_at = NOW()
                             WHERE post_id = :post_id";
            $stmtA = $pdo->prepare($updateAttach);
        } else {
            // 없으면 INSERT
            $updateAttach = "INSERT INTO attachments (post_id, original_name, stored_name, file_size, file_type)
                             VALUES (:post_id, :original_name, :stored_name, :file_size, :file_type)";
            $stmtA = $pdo->prepare($updateAttach);
        }
        $stmtA->execute([
            'post_id' => $postId,
            'original_name' => $fileName,
            'stored_name' => $storedName,
            'file_size' => $file['size'],
            'file_type' => $file['type']
        ]);
    }
} elseif (empty($existingFile)) {
    // 기존 파일 삭제 요청이 있었다면 삭제
    $delQuery = "DELETE FROM attachments WHERE post_id = :post_id";
    $delStmt = $pdo->prepare($delQuery);
    $delStmt->execute(['post_id' => $postId]);
}

// posts 내용 수정
$query = "UPDATE posts 
          SET title = :title, content = :content, category = :category, updated_at = NOW() 
          WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'id' => $postId,
    'title' => $title,
    'content' => $content,
    'category' => $category
]);

echo json_encode(['success' => true, 'message' => '게시물이 수정되었습니다.']);
?>
