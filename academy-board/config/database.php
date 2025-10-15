<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ✅ 1️⃣ .env 파일 로드
$envPath = __DIR__ . '/../.env'; // 프로젝트 루트 기준
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // 주석 무시
        if (!str_contains($line, '=')) continue; // 잘못된 라인 무시
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '.env 파일을 찾을 수 없습니다.']);
    exit;
}

// ✅ 2️⃣ 환경 변수로부터 DB 설정 불러오기
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'academy_board');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: 5432);

// ✅ 업로드 경로 설정 (.env에 있으면 우선 사용)
define('UPLOAD_DIR', getenv('UPLOAD_DIR') ?: (__DIR__ . '/../uploads/'));
define('PROFILE_DIR', UPLOAD_DIR . 'profiles/');
define('ATTACHMENT_DIR', UPLOAD_DIR . 'attachments/');

// ✅ 파일 업로드 관련 설정
define('MAX_FILE_SIZE', 10 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'hwp']);

// ✅ 3️⃣ DB 연결
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패: ' . $e->getMessage()]);
    exit;
}

// ✅ 유틸리티 함수
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        sendResponse(false, '로그인이 필요합니다.');
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        http_response_code(403);
        sendResponse(false, '권한이 없습니다.');
    }
}

function sanitizeFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return time() . '_' . $filename;
}

function validateFileUpload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 실패'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => '파일 크기는 10MB 이하여야 합니다.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
    }

    return ['success' => true];
}
?>
