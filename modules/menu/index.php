<?php
/**
 * Listado de ítems del menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Obtener todos los ítems de menú activos
$menu = obtenerMenuActivo();

// Obtener todas las categorías de menú activas para filtrar
$categorias = obtenerCategoriasMenuActivas();

// Filtrar por categoría si se especifica
$categoriaId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
if ($categoriaId) {
    // Filtrar manualmente ya que no podemos hacerlo directamente en la BD
    $menuFiltrado = [];
    foreach ($menu as $item) {
        // Obtener categorías de este ítem
        $categoriasItem = obtenerCategoriasDeMenu($item['MENU_ID_PK']);
        
        // Filtrar si pertenece a la categoría seleccionada
        foreach ($categoriasItem as $cat) {
            if ($cat['CATEGORIA_MENU_FK'] == $categoriaId) {
                $menuFiltrado[] = $item;
                break;
            }
        }
    }
    $menu = $menuFiltrado;
}

// Incluir el encabezado
$pageTitle = 'Gestión de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Menú</h1>
        <div>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Ítem
            </a>
            <a href="categorias.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Gestionar Categorías
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="categoria" class="form-label">Filtrar por Categoría</label>
                    <select class="form-select" id="categoria" name="categoria" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>" <?= $categoriaId == $categoria['CATEGORIA_MENU_ID_PK'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" placeholder="Buscar por nombre..." 
                           onkeyup="filterTable()">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <?php if (isset($_GET['categoria']) && $_GET['categoria']): ?>
                        <a href="index.php" class="btn btn-outline-secondary">Limpiar filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de ítems -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="menuTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Disponibilidad</th>
                            <th>Categorías</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($menu)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay ítems en el menú</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menu as $item): ?>
                                <tr>
                                    <td><?= $item['MENU_ID_PK'] ?></td>
                                    <td><?= htmlspecialchars($item['MENU_NOMBRE']) ?></td>
                                    <td><?= '₡' . number_format($item['MENU_PRECIO'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php if ($item['MENU_DISPONIBILIDAD'] == 'DISPONIBLE'): ?>
                                            <span class="badge bg-success">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No Disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $categoriasItem = obtenerCategoriasDeMenu($item['MENU_ID_PK']);
                                        foreach ($categoriasItem as $index => $cat): 
                                            $categoriaInfo = obtenerCategoriaMenuPorId($cat['CATEGORIA_MENU_FK']);
                                            if ($categoriaInfo):
                                        ?>
                                            <span class="badge bg-info me-1">
                                                <?= htmlspecialchars($categoriaInfo['CATEGORIA_MENU_NOMBRE']) ?>
                                            </span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?= $item['MENU_ID_PK'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $item['MENU_ID_PK'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminar(<?= $item['MENU_ID_PK'] ?>, '<?= htmlspecialchars($item['MENU_NOMBRE']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id, nombre) {
    if (confirm('¿Está seguro que desea desactivar el ítem "' + nombre + '"?')) {
        window.location.href = 'delete.php?id=' + id;
    }
}

function filterTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("search");
    filter = input.value.toUpperCase();
    table = document.getElementById("menuTable");
    tr = table.getElementsByTagName("tr");
    
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1]; // Columna de nombre
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>