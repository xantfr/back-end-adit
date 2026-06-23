<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$user   = requireAuth();
$userId = (int)$user['id_user'];
$ctrl   = new CategoryController();

if ($_SERVER['REQUEST_METHOD'] === 'GET')       $ctrl->index($userId);
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')  $ctrl->create($userId);
else http_response_code(405);
