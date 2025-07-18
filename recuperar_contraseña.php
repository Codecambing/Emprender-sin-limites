<?php
include_once("servicios/iniciosesion.php");
  ?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" href="images/logo.jpg">
    <link rel="stylesheet" href="css/recuperar_contraseña.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña - Emprender sin limites</title>
</head>

<body>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="login.php" method="POST" autocomplete="off">
                    <h2>Recuperar contraseña</h2>
                    <br>
                <input type="hidden" name="proceso">
                <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" id="password" required>
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" id="password" required>
                        <label for="password">Repita nuevamente la contraseña</label>
                    </div>
                    <button type="submit" name="login">Cambiar contraseña</button>
                    <div class="register">
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>