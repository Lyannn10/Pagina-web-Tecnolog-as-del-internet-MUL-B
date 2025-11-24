<?php
// actualizar_reserva.php
session_start();
require_once 'db.php';

// Solo admins
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

// Validar datos
$reservaId = filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT);
$nuevoEstado = $_POST['nuevo_estado'] ?? '';

$estadosPermitidos = ['Pendiente', 'Confirmada', 'Cancelada'];

if (!$reservaId || !in_array($nuevoEstado, $estadosPermitidos, true)) {
    header('Location: admin.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'UPDATE reservas 
         SET estado = :estado
         WHERE id = :id'
    );
    $stmt->execute([
        'estado' => $nuevoEstado,
        'id'     => $reservaId,
    ]);

    header('Location: admin.php');
    exit;

} catch (PDOException $e) {
    // En caso de error, volvemos igual al admin
    header('Location: admin.php');
    exit;
}
