<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') (new AuthController())->login();
else http_response_code(405);
