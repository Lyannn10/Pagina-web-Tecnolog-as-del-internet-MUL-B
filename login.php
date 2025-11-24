<?php
// NADA antes de este <?php (ni espacios, ni saltos de línea)
session_start();
require 'db.php'; // aquí incluyes tu conexión PDO en $pdo

// Muestra errores mientras depuras (luego puedes quitar esto)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    // Por si llegan en blanco
    $email    = trim($email);
    $password = trim($password);

    if ($email === '' || $password === '') {
        echo "Faltan datos de correo o contraseña.";
        exit;
    }

    try {
        // Buscar usuario por email en tabla `usuarios`
        $sql = "SELECT id, nombre, password_hash, rol 
                FROM usuarios 
                WHERE email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validar usuario y contraseña
        if ($user && password_verify($password, $user['password_hash'])) {

            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['rol']        = $user['rol']; // admin | cliente

            // Redirigir según rol
            if ($user['rol'] === 'admin') {
                header('Location: admin.php');
                exit;
            } else {
                header('Location: index.html#home');
                exit;
            }

        } else {
            // Credenciales incorrectas
            echo "Correo o contraseña incorrectos.";
            exit;
        }

    } catch (PDOException $e) {
        // Error de base de datos
        echo "Error al conectar con la base de datos.";
        // Si quieres ver el detalle mientras depuras:
        // echo $e->getMessage();
        exit;
    }

} else {
    // Si acceden a login.php por GET, devolverlos al formulario
    header('Location: index.html#login');
    exit;
}
