// list.php
<?php
require_once '../../config/database.php';

// board_type을 쿼리 파라미터로 받음
$boardType = $_GET['type'] ?? 'notice'; // 기본값 'notice'

// 게시물 목록을 가져오는 SQL 쿼리
$sql = "SELECT * FROM posts WHERE board_type = :board_type";
$stmt = $pdo->prepare($sql);
$stmt->execute(['board_type' => $boardType]);

// 결과를 JSON 형식으로 반환
$posts = $stmt->fetchAll();
echo json_encode($posts);
?>
