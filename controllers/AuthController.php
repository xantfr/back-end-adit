<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/jwt_helper.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function register() {
        $data = getBody();
        $err  = validateRequired($data, ['nama', 'email', 'password']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare("SELECT id_user FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) respond(false, 'Email sudah terdaftar', null, 409);

        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        $stmt->execute([trim($data['nama']), strtolower(trim($data['email'])), $hash]);
        $id = $this->db->lastInsertId();

        $user  = $this->getUser($id);
        $token = JWT::encode(['id_user' => $id, 'exp' => time() + 86400 * 30]);
        respond(true, 'Registrasi berhasil', ['token' => $token, 'user' => $user], 201);
    }

    public function login() {
        $data = getBody();
        $err  = validateRequired($data, ['email', 'password']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([strtolower(trim($data['email']))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            respond(false, 'Email atau password salah', null, 401);
        }

        $token = JWT::encode(['id_user' => $user['id_user'], 'exp' => time() + 86400 * 30]);
        unset($user['password']);
        respond(true, 'Login berhasil', ['token' => $token, 'user' => $user]);
    }

    public function logout() {
        respond(true, 'Logout berhasil');
    }

    private function getUser($id): array {
        $stmt = $this->db->prepare("SELECT id_user, nama, email, foto_profil, created_at FROM users WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
