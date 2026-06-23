<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/SubtaskController.php';
$user   = requireAuth();
$taskId = (int)($_GET['id_task'] ?? 0);
$ctrl   = new SubtaskController();
if ($_SERVER['REQUEST_METHOD'] === 'GET')  $ctrl->index($user['id_user'], $taskId);
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->create($user['id_user'], $taskId);
else http_response_code(405);
