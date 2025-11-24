<?php
session_start();

// Limpiar todas las variables de sesión
$_SESSION = [];
session_unset();
session_destroy();

// Borrar cookie de email si la estás usando
setcookie('ng_email', '', time() - 3600, '/');

// Redirigir al home
header('Location: /index.html#home');
exit;
