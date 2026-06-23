<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/ReminderController.php';
$user = requireAuth();
$ctrl = new ReminderController();
if ($_SERVER['REQUEST_METHOD'] === 'GET')  $ctrl->index($user['id_user']);
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->create($user['id_user']);
else http_response_code(405);
