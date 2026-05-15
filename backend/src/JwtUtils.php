<?php
namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtUtils {
    public static function generateToken($employeeId) {
        $secretKey = getenv('JWT_SECRET') ?: 'default_secret';
        $issuedAt   = new \DateTimeImmutable();
        $expire     = $issuedAt->modify('+30 days')->getTimestamp();      
        $serverName = "asistencia_pwa";

        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'data' => [
                'employee_id' => $employeeId,
            ]
        ];

        return JWT::encode($data, $secretKey, 'HS256');
    }

    public static function validateToken($token) {
        try {
            $secretKey = getenv('JWT_SECRET') ?: 'default_secret';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function getBearerToken() {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
