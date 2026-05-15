<?php
// Autoloader simple PSR-4 para el namespace App\ → src/
spl_autoload_register(function (string $class) {
    if (str_starts_with($class, 'App\\')) {
        $path = __DIR__ . '/../src/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
        if (file_exists($path)) require_once $path;
    }
});

use App\JwtUtils;
use App\Controllers\AuthController;
use App\Controllers\AttendanceController;

header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// El proxy de Nginx redirige todo /api/ al backend, así que la URI será /api/...
$uri = str_replace('/api', '', $uri);

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Rutas Públicas
if ($uri === '/login' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new AuthController();
    $controller->login($data);
    exit;
}

// Middleware Autenticación para Rutas Protegidas
$token = JwtUtils::getBearerToken();
$decodedData = null;
if ($token) {
    $decodedData = JwtUtils::validateToken($token);
}

if (!$decodedData) {
    http_response_code(401);
    echo json_encode(["message" => "Acceso no autorizado, token inválido o expirado"]);
    exit;
}

$employeeId = $decodedData->employee_id;

// Rutas Protegidas
if ($uri === '/attendance' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new AttendanceController();
    $controller->registerPunch($data, $employeeId);
    exit;
}

if ($uri === '/sync' && $method === 'POST') {
    // Para modo offline masivo
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['punches']) || !is_array($data['punches'])) {
        http_response_code(400);
        echo json_encode(["message" => "Formato incorrecto. Se espera 'punches' como array."]);
        exit;
    }
    
    $controller = new AttendanceController();
    $results = [];
    // Capturar la salida de registerPunch para no cortar el flujo
    foreach ($data['punches'] as $punch) {
        ob_start();
        $controller->registerPunch($punch, $employeeId);
        $output = json_decode(ob_get_clean(), true);
        $code = http_response_code();
        $results[] = [
            "punch" => $punch,
            "status_code" => $code,
            "response" => $output
        ];
        http_response_code(200); // reset
    }
    
    echo json_encode(["message" => "Sincronización completada", "results" => $results]);
    exit;
}

http_response_code(404);
echo json_encode(["message" => "Ruta no encontrada"]);
