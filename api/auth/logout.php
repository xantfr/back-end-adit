<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
requireAuth();
if ($_SERVER['REQUEST_METHOD'] === 'POST') (new AuthController())->logout();
else http_response_code(405);
