<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
  $boardType = $_GET['type'] ?? 'notice';

  // posts와 users 테이블 조인
  $sql = "SELECT 
            p.id, 
            p.title, 
            p.created_at,
	    p.category,
            u.nickname AS author_nickname
          FROM posts p
          LEFT JOIN users u ON p.user_id = u.id
          WHERE p.board_type = :board_type 
          ORDER BY p.created_at DESC";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['board_type' => $boardType]);

  $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($posts ?: []);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>
