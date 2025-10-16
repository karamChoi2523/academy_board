<?php
require_once '../../config/database.php';

// POST 데이터는 multipart/form-data 형식으로 전송됨
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
$filePath = null;

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($file['name']);
    $storedName = time() . '_' . $fileName; // 중복 방지
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

// DB 저장
$query = "INSERT INTO posts (category, title, content, board_type, user_id, created_at, file_path)
          VALUES (:category, :title, :content, :board_type, :user_id, NOW(), :file_path)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':content', $content);
$stmt->bindParam(':board_type', $board_type);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':category', $category);
$stmt->bindParam(':file_path', $filePath);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '게시물이 생성되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 생성에 실패했습니다.']);
}
?>
