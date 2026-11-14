
<?php

// Incluye las funciones del proyecto y comprueba que sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Incluye la conexión con MySQL.
require_once __DIR__ . '/../includes/conexion.php';

// Obtiene el ID del reporte desde la URL.
$id = (int)($_GET['id'] ?? 0);

// Comprueba que el ID sea válido.
if ($id <= 0) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

// Consulta las categorías disponibles en MySQL.
$consultaCategorias = $conexion->query(
    "SELECT id, nombre FROM categorias ORDER BY nombre ASC"
);

$categorias = $consultaCategorias->fetchAll();

// Consulta el reporte que se desea editar.
$consultaReporte = $conexion->prepare(
    "SELECT * FROM reportes WHERE id = :id LIMIT 1"
);

$consultaReporte->execute([
    ':id' => $id
]);

$reporte = $consultaReporte->fetch();

// Si el reporte no existe, muestra un error 404.
if (!$reporte) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

// Variable para mostrar los mensajes de error.
$error = '';

// Estados permitidos por la base de datos.
$estadosPermitidos = ['Pendiente', 'En revisión', 'Atendido'];

// Comprueba si el formulario fue enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene los datos enviados por el administrador.
    $categoriaId = filter_input(INPUT_POST, 'categoria', FILTER_VALIDATE_INT);
    $sector = trim($_POST['sector'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? '';

    // Comprueba que la categoría seleccionada exista.
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
        $error = 'Completa todos los campos.';

    } elseif (!in_array($estado, $estadosPermitidos, true)) {
        $error = 'Selecciona un estado válido.';

    } else {

        try {

            // Actualiza los datos del reporte directamente en MySQL.
            $actualizar = $conexion->prepare(
                "UPDATE reportes
                SET categoria_id = :categoria_id,
                    sector = :sector,
                    descripcion = :descripcion,
                    estado = :estado
                WHERE id = :id"
            );

            // Ejecuta la actualización con los nuevos valores.
            $actualizar->execute([
                ':categoria_id' => $categoriaId,
                ':sector' => $sector,
                ':descripcion' => $descripcion,
                ':estado' => $estado,
                ':id' => $id
            ]);

            // Regresa al panel administrativo.
            header('Location: reportes.php');
            exit;

        } catch (PDOException $e) {
            $error = 'No se pudieron guardar los cambios. Inténtalo nuevamente.';
        }
    }

    // Actualiza los datos que se muestran en el formulario.
    // Así no se pierden los valores si ocurre algún error.
    $reporte['categoria_id'] = $categoriaId;
    $reporte['sector'] = $sector;
    $reporte['descripcion'] = $descripcion;
    $reporte['estado'] = $estado;
}

// Incluye el encabezado general de la página.
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Encabezado de la página de edición. -->
<section class="page-heading">
    <div>
        <h1>Editar reporte #<?= (int)$reporte['id'] ?></h1>
        <p>Modifica la información y el estado del reporte.</p>
    </div>
</section>

<!-- Contenedor del formulario. -->
<section class="form-container">

    <!-- Muestra los errores si existen. -->
    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para actualizar el reporte. -->
    <form method="POST" class="main-form">

        <!-- Selector de categorías disponibles en MySQL. -->
        <label for="categoria">Categoría</label>

        <select name="categoria" id="categoria" required>

            <option value="">Selecciona una categoría</option>

            <?php foreach ($categorias as $categoria): ?>

                <option
                    value="<?= (int)$categoria['id'] ?>"
                    <?= (string)$reporte['categoria_id'] === (string)$categoria['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Campo para modificar el sector. -->
        <label for="sector">Sector</label>

        <input
            type="text"
            name="sector"
            id="sector"
            value="<?= htmlspecialchars($reporte['sector'], ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <!-- Campo para modificar la descripción. -->
        <label for="descripcion">Descripción</label>

        <textarea
            name="descripcion"
            id="descripcion"
            rows="5"
            required
        ><?= htmlspecialchars($reporte['descripcion'], ENT_QUOTES, 'UTF-8') ?></textarea>

        <!-- Selector para cambiar el estado del reporte. -->
        <label for="estado">Estado del reporte</label>

        <select name="estado" id="estado" required>

            <?php foreach ($estadosPermitidos as $estado): ?>

                <option
                    value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $reporte['estado'] === $estado ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Botón para guardar los cambios. -->
        <button type="submit" class="btn-primary">
            Guardar cambios
        </button>

        <!-- Enlace para cancelar la edición. -->
        <a href="reportes.php" class="btn-secondary">
            Cancelar
        </a>

    </form>
</section>

<?php
// Incluye el pie de página general.
require_once __DIR__ . '/../includes/footer.php';
?>
