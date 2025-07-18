<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar que el ID del usuario esté en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Usuario no especificado.'); window.location.href='lista_usuarios.php';</script>";
    exit();
}

$id_usuario = intval($_GET['id']);

// Verificar si el usuario logueado es administrador
$is_admin = false;
if (isset($_SESSION['id_usuario'])) {
    $id_usuario_logueado = $_SESSION['id_usuario'];
    $query_admin = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_admin);
    $stmt->bind_param("i", $id_usuario_logueado);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    $logged_user = $result_admin->fetch_assoc();
    $is_admin = ($logged_user['privilegio'] ?? '') === 'Administrador';
}

// Procesar el formulario de dar de baja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dar_de_baja'])) {
    $fecha_baja = date('Y-m-d H:i:s');
    $query_baja = "UPDATE usuarios SET dado_de_baja = 'SI', fecha_baja = ? WHERE id_usuario = ?";
    $stmt_baja = $conn->prepare($query_baja);
    $stmt_baja->bind_param("si", $fecha_baja, $id_usuario);
    if ($stmt_baja->execute()) {
        echo "<script>alert('Usuario dado de baja exitosamente.'); window.location.href='lista_usuarios.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error al dar de baja al usuario.');</script>";
    }
}

// Obtener los datos del usuario
$query = "SELECT nombre, correo, telefono, fotoperfil, privilegio, dado_de_baja FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si el usuario existe
if ($result->num_rows === 0) {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='lista_usuarios.php';</script>";
    exit();
}

$user = $result->fetch_assoc();

// Obtener las experiencias del usuario
$query_experiencias = "
    SELECT tne.nombre_nivel_educacional, e.experiencia_profesional
    FROM experiencias e
    JOIN tipo_nivel_educacional tne ON e.tipo_nivel_educacional_id_tipo_nivel_educacional = tne.id_tipo_nivel_educacional
    WHERE e.usuarios_id_usuario = ? AND e.fecha_baja IS NULL
";
$stmt_experiencias = $conn->prepare($query_experiencias);
$stmt_experiencias->bind_param("i", $id_usuario);
$stmt_experiencias->execute();
$result_experiencias = $stmt_experiencias->get_result();
$experiencias = $result_experiencias->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['nombre']); ?></title>
    <link rel="stylesheet" href="css/perfil_usuario.css">
</head>
<body>
    <div class="contenedor">
        <div class="profile-section">
            <div class="report-section">
                <!-- Botón de reporte -->
                <?php if ($id_usuario_logueado !== $id_usuario): ?>
                    <form action="reportar_usuario.php" method="GET">
                        <input type="hidden" name="usuario_reportado" value="<?php echo $id_usuario; ?>">
                        <input type="hidden" name="nombre_usuario_reportado" value="<?php echo htmlspecialchars($user['nombre']); ?>">
                        <button type="submit" class="btn-report">Reportar Usuario</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="profile-contenedor">
                <h1>Perfil de <?php echo htmlspecialchars($user['nombre']); ?></h1>
                <div class="profile-photo-contenedor">
                    <img src="<?php echo htmlspecialchars($user['fotoperfil']) ?: 'pfp/default-profile.jpg'; ?>" alt="Foto de perfil" class="profile-photo">
                </div>
                <div class="profile-info">
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['correo']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user['telefono']); ?></p>
                </div>

                <!-- Botón para dar de baja -->
                <?php if ($is_admin && $user['privilegio'] !== 'Administrador' && $user['dado_de_baja'] !== 'SI'): ?>
                    <form method="post">
                        <button type="submit" name="dar_de_baja" class="btn-baja">Dar de Baja</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de experiencias -->
        <div class="experiences-section">
            <h2>Experiencias</h2>
            <?php if (count($experiencias) > 0): ?>
                <ul>
                    <?php foreach ($experiencias as $exp): ?>
                        <li>
                            <p><strong>Nivel Educacional:</strong> <?php echo htmlspecialchars($exp['nombre_nivel_educacional']); ?></p>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($exp['experiencia_profesional']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>El usuario no tiene experiencias registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
