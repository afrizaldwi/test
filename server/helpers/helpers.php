<?php
function sendResponse($status, $code, $message, $data = null)
{
    http_response_code($code);
    $response = [
        'status'  => $status,
        'message' => $message,
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}
function requireRole(string $role): void
{
    if (!isset($_COOKIE['kost_token'])) {
        sendResponse('error', 401, 'Unauthorized');
    }

    $payload = verifyJWT($_COOKIE['kost_token']);

    if (!$payload) {
        sendResponse('error', 401, 'Invalid token');
    }

    if (($payload['exp'] ?? 0) < time()) {
        sendResponse('error', 401, 'Token expired');
    }

    if ($payload['role'] !== $role) {
        sendResponse('error', 403, 'Forbidden');
    }
}

function generateJWT(array $payload): string
{
    $secret = $_ENV['JWT_SECRET'] ?? 'fallback_secret';

    $header    = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload   = base64_encode(json_encode($payload));
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

    return "$header.$payload.$signature";
}

function verifyJWT(string $token): ?array
{
    $secret = $_ENV['JWT_SECRET'] ?? 'fallback_secret';
    $parts  = explode('.', $token);

    if (count($parts) !== 3) return null;

    [$header, $payload, $signature] = $parts;

    $expected = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

    if (!hash_equals($expected, $signature)) return null;

    return json_decode(base64_decode($payload), true);
}
