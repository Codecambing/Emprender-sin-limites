<?php
if (isset($_REQUEST['login'])) {
    session_start();
    $correo = $_REQUEST['correo'] ?? '';
    $password = $_REQUEST['password'] ?? '';

    include_once "servicios/conexion.php";
    $conn = mysqli_connect($servername, $username, $password_db, $dbname);

    // Verificar si la conexión a la base de datos es exitosa
    if (!$conn) {
        die("Error en la conexión a la base de datos.");
    }

    // Consulta para obtener el usuario y la contraseña
    $query = "SELECT id_usuario, correo, nombre, password FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_usuario, $correo_bd, $nombre_bd, $password_bd);

    // Verificar si el usuario existe y comparar las contraseñas
    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        // Comparar la contraseña en texto plano con la guardada en la base de datos
        if ($password === $password_bd) {
            // Si las contraseñas coinciden, se inicia la sesión
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['correo'] = $correo_bd;
            $_SESSION['nombre'] = $nombre_bd;
            header("location: inicio.php");
        } else {
            // Si la contraseña no es correcta
            echo "<script>
                    alert('Correo o contraseña incorrectos.');
                    window.location.href = 'login.php';
                  </script>";
        }
    } else {
        // Si el correo no existe en la base de datos
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location.href = 'login.php';
              </script>";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
?>
