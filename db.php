<?php
declare(strict_types=1);

$host = 'sql211.infinityfree.com';             // ⚠️ Cambia por el "MySQL Hostname" de InfinityFree
$db   = 'if0_40492978_nailgrace';      // ⚠️ Cambia por "MySQL DB Name"
$user = 'if0_40492978';                // ⚠️ Cambia por "MySQL Username"
$pass = 'LD241125';         // ⚠️ Pon aquí la contraseña que te muestra InfinityFree

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // No mostramos el mensaje real en producción por seguridad
    http_response_code(500);
    echo 'Error de conexión a la base de datos.';
    exit;
}
