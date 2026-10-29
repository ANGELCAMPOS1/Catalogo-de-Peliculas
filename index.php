<?php
// Obtiene las películas y muestra la portada o el catálogo según la sesión.
session_start();
require_once 'includes/db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$peliculas = [];
$busqueda = '';
$orden = 'anio_estreno';

if ($isLoggedIn) {
    // Leer filtros y permitir solo columnas conocidas para ordenar resultados.
    $busqueda = $_GET['busqueda'] ?? '';
    $orden = $_GET['orden'] ?? 'anio_estreno';

    $columnas_validas = ['anio_estreno', 'calificacion', 'titulo'];
    if (!in_array($orden, $columnas_validas)) {
        $orden = 'anio_estreno';
    }

    $sql = "SELECT * FROM peliculas WHERE titulo LIKE ? OR genero LIKE ? ORDER BY $orden DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$busqueda%", "%$busqueda%"]);
    $peliculas = $stmt->fetchAll();
}

// Cargar la navegación común de la página.
include 'includes/header.php';
?>

<!-- Estilos propios para el fondo, la portada y los colores del catálogo. -->
<style>
    body {
    font-family: "Times New Roman", Times, serif;
}
    body {
        /* Reducimos la opacidad de la capa oscura a 0.4 para que la imagen de fondo resalte mucho más */
        background: linear-gradient(rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.4)), 
                    url('img/fondo_cine.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa;
        min-height: 100vh;
    }

    .hero-landing {
        height: 70vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        color: #ffc107;
        text-shadow: 2px 2px 15px rgba(0, 0, 0, 0.9);
        margin-bottom: 20px;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: #f8f9fa;
        margin-bottom: 30px;
        text-shadow: 1px 1px 8px rgba(0, 0, 0, 0.9);
        font-weight: 500;
    }

    .translucent-card {
        background-color: rgba(33, 37, 41, 0.85) !important;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
    }

    .table-translucent th, .table-translucent td {
        background-color: rgba(33, 37, 41, 0.75) !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.1) !important;

    }
    .author-name {
    color: #ffc107;
    font-weight: 800;
}
    .table-translucent td.movie-title {
        color: #8ecbff !important;
    }

    .table-translucent td .genre-badge {
        background-color: rgba(255, 255, 255, 0.75) !important;
        color: #000 !important;
    }

    .table-translucent td.movie-rating {
        color: #198754 !important;
    }
</style>

<?php if (!$isLoggedIn): ?>
    <!-- Presentación visible para usuarios que aún no iniciaron sesión. -->
    <div class="hero-landing">
        <div class="container">
            <h1 class="hero-title"><i class="fa-solid fa-film me-3"></i>Catálogo de Películas</h1>
            <h3 class="author-name">Bienvenido </h3>
            <p class="hero-subtitle">Organiza, descubre y califica tu colección cinematográfica favorita en un solo lugar.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="login.php" class="btn btn-warning btn-lg fw-bold px-4 shadow-lg">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión para Ver Catálogo
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Mensajes de resultado después de crear, editar o eliminar películas. -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success text-center shadow">
            <?php 
                if ($_GET['msg'] === 'created') echo "Película agregada con éxito.";
                elseif ($_GET['msg'] === 'updated') echo "Película actualizada con éxito.";
                elseif ($_GET['msg'] === 'deleted') echo "Película eliminada con éxito.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center shadow">
            <?php 
                if ($_GET['error'] === 'unauthorized') echo "Acceso no autorizado. Se requieren permisos de Administrador.";
            ?>
        </div>
    <?php endif; ?>

    <!-- Título del catálogo y acceso de administración para usuarios autorizados. -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h2 class="text-white fw-bold"><i class="fa-solid fa-clapperboard me-2"></i>Catálogo de Películas</h2>
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'Admin'): ?>
            <a href="agregar.php" class="btn btn-success shadow"><i class="fa-solid fa-plus me-1"></i> Nueva Película</a>
        <?php endif; ?>
    </div>

    <!-- Filtros para buscar películas y elegir el orden de los resultados. -->
    <div class="card translucent-card mb-4 p-3 shadow">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="busqueda" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por título o género..." value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <div class="col-md-4">
                <select name="orden" class="form-select bg-dark text-white border-secondary">
                    <option value="anio_estreno" <?= $orden === 'anio_estreno' ? 'selected' : '' ?>>Ordenar por Año</option>
                    <option value="calificacion" <?= $orden === 'calificacion' ? 'selected' : '' ?>>Ordenar por Calificación</option>
                    <option value="titulo" <?= $orden === 'titulo' ? 'selected' : '' ?>>Ordenar por Título</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-warning w-100 fw-bold">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Tabla con las películas encontradas y sus acciones administrativas. -->
    <div class="card translucent-card shadow mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-translucent table-hover mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Género</th>
                            <th>Año</th>
                            <th>Calificación</th>
                            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'Admin'): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($peliculas) > 0): ?>
                            <?php foreach ($peliculas as $index => $pelicula): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-bold movie-title"><?= htmlspecialchars($pelicula['titulo']) ?></td>
                                    <td><span class="badge genre-badge"><?= htmlspecialchars($pelicula['genero']) ?></span></td>
                                    <td><?= htmlspecialchars($pelicula['anio_estreno']) ?></td>
                                    <td class="movie-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-<?= $i <= $pelicula['calificacion'] ? 'solid' : 'regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'Admin'): ?>
                                        <td>
                                            <a href="editar.php?id=<?= $pelicula['id'] ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></a>
                                            <a href="eliminar.php?id=<?= $pelicula['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta película?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 text-light">No se encontraron películas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Cargar el pie de página y cerrar la estructura HTML. -->
<?php include 'includes/footer.php'; ?>