
<?php

// Incluye las funciones necesarias para controlar las sesiones y los usuarios.
require_once __DIR__ . '/includes/funciones.php';

// Incluye la conexión con la base de datos MySQL.
require_once __DIR__ . '/includes/conexion.php';

// Comprueba que el ciudadano haya iniciado sesión.
requiereLogin();

// Si el usuario es administrador, lo redirige al panel administrativo.
if (esAdmin()) {
    header('Location: admin/reportes.php');
    exit;
}

// Obtiene las categorías disponibles desde MySQL.
$consultaCategorias = $conexion->query(
    "SELECT id, nombre FROM categorias ORDER BY nombre ASC"
);

$categorias = $consultaCategorias->fetchAll();

// Variable que almacena los mensajes de error del formulario.
$error = '';

// Comprueba si el formulario fue enviado mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene la categoría seleccionada.
    $categoriaId = filter_input(INPUT_POST, 'categoria', FILTER_VALIDATE_INT);

    // Obtiene el sector y la descripción.
    $sector = trim($_POST['sector'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Comprueba que la categoría seleccionada exista en MySQL.
    $categoriaValida = false;

    foreach ($categorias as $categoria) {
        if ((int)$categoria['id'] === (int)$categoriaId) {
            $categoriaValida = true;
            break;
        }
    }

    if (!$categoriaValida) {
        $error = 'Selecciona una categoría válida.';

    } elseif ($sector === '' || $descripcion === '') {
        $error = 'Completa todos los campos obligatorios.';

    } else {

        // Variable que almacena el nombre de la fotografía.
        $fotoNombre = null;

        // Comprueba si se seleccionó una fotografía.
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

            // Comprueba si ocurrió algún error al subir la fotografía.
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = 'No se pudo subir la fotografía.';

            } else {

                // Detecta el tipo MIME real del archivo.
                $tipo = mime_content_type($_FILES['foto']['tmp_name']);

                // Define los formatos de imagen permitidos.
                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                // Comprueba que el archivo sea una imagen permitida.
                if (!isset($tiposPermitidos[$tipo])) {
                    $error = 'La fotografía debe ser JPG, PNG o WEBP.';

                // Comprueba que el tamaño no supere los 5 MB.
                } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    $error = 'La fotografía no puede superar los 5 MB.';

                } else {

                    // Define la carpeta donde se almacenarán las fotografías.
                    $carpeta = __DIR__ . '/uploads';

                    // Crea la carpeta si todavía no existe.
                    if (!is_dir($carpeta)) {
                        mkdir($carpeta, 0775, true);
                    }

                    // Genera un nombre único para la fotografía.
                    $fotoNombre = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipo];

                    // Mueve la fotografía a la carpeta uploads.
                    if (!move_uploaded_file(
                        $_FILES['foto']['tmp_name'],
                        $carpeta . '/' . $fotoNombre
                    )) {
                        $error = 'No se pudo guardar la fotografía.';
                        $fotoNombre = null;
                    }
                }
            }
        }

        // Si no existen errores, guarda el reporte en MySQL.
        if ($error === '') {

            // Obtiene los datos del ciudadano que inició sesión.
            $usuario = obtenerUsuario();

            try {

                // Prepara la consulta para insertar el nuevo reporte.
                $insertar = $conexion->prepare(
                    "INSERT INTO reportes
                    (usuario_id, categoria_id, sector, descripcion, imagen, estado)
                    VALUES
                    (:usuario_id, :categoria_id, :sector, :descripcion, :imagen, 'Pendiente')"
                );

                // Ejecuta la consulta con los datos del reporte.
                $insertar->execute([
                    ':usuario_id' => $usuario['id'],
                    ':categoria_id' => $categoriaId,
                    ':sector' => $sector,
                    ':descripcion' => $descripcion,
                    ':imagen' => $fotoNombre
                ]);

                // Redirige al ciudadano a la lista de reportes.
                header('Location: reportes.php');
                exit;

            } catch (PDOException $e) {

                // Si falla el registro, elimina la fotografía que se había subido.
                if ($fotoNombre !== null && file_exists(__DIR__ . '/uploads/' . $fotoNombre)) {
                    unlink(__DIR__ . '/uploads/' . $fotoNombre);
                }

                // Muestra un mensaje de error.
                $error = 'No se pudo guardar el reporte. Inténtalo nuevamente.';
            }
        }
    }
}

// Incluye el encabezado general de la página.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Encabezado del formulario de creación de reportes. -->
<section class="page-heading">
    <div>
        <h1>Crear nuevo reporte</h1>
        <p>Describe el problema que encontraste en el andén.</p>
    </div>
</section>

<!-- Contenedor principal del formulario. -->
<section class="form-container">

    <!-- Muestra los mensajes de error si existen. -->
    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para registrar un reporte y adjuntar una fotografía. -->
    <form method="POST" enctype="multipart/form-data" class="main-form">

        <!-- Campo para seleccionar la categoría del daño. -->
        <label for="categoria">Tipo de daño</label>

        <select name="categoria" id="categoria" required>

            <option value="">Selecciona una categoría</option>

            <!-- Muestra las categorías almacenadas en MySQL. -->
            <?php foreach ($categorias as $categoria): ?>

                <option
                    value="<?= (int)$categoria['id'] ?>"
                    <?= (string)($_POST['categoria'] ?? '') === (string)$categoria['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Campo para ingresar el sector del problema. -->
        <label for="sector">Sector o ubicación</label>

        <input
            type="text"
            name="sector"
            id="sector"
            placeholder="Ej. Alameda de la Victoria"
            value="<?= htmlspecialchars($_POST['sector'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <!-- Campo para describir el problema encontrado. -->
        <label for="descripcion">Descripción del problema</label>

        <textarea
            name="descripcion"
            id="descripcion"
            rows="5"
            placeholder="Describe el daño que encontraste..."
            required
        ><?= htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

        <!-- Campo opcional para adjuntar una fotografía. -->
        <label for="foto">Fotografía del daño (opcional)</label>

        <input
            type="file"
            name="foto"
            id="foto"
            accept="image/jpeg,image/png,image/webp"
        >

        <!-- Botón para enviar el reporte. -->
        <button type="submit" class="btn-primary">
            Enviar reporte
        </button>

        <!-- Enlace para cancelar el registro. -->
        <a href="reportes.php" class="btn-secondary">
            Cancelar
        </a>

    </form>
</section>

<?php
// Incluye el pie de página y los elementos finales compartidos del sitio.
require_once __DIR__ . '/includes/footer.php';
?>
