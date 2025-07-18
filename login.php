<?php
include_once("servicios/iniciosesion.php");
  ?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" href="images/logo.jpg">
    <link rel="stylesheet" href="css/login.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicia sesión - Emprender sin limites</title>
</head>

<body>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="login.php" method="POST" autocomplete="off">
                    <h2>Emprender sin limites</h2>
                    <br>
                <input type="hidden" name="proceso">
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="correo" name="correo" id="correo" required>
                        <label for="correo">Correo electronico</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" id="password" required>
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="forget">
                        <label for=""><input type="checkbox" value="">Recordarme <a href="recuperar_contraseña.php">¿Olvidaste tu
                                contraseña? </a></label>
                    </div>
                    <button type="submit" name="login">Iniciar Sesión</button>
                    <div class="register">
                        <p>No tienes una cuenta? <a href="register.php">Registrate!</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>