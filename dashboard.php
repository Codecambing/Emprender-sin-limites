<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = intval($_SESSION['id_usuario']);

// Consultar los privilegios del usuario
$sql_privilegios = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_privilegios);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Usuario no encontrado en la base de datos.");
}

$usuario = $result->fetch_assoc();

// Verificar si el usuario tiene privilegios de administrador
if ($usuario['privilegio'] !== "Administrador") {
    echo "<script>
        alert('No tienes permisos, volverás a la página de inicio.');
        window.location.href = 'inicio.php';
    </script>";
    exit();
}
// Consultar estadísticas
$sql_proyectos_activos = "SELECT COUNT(*) AS total FROM proyectos WHERE dado_de_baja = 'no'";
$sql_proyectos_dados_de_baja = "SELECT COUNT(*) AS total FROM proyectos WHERE dado_de_baja = 'si'";
$sql_comentarios_visibles = "SELECT COUNT(*) AS total FROM valoraciones WHERE ocultado = 0";
$sql_comentarios_ocultos = "SELECT COUNT(*) AS total FROM valoraciones WHERE ocultado = 1";
$sql_usuarios_activos = "SELECT COUNT(*) AS total FROM usuarios WHERE dado_de_baja = 'no'";
$sql_usuarios_desactivados = "SELECT COUNT(*) AS total FROM usuarios WHERE dado_de_baja = 'si'";

$proyectos_activo = $conn->query($sql_proyectos_activos)->fetch_assoc();
$proyectos_dados_de_baja = $conn->query($sql_proyectos_dados_de_baja)->fetch_assoc();
$comentarios_visibles = $conn->query($sql_comentarios_visibles)->fetch_assoc();
$comentarios_ocultos = $conn->query($sql_comentarios_ocultos)->fetch_assoc();
$usuarios_activos = $conn->query($sql_usuarios_activos)->fetch_assoc();
$usuarios_desactivados = $conn->query($sql_usuarios_desactivados)->fetch_assoc();

$sql = "SELECT DATE(fecha_creacion) AS fecha, 
               (SELECT COUNT(*) FROM proyectos p2 WHERE p2.fecha_creacion <= p1.fecha_creacion AND p2.dado_de_baja = 'NO') AS activos, 
               (SELECT COUNT(*) FROM proyectos p2 WHERE p2.fecha_creacion <= p1.fecha_creacion AND p2.dado_de_baja = 'SI') AS inactivos
        FROM proyectos p1
        GROUP BY fecha
        ORDER BY fecha ASC";

$result = $conn->query($sql);

$fechas = [];
$proyectos_activos = [];
$proyectos_inactivos = [];

while ($row = $result->fetch_assoc()) {
    $fechas[] = $row['fecha'];
    $proyectos_activos[] = $row['activos'];
    $proyectos_inactivos[] = $row['inactivos'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativa</title>
    <link rel="stylesheet" href="css/dashboard.css"> <!-- Asegúrate de tener estilos adecuados -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Panel de Administración</h1>

        <!-- Contenedor del gráfico -->
        <div class="chart-container">
            <canvas id="proyectosChart"></canvas>
        </div>

        <!-- Contenedor de la tabla -->
        <div class="table-container">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
    <tr>
        <td><a href="proyectos_activos.php">Proyectos Activos</a></td>
        <td><?php echo $proyectos_activo['total']; ?></td>
    </tr>
    <tr>
        <td><a href="proyectos_inactivos.php">Proyectos Dados de Baja</a></td>
        <td><?php echo $proyectos_dados_de_baja['total']; ?></td>
    </tr>
    <tr>
        <td><a href="comentarios_visibles.php">Comentarios Visibles</a></td>
        <td><?php echo $comentarios_visibles['total']; ?></td>
    </tr>
    <tr>
        <td><a href="comentarios_ocultos.php">Comentarios Ocultos</a></td>
        <td><?php echo $comentarios_ocultos['total']; ?></td>
    </tr>
    <tr>
        <td><a href="cuentas_activas.php">Cuentas Activas</a></td>
        <td><?php echo $usuarios_activos['total']; ?></td>
    </tr>
    <tr>
        <td><a href="cuentas_inactivas.php">Cuentas Desactivadas</a></td>
        <td><?php echo $usuarios_desactivados['total']; ?></td>
    </tr>
</tbody>
            </table>
        </div>
    </div>
</body>
<script>
    const ctx = document.getElementById('proyectosChart').getContext('2d');
    const proyectosChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($fechas); ?>,
        datasets: [
            {
                label: 'Proyectos Activos',
                data: <?php echo json_encode($proyectos_activos); ?>,
                borderColor: 'blue',
                fill: false
            },
            {
                label: 'Proyectos Inactivos',
                data: <?php echo json_encode($proyectos_inactivos); ?>,
                borderColor: 'red',
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                ticks: {
                    stepSize: 1, // Muestra solo números enteros
                    callback: function(value) { return Math.round(value); } // Asegura que no haya decimales
                },
                title: { display: true, text: 'Cantidad de Proyectos' }
            },
            x: {
                title: { display: true, text: 'Fecha' }
            }
        }
    }
});
</script>
</html>