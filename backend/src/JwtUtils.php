<?php
namespace App;

/**
 * Implementación nativa de JWT usando HMAC-SHA256.
 * Sin dependencias externas — usa funciones nativas de PHP.
 */
class JwtUtils {

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function generateToken($employeeId): string {
        $secretKey = getenv('JWT_SECRET') ?: 'default_secret';
        $issuedAt  = time();
        $expire    = $issuedAt + (30 * 24 * 60 * 60); // 30 días

        $header  = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = self::base64UrlEncode(json_encode([
            'iat'  => $issuedAt,
            'iss'  => 'asistencia_pwa',
            'nbf'  => $issuedAt,
            'exp'  => $expire,
            'data' => ['employee_id' => $employeeId],
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $secretKey, true)
        );

        return "$header.$payload.$signature";
    }

    public static function validateToken(?string $token): ?\stdClass {
        if (!$token) return null;
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $secretKey       = getenv('JWT_SECRET') ?: 'default_secret';
        $expectedSig     = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $secretKey, true)
        );

        // Comparación segura contra timing attacks
        if (!hash_equals($expectedSig, $signature)) return null;

        $data = json_decode(self::base64UrlDecode($payload));
        if (!$data || !isset($data->exp) || $data->exp < time()) return null;

        return $data->data ?? null;
    }

    public static function getBearerToken(): ?string {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $reqHeaders = apache_request_headers();
            $reqHeaders = array_combine(array_map('ucwords', array_keys($reqHeaders)), array_values($reqHeaders));
            if (isset($reqHeaders['Authorization'])) {
                $headers = trim($reqHeaders['Authorization']);
            }
        }

        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

