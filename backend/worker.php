<?php
// Sin dependencias externas — usa la extensión Redis nativa de PHP (instalada vía PECL)

echo "Iniciando worker de Redis...\n";

$redis = null;
while ($redis === null) {
    try {
        $r = new Redis();
        $r->connect(getenv('REDIS_HOST') ?: 'redis', 6379);
        $r->ping();
        $redis = $r;
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
        $task = $redis->brPop('asistencia_tasks', 0);

        if ($task) {
            $taskData = json_decode($task[1], true);
            echo "Procesando tarea: " . $taskData['type'] . "\n";

            if ($taskData['type'] === 'email_notification') {
                echo "Enviando correo a empleado " . $taskData['employee_id'] . " por su " . $taskData['punch_type'] . " a las " . $taskData['time'] . "\n";
                sleep(1);
                echo "Correo enviado.\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error en worker: " . $e->getMessage() . "\n";
        sleep(2);
    }
}
