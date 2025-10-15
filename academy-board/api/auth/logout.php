<?php
session_start(); // 🔥 이게 핵심!
header('Content-Type: application/json; charset=utf-8');

// 세션 변수 모두 제거
$_SESSION = array();

// 세션 쿠키도 삭제 (선택사항이지만 권장)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 세션 파괴
session_destroy();

echo json_encode([
    'success' => true, 
    'message' => '로그아웃 되었습니다.'
]);
?>