<?php
// reservar.php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// 1. Verificar que el usuario esté logueado
if (empty($_SESSION['usuario_id'])) {
    echo json_encode([
        'status'  => 'error',
        'code'    => 'not_logged_in',
        'message' => 'Debes iniciar sesión para reservar.'
    ]);
    exit;
}

// 2. Aceptar solo método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Método no permitido.'
    ]);
    exit;
}

// 3. Recoger datos enviados desde JS
$fecha          = trim($_POST['fecha'] ?? '');
$hora           = trim($_POST['hora'] ?? '');
$servicioNombre = trim($_POST['servicio_nombre'] ?? '');

// Validaciones básicas
if ($fecha === '' || $hora === '' || $servicioNombre === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Fecha, hora y servicio son obligatorios.'
    ]);
    exit;
}

// Validar formato de fecha y hora (simple)
$fechaValida = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha);
$horaValida  = (bool) preg_match('/^\d{2}:\d{2}$/', $hora);

if (!$fechaValida || !$horaValida) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Formato de fecha u hora inválido.'
    ]);
    exit;
}

// Validar que la fecha/hora sean futuras
$tz = new DateTimeZone('America/Bogota');
$ahora = new DateTime('now', $tz);
$fechaHoraReserva = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora, $tz);

if (!$fechaHoraReserva || $fechaHoraReserva < $ahora) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'La fecha y hora deben ser futuras.'
    ]);
    exit;
}

try {
    // 4. Buscar el servicio por nombre
    $stmt = $pdo->prepare('SELECT id FROM servicios WHERE nombre = :nombre LIMIT 1');
    $stmt->execute(['nombre' => $servicioNombre]);
    $servicio = $stmt->fetch();

    if (!$servicio) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'El servicio seleccionado no existe.'
        ]);
        exit;
    }

    // 5. Insertar la reserva
    $stmt = $pdo->prepare(
        'INSERT INTO reservas (usuario_id, servicio_id, fecha, hora_inicio)
         VALUES (:usuario_id, :servicio_id, :fecha, :hora_inicio)'
    );

    $stmt->execute([
        'usuario_id'  => $_SESSION['usuario_id'],
        'servicio_id' => $servicio['id'],
        'fecha'       => $fecha,
        'hora_inicio' => $hora . ':00'
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Tu reserva se creó correctamente.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'No se pudo crear la reserva. Inténtalo más tarde.'
    ]);
}
