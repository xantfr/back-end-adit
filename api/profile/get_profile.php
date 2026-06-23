<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';
$user = requireAuth();
$ctrl = new ProfileController();
if ($_SERVER['REQUEST_METHOD'] === 'GET') $ctrl->get($user['id_user']);
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') $ctrl->update($user['id_user']);
else http_response_code(405);
