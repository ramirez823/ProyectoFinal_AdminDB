<?php
/**
 * Formulario para crear un nuevo ítem de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval(str_replace(['₡', ','], '', $_POST['precio'] ?? 0));
    $disponibilidad = $_POST['disponibilidad'] ?? 'DISPONIBLE';
    $categorias = $_POST['categorias'] ?? [];
    
    // Validación básica
    if (empty($nombre) || $precio <= 0) {
        $error = true;
        $mensaje = 'El nombre y el precio son obligatorios y el precio debe ser mayor a cero.';
    } else {
        // Preparamos los datos para insertar
        $datos = [
            'nombre' => $nombre,
            'precio' => $precio,
            'disponibilidad' => $disponibilidad
        ];
        
        // Intentamos insertar el ítem de menú
        $menuId = insertarMenu($datos);
        
        if ($menuId) {
            // Si se insertó correctamente, asignamos las categorías seleccionadas
            $categoriasAsignadas = true;
            foreach ($categorias as $categoriaId) {
                if (!asignarCategoriaAMenu($menuId, $categoriaId)) {
                    $categoriasAsignadas = false;
                }
            }
            
            if ($categoriasAsignadas) {
                // Redireccionamos a la vista de detalles para agregar ingredientes
                header("Location: view.php?id={$menuId}&mensaje=Ítem creado correctamente");
                exit;
            } else {
                $mensaje = 'El ítem se creó correctamente, pero hubo problemas al asignar algunas categorías.';
            }
        } else {
            $error = true;
            $mensaje = 'Error al crear el ítem. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Obtener todas las categorías de menú disponibles
$categorias = obtenerCategoriasMenuActivas();

// Incluir el encabezado
$pageTitle = 'Crear Ítem de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Crear Nuevo Ítem de Menú</h1>
       <div>
           <a href="index.php" class="btn btn-secondary">
               <i class="fas fa-arrow-left"></i> Volver
           </a>
       </div>
   </div>
   
   <?php if (!empty($mensaje)): ?>
       <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
           <?= htmlspecialchars($mensaje) ?>
       </div>
   <?php endif; ?>
   
   <div class="card shadow-sm">
       <div class="card-body">
           <form method="post" class="needs-validation" novalidate>
               <div class="row g-3">
                   <div class="col-md-6">
                       <div class="mb-3">
                           <label for="nombre" class="form-label">Nombre *</label>
                           <input type="text" class="form-control" id="nombre" name="nombre" 
                                  value="<?= htmlspecialchars($nombre ?? '') ?>" required>
                           <div class="invalid-feedback">
                               Por favor ingrese un nombre para el ítem.
                           </div>
                       </div>
                       
                       <div class="mb-3">
                           <label for="precio" class="form-label">Precio *</label>
                           <div class="input-group">
                               <span class="input-group-text">₡</span>
                               <input type="text" class="form-control" id="precio" name="precio" 
                                      value="<?= htmlspecialchars($precio ?? '') ?>" required>
                               <div class="invalid-feedback">
                                   Por favor ingrese un precio válido.
                               </div>
                           </div>
                       </div>
                       
                       <div class="mb-3">
                           <label for="disponibilidad" class="form-label">Disponibilidad</label>
                           <select class="form-select" id="disponibilidad" name="disponibilidad">
                               <option value="DISPONIBLE" <?= isset($disponibilidad) && $disponibilidad == 'DISPONIBLE' ? 'selected' : '' ?>>Disponible</option>
                               <option value="AGOTADO" <?= isset($disponibilidad) && $disponibilidad == 'AGOTADO' ? 'selected' : '' ?>>No Disponible</option>
                           </select>
                       </div>
                   </div>
                   
                   <div class="col-md-6">
                       <div class="mb-3">
                           <label class="form-label">Categorías</label>
                           <div class="card">
                               <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                   <?php if (empty($categorias)): ?>
                                       <p class="text-muted">No hay categorías disponibles. <a href="categorias.php">Crear categorías</a></p>
                                   <?php else: ?>
                                       <?php foreach ($categorias as $categoria): ?>
                                           <div class="form-check">
                                               <input class="form-check-input" type="checkbox" 
                                                      name="categorias[]" value="<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>" 
                                                      id="categoria_<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>">
                                               <label class="form-check-label" for="categoria_<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>">
                                                   <?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>
                                               </label>
                                           </div>
                                       <?php endforeach; ?>
                                   <?php endif; ?>
                               </div>
                           </div>
                       </div>
                       
                       <div class="alert alert-info">
                           <i class="fas fa-info-circle"></i> Después de crear el ítem, podrá agregar los ingredientes necesarios.
                       </div>
                   </div>
               </div>
               
               <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                   <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                   <button type="submit" class="btn btn-primary">Guardar Ítem</button>
               </div>
           </form>
       </div>
   </div>
</div>

<script>
// Validación del formulario
(function() {
   'use strict';
   window.addEventListener('load', function() {
       var forms = document.getElementsByClassName('needs-validation');
       Array.prototype.filter.call(forms, function(form) {
           form.addEventListener('submit', function(event) {
               if (form.checkValidity() === false) {
                   event.preventDefault();
                   event.stopPropagation();
               }
               form.classList.add('was-validated');
           }, false);
       });
   }, false);
})();

// Formato de moneda para el campo de precio
document.getElementById('precio').addEventListener('input', function(e) {
   let value = this.value.replace(/[^\d]/g, '');
   if (value !== '') {
       value = parseInt(value, 10);
       this.value = new Intl.NumberFormat('es-CR', {
           minimumFractionDigits: 0,
           maximumFractionDigits: 0
       }).format(value);
   }
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>