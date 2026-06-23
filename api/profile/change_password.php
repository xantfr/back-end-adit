<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';
$user = requireAuth();
if ($_SERVER['REQUEST_METHOD'] === 'POST') (new ProfileController())->changePassword($user['id_user']);
else http_response_code(405);
