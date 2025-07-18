<?php
if (isset($_REQUEST['registro'])) {
    include_once "servicios/conexion.php";
    $conn = mysqli_connect($servername, $username, $password_db, $dbname);

    // Sanear los datos con mysqli_real_escape_string (aunque con prepared statements no es necesario, es recomendable)
    $correo = $_REQUEST['correo'] ?? '';
    $password = $_REQUEST['password'] ?? '';
    $nombre = $_REQUEST['nombre'] ?? '';
    $nombreusuario = $_REQUEST['nombre_usuario'] ?? '';
    $telefono = $_REQUEST['telefono'] ?? '';

    // Validar que solo contenga números después de +569
    if (!preg_match('/^\+569[0-9]{8}$/', $telefono)) {
        echo "<script>alert('Número de teléfono inválido. Debe comenzar con +569 y contener 8 números adicionales.'); window.history.back();</script>";
        exit();
    }

    // Verificar si el correo ya existe con una consulta preparada
    $checkEmailQuery = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $correo); // "s" indica que estamos pasando un parámetro string
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Si el correo ya existe, mostrar mensaje de error
        echo "<script>
            alert('El correo ya está registrado. Por favor, usa otro correo.');
            window.history.back();
          </script>";
    } else {
        // Si el correo no existe, proceder con la inserción usando una consulta preparada
        // Usar password_hash para asegurar que la contraseña esté encriptada
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Preparar la consulta para insertar los datos del nuevo usuario
        // Aquí se añade 'Usuario' directamente al campo privilegio
        $query = "INSERT INTO usuarios (correo, password, nombre, nombre_usuario, telefono, privilegio) VALUES (?, ?, ?, ?, ?, 'Usuario')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $correo, $password, $nombre, $nombreusuario, $telefono);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Si el registro es exitoso, redirigir al login
            echo "<script>
            alert('Usuario creado correctamente! Ahora puedes iniciar sesión.');
            window.location.href = 'login.php';
          </script>";
        } else {
            // Mostrar error en caso de fallo en la consulta
            echo "<div class='alert alert-danger' role='alert'>
                    Error al crear usuario: " . mysqli_error($conn) . "
                  </div>";
        }
    }

    // Cerrar la conexión y la sentencia preparada
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="images/logo.jpg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/register.css">
    <title>Registro Cliente</title>
</head>

<body>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="register.php" method="POST" class="formulario" autocomplete="off">
                    <h2 class="titulo">Registro de Usuario</h2>
                    <div class="inputbox">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <input type="text" name="nombre" id="nombre" required>
                        <label for="nombre">Nombre completo</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="correo" name="correo" id="correo" required>
                        <span id="validaEmail" class="texto-span"></span>
                        <label for="correo">Correo electronico</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="person-outline"></ion-icon>
                        <input type="text" name="nombre_usuario" id="nombre_usuario" required>
                        <span id="validaUsuario" class="texto-span"></span>
                        <label for="nombre_usuario">Nombre usuario</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" id="password" required>
                        <label for="password">Contraseña</label>
                    </div>
                    <!-- <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="repassword" id="repassword" required>
                        <label for="repassword">Repetir contraseña</label>
                    </div> -->
                    <div class="inputbox">
                        <ion-icon name="call-outline"></ion-icon>
                        <input type="text" name="telefono" id="telefono" value="+569" maxlength="12" oninput="validarTelefono(this)" required>
                        <label for="tel">Teléfono</label>
                    </div>
                    <div class="forget">
                        <button type="submit" value="Registrar" name="registro">Registrar</button>
                    </div>
                    <div class="alert">
                    </div>
                    <div class="login">
                        <p2>Ya tienes una cuenta? <a href="login.php">Inicia sesion!</a></p2>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector("form");
        const correoInput = document.getElementById("correo");
        const validaEmail = document.getElementById("validaEmail");

        form.addEventListener("submit", function(event) {
            const correo = correoInput.value;
            // Expresión regular para validar el correo
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailRegex.test(correo)) {
                validaEmail.textContent = "Por favor ingrese un correo electrónico válido.";
                event.preventDefault(); // Evita el envío del formulario si el correo no es válido
            } else {
                validaEmail.textContent = ""; // Borra el mensaje de error si el correo es válido
            }
        });
    });
</script>
<script>
    function validarTelefono(input) {
        let valor = input.value;

        // Asegurar que el prefijo sea siempre "+569"
        if (!valor.startsWith("+569")) {
            input.value = "+569";
        }

        // Limitar la cantidad de números después del prefijo
        let numeros = valor.replace("+569", "");
        if (numeros.length > 8) {
            input.value = "+569" + numeros.slice(0, 8);
        }

        // Validar que solo contenga números después del prefijo
        if (isNaN(numeros)) {
            input.value = "+569";
        }
    }
</script>

</html>