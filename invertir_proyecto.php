<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario']; // ID del usuario logueado

// Verificar si se recibió un ID de proyecto
if (!isset($_GET['id'])) {
    die("ID de proyecto no proporcionado.");
}

$id_proyecto = intval($_GET['id']);

// Consultar los datos del proyecto
$sql_proyecto = "SELECT 
                    p.id_proyectos, 
                    p.nombre_proyecto, 
                    p.banner, 
                    p.meta_financiamiento, 
                    p.recompensa_menor,
                    p.recompensa_media,
                    p.recompensa_mayor,
                    p.info_recompensa_menor,
                    p.info_recompensa_media,
                    p.info_recompensa_mayor,
                    p.cantidad_invertida,
                    u.nombre
                FROM proyectos p
                JOIN usuarios u ON p.usuarios_id_usuario = u.id_usuario
                WHERE p.id_proyectos = ?";

$stmt_proyecto = $conn->prepare($sql_proyecto);
$stmt_proyecto->bind_param("i", $id_proyecto);
$stmt_proyecto->execute();
$result_proyecto = $stmt_proyecto->get_result();

if ($result_proyecto->num_rows == 0) {
    die("El proyecto solicitado no existe.");
}

$proyecto = $result_proyecto->fetch_assoc();

// Consultar la suma de las inversiones
$sql_inversiones = "SELECT SUM(ip.monto) AS total_invertido
                    FROM inversion_proyectos ip
                    WHERE ip.proyectos_id_proyectos = ? AND ip.fecha_anulacion IS NULL";


$stmt_inversiones = $conn->prepare($sql_inversiones);
$stmt_inversiones->bind_param("i", $id_proyecto);
$stmt_inversiones->execute();
$result_inversiones = $stmt_inversiones->get_result();
$row_inversiones = $result_inversiones->fetch_assoc();

$total_invertido = $row_inversiones['total_invertido'] ? $row_inversiones['total_invertido'] : 0;

// Actualizamos el valor de 'cantidad_invertida' en el proyecto (aunque este paso puede no ser necesario si ya se está manejando correctamente)
$sql_update = "UPDATE proyectos SET cantidad_invertida = ? WHERE id_proyectos = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("di", $total_invertido, $id_proyecto);
$stmt_update->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invierte en <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></title>
    <link rel="stylesheet" href="css/invertir_proyecto.css">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Mostrar el formulario de recompensa seleccionado
            document.querySelectorAll(".reward-button").forEach((button) => {
                button.addEventListener("click", () => {
                    const formId = button.getAttribute("data-form");
                    document.querySelectorAll(".reward-form").forEach(form => form.style.display = "none");
                    document.getElementById(formId).style.display = "block";
                });
            });

            // Mostrar el formulario para invertir sin recompensa
            const investButton = document.getElementById("show-invest-form");
            const investForm = document.getElementById("form-invest-without-reward");
            investButton.addEventListener("click", () => {
                document.querySelectorAll(".reward-form").forEach(form => form.style.display = "none");
                investForm.style.display = "block";
            });
        });
    </script>
</head>
<body>
<div class="investment-container">
    <h1>Invierte en <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
    <img src="banners/<?= htmlspecialchars($proyecto['banner']); ?>" alt="Banner del proyecto" class="project-banner">

    <p><strong>Creado por:</strong> <?= htmlspecialchars($proyecto['nombre']); ?></p>
    <p><strong>Meta Financiera:</strong> <?= number_format($proyecto['meta_financiamiento'], 0, ',', '.'); ?>$</p>
    <p><strong>Contribuido:</strong> <?= number_format($total_invertido, 0, ',', '.'); ?>$</p>

    <div class="reward-buttons">
        <?php if ($proyecto['recompensa_menor'] > 1): ?>
            <button class="reward-button" data-form="form-reward-menor">Invertir <?= number_format($proyecto['recompensa_menor'], 0, ',', '.'); ?>$ - <?= htmlspecialchars($proyecto['info_recompensa_menor']); ?></button>
            <form action="procesar_inversion.php" method="POST" id="form-reward-menor" class="reward-form" style="display: none;">
                <input type="hidden" name="id_proyecto" value="<?= $id_proyecto; ?>">
                <input type="hidden" name="monto" value="<?= $proyecto['recompensa_menor']; ?>">
                <input type="hidden" name="recompensa" value="Menor"> <!-- Valor de la recompensa -->
                <label for="tipo-transaccion-menor">Tipo de Transacción:</label>
                <select id="tipo-transaccion-menor" name="tipo_transaccion" required>
                    <option value="">Selecciona un tipo</option>
                    <option value="1">Transferencia Bancaria</option>
                    <option value="2">PayPal</option>
                </select>
                <button type="submit" class="invest-button">Confirmar inversión</button>
            </form>
        <?php endif; ?>

        <?php if ($proyecto['recompensa_media'] > 1): ?>
            <button class="reward-button" data-form="form-reward-media">Invertir <?= number_format($proyecto['recompensa_media'], 0, ',', '.'); ?>$ - <?= htmlspecialchars($proyecto['info_recompensa_media']); ?></button>
            <form action="procesar_inversion.php" method="POST" id="form-reward-media" class="reward-form" style="display: none;">
                <input type="hidden" name="id_proyecto" value="<?= $id_proyecto; ?>">
                <input type="hidden" name="monto" value="<?= $proyecto['recompensa_media']; ?>">
                <input type="hidden" name="recompensa" value="Media"> <!-- Valor de la recompensa -->
                <label for="tipo-transaccion-media">Tipo de Transacción:</label>
                <select id="tipo-transaccion-media" name="tipo_transaccion" required>
                    <option value="">Selecciona un tipo</option>
                    <option value="1">Transferencia Bancaria</option>
                    <option value="2">PayPal</option>
                </select>
                <button type="submit" class="invest-button">Confirmar inversión</button>
            </form>
        <?php endif; ?>

        <?php if ($proyecto['recompensa_mayor'] > 1): ?>
            <button class="reward-button" data-form="form-reward-mayor">Invertir <?= number_format($proyecto['recompensa_mayor'], 0, ',', '.'); ?>$ - <?= htmlspecialchars($proyecto['info_recompensa_mayor']); ?></button>
            <form action="procesar_inversion.php" method="POST" id="form-reward-mayor" class="reward-form" style="display: none;">
                <input type="hidden" name="id_proyecto" value="<?= $id_proyecto; ?>">
                <input type="hidden" name="monto" value="<?= $proyecto['recompensa_mayor']; ?>">
                <input type="hidden" name="recompensa" value="Mayor"> <!-- Valor de la recompensa -->
                <label for="tipo-transaccion-mayor">Tipo de Transacción:</label>
                <select id="tipo-transaccion-mayor" name="tipo_transaccion" required>
                    <option value="">Selecciona un tipo</option>
                    <option value="1">Transferencia Bancaria</option>
                    <option value="2">PayPal</option>
                </select>
                <button type="submit" class="invest-button">Confirmar inversión</button>
            </form>
        <?php endif; ?>
        <!-- Lo mismo para las recompensas media y mayor -->

        <button class="invest-without-reward" id="show-invest-form">Invertir sin recompensa</button>
        <form action="procesar_inversion.php" method="POST" id="form-invest-without-reward" class="investment-form" style="display: none;">
            <input type="hidden" name="id_proyecto" value="<?= $id_proyecto; ?>">
            <label for="monto">Cantidad a Invertir:</label>
            <input type="number" id="monto" name="monto" min="1" required>
            <label for="tipo-transaccion">Tipo de Transacción:</label>
            <select id="tipo-transaccion" name="tipo_transaccion" required>
                <option value="">Selecciona un tipo</option>
                <option value="1">Transferencia Bancaria</option>
                <option value="2">PayPal</option>
            </select>
            <button type="submit" class="invest-button">Confirmar inversión</button>
        </form>
    </div>
</div>
</body>
</html>
