<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class ProfileController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function get(int $userId) {
        $stmt = $this->db->prepare(
            "SELECT id_user, nama, email, foto_profil, created_at, updated_at FROM users WHERE id_user = ?"
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) respond(false, 'User tidak ditemukan', null, 404);
        respond(true, 'OK', $user);
    }

    public function update(int $userId) {
        $data   = getBody();
        $fields = [];
        $params = [];
        foreach (['nama','email','foto_profil'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) respond(false, 'Tidak ada data', null, 400);

        // Cek email duplikat jika diubah
        if (!empty($data['email'])) {
            $stmt = $this->db->prepare("SELECT id_user FROM users WHERE email = ? AND id_user != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetch()) respond(false, 'Email sudah digunakan', null, 409);
        }

        $params[] = $userId;
        $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id_user = ?")->execute($params);
        $this->get($userId);
    }

    public function changePassword(int $userId) {
        $data = getBody();
        $err  = validateRequired($data, ['old_password', 'new_password']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare("SELECT password FROM users WHERE id_user = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($data['old_password'], $user['password'])) {
            respond(false, 'Password lama tidak sesuai', null, 400);
        }
        if (strlen($data['new_password']) < 6) {
            respond(false, 'Password baru minimal 6 karakter', null, 400);
        }

        $hash = password_hash($data['new_password'], PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?")->execute([$hash, $userId]);
        respond(true, 'Password berhasil diubah');
    }
}
