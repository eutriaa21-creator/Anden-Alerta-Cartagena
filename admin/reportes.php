
<?php

// Incluimos las funciones del proyecto y verificamos que el usuario sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Conectamos el proyecto con la base de datos MySQL.
require_once __DIR__ . '/../includes/conexion.php';

// Consultamos las categorías disponibles en la base de datos.
$consultaCategorias = $conexion->query(
    "SELECT id, nombre FROM categorias ORDER BY nombre ASC"
);

$categorias = $consultaCategorias->fetchAll();

// Revisamos si se envió una solicitud para eliminar un reporte.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {

    // Convertimos el ID recibido a número entero.
    $idEliminar = filter_input(INPUT_POST, 'eliminar_id', FILTER_VALIDATE_INT);

    if ($idEliminar) {

        try {
            // Buscamos la imagen asociada al reporte antes de eliminarlo.
            $consultaImagen = $conexion->prepare(
                "SELECT imagen FROM reportes WHERE id = ?"
            );

            $consultaImagen->execute([$idEliminar]);
            $reporteEliminar = $consultaImagen->fetch();

            if ($reporteEliminar) {

                // Eliminamos el reporte de la base de datos.
                $eliminar = $conexion->prepare(
                    "DELETE FROM reportes WHERE id = ?"
                );

                $eliminar->execute([$idEliminar]);

                // Si el reporte tenía una imagen, también eliminamos el archivo.
                if (!empty($reporteEliminar['imagen'])) {
                    $rutaImagen = __DIR__ . '/../uploads/' . basename($reporteEliminar['imagen']);

                    if (is_file($rutaImagen)) {
                        unlink($rutaImagen);
                    }
                }
            }

            // Volvemos al panel para mostrar la lista actualizada.
            header('Location: reportes.php');
            exit;

        } catch (PDOException $e) {
            die("No se pudo eliminar el reporte. Inténtalo nuevamente.");
        }
    }
}

// Recibimos los filtros que se seleccionaron en el panel.
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroFecha = trim($_GET['fecha'] ?? '');

// Consultamos la cantidad total de reportes.
$totalReportes = (int) $conexion->query(
    "SELECT COUNT(*) FROM reportes"
)->fetchColumn();

// Consultamos la cantidad de reportes pendientes.
$consultaPendientes = $conexion->query(
    "SELECT COUNT(*) FROM reportes WHERE estado = 'Pendiente'"
);

$totalPendientes = (int) $consultaPendientes->fetchColumn();

// Consultamos la cantidad de reportes en revisión.
$consultaRevision = $conexion->query(
    "SELECT COUNT(*) FROM reportes WHERE estado = 'En revisión'"
);

$totalRevision = (int) $consultaRevision->fetchColumn();

// Consultamos la cantidad de reportes atendidos.
$consultaAtendidos = $conexion->query(
    "SELECT COUNT(*) FROM reportes WHERE estado = 'Atendido'"
);

$totalAtendidos = (int) $consultaAtendidos->fetchColumn();

// Construimos la consulta para mostrar los reportes y los datos de sus autores.
$sql = "
    SELECT
        r.id,
        c.nombre AS categoria,
        r.sector,
        u.nombre AS usuario_nombre,
        u.correo AS usuario_correo,
        r.estado,
        r.fecha_reporte AS fecha
    FROM reportes r
    INNER JOIN categorias c ON r.categoria_id = c.id
    INNER JOIN usuarios u ON r.usuario_id = u.id
";

$condiciones = [];
$parametros = [];

// Aplicamos el filtro por categoría si el administrador seleccionó una.
if ($filtroCategoria !== '' && ctype_digit($filtroCategoria)) {
    $condiciones[] = "r.categoria_id = ?";
    $parametros[] = (int) $filtroCategoria;
}

// Aplicamos el filtro por fecha si se seleccionó una.
if ($filtroFecha !== '') {
    $fechaValida = DateTime::createFromFormat('Y-m-d', $filtroFecha);

    if ($fechaValida && $fechaValida->format('Y-m-d') === $filtroFecha) {
        $condiciones[] = "DATE(r.fecha_reporte) = ?";
        $parametros[] = $filtroFecha;
    }
}

// Agregamos las condiciones a la consulta.
if (!empty($condiciones)) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

// Mostramos primero los reportes más recientes.
$sql .= " ORDER BY r.fecha_reporte DESC";

// Ejecutamos la consulta con los filtros seleccionados.
$consultaReportes = $conexion->prepare($sql);
$consultaReportes->execute($parametros);

$reportesFiltrados = $consultaReportes->fetchAll();

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
            <p><?= $totalReportes ?></p>
        </div>

        <div class="summary-card">
            <h3>Pendientes</h3>
            <p><?= $totalPendientes ?></p>
        </div>

        <div class="summary-card">
            <h3>En revisión</h3>
            <p><?= $totalRevision ?></p>
        </div>

        <div class="summary-card">
            <h3>Atendidos</h3>
            <p><?= $totalAtendidos ?></p>
        </div>

    </div>

    <!-- Formulario que permite filtrar los reportes por categoría y fecha. -->
    <form method="GET" class="filters-container">

        <!-- Lista de categorías disponibles para realizar la búsqueda. -->
        <select name="categoria" class="filter-select">
            <option value="">Todas las categorías</option>

            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= (int) $categoria['id'] ?>"
                    <?= $filtroCategoria === (string) $categoria['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($categoria['nombre']) ?>
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
        Mostrando <?= count($reportesFiltrados) ?> de <?= $totalReportes ?> reportes
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

                    <!-- Recorremos los reportes consultados en MySQL para mostrar cada uno. -->
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
