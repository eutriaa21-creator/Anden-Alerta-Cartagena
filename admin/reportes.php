
<?php

// Incluimos las funciones del proyecto y verificamos que el usuario sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Ubicamos los archivos JSON donde guardamos los reportes y las categorías.
$archivo = __DIR__ . '/../data/reportes.json';
$archivoCategorias = __DIR__ . '/../data/categorias.json';

// Cargamos los reportes guardados. Si el archivo no existe o está vacío, usamos un arreglo vacío.
$reportes = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?? [])
    : [];

// Hacemos lo mismo con las categorías disponibles para poder usarlas en los filtros.
$categorias = file_exists($archivoCategorias)
    ? (json_decode(file_get_contents($archivoCategorias), true) ?? [])
    : [];

// Revisamos si se envió una solicitud para eliminar un reporte.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {

    // Convertimos el ID recibido a número entero para trabajar con él.
    $idEliminar = (int) $_POST['eliminar_id'];

    // Buscamos dentro de los reportes cuál coincide con el ID que queremos eliminar.
    foreach ($reportes as $indice => $reporte) {
        if ((int) $reporte['id'] === $idEliminar) {

            // Si el reporte tiene una foto, también buscamos el archivo para eliminarlo.
            if (!empty($reporte['foto'])) {
                $rutaFoto = __DIR__ . '/../uploads/' . basename($reporte['foto']);

                // Comprobamos que la imagen exista antes de intentar borrarla.
                if (is_file($rutaFoto)) {
                    unlink($rutaFoto);
                }
            }

            // Quitamos el reporte del arreglo y reorganizamos sus posiciones.
            unset($reportes[$indice]);
            $reportes = array_values($reportes);

            // Guardamos nuevamente el archivo JSON con los reportes actualizados.
            file_put_contents(
                $archivo,
                json_encode($reportes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );

            // Volvemos al panel para mostrar la lista actualizada.
            header('Location: reportes.php');
            exit;
        }
    }
}

// Recibimos los filtros que se seleccionaron en el panel.
// Si no se seleccionó nada, las variables quedan vacías.
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroFecha = trim($_GET['fecha'] ?? '');

// Filtramos los reportes según la categoría y la fecha que haya seleccionado el administrador.
$reportesFiltrados = array_filter($reportes, function ($reporte) use ($filtroCategoria, $filtroFecha) {

    // Revisamos si el reporte pertenece a la categoría seleccionada.
    $coincideCategoria = $filtroCategoria === ''
        || $reporte['categoria'] === $filtroCategoria;

    // Obtenemos solamente la parte de la fecha para poder compararla con el filtro.
    $fechaReporte = substr($reporte['fecha'] ?? '', 0, 10);

    // Comprobamos si la fecha del reporte coincide con la que se seleccionó.
    $coincideFecha = $filtroFecha === ''
        || $fechaReporte === $filtroFecha;

    // El reporte se muestra cuando cumple con los dos filtros.
    return $coincideCategoria && $coincideFecha;
});

// Incluimos la parte superior de la página, donde está el menú y el encabezado.
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Título y descripción de la sección de administración. -->
<section class="page-heading">
    <div>
        <h1>Panel de reportes</h1>
        <p>Administra los reportes registrados por los ciudadanos.</p>
    </div>
</section>

<!-- Contenedor principal donde se organiza toda la información del panel. -->
<section class="admin-container">

    <!-- Resumen con las cantidades de reportes según su estado. -->
    <div class="admin-summary">

        <div class="summary-card">
            <h3>Total de reportes</h3>
            <p><?= count($reportes) ?></p>
        </div>

        <div class="summary-card">
            <h3>Pendientes</h3>
            <p><?= count(array_filter($reportes, fn($r) => $r['estado'] === 'Pendiente')) ?></p>
        </div>

        <div class="summary-card">
            <h3>En proceso</h3>
            <p><?= count(array_filter($reportes, fn($r) => $r['estado'] === 'En proceso')) ?></p>
        </div>

        <div class="summary-card">
            <h3>Resueltos</h3>
            <p><?= count(array_filter($reportes, fn($r) => $r['estado'] === 'Resuelto')) ?></p>
        </div>

    </div>

    <!-- Formulario que permite filtrar los reportes por categoría y fecha. -->
    <form method="GET" class="filters-container">

        <!-- Lista de categorías disponibles para realizar la búsqueda. -->
        <select name="categoria" class="filter-select">
            <option value="">Todas las categorías</option>

            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= htmlspecialchars($categoria) ?>"
                    <?= $filtroCategoria === $categoria ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categoria) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Campo para seleccionar la fecha de los reportes que queremos consultar. -->
        <input
            type="date"
            name="fecha"
            class="filter-date"
            value="<?= htmlspecialchars($filtroFecha) ?>"
        >

        <!-- Botón para aplicar los filtros seleccionados. -->
        <button type="submit" class="filter-button">
            Filtrar
        </button>

        <!-- Enlace para quitar los filtros y volver a mostrar todos los reportes. -->
        <a href="reportes.php" class="filter-clear">
            Limpiar
        </a>

    </form>

    <!-- Indicamos cuántos reportes coinciden con la búsqueda. -->
    <p class="filter-results">
        Mostrando <?= count($reportesFiltrados) ?> de <?= count($reportes) ?> reportes
    </p>

    <!-- Tabla donde el administrador puede consultar y gestionar los reportes. -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Sector</th>
                    <th>Ciudadano</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <!-- Si no hay resultados, mostramos un mensaje en lugar de dejar la tabla vacía. -->
                <?php if (empty($reportesFiltrados)): ?>
                    <tr>
                        <td colspan="7">No se encontraron reportes.</td>
                    </tr>
                <?php else: ?>

                    <!-- Recorremos los reportes filtrados para mostrar cada uno en una fila. -->
                    <?php foreach ($reportesFiltrados as $reporte): ?>
                        <tr>
                            <td><?= (int) $reporte['id'] ?></td>

                            <td><?= htmlspecialchars($reporte['categoria']) ?></td>

                            <td><?= htmlspecialchars($reporte['sector']) ?></td>

                            <!-- Mostramos el nombre y el correo de la persona que hizo el reporte. -->
                            <td>
                                <?= htmlspecialchars($reporte['usuario_nombre']) ?>
                                <br>
                                <small><?= htmlspecialchars($reporte['usuario_correo']) ?></small>
                            </td>

                            <td><?= htmlspecialchars($reporte['estado']) ?></td>

                            <td><?= htmlspecialchars($reporte['fecha']) ?></td>

                            <!-- Acciones disponibles para administrar cada reporte. -->
                            <td>

                                <!-- Permite abrir la información completa del reporte. -->
                                <a href="../detalle-reporte.php?id=<?= (int) $reporte['id'] ?>">
                                    Ver
                                </a>

                                <!-- Permite modificar los datos y el estado del reporte. -->
                                <a href="editar-reporte.php?id=<?= (int) $reporte['id'] ?>">
                                    Editar
                                </a>

                                <!-- Formulario para eliminar el reporte seleccionado. -->
                                <form method="POST" class="inline-form"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este reporte?');">

                                    <!-- Enviamos el ID del reporte que se quiere eliminar. -->
                                    <input
                                        type="hidden"
                                        name="eliminar_id"
                                        value="<?= (int) $reporte['id'] ?>"
                                    >

                                    <button type="submit" class="btn-delete">
                                        Eliminar
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<!-- Incluimos el pie de página común del proyecto. -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
