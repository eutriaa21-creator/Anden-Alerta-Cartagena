
<?php
// Incluye las funciones necesarias para controlar las sesiones y los usuarios.
require_once __DIR__ . '/includes/funciones.php';

// Comprueba que el ciudadano haya iniciado sesión.
requiereLogin();

// Si el usuario es administrador, lo redirige al panel administrativo.
if (esAdmin()) {
    header('Location: admin/reportes.php');
    exit;
}

// Define la ubicación del archivo JSON que contiene las categorías disponibles.
$archivoCategorias = __DIR__ . '/data/categorias.json';

// Comprueba si el archivo de categorías existe.
// Si existe, lee su contenido y lo convierte en un arreglo.
// Si no existe, utiliza un arreglo vacío.
$categorias = file_exists($archivoCategorias)
    ? (json_decode(file_get_contents($archivoCategorias), true) ?? [])
    : [];

// Variable que almacena los mensajes de error del formulario.
$error = '';

// Comprueba si el formulario fue enviado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene la categoría seleccionada y elimina los espacios sobrantes.
    $categoria = trim($_POST['categoria'] ?? '');

    // Obtiene el sector o ubicación del problema.
    $sector = trim($_POST['sector'] ?? '');

    // Obtiene la descripción escrita por el ciudadano.
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Comprueba que la categoría seleccionada exista en el archivo de categorías.
    if (!in_array($categoria, $categorias, true)) {
        $error = 'Selecciona una categoría válida.';

    // Comprueba que el sector y la descripción no estén vacíos.
    } elseif ($sector === '' || $descripcion === '') {
        $error = 'Completa todos los campos obligatorios.';

    } else {

        // Define la ubicación del archivo JSON donde se guardan los reportes.
        $archivo = __DIR__ . '/data/reportes.json';

        // Lee los reportes existentes y los convierte en un arreglo.
        // Si el archivo no existe, comienza con un arreglo vacío.
        $reportes = file_exists($archivo)
            ? (json_decode(file_get_contents($archivo), true) ?? [])
            : [];

        // Variable que almacena el nombre de la fotografía.
        // Comienza vacía porque la fotografía es opcional.
        $fotoNombre = '';

        // Comprueba si se seleccionó una fotografía en el formulario.
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

            // Comprueba si ocurrió algún error durante la carga de la fotografía.
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = 'No se pudo subir la fotografía.';

            } else {

                // Detecta el tipo MIME real del archivo temporal.
                $tipo = mime_content_type($_FILES['foto']['tmp_name']);

                // Define los formatos de imagen permitidos y su extensión correspondiente.
                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                // Comprueba que la fotografía tenga un formato permitido.
                if (!isset($tiposPermitidos[$tipo])) {
                    $error = 'La fotografía debe ser JPG, PNG o WEBP.';

                // Comprueba que el tamaño de la fotografía no supere los 5 MB.
                } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    $error = 'La fotografía no puede superar los 5 MB.';

                } else {

                    // Define la carpeta donde se almacenarán las fotografías.
                    $carpeta = __DIR__ . '/uploads';

                    // Si la carpeta no existe, la crea.
                    if (!is_dir($carpeta)) {
                        mkdir($carpeta, 0775, true);
                    }

                    // Genera un nombre único para evitar que dos fotografías tengan el mismo nombre.
                    // También agrega la extensión correspondiente al formato de la imagen.
                    $fotoNombre = uniqid('reporte_', true) . '.' . $tiposPermitidos[$tipo];

                    // Mueve la fotografía temporal a la carpeta uploads.
                    if (!move_uploaded_file(
                        $_FILES['foto']['tmp_name'],
                        $carpeta . '/' . $fotoNombre
                    )) {

                        // Si no se pudo mover la fotografía, muestra un mensaje de error.
                        $error = 'No se pudo guardar la fotografía.';
                    }
                }
            }
        }

        // Si no hay errores, continúa con la creación del reporte.
        if ($error === '') {

            // Obtiene los datos del ciudadano que inició sesión.
            $usuario = obtenerUsuario();

            // Construye un arreglo con toda la información del nuevo reporte.
            $nuevoReporte = [

                // Calcula el siguiente ID disponible.
                // Si no existen reportes, comienza desde el número 1.
                'id' => count($reportes) > 0
                    ? max(array_column($reportes, 'id')) + 1
                    : 1,

                // Guarda el ID del ciudadano que realizó el reporte.
                'usuario_id' => $usuario['id'],

                // Guarda el nombre del ciudadano que realizó el reporte.
                'usuario_nombre' => $usuario['nombre'],

                // Guarda el correo del ciudadano que realizó el reporte.
                'usuario_correo' => $usuario['correo'],

                // Guarda la categoría del daño seleccionado.
                'categoria' => $categoria,

                // Guarda el sector o ubicación del problema.
                'sector' => $sector,

                // Guarda la descripción del problema.
                'descripcion' => $descripcion,

                // Guarda el nombre de la fotografía, si se adjuntó alguna.
                'foto' => $fotoNombre,

                // Establece el estado inicial del reporte.
                'estado' => 'Pendiente',

                // Registra la fecha y hora en la que se creó el reporte.
                'fecha' => date('Y-m-d H:i:s')
            ];

            // Agrega el nuevo reporte al arreglo de reportes existentes.
            $reportes[] = $nuevoReporte;

            // Convierte todos los reportes a formato JSON y los guarda en el archivo.
            // JSON_PRETTY_PRINT organiza el contenido para facilitar su lectura.
            // JSON_UNESCAPED_UNICODE conserva las tildes y caracteres especiales.
            // LOCK_EX evita que otros procesos escriban en el archivo al mismo tiempo.
            $guardado = file_put_contents(
                $archivo,
                json_encode($reportes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );

            // Comprueba si el reporte se guardó correctamente.
            if ($guardado !== false) {

                // Redirige al ciudadano a la página donde se muestran los reportes.
                header('Location: reportes.php');
                exit;

            } else {

                // Muestra un mensaje si ocurrió un problema al guardar el reporte.
                $error = 'No se pudo guardar el reporte.';
            }
        }
    }
}

// Incluye el encabezado general de la página.
// Contiene elementos compartidos, como la navegación.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Encabezado que presenta el formulario de creación de reportes. -->
<section class="page-heading">
    <div>
        <h1>Crear nuevo reporte</h1>
        <p>Describe el problema que encontraste en el andén.</p>
    </div>
</section>

<!-- Contenedor principal del formulario de registro de problemas. -->
<section class="form-container">

    <!-- Si existe un error, lo muestra encima del formulario. -->
    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Formulario que envía los datos mediante POST.
         multipart/form-data permite adjuntar fotografías. -->
    <form method="POST" enctype="multipart/form-data" class="main-form">

        <!-- Campo para seleccionar la categoría del daño. -->
        <label for="categoria">Tipo de daño</label>

        <select name="categoria" id="categoria" required>

            <!-- Opción inicial que solicita seleccionar una categoría. -->
            <option value="">Selecciona una categoría</option>

            <!-- Recorre las categorías guardadas por el administrador. -->
            <?php foreach ($categorias as $categoria): ?>

                <!-- Crea una opción por cada categoría disponible.
                     Mantiene seleccionada la categoría anterior si hubo un error. -->
                <option value="<?= htmlspecialchars($categoria) ?>"
                    <?= ($_POST['categoria'] ?? '') === $categoria ? 'selected' : '' ?>>

                    <!-- Muestra el nombre de la categoría. -->
                    <?= htmlspecialchars($categoria) ?>

                </option>
            <?php endforeach; ?>

        </select>

        <!-- Campo para ingresar el sector o ubicación del problema. -->
        <label for="sector">Sector o ubicación</label>
        <input
            type="text"
            name="sector"
            id="sector"
            placeholder="Ej. Alameda de la Victoria"
            value="<?= htmlspecialchars($_POST['sector'] ?? '') ?>"
            required
        >

        <!-- Campo de texto para describir detalladamente el problema encontrado. -->
        <label for="descripcion">Descripción del problema</label>
        <textarea
            name="descripcion"
            id="descripcion"
            rows="5"
            placeholder="Describe el daño que encontraste..."
            required
        ><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>

        <!-- Campo para adjuntar una fotografía del daño.
             La imagen es opcional y solo acepta JPG, PNG y WEBP. -->
        <label for="foto">Fotografía del daño (opcional)</label>
        <input
            type="file"
            name="foto"
            id="foto"
            accept="image/jpeg,image/png,image/webp"
        >

        <!-- Botón que envía el formulario para registrar el nuevo reporte. -->
        <button type="submit" class="btn-primary">
            Enviar reporte
        </button>

        <!-- Enlace para cancelar el registro y regresar a los reportes. -->
        <a href="reportes.php" class="btn-secondary">
            Cancelar
        </a>

    </form>
</section>

<?php
// Incluye el pie de página y los elementos finales compartidos del sitio.
require_once __DIR__ . '/includes/footer.php';
?>
