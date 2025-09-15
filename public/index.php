<?php
declare(strict_types=1);

$frontendUrl = "http://localhost:5173"; // your Vue frontend URL

header("Access-Control-Allow-Origin: $frontendUrl"); // must match frontend exactly
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true"); // this is critical

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

// use App\Core\Router;
// use App\Core\RouterAPI;

// $router = new Router();
// $routerApi = new RouterAPI();

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isApiRequest = strpos($requestUri, '/LMS-backend/public/api/') === 0;

// Always apply CORS middleware first for API requests
if ($isApiRequest) {
    require __DIR__ . '/../App/routes/api.php';
    
} else {
    require __DIR__ . '/../App/routes/web.php';
}



