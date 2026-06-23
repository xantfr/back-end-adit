<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/TaskController.php';
$user   = requireAuth();
$taskId = (int)($_GET['id'] ?? 0);
if (!$taskId) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id diperlukan']); exit(); }
$ctrl = new TaskController();
if ($_SERVER['REQUEST_METHOD'] === 'GET')    $ctrl->detail($user['id_user'], $taskId);
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') $ctrl->update($user['id_user'], $taskId);
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') $ctrl->delete($user['id_user'], $taskId);
else http_response_code(405);
