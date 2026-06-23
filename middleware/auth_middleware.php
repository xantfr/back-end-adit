<?php
require_once __DIR__ . '/../helpers/jwt_helper.php';

function requireAuth(): array {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $auth, $m)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit();
    }
    $payload = JWT::decode($m[1]);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid atau kadaluarsa']);
        exit();
    }
    return $payload;
}
