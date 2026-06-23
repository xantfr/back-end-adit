<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/SubtaskController.php';
$user = requireAuth();
$id   = (int)($_GET['id'] ?? 0);
$ctrl = new SubtaskController();
if ($_SERVER['REQUEST_METHOD'] === 'PUT')    $ctrl->update($user['id_user'], $id);
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') $ctrl->delete($user['id_user'], $id);
else http_response_code(405);
