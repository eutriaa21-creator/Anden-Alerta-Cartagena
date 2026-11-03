
<?php
// Incluye las funciones necesarias para manejar las sesiones y la autenticación.
require_once __DIR__ . '/includes/funciones.php';

// Si el usuario ya inició sesión, lo redirige a la página de reportes.
if (usuarioAutenticado()) {
    header('Location: reportes.php');
    exit;
}

// Variable que almacena los mensajes de error del registro.
$error = '';

// Comprueba si el formulario fue enviado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene el nombre ingresado y elimina los espacios al principio y al final.
    $nombre = trim($_POST['nombre'] ?? '');

    // Obtiene el correo, elimina espacios y convierte las letras a minúsculas.
    $correo = strtolower(trim($_POST['correo'] ?? ''));

    // Obtiene la contraseña ingresada por el usuario.
    $contrasena = $_POST['contrasena'] ?? '';

    // Obtiene la contraseña escrita en el campo de confirmación.
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

        // Define la ruta de la carpeta donde se guardan los archivos JSON.
        $carpeta = __DIR__ . '/data';

        // Si la carpeta no existe, la crea.
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0775, true);
        }

        // Define la ruta del archivo que almacena los usuarios registrados.
        $archivo = $carpeta . '/usuarios.json';

        // Si el archivo no existe, lo crea con un arreglo JSON vacío.
        if (!file_exists($archivo)) {
            file_put_contents($archivo, '[]');
        }

        // Lee los usuarios guardados y convierte el contenido JSON en un arreglo de PHP.
        // Si no hay información válida, utiliza un arreglo vacío.
        $usuarios = json_decode(file_get_contents($archivo), true) ?? [];

        // Variable que indica si el correo ya está registrado.
        $existe = false;

        // Recorre todos los usuarios para comprobar si el correo ya existe.
        foreach ($usuarios as $usuario) {

            // Compara el correo ingresado con los correos registrados.
            if (strtolower($usuario['correo']) === $correo) {

                // Indica que el correo ya pertenece a otra cuenta.
                $existe = true;

                // Detiene el recorrido porque ya encontró una coincidencia.
                break;
            }
        }

        // Si el correo ya existe, muestra un mensaje de error.
        if ($existe) {
            $error = 'Este correo ya está registrado.';

        } else {

            // Agrega el nuevo usuario al arreglo de usuarios.
            $usuarios[] = [
                // Asigna un identificador al nuevo usuario.
                'id' => count($usuarios) + 1,

                // Guarda el nombre completo del usuario.
                'nombre' => $nombre,

                // Guarda el correo electrónico.
                'correo' => $correo,

                // Convierte la contraseña en un hash para no almacenarla en texto plano.
                'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),

                // Define el rol de la cuenta como usuario normal.
                'rol' => 'usuario'
            ];

            // Convierte el arreglo de usuarios a formato JSON y lo guarda en el archivo.
            // JSON_PRETTY_PRINT organiza el contenido para facilitar su lectura.
            // JSON_UNESCAPED_UNICODE permite conservar correctamente las tildes y caracteres especiales.
            // LOCK_EX evita que otros procesos escriban en el archivo al mismo tiempo.
            $guardado = file_put_contents(
                $archivo,
                json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );

            // Comprueba si el usuario se guardó correctamente.
            if ($guardado !== false) {

                // Redirige al inicio de sesión indicando que el registro fue exitoso.
                header('Location: index.php?registro=exitoso');
                exit;

            } else {

                // Muestra un mensaje si ocurrió un problema al guardar los datos.
                $error = 'No se pudo guardar el usuario.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Define la codificación para mostrar correctamente tildes y caracteres especiales. -->
    <meta charset="UTF-8">

    <!-- Permite que el diseño se adapte a celulares, tabletas y computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título que aparece en la pestaña del navegador. -->
    <title>Registro | Andén Alerta</title>

    <!-- Conecta el archivo CSS que contiene los estilos de la página. -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- Contenedor principal que centra el formulario de registro en la pantalla. -->
<div class="auth-container">

    <!-- Tarjeta que agrupa todos los elementos del formulario. -->
    <div class="auth-card">

        <!-- Título y descripción de la página de registro. -->
        <h1>Crear cuenta</h1>
        <p>Únete a Andén Alerta Cartagena</p>

        <!-- Si existe un error, lo muestra encima del formulario. -->
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario que envía los datos al mismo archivo mediante POST. -->
        <form method="POST">

            <!-- Campo para ingresar el nombre completo del ciudadano. -->
            <label for="nombre">Nombre completo</label>
            <input type="text" name="nombre" id="nombre" required>

            <!-- Campo para ingresar el correo electrónico. -->
            <label for="correo">Correo electrónico</label>
            <input type="email" name="correo" id="correo" required>

            <!-- Campo para ingresar una contraseña de mínimo 6 caracteres. -->
            <label for="contrasena">Contraseña</label>
            <input type="password" name="contrasena" id="contrasena" minlength="6" required>

            <!-- Campo para confirmar que la contraseña fue escrita correctamente. -->
            <label for="confirmar">Confirmar contraseña</label>
            <input type="password" name="confirmar" id="confirmar" required>

            <!-- Botón que envía los datos para crear la cuenta. -->
            <button type="submit" class="btn-primary">
                Registrarme
            </button>

        </form>

        <!-- Enlace que permite regresar a la página de inicio de sesión. -->
        <p class="auth-link">
            ¿Ya tienes cuenta?
            <a href="index.php">Inicia sesión</a>
        </p>

    </div>
</div>

</body>
</html>
