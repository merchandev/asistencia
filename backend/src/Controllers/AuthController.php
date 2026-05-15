<?php
namespace App\Controllers;

use App\Database;
use App\JwtUtils;

class AuthController {
    public function login($data) {
        if (!isset($data['employee_id']) || !isset($data['pin'])) {
            http_response_code(400);
            echo json_encode(["message" => "Faltan credenciales"]);
            return;
        }

        $employeeId = $data['employee_id'];
        $pin = $data['pin'];

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT pin_hash FROM employees WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch();

        if ($employee && password_verify($pin, $employee['pin_hash'])) {
            $token = JwtUtils::generateToken($employeeId);
            
            // Actualizar token en DB
            $updateStmt = $db->prepare("UPDATE employees SET jwt_token = ? WHERE employee_id = ?");
            $updateStmt->execute([$token, $employeeId]);

            echo json_encode([
                "message" => "Login exitoso",
                "token" => $token,
                "employee_id" => $employeeId
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Credenciales inválidas"]);
        }
    }
}
