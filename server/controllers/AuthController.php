<?php

require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../helpers/helpers.php';

class AuthController
{
    private $auth;

    public function login($email, $pass)
    {
        try {
            $this->auth = new Auth();

            $result = $this->auth->getUserByEmail($email);

            if (empty($result)) {
                sendResponse("error", 404, "User not found");
            }

            $user = $result[0];

            if (!password_verify($pass, $user['password'])) {
                sendResponse("error", 401, "Wrong password");
            }

            $token = generateJWT([
                'id_auth' => $user['id_auth'],
                'email'   => $user['email'],
                'role'    => $user['role'],
                'exp'     => time() + 86400
            ]);

            setcookie('kost_token', $token, [
                'expires'  => time() + 86400,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            $data = [
                'id_auth' => $user['id_auth'],
                'email' => $user['email'],
                'role' => $user['role'],
                'token' => $token
            ];
            sendResponse("success", 200, "successfully retrivied user data", $data);
        } catch (\Throwable $e) {
            sendResponse("error", 500, $e->getMessage());
        }
    }

    public function check()
    {
        $token = null;
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        } elseif (isset($_COOKIE['kost_token'])) {
            $token = $_COOKIE['kost_token'];
        } else {
            sendResponse('error', 401, 'Unauthorized');
        }

        $payload = verifyJWT($token);

        if (!$payload) {
            sendResponse("error", 401, "Invalid token");
        }

        if (($payload['exp'] ?? 0) < time()) {
            sendResponse("error", 401, "Token expired");
        }

        sendResponse("success", 200, "successfully retrieved user data", $payload);
    }
    public function logout()
    {
        setcookie('kost_token', '', time() - 3600, '/');
        sendResponse("success", 200, "logout success");
    }
}
