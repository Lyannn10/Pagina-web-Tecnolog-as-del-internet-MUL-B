<?php
session_start();
header('Content-Type: application/javascript; charset=UTF-8');

$isLoggedIn = isset($_SESSION['usuario_id']);
$rol        = $_SESSION['usuario_rol'] ?? null;
$nombre     = $_SESSION['usuario_nombre'] ?? null;

echo 'window.ngIsLoggedIn = ' . ($isLoggedIn ? 'true' : 'false') . ';';
echo 'window.ngUserRole = ' . json_encode($rol) . ';';
echo 'window.ngUserName = ' . json_encode($nombre) . ';';
