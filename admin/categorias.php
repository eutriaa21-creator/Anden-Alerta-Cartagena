
<?php

// Activamos la visualización de errores para poder detectar problemas mientras desarrollamos.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluimos las funciones del proyecto y verificamos que sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Conectamos el proyecto con la base de datos MySQL.
require_once __DIR__ . '/../includes/conexion.php';

// Esta variable nos permite guardar los mensajes de error del formulario.
$error = '';

// Guardamos los datos de la categoría que se está editando.
$categoriaEditar = null;

// Revisamos si se envió algún formulario desde esta página.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Comprobamos si el administrador está intentando agregar una categoría.
    if (isset($_POST['nueva_categoria'])) {

        // Obtenemos el nombre y la descripción escritos.
        $nueva = trim($_POST['nueva_categoria']);
        $descripcion = trim($_POST['descripcion'] ?? '');

        // No permitimos que se agreguen categorías sin nombre.
        if ($nueva === '') {
            $error = 'Escribe el nombre de la categoría.';

        } else {
            try {
                // Insertamos la nueva categoría con su descripción en MySQL.
                $insertar = $conexion->prepare(
                    "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)"
                );

                $insertar->execute([
                    $nueva,
                    $descripcion !== '' ? $descripcion : null
                ]);

                // Recargamos la página para mostrar la lista actualizada.
                header('Location: categorias.php');
                exit;

            } catch (PDOException $e) {

                // Comprobamos si el nombre ya está registrado.
                if ($e->getCode() === '23000') {
                    $error = 'Esta categoría ya existe.';
                } else {
                    $error = 'No se pudo agregar la categoría. Inténtalo nuevamente.';
                }
            }
        }
    }

    // Comprobamos si el administrador está intentando actualizar una categoría.
    if (isset($_POST['editar_categoria'])) {

        // Obtenemos el ID, el nombre y la descripción.
        $idEditar = filter_input(INPUT_POST, 'editar_categoria', FILTER_VALIDATE_INT);
        $nombreEditar = trim($_POST['nombre_editar'] ?? '');
        $descripcionEditar = trim($_POST['descripcion_editar'] ?? '');

        if (!$idEditar || $nombreEditar === '') {
            $error = 'Escribe un nombre válido para la categoría.';

        } else {
            try {
                // Actualizamos el nombre y la descripción en MySQL.
                $actualizar = $conexion->prepare(
                    "UPDATE categorias
                     SET nombre = ?, descripcion = ?
                     WHERE id = ?"
                );

                $actualizar->execute([
                    $nombreEditar,
                    $descripcionEditar !== '' ? $descripcionEditar : null,
                    $idEditar
                ]);

                // Recargamos la página para mostrar los cambios.
                header('Location: categorias.php');
                exit;

            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $error = 'Ya existe una categoría con ese nombre.';
                } else {
                    $error = 'No se pudo actualizar la categoría.';
                }
            }
        }
    }

    // Comprobamos si el administrador está intentando eliminar una categoría.
    if (isset($_POST['eliminar_categoria'])) {

        // Convertimos el ID recibido a número entero.
        $idEliminar = filter_input(
            INPUT_POST,
            'eliminar_categoria',
            FILTER_VALIDATE_INT
        );

        if ($idEliminar) {
            try {
                // Buscamos si existen reportes asociados a esta categoría.
                $consultaReportes = $conexion->prepare(
                    "SELECT COUNT(*) FROM reportes WHERE categoria_id = ?"
                );

                $consultaReportes->execute([$idEliminar]);
                $cantidadReportes = (int) $consultaReportes->fetchColumn();

                if ($cantidadReportes > 0) {
                    $error = 'No puedes eliminar esta categoría porque tiene reportes asociados.';

                } else {
                    // Eliminamos la categoría de la base de datos.
                    $eliminar = $conexion->prepare(
                        "DELETE FROM categorias WHERE id = ?"
                    );

                    $eliminar->execute([$idEliminar]);

                    // Recargamos la página para mostrar la lista actualizada.
                    header('Location: categorias.php');
                    exit;
                }

            } catch (PDOException $e) {
                $error = 'No se pudo eliminar la categoría. Inténtalo nuevamente.';
            }
        }
    }
}

// Comprobamos si se solicitó editar una categoría desde la tabla.
if (isset($_GET['editar'])) {

    // Obtenemos el ID de la categoría seleccionada.
    $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

    if ($idEditar) {
        // Consultamos los datos de la categoría en MySQL.
        $consultaEditar = $conexion->prepare(
            "SELECT id, nombre, descripcion FROM categorias WHERE id = ?"
        );

        $consultaEditar->execute([$idEditar]);
        $categoriaEditar = $consultaEditar->fetch();
    }
}

// Consultamos todas las categorías almacenadas en MySQL.
$consultaCategorias = $conexion->query(
    "SELECT id, nombre, descripcion FROM categorias ORDER BY id ASC"
);

$categorias = $consultaCategorias->fetchAll();

// Incluimos el encabezado general de la página.
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Título y descripción de la sección de categorías. -->
<section class="page-heading">
    <div>
        <h1>Gestión de categorías</h1>
        <p>Administra los tipos de daños que pueden reportar los ciudadanos.</p>
    </div>
</section>

<!-- Contenedor principal donde se organiza la gestión de categorías. -->
<section class="categories-container">

    <!-- Si ocurrió algún error, mostramos el mensaje correspondiente. -->
    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para registrar una nueva categoría. -->
    <div class="form-container">
        <h2>Agregar categoría</h2>

        <form method="POST" class="main-form">

            <!-- Campo donde se escribe el nombre de la nueva categoría. -->
            <label for="nueva_categoria">Nombre de la categoría</label>

            <input
                type="text"
                id="nueva_categoria"
                name="nueva_categoria"
                placeholder="Ej. Señalización dañada"
                maxlength="100"
                required
            >

            <!-- Campo para escribir la descripción de la categoría. -->
            <label for="descripcion">Descripción</label>

            <textarea
                id="descripcion"
                name="descripcion"
                placeholder="Describe brevemente el tipo de daño..."
                rows="3"
                maxlength="255"
            ></textarea>

            <!-- Botón que envía el formulario para agregar la categoría. -->
            <button type="submit" class="btn-primary">
                Agregar categoría
            </button>
        </form>
    </div>

    <!-- Si se seleccionó una categoría para editar, mostramos su formulario. -->
    <?php if ($categoriaEditar): ?>
        <div class="form-container">
            <h2>Editar categoría</h2>

            <form method="POST" class="main-form">

                <!-- Enviamos el ID de la categoría que estamos modificando. -->
                <input
                    type="hidden"
                    name="editar_categoria"
                    value="<?= (int) $categoriaEditar['id'] ?>"
                >

                <!-- Campo para modificar el nombre. -->
                <label for="nombre_editar">Nombre de la categoría</label>

                <input
                    type="text"
                    id="nombre_editar"
                    name="nombre_editar"
                    value="<?= htmlspecialchars($categoriaEditar['nombre']) ?>"
                    maxlength="100"
                    required
                >

                <!-- Campo para modificar la descripción. -->
                <label for="descripcion_editar">Descripción</label>

                <textarea
                    id="descripcion_editar"
                    name="descripcion_editar"
                    rows="3"
                    maxlength="255"
                ><?= htmlspecialchars($categoriaEditar['descripcion'] ?? '') ?></textarea>

                <!-- Botón para guardar los cambios. -->
                <button type="submit" class="btn-primary">
                    Guardar cambios
                </button>

                <!-- Enlace para cancelar la edición. -->
                <a href="categorias.php" class="filter-clear">
                    Cancelar
                </a>
            </form>
        </div>
    <?php endif; ?>

    <!-- Tabla donde mostramos todas las categorías que están registradas. -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>N.º</th>
                    <th>Nombre de la categoría</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <!-- Si no hay categorías guardadas, mostramos un mensaje. -->
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="4">No hay categorías registradas.</td>
                    </tr>
                <?php else: ?>

                    <!-- Recorremos las categorías para mostrarlas una por una. -->
                    <?php foreach ($categorias as $indice => $categoria): ?>
                        <tr>

                            <!-- Mostramos la posición de cada categoría en la lista. -->
                            <td><?= $indice + 1 ?></td>

                            <!-- Mostramos el nombre de la categoría. -->
                            <td><?= htmlspecialchars($categoria['nombre']) ?></td>

                            <!-- Mostramos la descripción de la categoría. -->
                            <td>
                                <?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?>
                            </td>

                            <td>
                                <!-- Enlace para editar la categoría seleccionada. -->
                                <a href="categorias.php?editar=<?= (int) $categoria['id'] ?>">
                                    Editar
                                </a>

                                <!-- Formulario para eliminar la categoría seleccionada. -->
                                <form method="POST" class="inline-form"
                                    onsubmit="return confirm('¿Eliminar esta categoría?');">

                                    <!-- Enviamos el ID de la categoría que queremos eliminar. -->
                                    <input
                                        type="hidden"
                                        name="eliminar_categoria"
                                        value="<?= (int) $categoria['id'] ?>"
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

<!-- Incluimos el pie de página general del proyecto. -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
