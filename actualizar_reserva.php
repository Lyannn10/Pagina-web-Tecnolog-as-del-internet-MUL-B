<?php
declare(strict_types=1);

session_start();

// 1. Verificar que sea admin
if (
    !isset($_SESSION['usuario_id'], $_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    // Si no es admin, lo mandamos al inicio
    header('Location: index.html#home');
    exit;
}

// 2. Verificar método y datos recibidos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$reservaId    = isset($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : 0;
$nuevoEstado  = $_POST['nuevo_estado'] ?? '';

// Estados permitidos (deben coincidir EXACTO con lo que guardas en la BD)
$estadosPermitidos = ['Pendiente', 'Confirmada', 'Cancelada'];

if ($reservaId <= 0 || !in_array($nuevoEstado, $estadosPermitidos, true)) {
    // Algo raro con los datos → volvemos al panel
    header('Location: admin.php');
    exit;
}

// 3. Conectar a la base de datos
require_once 'db.php';

// 4. Actualizar la reserva
try {
    $stmt = $pdo->prepare('UPDATE reservas SET estado = :estado WHERE id = :id');
    $stmt->execute([
        ':estado' => $nuevoEstado,
        ':id'     => $reservaId
    ]);

    // Da igual si cambió o no, volvemos al panel para no complicar
    header('Location: admin.php');
    exit;

} catch (PDOException $e) {
    // Error en BD → también volvemos al panel
    header('Location: admin.php');
    exit;
}
