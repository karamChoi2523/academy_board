<?php
session_start();
session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => '로그아웃 되었습니다.']);
?>
