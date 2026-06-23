<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/validation_helper.php';
require_once __DIR__ . '/../config/response.php';

class TaskController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function index(int $userId) {
        // Auto-update overdue status
        $this->db->prepare(
            "UPDATE tasks SET status = 'overdue'
             WHERE id_user = ? AND deadline < NOW()
             AND status NOT IN ('completed','overdue')"
        )->execute([$userId]);

        $filters = [];
        $params  = [$userId];
        $sql     = "SELECT t.*, c.nama_category, c.warna, c.icon
                    FROM tasks t
                    LEFT JOIN categories c ON t.id_category = c.id_category
                    WHERE t.id_user = ?";

        if (!empty($_GET['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['priority'])) {
            $sql .= " AND t.priority = ?";
            $params[] = $_GET['priority'];
        }
        if (!empty($_GET['id_category'])) {
            $sql .= " AND t.id_category = ?";
            $params[] = $_GET['id_category'];
        }
        if (!empty($_GET['search'])) {
            $sql .= " AND (t.judul LIKE ? OR t.deskripsi LIKE ?)";
            $params[] = '%' . $_GET['search'] . '%';
            $params[] = '%' . $_GET['search'] . '%';
        }
        $sql .= " ORDER BY t.deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(true, 'OK', $tasks);
    }

    public function detail(int $userId, int $taskId) {
        $stmt = $this->db->prepare(
            "SELECT t.*, c.nama_category, c.warna, c.icon
             FROM tasks t
             LEFT JOIN categories c ON t.id_category = c.id_category
             WHERE t.id_task = ? AND t.id_user = ?"
        );
        $stmt->execute([$taskId, $userId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$task) respond(false, 'Task tidak ditemukan', null, 404);

        // subtasks
        $s = $this->db->prepare("SELECT * FROM subtasks WHERE id_task = ? ORDER BY created_at ASC");
        $s->execute([$taskId]);
        $task['subtasks'] = $s->fetchAll(PDO::FETCH_ASSOC);

        // reminders
        $r = $this->db->prepare("SELECT * FROM reminders WHERE id_task = ? ORDER BY reminder_time ASC");
        $r->execute([$taskId]);
        $task['reminders'] = $r->fetchAll(PDO::FETCH_ASSOC);

        respond(true, 'OK', $task);
    }

    public function create(int $userId) {
        $data = getBody();
        $err  = validateRequired($data, ['id_category', 'judul', 'deadline']);
        if ($err) respond(false, $err, null, 400);

        $stmt = $this->db->prepare(
            "INSERT INTO tasks (id_user, id_category, judul, deskripsi, deadline, priority, progress, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId,
            $data['id_category'],
            trim($data['judul']),
            $data['deskripsi'] ?? null,
            $data['deadline'],
            $data['priority'] ?? 'medium',
            $data['progress'] ?? 0,
            $data['status']   ?? 'pending',
        ]);
        $id = $this->db->lastInsertId();
        $this->detail($userId, $id);
    }

    public function update(int $userId, int $taskId) {
        $stmt = $this->db->prepare("SELECT id_task FROM tasks WHERE id_task = ? AND id_user = ?");
        $stmt->execute([$taskId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Task tidak ditemukan', null, 404);

        $data   = getBody();
        $fields = [];
        $params = [];
        $allowed = ['id_category','judul','deskripsi','deadline','priority','progress','status'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) respond(false, 'Tidak ada data yang diupdate', null, 400);

        $params[] = $taskId;
        $this->db->prepare("UPDATE tasks SET " . implode(', ', $fields) . " WHERE id_task = ?")->execute($params);
        $this->detail($userId, $taskId);
    }

    public function delete(int $userId, int $taskId) {
        $stmt = $this->db->prepare("SELECT id_task FROM tasks WHERE id_task = ? AND id_user = ?");
        $stmt->execute([$taskId, $userId]);
        if (!$stmt->fetch()) respond(false, 'Task tidak ditemukan', null, 404);

        $this->db->prepare("DELETE FROM tasks WHERE id_task = ?")->execute([$taskId]);
        respond(true, 'Task berhasil dihapus');
    }
}
