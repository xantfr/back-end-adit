<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class SubtaskController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function index(int $userId, int $taskId) {
        // Verifikasi task milik user
        $stmt = $this->db->prepare("SELECT id_task FROM tasks WHERE id_task = ? AND id_user = ?");
        $stmt->execute([$taskId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Task tidak ditemukan', null, 404);

        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE id_task = ? ORDER BY created_at ASC");
        $stmt->execute([$taskId]);
        respond(true, 'OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(int $userId, int $taskId) {
        $stmt = $this->db->prepare("SELECT id_task FROM tasks WHERE id_task = ? AND id_user = ?");
        $stmt->execute([$taskId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Task tidak ditemukan', null, 404);

        $data = getBody();
        $err  = validateRequired($data, ['judul']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare("INSERT INTO subtasks (id_user, id_task, judul, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $taskId, trim($data['judul']), $data['status'] ?? 'pending']);
        $id = $this->db->lastInsertId();

        // Update progress task
        $this->updateTaskProgress($taskId);

        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE id_subtask = ?");
        $stmt->execute([$id]);
        respond(true, 'Subtask berhasil dibuat', $stmt->fetch(PDO::FETCH_ASSOC), 201);
    }

    public function update(int $userId, int $subtaskId) {
        $stmt = $this->db->prepare(
            "SELECT s.id_subtask, s.id_task FROM subtasks s
             JOIN tasks t ON s.id_task = t.id_task
             WHERE s.id_subtask = ? AND t.id_user = ?"
        );
        $stmt->execute([$subtaskId, $userId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sub) respond(false, 'Subtask tidak ditemukan', null, 404);

        $data   = getBody();
        $fields = [];
        $params = [];
        foreach (['judul','status'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) respond(false, 'Tidak ada data', null, 400);
        $params[] = $subtaskId;
        $this->db->prepare("UPDATE subtasks SET " . implode(', ', $fields) . " WHERE id_subtask = ?")->execute($params);

        // Update progress task
        $this->updateTaskProgress($sub['id_task']);

        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE id_subtask = ?");
        $stmt->execute([$subtaskId]);
        respond(true, 'Subtask berhasil diupdate', $stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function delete(int $userId, int $subtaskId) {
        $stmt = $this->db->prepare(
            "SELECT s.id_subtask, s.id_task FROM subtasks s
             JOIN tasks t ON s.id_task = t.id_task
             WHERE s.id_subtask = ? AND t.id_user = ?"
        );
        $stmt->execute([$subtaskId, $userId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sub) respond(false, 'Subtask tidak ditemukan', null, 404);

        $this->db->prepare("DELETE FROM subtasks WHERE id_subtask = ?")->execute([$subtaskId]);
        $this->updateTaskProgress($sub['id_task']);
        respond(true, 'Subtask berhasil dihapus');
    }

    private function updateTaskProgress(int $taskId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total, SUM(status='completed') as done FROM subtasks WHERE id_task = ?");
        $stmt->execute([$taskId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $progress = $row['total'] > 0 ? round(($row['done'] / $row['total']) * 100) : 0;
        $this->db->prepare("UPDATE tasks SET progress = ? WHERE id_task = ?")->execute([$progress, $taskId]);
    }
}
