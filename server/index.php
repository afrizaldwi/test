<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://tugas-mandiri.test');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        // Using the double-trim to fix the Windows \r\n bug!
        $_ENV[trim($key)] = trim(trim($value), " '\"");
    }
}
require_once __DIR__ . '/routes/api.php';
