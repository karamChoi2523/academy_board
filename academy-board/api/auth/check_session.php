<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'nickname' => $_SESSION['nickname'],
            'role' => $_SESSION['role'],
            'profile_image' => $_SESSION['profile_image']
        ]
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
