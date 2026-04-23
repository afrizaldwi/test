<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {
    case '/server/api/auth/login':
        if ($requestMethod === 'POST') {
            $email = $_POST['email'];
            $pass = $_POST['pass'];

            $authController = new AuthController();
            $authController->login($email, $pass);
        } else {
            sendResponse405();
        }
        break;
    case '/server/api/auth/check':
        if ($requestMethod === "GET") {
            $authController = new AuthController();
            $authController->check();
        } else {
            sendResponse405();
        }
        break;
    case '/server/api/auth/logout':
        if ($requestMethod === "POST") {
            $authController = new AuthController();
            $authController->logout();
        } else {
            sendResponse405();
        }
        break;
    case '/server/api/dashboard/admin':
        if ($requestMethod === "GET") {
            $dashboardController = new DashboardController;
            $dashboardController->getAdminDashboard();
        } else {
            sendResponse405();
        }
        break;
    case '/server/api/dashboard/penyewa':
        if ($requestMethod === "GET") {
            $dashboardController = new DashboardController;
            $dashboardController->getPenyewaDashboard();
        } else {
            sendResponse405();
        }
        break;
    default:
        http_response_code(404);
        echo "Error 404! No route found!";
        break;
}
function sendResponse405()
{
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
}
