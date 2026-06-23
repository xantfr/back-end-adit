<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$user   = requireAuth();
$userId = (int)$user['id_user'];
$id     = (int)($_GET['id'] ?? 0);
$ctrl   = new CategoryController();

if ($_SERVER['REQUEST_METHOD'] === 'PUT')          $ctrl->update($id, $userId);
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE')   $ctrl->delete($id, $userId);
else http_response_code(405);
