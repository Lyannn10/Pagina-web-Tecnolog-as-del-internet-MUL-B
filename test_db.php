<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

try {
    // Si llega aquí, la conexión funcionó
    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM usuarios');
    $row  = $stmt->fetch();
    echo 'Conexión OK. Usuarios en la tabla: ' . (int)$row['total'];
} catch (Throwable $e) {
    echo 'Error al consultar la tabla usuarios: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
