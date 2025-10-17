<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// 데이터베이스 연결
require_once '../../config/database.php';

// 🔐 세션 기반 로그인 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // ✅ 사용자 정보 조회 쿼리
    $query = "
        SELECT 
            id, 
            email, 
            nickname, 
            role, 
            profile_image, 
            TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') AS created_at
        FROM users
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '사용자 정보를 찾을 수 없습니다.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 오류: ' . $e->getMessage()
    ]);
}
?>
