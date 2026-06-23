<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class CategoryController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Hanya tampilkan category milik user yang login
    public function index(int $userId) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id_user = ? ORDER BY nama_category ASC");
        $stmt->execute([$userId]);
        respond(true, 'OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Simpan id_user saat membuat category
    public function create(int $userId) {
        $data = getBody();
        $err  = validateRequired($data, ['nama_category', 'warna']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare("INSERT INTO categories (nama_category, warna, icon, id_user) VALUES (?, ?, ?, ?)");
        $stmt->execute([trim($data['nama_category']), $data['warna'], $data['icon'] ?? null, $userId]);
        $id   = $this->db->lastInsertId();
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id_category = ?");
        $stmt->execute([$id]);
        respond(true, 'Kategori berhasil dibuat', $stmt->fetch(PDO::FETCH_ASSOC), 201);
    }

    // Pastikan category yang diupdate milik user yang login
    public function update(int $id, int $userId) {
        $stmt = $this->db->prepare("SELECT id_category FROM categories WHERE id_category = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) respond(false, 'Kategori tidak ditemukan', null, 404);

        $data    = getBody();
        $fields  = [];
        $params  = [];
        foreach (['nama_category','warna','icon'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) respond(false, 'Tidak ada data', null, 400);
        $params[] = $id;
        $params[] = $userId;
        $this->db->prepare("UPDATE categories SET " . implode(', ', $fields) . " WHERE id_category = ? AND id_user = ?")->execute($params);

        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id_category = ?");
        $stmt->execute([$id]);
        respond(true, 'Kategori berhasil diupdate', $stmt->fetch(PDO::FETCH_ASSOC));
    }

    // Pastikan category yang dihapus milik user yang login
    public function delete(int $id, int $userId) {
        $stmt = $this->db->prepare("SELECT id_category FROM categories WHERE id_category = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) respond(false, 'Kategori tidak ditemukan', null, 404);

        $this->db->prepare("DELETE FROM categories WHERE id_category = ? AND id_user = ?")->execute([$id, $userId]);
        respond(true, 'Kategori berhasil dihapus');
    }
}
