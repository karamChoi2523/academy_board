<?php
// 데이터베이스 연결
require_once '../../config/database.php';

// 게시판 타입 (예: notice, qna, homework)
$type = $_GET['type'] ?? 'notice'; 

$query = "SELECT * FROM posts WHERE board_type = :type ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':type', $type);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($posts);
?>
