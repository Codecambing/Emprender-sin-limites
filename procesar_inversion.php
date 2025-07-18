<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];

// Verificar si se ha enviado el formulario de inversión
if (isset($_POST['id_proyecto'], $_POST['monto'], $_POST['tipo_transaccion'])) {
    $id_proyecto = intval($_POST['id_proyecto']);
    $monto = intval($_POST['monto']);
    $tipo_transaccion = intval($_POST['tipo_transaccion']);

    // Verificar si el monto es válido
    if ($monto <= 0) {
        die("El monto debe ser mayor a cero.");
    }

    // Verificar si el usuario ya ha invertido en este proyecto
    $sql = "SELECT COUNT(*) FROM inversion_proyectos WHERE usuarios_id_usuario = ? AND proyectos_id_proyectos = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario_actual, $id_proyecto);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();

    if ($row[0] > 0) {
        die("Ya has invertido en este proyecto.");
    }

    // Determinar si el usuario está invirtiendo sin recompensa
    $recompensa = isset($_POST['recompensa']) ? $_POST['recompensa'] : 'sin recompensa';  // Si no hay recompensa, asignar "sin recompensa"
    
    // Generar el número de transacción (usamos uniqid para hacer el número único)
    $numero_transaccion = uniqid("TRX_", true);  // Prefijo "TRX_" seguido de un identificador único

    // Obtener la fecha actual
    $fecha_contribucion = date('Y-m-d H:i:s'); // Formato de fecha en MySQL (YYYY-MM-DD HH:MM:SS)

    // Inserción de la inversión en la tabla inversion_proyectos
    $sql = "INSERT INTO inversion_proyectos (usuarios_id_usuario, proyectos_id_proyectos, monto, recompensa, tipo_transaccion_id_tipo_transaccion, numero_transaccion, fecha_contribucion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $id_usuario_actual, $id_proyecto, $monto, $recompensa, $tipo_transaccion, $numero_transaccion, $fecha_contribucion);
    
    if ($stmt->execute()) {
        // Ahora obtenemos el valor actual de cantidad_invertida
        $sql_actualizacion = "SELECT cantidad_invertida FROM proyectos WHERE id_proyectos = ?";
        $stmt_actualizacion = $conn->prepare($sql_actualizacion);
        $stmt_actualizacion->bind_param("i", $id_proyecto);
        $stmt_actualizacion->execute();
        $stmt_actualizacion->bind_result($cantidad_invertida_actual);
        $stmt_actualizacion->fetch(); // Procesar los resultados

        // Asegurarse de liberar el resultado antes de ejecutar otra consulta
        $stmt_actualizacion->free_result();

        // Sumar el monto de inversión a la cantidad actual invertida
        $nueva_cantidad_invertida = $cantidad_invertida_actual + $monto;

        // Actualizar la cantidad invertida en la tabla proyectos
        $sql_update = "UPDATE proyectos SET cantidad_invertida = ? WHERE id_proyectos = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("di", $nueva_cantidad_invertida, $id_proyecto);
        $stmt_update->execute();

        // Redirigir al usuario o mostrar un mensaje de éxito
        echo "<script>
            alert('Procesando pago...');
            setTimeout(() => {
                alert('Inversión confirmada');
                window.location.href = 'inversiones_personales.php';
            }, 3000);
          </script>";
        exit();
    } else {
        die("Error al procesar la inversión.");
    }
} else {
    die("Datos incompletos.");
}
?>
