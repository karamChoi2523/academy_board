<?php
require_once '../../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, '잘못된 요청 방식입니다.');
}

$boardType = $_POST['board_type'] ?? '';
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category = $_POST['category'] ?? null;

// 유효성 검사
if (empty($boardType) || empty($title) || empty($content)) {
    sendResponse(false, '제목과 내용을 입력해주세요.');
}

// 게시판 타입 확인
$stmt = $pdo->prepare("SELECT id FROM board_types WHERE code = ?");
$stmt->execute([$boardType]);
$boardTypeData = $stmt->fetch();

if (!$boardTypeData) {
    sendResponse(false, '유효하지 않은 게시판입니다.');
}

$boardTypeId = $boardTypeData['id'];

// 권한 확인 (공지사항은 선생님만)
if ($boardType === 'notice' && getUserRole() !== 'teacher') {
    http_response_code(403);
    sendResponse(false, '공지사항은 선생님만 작성할 수 있습니다.');
}

// 과제 게시판 카테고리 검증
if ($boardType === 'assignment') {
    if (getUserRole() === 'teacher' && $category !== '과제') {
        sendResponse(false, '선생님은 [과제] 카테고리를 선택해야 합니다.');
    }
    if (getUserRole() === 'student' && $category !== '제출') {
        sendResponse(false, '학생은 [제출] 카테고리를 선택해야 합니다.');
    }
}

try {
    $pdo->beginTransaction();
    
    // 게시글 생성
    $stmt = $pdo->prepare("
        INSERT INTO posts (board_type_id, user_id, title, content, category) 
        VALUES (?, ?, ?, ?, ?)
        RETURNING id
    ");
    $stmt->execute([$boardTypeId, $_SESSION['user_id'], $title, $content, $category]);
    $postId = $stmt->fetch()['id'];
    
    // 파일 업로드 처리
    if (isset($_FILES['attachments'])) {
        $files = $_FILES['attachments'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $validation = validateFileUpload($file);
                if (!$validation['success']) {
                    $pdo->rollBack();
                    sendResponse(false, $validation['message']);
                }
                
                $originalName = $file['name'];
                $storedName = sanitizeFileName($originalName);
                $uploadPath = ATTACHMENT_DIR . $storedName;
                
                if (!is_dir(ATTACHMENT_DIR)) {
                    mkdir(ATTACHMENT_DIR, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO attachments (post_id, original_name, stored_name, file_size, file_type) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$postId, $originalName, $storedName, $file['size'], $file['type']]);
                }
            }
        }
    }
    
    $pdo->commit();
    sendResponse(true, '게시글이 작성되었습니다.', ['post_id' => $postId]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    sendResponse(false, '게시글 작성 중 오류가 발생했습니다.');
}
?>