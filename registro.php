
<?php

// Incluye las funciones necesarias para manejar las sesiones y la autenticación.
require_once __DIR__ . '/includes/funciones.php';

// Incluye la conexión con la base de datos MySQL.
require_once __DIR__ . '/includes/conexion.php';

// Si el usuario ya inició sesión, lo redirige a la página de reportes.
if (usuarioAutenticado()) {
    header('Location: reportes.php');
    exit;
}

// Variable que almacena los mensajes de error del registro.
$error = '';

// Comprueba si el formulario fue enviado mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene y limpia los datos ingresados.
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Comprueba que los campos obligatorios no estén vacíos.
    if ($nombre === '' || $correo === '' || $contrasena === '') {
        $error = 'Completa todos los campos.';

    // Comprueba que el correo tenga un formato válido.
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';

    // Comprueba que la contraseña tenga al menos 6 caracteres.
    } elseif (strlen($contrasena) < 6) {
        $error = 'La contraseña debe tener mínimo 6 caracteres.';

    // Comprueba que ambas contraseñas sean iguales.
    } elseif ($contrasena !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';

    } else {

        try {

            // Comprueba si el correo ya está registrado en MySQL.
            $consulta = $conexion->prepare(
                "SELECT id FROM usuarios WHERE correo = :correo"
            );

            $consulta->execute([
                ':correo' => $correo
            ]);

            // Si encuentra un usuario, significa que el correo ya existe.
            if ($consulta->fetch()) {

                $error = 'Este correo ya está registrado.';

            } else {

                // Convierte la contraseña en un hash seguro.
                $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);

                // Inserta el nuevo usuario en la base de datos.
                $insertar = $conexion->prepare(
                    "INSERT INTO usuarios (nombre, correo, password, rol)
                     VALUES (:nombre, :correo, :password, 'usuario')"
                );

                $insertar->execute([
                    ':nombre' => $nombre,
                    ':correo' => $correo,
                    ':password' => $passwordHash
                ]);

                // Redirige al inicio de sesión indicando que el registro fue exitoso.
                header('Location: index.php?registro=exitoso');
                exit;
            }

        } catch (PDOException $e) {

            // Muestra un mensaje si ocurre un problema con la base de datos.
            $error = 'No se pudo registrar el usuario. Inténtalo nuevamente.';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro | Andén Alerta</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <h1>Crear cuenta</h1>
        <p>Únete a Andén Alerta Cartagena</p>

        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label for="nombre">Nombre completo</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="correo">Correo electrónico</label>
            <input type="email" name="correo" id="correo" required>

            <label for="contrasena">Contraseña</label>
            <input type="password" name="contrasena" id="contrasena" minlength="6" required>

            <label for="confirmar">Confirmar contraseña</label>
            <input type="password" name="confirmar" id="confirmar" required>

            <button type="submit" class="btn-primary">
                Registrarme
            </button>

        </form>

        <p class="auth-link">
            ¿Ya tienes cuenta?
            <a href="index.php">Inicia sesión</a>
        </p>

    </div>
</div>

</body>
</html>
