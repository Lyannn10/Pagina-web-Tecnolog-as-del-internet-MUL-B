<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db.php';

// 1. Comprobar que haya sesión
if (empty($_SESSION['usuario_id'])) {
    header('Location: index.html#login');
    exit;
}

// 2. Comprobar que el rol sea admin
// CAMBIO: antes era $_SESSION['usuario_rol']
$rol = $_SESSION['rol'] ?? 'cliente';
if ($rol !== 'admin') {
    http_response_code(403);
    echo 'Acceso denegado. Esta sección es solo para administradores.';
    exit;
}

// 3. Cargar usuarios y reservas desde la base de datos
try {
    // Usuarios
    $stmtUsers = $pdo->query(
        'SELECT id, nombre, email, telefono, rol, creado_en
         FROM usuarios
         ORDER BY creado_en DESC'
    );
    $usuarios = $stmtUsers->fetchAll();

    // Reservas (JOIN con usuarios y servicios)
    $stmtRes = $pdo->query(
        'SELECT 
            r.id,
            u.nombre AS cliente,
            u.email,
            s.nombre AS servicio,
            r.fecha,
            r.hora_inicio,
            r.estado,
            r.creado_en
        FROM reservas r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        INNER JOIN servicios s ON r.servicio_id = s.id
        ORDER BY r.fecha DESC, r.hora_inicio DESC'
    );
    $reservas = $stmtRes->fetchAll();

} catch (PDOException $e) {
    die('Error al cargar los datos del administrador.');
}

// Función para escapar texto
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Panel administrador - NailGrace</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Estilos mínimos para el panel admin, podrías moverlos a styles.css si quieres */
    .admin-wrapper{
      max-width: var(--max-desktop, 1100px);
      margin: 20px auto 40px;
      padding: 16px;
    }
    .admin-title{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:20px;
    }
    .admin-title h1{
      font-size:24px;
      margin:0;
    }
    .admin-section{
      margin-top:24px;
      background:#fff;
      border-radius:12px;
      padding:16px;
      box-shadow:0 10px 25px rgba(15,23,42,0.04);
    }
    .admin-section h2{
      margin-top:0;
      font-size:18px;
    }
    .admin-table{
      width:100%;
      border-collapse:collapse;
      font-size:14px;
    }
    .admin-table th,
    .admin-table td{
      padding:8px 10px;
      border-bottom:1px solid rgba(148,163,184,0.25);
      text-align:left;
      vertical-align:middle;
    }
    .admin-table th{
      background: #f9fafb;
      font-weight:600;
    }
    .badge{
      display:inline-flex;
      align-items:center;
      padding:2px 8px;
      border-radius:999px;
      font-size:12px;
    }
    .badge-admin{
      background:rgba(52,211,153,0.15);
      color:#047857;
    }
    .badge-cliente{
      background:rgba(59,130,246,0.12);
      color:#1d4ed8;
    }
    .badge-pendiente{
      background:rgba(251,191,36,0.15);
      color:#92400e;
    }
    .badge-confirmada{
      background:rgba(16,185,129,0.15);
      color:#065f46;
    }
    .badge-cancelada{
      background:rgba(248,113,113,0.15);
      color:#b91c1c;
    }
    .admin-actions{
      display:flex;
      flex-wrap:wrap;
      gap:4px;
    }
    .btn-small{
      font-size:12px;
      padding:4px 8px;
      border-radius:999px;
      border:none;
      cursor:pointer;
    }
    .btn-outline{
      background:#fff;
      border:1px solid #e5e7eb;
      color:#374151;
    }
    .btn-success{
      background:#22c55e;
      color:#fff;
    }
    .btn-danger{
      background:#ef4444;
      color:#fff;
    }
  </style>
</head>
<body>
  <div class="site container">
    <header class="header">
      <img src="Imagenes/logo.png" alt="NailGrace logo" height="50">
      <nav class="mainnav">
        <a href="index.html#home">Inicio</a>
        <a href="index.html#services">Servicios</a>
        <a href="index.html#home" class="reserve-link">Volver al sitio</a>
        <a href="logout.php" class="btn btn-light" style="margin-left:12px;">Cerrar sesión</a>
      </nav>
    </header>

    <main class="admin-wrapper">
      <div class="admin-title">
        <h1>Panel de administración</h1>
        <p class="muted" style="margin:0;">
          Hola,
          <?php
          // CAMBIO: antes era $_SESSION['usuario_nombre']
          echo e($_SESSION['nombre'] ?? '');
          ?>
          (admin)
        </p>
      </div>

      <!-- Sección usuarios -->
      <section class="admin-section">
        <h2>Usuarios registrados</h2>
        <div style="overflow-x:auto;">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Creado</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
              <tr><td colspan="6">No hay usuarios registrados.</td></tr>
            <?php else: ?>
              <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td><?php echo e($u['id']); ?></td>
                  <td><?php echo e($u['nombre']); ?></td>
                  <td><?php echo e($u['email']); ?></td>
                  <td><?php echo e($u['telefono']); ?></td>
                  <td>
                    <?php if ($u['rol'] === 'admin'): ?>
                      <span class="badge badge-admin">Admin</span>
                    <?php else: ?>
                      <span class="badge badge-cliente">Cliente</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo e($u['creado_en']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Sección reservas -->
      <section class="admin-section">
        <h2>Reservas</h2>
        <div style="overflow-x:auto;">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Email</th>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Creada</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($reservas)): ?>
              <tr><td colspan="9">No hay reservas registradas.</td></tr>
            <?php else: ?>
              <?php foreach ($reservas as $r): ?>
                <tr>
                  <td><?php echo e($r['id']); ?></td>
                  <td><?php echo e($r['cliente']); ?></td>
                  <td><?php echo e($r['email']); ?></td>
                  <td><?php echo e($r['servicio']); ?></td>
                  <td><?php echo e($r['fecha']); ?></td>
                  <td><?php echo e(substr($r['hora_inicio'], 0, 5)); ?></td>
                  <td>
                    <?php
                      $estado = $r['estado'];
                      $class = 'badge-pendiente';
                      if ($estado === 'Confirmada') $class = 'badge-confirmada';
                      if ($estado === 'Cancelada')  $class = 'badge-cancelada';
                    ?>
                    <span class="badge <?php echo e($class); ?>"><?php echo e($estado); ?></span>
                  </td>
                  <td><?php echo e($r['creado_en']); ?></td>
                  <td>
                    <div class="admin-actions">
                      <!-- Botón Poner en Pendiente -->
                      <form method="POST" action="actualizar_reserva.php" style="display:inline;">
                        <input type="hidden" name="reserva_id" value="<?php echo e($r['id']); ?>">
                        <input type="hidden" name="nuevo_estado" value="Pendiente">
                        <button class="btn-small btn-outline" type="submit">Pendiente</button>
                      </form>

                      <!-- Botón Confirmar -->
                      <form method="POST" action="actualizar_reserva.php" style="display:inline;">
                        <input type="hidden" name="reserva_id" value="<?php echo e($r['id']); ?>">
                        <input type="hidden" name="nuevo_estado" value="Confirmada">
                        <button class="btn-small btn-success" type="submit">Confirmar</button>
                      </form>

                      <!-- Botón Cancelar -->
                      <form method="POST" action="actualizar_reserva.php" style="display:inline;">
                        <input type="hidden" name="reserva_id" value="<?php echo e($r['id']); ?>">
                        <input type="hidden" name="nuevo_estado" value="Cancelada">
                        <button class="btn-small btn-danger" type="submit">Cancelar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>

    <footer class="site-footer">
      <div class="footer-bottom">
        © 2025 NailGrace · Panel administrador
      </div>
    </footer>
  </div>
</body>
</html>
