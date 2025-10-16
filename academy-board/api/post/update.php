<?php
require_once '../../config/database.php';

$postId = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$category = $_POST['category'] ?? null;
$existingFile = $_POST['existingFileName'] ?? ''; // 기존 파일명 (빈 문자열이면 삭제 요청)
$uploadDir = '../../uploads/';

if (!$postId || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

$newFilePath = $existingFile; // 기본: 기존 파일 유지
$file = $_FILES['file'] ?? null;

// ✅ 새로운 파일 업로드 시 (교체)
if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($file['name']);
    $storedName = time() . '_' . $fileName;
    $filePath = $uploadDir . $storedName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $newFilePath = $storedName;

        // 기존 파일 삭제
        $stmtOld = $pdo->prepare("SELECT stored_name FROM attachments WHERE post_id = :post_id");
        $stmtOld->execute(['post_id' => $postId]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if ($old && file_exists($uploadDir . $old['stored_name'])) {
            unlink($uploadDir . $old['stored_name']);
        }

        // attachments 테이블 갱신
        $checkQuery = "SELECT id FROM attachments WHERE post_id = :post_id";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['post_id' => $postId]);

        if ($checkStmt->fetch()) {
            $updateAttach = "UPDATE attachments 
                             SET original_name = :original_name, stored_name = :stored_name, 
                                 file_size = :file_size, file_type = :file_type, created_at = NOW()
                             WHERE post_id = :post_id";
            $stmtA = $pdo->prepare($updateAttach);
        } else {
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
}
// ✅ 기존 파일 삭제만 요청된 경우
elseif (empty($existingFile)) {
    // uploads 폴더에서도 삭제
    $stmtOld = $pdo->prepare("SELECT stored_name FROM attachments WHERE post_id = :post_id");
    $stmtOld->execute(['post_id' => $postId]);
    $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
    if ($old && file_exists($uploadDir . $old['stored_name'])) {
        unlink($uploadDir . $old['stored_name']); // 실제 파일 삭제
    }

    // DB에서 첨부 삭제
    $delQuery = "DELETE FROM attachments WHERE post_id = :post_id";
    $delStmt = $pdo->prepare($delQuery);
    $delStmt->execute(['post_id' => $postId]);
}

// ✅ 게시물 내용 수정
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
