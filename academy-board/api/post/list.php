<?php
//header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
  $boardType = $_GET['type'] ?? 'notice';

  $sql = "SELECT id, title, created_at FROM posts WHERE board_type = :board_type ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['board_type' => $boardType]);

  $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($posts ?: []);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>