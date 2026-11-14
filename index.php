
<?php

// Incluye las funciones necesarias para manejar las sesiones y la autenticación.
require_once __DIR__ . '/includes/funciones.php';

// Incluye la conexión con la base de datos MySQL.
require_once __DIR__ . '/includes/conexion.php';

// Comprueba si el usuario ya inició sesión.
if (usuarioAutenticado()) {

    // Comprueba el rol del usuario para dirigirlo a su página correspondiente.
    if ($_SESSION['usuario']['rol'] === 'admin') {
        header('Location: admin/reportes.php');
    } else {
        header('Location: reportes.php');
    }

    exit;
}

// Variable que almacena los mensajes de error.
$error = '';

// Comprueba si el formulario fue enviado mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene el correo ingresado y lo convierte a minúsculas.
    $correo = strtolower(trim($_POST['correo'] ?? ''));

    // Obtiene la contraseña ingresada.
    $contrasena = $_POST['contrasena'] ?? '';

    // Comprueba que los campos no estén vacíos.
    if ($correo === '' || $contrasena === '') {

        $error = 'Completa todos los campos.';

    } else {

        try {

            // Busca al usuario en la base de datos mediante su correo.
            $consulta = $conexion->prepare(
                "SELECT id, nombre, correo, password, rol
                 FROM usuarios
                 WHERE correo = :correo
                 LIMIT 1"
            );

            $consulta->execute([
                ':correo' => $correo
            ]);

            // Obtiene los datos de la cuenta encontrada.
            $usuario = $consulta->fetch();

            // Comprueba si existe el usuario y si la contraseña es correcta.
            if ($usuario && password_verify($contrasena, $usuario['password'])) {

                // Regenera el identificador de sesión por seguridad.
                session_regenerate_id(true);

                // Guarda los datos del usuario en la sesión.
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => $usuario['rol']
                ];

                // Redirige según el rol de la cuenta.
                if ($usuario['rol'] === 'admin') {
                    header('Location: admin/reportes.php');
                } else {
                    header('Location: reportes.php');
                }

                exit;

            } else {

                // Muestra un mensaje si los datos son incorrectos.
                $error = 'Correo o contraseña incorrectos.';

            }

        } catch (PDOException $e) {

            // Informa si ocurre un problema con la base de datos.
            $error = 'No se pudo iniciar sesión. Inténtalo nuevamente.';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | Andén Alerta Cartagena</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">

<div class="login-layout">

    <section class="login-brand">

        <div class="brand-content">

            <div class="brand-icon">
                <img src="img/logo.png" alt="Logo Andén Alerta">
            </div>

            <h1>Andén Alerta</h1>

            <span class="brand-location">CARTAGENA</span>

            <div class="brand-line"></div>

            <h2>Tu ciudad también se construye caminando.</h2>

            <p>
                Reporta daños en andenes, obstáculos y problemas
                de movilidad peatonal. Hagamos visible lo que
                necesita mejorar.
            </p>

        </div>

        <div class="brand-footer">
            ANDÉN ALERTA CARTAGENA · 2026
        </div>

    </section>

    <section class="login-panel">

        <div class="login-card">

            <span class="login-eyebrow">BIENVENIDO</span>

            <h2>Iniciar sesión</h2>

            <p class="login-description">
                Ingresa tus datos para continuar.
            </p>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                <div class="alert-success">
                    Tu cuenta fue creada correctamente. Ahora puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <label for="correo">Correo electrónico</label>
                <input
                    type="email"
                    name="correo"
                    id="correo"
                    placeholder="ejemplo@correo.com"
                    required
                >

                <label for="contrasena">Contraseña</label>
                <input
                    type="password"
                    name="contrasena"
                    id="contrasena"
                    placeholder="Ingresa tu contraseña"
                    required
                >

                <button type="submit" class="login-submit">
                    Iniciar sesión
                </button>

            </form>

            <div class="login-register">
                ¿Todavía no tienes una cuenta?
                <a href="registro.php">Regístrate aquí</a>
            </div>

        </div>

        <p class="login-copyright">
            Una iniciativa comunitaria para mejorar la movilidad peatonal.
        </p>

    </section>

</div>

</body>
</html>
