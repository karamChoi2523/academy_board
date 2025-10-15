<?php
session_start();
session_unset();
session_destroy();

setcookie(session_name(), '', time() - 3600, '/'); // 세션 쿠키 삭제
echo json_encode(['success' => true, 'message' => '로그아웃 되었습니다.']);
?>
