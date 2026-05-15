<?php
require __DIR__ . '/vendor/autoload.php';

use Predis\Client;

echo "Iniciando worker de Redis...\n";

$redis = null;
while ($redis === null) {
    try {
        $redis = new Client([
            'scheme' => 'tcp',
            'host'   => getenv('REDIS_HOST') ?: 'redis',
            'port'   => 6379,
            'read_write_timeout' => 0, // No timeout
        ]);
        $redis->ping();
    } catch (\Exception $e) {
        echo "Esperando a Redis...\n";
        sleep(2);
        $redis = null;
    }
}

echo "Conectado a Redis. Escuchando tareas...\n";

while (true) {
    try {
        // Bloquea hasta que haya un elemento en la lista 'asistencia_tasks'
        $task = $redis->brpop('asistencia_tasks', 0);
        
        if ($task) {
            $taskData = json_decode($task[1], true);
            echo "Procesando tarea: " . $taskData['type'] . "\n";
            
            if ($taskData['type'] === 'email_notification') {
                // Simular envío de correo
                echo "Enviando correo a empleado " . $taskData['employee_id'] . " por su " . $taskData['punch_type'] . " a las " . $taskData['time'] . "\n";
                sleep(1); // Simular delay
                echo "Correo enviado.\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error en worker: " . $e->getMessage() . "\n";
        sleep(2);
    }
}
