
<?php
// Incluye el archivo que contiene las funciones de autenticación y manejo de sesión.
require_once __DIR__ . '/includes/funciones.php';

// Comprueba si el usuario ya inició sesión.
// Si está autenticado, lo envía directamente a la página de reportes.
if (usuarioAutenticado()) {
    header('Location: reportes.php');
    exit; // Detiene la ejecución del código.
}

// Variable que almacenará los mensajes de error del inicio de sesión.
$error = '';

// Comprueba si el formulario fue enviado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene el correo ingresado, elimina espacios y lo convierte a minúsculas.
    $correo = strtolower(trim($_POST['correo'] ?? ''));

    // Obtiene la contraseña ingresada por el usuario.
    $contrasena = $_POST['contrasena'] ?? '';

    // Comprueba si los datos corresponden al administrador predeterminado.
    if ($correo === 'admin@anden.com' && $contrasena === 'admin123') {

        // Guarda los datos del administrador en la sesión.
        $_SESSION['usuario'] = [
            'id' => 1,
            'nombre' => 'Administrador',
            'correo' => $correo,
            'rol' => 'admin'
        ];

        // Redirige al administrador al panel de reportes.
        header('Location: admin/reportes.php');
        exit;
    }

    // Define la ubicación del archivo JSON donde se guardan los usuarios registrados.
    $archivo = __DIR__ . '/data/usuarios.json';

    // Comprueba si el archivo de usuarios existe.
    if (file_exists($archivo)) {

        // Lee el contenido del archivo JSON y lo convierte en un arreglo de PHP.
        // Si no hay información válida, utiliza un arreglo vacío.
        $usuarios = json_decode(file_get_contents($archivo), true) ?? [];

        // Recorre todos los usuarios registrados para buscar una coincidencia.
        foreach ($usuarios as $usuario) {

            // Compara el correo ingresado y verifica la contraseña cifrada.
            if (
                strtolower($usuario['correo']) === $correo &&
                password_verify($contrasena, $usuario['contrasena'])
            ) {

                // Guarda los datos del usuario en la sesión.
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => 'usuario'
                ];

                // Si los datos son correctos, dirige al usuario a los reportes.
                header('Location: reportes.php');
                exit;
            }
        }
    }

    // Si no se encontró una cuenta con esos datos, muestra un mensaje de error.
    $error = 'Correo o contraseña incorrectos.';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Define la codificación para mostrar correctamente los caracteres y tildes. -->
    <meta charset="UTF-8">

    <!-- Permite que la página se adapte al tamaño de pantalla de cada dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título que aparece en la pestaña del navegador. -->
    <title>Iniciar sesión | Andén Alerta Cartagena</title>

    <!-- Conecta el archivo CSS encargado del diseño y los estilos de la página. -->
    <link rel="stylesheet" href="css/style.css">
</head>

<!-- Clase utilizada para aplicar los estilos específicos de la página de inicio de sesión. -->
<body class="login-page">

<!-- Contenedor principal que organiza los dos paneles de la pantalla. -->
<div class="login-layout">

    <!-- Panel izquierdo: contiene la identidad y presentación del proyecto. -->
    <section class="login-brand">

        <div class="brand-content">

            <!-- Espacio destinado al logotipo de Andén Alerta. -->
            <div class="brand-icon">
                <img src="img/logo.png" alt="Logo Andén Alerta">
            </div>

            <!-- Nombre principal de la plataforma. -->
            <h1>Andén Alerta</h1>

            <!-- Ciudad donde se desarrolla el proyecto. -->
            <span class="brand-location">CARTAGENA</span>

            <!-- Elemento decorativo que funciona como separador visual. -->
            <div class="brand-line"></div>

            <!-- Frase principal que representa el propósito de la plataforma. -->
            <h2>Tu ciudad también se construye caminando.</h2>

            <!-- Descripción breve de la función de la página web. -->
            <p>
                Reporta daños en andenes, obstáculos y problemas
                de movilidad peatonal. Hagamos visible lo que
                necesita mejorar.
            </p>

        </div>

        <!-- Texto ubicado en la parte inferior del panel izquierdo. -->
        <div class="brand-footer">
            ANDÉN ALERTA CARTAGENA · 2026
        </div>

    </section>

    <!-- Panel derecho: contiene el formulario para iniciar sesión. -->
    <section class="login-panel">

        <!-- Tarjeta que agrupa los elementos del formulario. -->
        <div class="login-card">

            <!-- Texto de bienvenida que aparece encima del formulario. -->
            <span class="login-eyebrow">BIENVENIDO</span>

            <!-- Título del formulario. -->
            <h2>Iniciar sesión</h2>

            <!-- Instrucción para el usuario. -->
            <p class="login-description">
                Ingresa tus datos para continuar.
            </p>

            <!-- Comprueba si existe un mensaje de error para mostrarlo en pantalla. -->
            <?php if ($error): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario que envía los datos al mismo archivo mediante POST. -->
            <form method="POST">

                <!-- Etiqueta y campo para ingresar el correo electrónico. -->
                <label for="correo">Correo electrónico</label>
                <input
                    type="email"
                    name="correo"
                    id="correo"
                    placeholder="ejemplo@correo.com"
                    required
                >

                <!-- Etiqueta y campo para ingresar la contraseña. -->
                <label for="contrasena">Contraseña</label>
                <input
                    type="password"
                    name="contrasena"
                    id="contrasena"
                    placeholder="Ingresa tu contraseña"
                    required
                >

                <!-- Botón que envía el formulario para validar las credenciales. -->
                <button type="submit" class="login-submit">
                    Iniciar sesión
                </button>

            </form>

            <!-- Enlace que dirige al formulario de creación de una cuenta. -->
            <div class="login-register">
                ¿Todavía no tienes una cuenta?
                <a href="registro.php">Regístrate aquí</a>
            </div>

        </div>

        <!-- Texto informativo ubicado debajo de la tarjeta de inicio de sesión. -->
        <p class="login-copyright">
            Una iniciativa comunitaria para mejorar la movilidad peatonal.
        </p>

    </section>

</div>

</body>
</html>
