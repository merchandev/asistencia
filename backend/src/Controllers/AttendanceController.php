<?php
namespace App\Controllers;

use App\Database;
// Usa la extensión nativa Redis (instalada vía PECL), sin Predis

class AttendanceController {
    public function registerPunch($data, $employeeId) {
        $required = ['branch_code', 'punch_type', 'latitude', 'longitude', 'device_timestamp'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["message" => "Faltan campos requeridos: $field"]);
                return;
            }
        }

        $db = Database::getInstance()->getConnection();
        
        // 1. Validar manipulación de hora
        $deviceTime = new \DateTime($data['device_timestamp']);
        $serverTime = new \DateTime();
        $diff = abs($serverTime->getTimestamp() - $deviceTime->getTimestamp());
        
        // Si la diferencia es mayor a 5 minutos (300 segundos), rechazar
        if ($diff > 300) {
            http_response_code(403);
            echo json_encode(["message" => "Diferencia de tiempo excesiva. Posible manipulación de hora."]);
            return;
        }

        // 2. Validar Geocerca
        $stmt = $db->prepare("SELECT radius_meters, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance FROM branches WHERE branch_code = ?");
        $stmt->execute([$data['longitude'], $data['latitude'], $data['branch_code']]);
        $branch = $stmt->fetch();

        if (!$branch) {
            http_response_code(404);
            echo json_encode(["message" => "Sucursal no encontrada"]);
            return;
        }

        if ($branch['distance'] > $branch['radius_meters']) {
            http_response_code(403);
            echo json_encode(["message" => "Estás fuera de la geocerca de la sucursal (Distancia: " . round($branch['distance']) . "m)"]);
            return;
        }

        // 3. Validar doble fichaje
        $stmtLast = $db->prepare("SELECT punch_type FROM attendances WHERE employee_id = ? ORDER BY server_timestamp DESC LIMIT 1");
        $stmtLast->execute([$employeeId]);
        $lastPunch = $stmtLast->fetch();

        if ($lastPunch && $lastPunch['punch_type'] == $data['punch_type']) {
            http_response_code(409);
            echo json_encode(["message" => "No puedes marcar " . ($data['punch_type'] == 'in' ? 'Entrada' : 'Salida') . " dos veces seguidas."]);
            return;
        }

        // 4. Registrar Asistencia
        $insertStmt = $db->prepare("
            INSERT INTO attendances 
            (employee_id, branch_code, punch_type, device_timestamp, server_timestamp, latitude, longitude, status, synced_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'synced', ?)
        ");
        
        $serverTimeStr = $serverTime->format('Y-m-d H:i:s');
        $insertStmt->execute([
            $employeeId,
            $data['branch_code'],
            $data['punch_type'],
            $data['device_timestamp'],
            $serverTimeStr,
            $data['latitude'],
            $data['longitude'],
            $serverTimeStr
        ]);

        // 5. Enviar a Cola Redis para notificaciones (Asíncrono)
        try {
            $redis = new \Redis();
            $redis->connect(getenv('REDIS_HOST') ?: 'redis', 6379);

            $taskData = [
                'type'        => 'email_notification',
                'employee_id' => $employeeId,
                'punch_type'  => $data['punch_type'],
                'time'        => $serverTimeStr
            ];
            $redis->lPush('asistencia_tasks', json_encode($taskData));
        } catch (\Exception $e) {
            // Ignorar errores de Redis para no fallar el request principal
        }

        echo json_encode([
            "message" => "Fichaje registrado correctamente",
            "synced" => true,
            "server_timestamp" => $serverTimeStr
        ]);
    }
}
