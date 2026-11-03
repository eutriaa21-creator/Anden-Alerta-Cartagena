
<?php

// Incluimos las funciones del proyecto y comprobamos que sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Ubicamos el archivo donde tenemos guardados todos los reportes.
$archivo = __DIR__ . '/../data/reportes.json';

// Cargamos los reportes desde el archivo JSON.
// Si no existe o no tiene información válida, dejamos un arreglo vacío.
$reportes = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?? [])
    : [];

// Recibimos el ID del reporte que queremos editar.
$id = (int)($_GET['id'] ?? 0);
$indiceReporte = null;

// Buscamos el reporte dentro del arreglo usando el ID recibido.
foreach ($reportes as $indice => $item) {
    if ((int)$item['id'] === $id) {
        $indiceReporte = $indice;
        break;
    }
}

// Si no encontramos el reporte, mostramos un error 404 y detenemos el archivo.
if ($indiceReporte === null) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

// Esta variable nos sirve para mostrar mensajes cuando hay algún error.
$error = '';

// Revisamos si el formulario fue enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibimos los datos que el administrador escribió en el formulario.
    $categoria = trim($_POST['categoria'] ?? '');
    $sector = trim($_POST['sector'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? '';

    // Estos son los únicos estados que se pueden asignar a un reporte.
    $estadosPermitidos = ['Pendiente', 'En proceso', 'Resuelto'];

    // Comprobamos que los campos principales no estén vacíos.
    if ($categoria === '' || $sector === '' || $descripcion === '') {
        $error = 'Completa todos los campos.';

    // Verificamos que el estado seleccionado esté dentro de los permitidos.
    } elseif (!in_array($estado, $estadosPermitidos, true)) {
        $error = 'Selecciona un estado válido.';

    } else {

        // Actualizamos la información del reporte que encontramos anteriormente.
        $reportes[$indiceReporte]['categoria'] = $categoria;
        $reportes[$indiceReporte]['sector'] = $sector;
        $reportes[$indiceReporte]['descripcion'] = $descripcion;
        $reportes[$indiceReporte]['estado'] = $estado;

        // Guardamos los cambios en el archivo JSON.
        file_put_contents(
            $archivo,
            json_encode($reportes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        // Cuando terminamos, volvemos al panel de reportes.
        header('Location: reportes.php');
        exit;
    }
}

// Guardamos los datos actuales del reporte para mostrarlos en el formulario.
$reporte = $reportes[$indiceReporte];

// Incluimos el encabezado general de la página.
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Mostramos el título de la página y el ID del reporte que estamos editando. -->
<section class="page-heading">
    <div>
        <h1>Editar reporte #<?= (int)$reporte['id'] ?></h1>
        <p>Modifica la información y el estado del reporte.</p>
    </div>
</section>

<!-- Contenedor donde se encuentra el formulario de edición. -->
<section class="form-container">

    <!-- Si ocurrió algún error, mostramos el mensaje correspondiente. -->
    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Formulario para modificar los datos del reporte. -->
    <form method="POST" class="main-form">

        <!-- Campo para cambiar la categoría del reporte. -->
        <label for="categoria">Categoría</label>
        <input type="text" name="categoria" id="categoria"
            value="<?= htmlspecialchars($reporte['categoria']) ?>" required>

        <!-- Campo para modificar el sector donde se encuentra el problema. -->
        <label for="sector">Sector</label>
        <input type="text" name="sector" id="sector"
            value="<?= htmlspecialchars($reporte['sector']) ?>" required>

        <!-- Campo para actualizar la descripción del problema. -->
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="5"
            required><?= htmlspecialchars($reporte['descripcion']) ?></textarea>

        <!-- Selector para cambiar el estado actual del reporte. -->
        <label for="estado">Estado del reporte</label>
        <select name="estado" id="estado" required>

            <!-- Mostramos los estados disponibles y dejamos seleccionado el actual. -->
            <?php foreach (['Pendiente', 'En proceso', 'Resuelto'] as $estado): ?>
                <option value="<?= $estado ?>"
                    <?= $reporte['estado'] === $estado ? 'selected' : '' ?>>
                    <?= $estado ?>
                </option>
            <?php endforeach; ?>

        </select>

        <!-- Botón para guardar los cambios realizados. -->
        <button type="submit" class="btn-primary">Guardar cambios</button>

        <!-- Enlace para salir sin guardar y volver al panel. -->
        <a href="reportes.php" class="btn-secondary">Cancelar</a>

    </form>
</section>

<!-- Incluimos el pie de página general del proyecto. -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>