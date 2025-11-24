<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html#login');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.html#login');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: index.html#login');
        exit;
    }

    // Guardar datos en sesión
    $_SESSION['usuario_id']     = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['usuario_email']  = $user['email'];
    $_SESSION['usuario_rol']    = $user['rol'];  // 👈 IMPORTANTE

    setcookie('ng_email', $user['email'], time() + 7*24*60*60, '/');

    header('Location: admin.php'); // si el admin entra directo al panel
    exit;

} catch (PDOException $e) {
    header('Location: index.html#login');
    exit;
}
