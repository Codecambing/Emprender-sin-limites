<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del proyecto desde la URL
if (!isset($_GET['id'])) {
    die("ID del proyecto no proporcionado.");
}

$id_proyecto = intval($_GET['id']);

// Consultar los datos del proyecto
$sql = "SELECT 
            nombre_proyecto, 
            banner, 
            fecha_limite, 
            cantidad_invertida, 
            meta_financiamiento
        FROM proyectos
        WHERE id_proyectos = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("El proyecto solicitado no existe.");
}

$proyecto = $result->fetch_assoc();

// Consulta para obtener la cantidad invertida
$sql_cantidad_invertida = "SELECT cantidad_invertida FROM proyectos WHERE id_proyectos = ?";
$stmt_cantidad = $conn->prepare($sql_cantidad_invertida);
$stmt_cantidad->bind_param("i", $id_proyecto);
$stmt_cantidad->execute();
$result_cantidad = $stmt_cantidad->get_result();

if ($result_cantidad->num_rows == 0) {
    die("El proyecto no existe o no tiene contribuciones.");
}

$proyecto_data = $result_cantidad->fetch_assoc();
$cantidad_invertida = $proyecto_data['cantidad_invertida'];

// Calcular el monto post comisión
$comision = $cantidad_invertida * 0.02; // 2% de comisión
$monto_post_comision = $cantidad_invertida - $comision;


// Consulta para obtener los tipos de cuenta
$sql_tipos_cuenta = "SELECT id_tipo_cuenta_bancaria, nombre_tipo_cuenta_bancaria FROM tipo_cuenta_bancaria";
$result_tipos_cuenta = $conn->query($sql_tipos_cuenta);

if (!$result_tipos_cuenta) {
    die("Error al obtener tipos de cuenta: " . $conn->error);
}

// Convertir los resultados a un arreglo
$tipos_cuenta = $result_tipos_cuenta->fetch_all(MYSQLI_ASSOC);

// Obtener la lista de bancos
$sql_bancos = "SELECT id_bancos_solicitud, nombre_banco FROM bancos_retiro";
$result_bancos = $conn->query($sql_bancos);
$bancos = $result_bancos->fetch_all(MYSQLI_ASSOC);

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut_transferencia = $_POST['rut_transferencia'];
    $nombre_transferencia = $_POST['nombre_transferencia'];
    $correo_transferencia = $_POST['correo_transferencia'];
    $banco_transferencia = intval($_POST['banco_transferencia']);
    $nombre_tipo_cuenta_transferencia = $_POST['nombre_tipo_cuenta_transferencia'];
    $numero_cuenta_transferencia = $_POST['numero_cuenta_transferencia'];
    $id_usuario = $_SESSION['id_usuario'];

    $nombre_tipo_cuenta_transferencia = intval($_POST['nombre_tipo_cuenta_transferencia']); // ID del tipo de cuenta

if ($nombre_tipo_cuenta_transferencia <= 0) {
    die("Tipo de cuenta inválido.");
}


    // Insertar los datos en la tabla solicitudes_retiro
    // Modificar la consulta para incluir el monto post comisión
$sql_insert = "INSERT INTO solicitudes_retiro 
(proyectos_id_proyecto, usuarios_id_usuario, rut_transferencia, nombre_transferencia, correo_transferencia, banco_transferencia, nombre_tipo_cuenta_bancaria, numero_cuenta_transferencia, monto, fecha_solicitud) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param(
"iisssssdi", 
$id_proyecto, 
$id_usuario, 
$rut_transferencia, 
$nombre_transferencia, 
$correo_transferencia, 
$banco_transferencia, 
$nombre_tipo_cuenta_transferencia, 
$numero_cuenta_transferencia, 
$monto_post_comision
);

if ($stmt_insert->execute()) {
echo "<script>alert('Solicitud de retiro enviada exitosamente.'); window.location.href='mis_proyectos.php';</script>";
} else {
echo "<script>alert('Error al enviar la solicitud. Por favor, intente nuevamente.');</script>";
}
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Retiro - <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></title>
    <link rel="stylesheet" href="css/solicitar_retiro.css">
</head>
<body>
<div class="project-info">
    <h1><?= htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
    <img src="banners/<?= htmlspecialchars($proyecto['banner']); ?>" alt="Banner del proyecto" class="project-banner">
    <p><strong>Fecha Límite:</strong> <?= date("d/m/Y", strtotime($proyecto['fecha_limite'])); ?></p>
    <p><strong>Total Recaudado:</strong> <?= number_format($proyecto['cantidad_invertida'], 0, ',', '.'); ?>$</p>
    <p><strong>Meta Financiera:</strong> <?= number_format($proyecto['meta_financiamiento'], 0, ',', '.'); ?>$</p>
    <p><strong>Monto a Retirar (Post Comisión):</strong> <?= number_format($monto_post_comision, 0, ',', '.'); ?>$</p>
</div>

<div class="withdraw-form">
    <h2>Formulario para Solicitar Retiro</h2>
    <form method="POST">
        <label for="rut_transferencia">RUT de la Cuenta Bancaria:</label>
        <input type="text" id="rut_transferencia" name="rut_transferencia" required>

        <label for="nombre_transferencia">Nombre y apellido de la Cuenta:</label>
        <input type="text" id="nombre_transferencia" name="nombre_transferencia" required>

        <label for="correo_transferencia">Correo de Transferencia:</label>
        <input type="email" id="correo_transferencia" name="correo_transferencia" required>

        <label for="banco_transferencia">Banco:</label>
        <select id="banco_transferencia" name="banco_transferencia" required>
            <option value="" disabled selected>Seleccione un banco</option>
            <?php foreach ($bancos as $banco): ?>
                <option value="<?= $banco['id_bancos_solicitud'] ?>"><?= htmlspecialchars($banco['nombre_banco']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="nombre_tipo_cuenta_transferencia">Tipo de Cuenta:</label>
<select id="nombre_tipo_cuenta_transferencia" name="nombre_tipo_cuenta_transferencia" required>
    <option value="" disabled selected>Seleccione un tipo de cuenta</option>
    <?php foreach ($tipos_cuenta as $tipo): ?>
        <option value="<?= $tipo['id_tipo_cuenta_bancaria'] ?>">
            <?= htmlspecialchars($tipo['nombre_tipo_cuenta_bancaria']) ?>
        </option>
    <?php endforeach; ?>
</select>
        <label for="numero_cuenta_transferencia">Número de Cuenta:</label>
        <input type="text" id="numero_cuenta_transferencia" name="numero_cuenta_transferencia" required>

        <button type="submit">Enviar Solicitud</button>
    </form>
</div>
</body>
</html>
