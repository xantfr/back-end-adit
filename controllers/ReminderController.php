<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class ReminderController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // GET /reminder?id_task=X atau semua milik user
    public function index(int $userId) {
        if (!empty($_GET['id_task'])) {
            $stmt = $this->db->prepare(
                "SELECT r.* FROM reminders r
                 JOIN tasks t ON r.id_task = t.id_task
                 WHERE r.id_task = ? AND t.id_user = ?
                 ORDER BY r.reminder_time ASC"
            );
            $stmt->execute([$_GET['id_task'], $userId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT r.* FROM reminders r
                 JOIN tasks t ON r.id_task = t.id_task
                 WHERE t.id_user = ?
                 ORDER BY r.reminder_time ASC"
            );
            $stmt->execute([$userId]);
        }
        respond(true, 'OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(int $userId) {
        $data = getBody();
        $err  = validateRequired($data, ['id_task', 'reminder_time']);
        if ($err) respond(false, $err, null, 400);

        // Pastikan task milik user
        $stmt = $this->db->prepare("SELECT id_task FROM tasks WHERE id_task = ? AND id_user = ?");
        $stmt->execute([$data['id_task'], $userId]);
        if (!$stmt->fetch()) respond(false, 'Task tidak ditemukan', null, 404);

        $stmt = $this->db->prepare("INSERT INTO reminders (id_task, reminder_time, is_active) VALUES (?, ?, ?)");
        $stmt->execute([$data['id_task'], $data['reminder_time'], $data['is_active'] ?? 1]);
        $id = $this->db->lastInsertId();

        $stmt = $this->db->prepare("SELECT * FROM reminders WHERE id_reminder = ?");
        $stmt->execute([$id]);
        respond(true, 'Reminder berhasil dibuat', $stmt->fetch(PDO::FETCH_ASSOC), 201);
    }

    public function update(int $userId, int $reminderId) {
        $stmt = $this->db->prepare(
            "SELECT r.id_reminder FROM reminders r
             JOIN tasks t ON r.id_task = t.id_task
             WHERE r.id_reminder = ? AND t.id_user = ?"
        );
        $stmt->execute([$reminderId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Reminder tidak ditemukan', null, 404);

        $data   = getBody();
        $fields = [];
        $params = [];
        foreach (['reminder_time','is_active'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) respond(false, 'Tidak ada data', null, 400);
        $params[] = $reminderId;
        $this->db->prepare("UPDATE reminders SET " . implode(', ', $fields) . " WHERE id_reminder = ?")->execute($params);

        $stmt = $this->db->prepare("SELECT * FROM reminders WHERE id_reminder = ?");
        $stmt->execute([$reminderId]);
        respond(true, 'Reminder berhasil diupdate', $stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function delete(int $userId, int $reminderId) {
        $stmt = $this->db->prepare(
            "SELECT r.id_reminder FROM reminders r
             JOIN tasks t ON r.id_task = t.id_task
             WHERE r.id_reminder = ? AND t.id_user = ?"
        );
        $stmt->execute([$reminderId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Reminder tidak ditemukan', null, 404);

        $this->db->prepare("DELETE FROM reminders WHERE id_reminder = ?")->execute([$reminderId]);
        respond(true, 'Reminder berhasil dihapus');
    }
}
