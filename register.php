<?php
// register.php
session_start();
require_once 'db.php';

// 1. Solo aceptar peticiones POST (desde el formulario)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html#register');
    exit;
}

// 2. Leer datos del formulario
$nombre   = trim($_POST['nombre']   ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// 3. Validar campos mínimos
if ($nombre === '' || $email === '' || $password === '') {
    // Si falta algo, volvemos al formulario
    header('Location: index.html#register');
    exit;
}

try {
    // 4. Comprobar si ya existe un usuario con ese email
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        // Ya existe ese correo -> volvemos al registro
        header('Location: index.html#register');
        exit;
    }

    // 5. Hashear contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 6. Insertar nuevo usuario con rol "cliente"
    $stmt = $pdo->prepare('
        INSERT INTO usuarios (nombre, telefono, email, password_hash, rol)
        VALUES (:nombre, :telefono, :email, :password_hash, :rol)
    ');

    $stmt->execute([
        'nombre'        => $nombre,
        'telefono'      => $telefono,
        'email'         => $email,
        'password_hash' => $passwordHash,
        'rol'           => 'cliente',   // <-- IMPORTANTE
    ]);

    // 7. Después de registrar, lo mandamos a iniciar sesión
    header('Location: index.html#login');
    exit;

} catch (PDOException $e) {
    // Si algo falla en la BD, volvemos al registro
    header('Location: index.html#register');
    exit;
}
