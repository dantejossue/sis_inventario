<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DocumentoAdjuntoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\CategoriaActivoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\ActivoImportController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\BajaActivoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\OcsInventoryController;

// Main Page Route
// ==========================================
// RUTAS PARA INVITADOS (NO LOGUEADOS)
// ==========================================
Route::middleware('guest')->group(function () {

  // Vista del login
  Route::get('/', [LoginBasic::class, 'index']);

  // VITAL: Esta ruta debe llamarse 'login' para que Laravel sepa a dónde enviar a los intrusos
  Route::get('/login', [LoginBasic::class, 'index'])->name('login');

  // El procesamiento del formulario
  Route::post('/login', [LoginBasic::class, 'authenticate'])->name('login.post');
});

// ==========================================
// RUTAS PROTEGIDAS (SOLO LOGUEADOS)
// ==========================================
Route::middleware(['auth', 'activo', 'no.cache'])->group(function () {

  Route::get('/home', [Analytics::class, 'index'])->name('home');
  Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');

  // ── Portal Proveedor ───────────────────────────────────────────────
  Route::middleware('role:PROVEEDOR')->prefix('proveedor')->name('proveedor.')->group(function () {
    Route::get('/dashboard', [ProveedorController::class, 'dashboard'])->name('dashboard');
  });

  // ── Operación y datos maestros: ADMINISTRADOR + OPERADOR ───────────
  // El OPERADOR gestiona todo el ciclo operativo (activos, movimientos,
  // mantenimientos, bajas), los catálogos y los datos maestros, pero NO la
  // configuración del sistema (accesos: usuarios y roles) ni la auditoría.
  Route::middleware('role:ADMINISTRADOR,OPERADOR')->group(function () {

    // ── Configuración del sistema · SOLO ADMINISTRADOR ──────────────
    // Gestión de accesos (usuarios). El OPERADOR queda excluido.
    Route::middleware('role:ADMINISTRADOR')->group(function () {
      Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
      Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
      Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
      Route::post('/usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
      Route::post('/usuarios/{id}/change-password', [UsuarioController::class, 'changePassword'])->name('usuarios.change-password');
    });

    Route::get('/sedes', [SedeController::class, 'index'])->name('sedes.index');
    Route::post('/sedes', [SedeController::class, 'store'])->name('sedes.store');
    Route::put('/sedes/{id}', [SedeController::class, 'update'])->name('sedes.update');
    Route::post('/sedes/{id}/toggle-estado', [SedeController::class, 'toggleEstado'])->name('sedes.toggle-estado');
    Route::get('/sedes/{id}/dependencias', [SedeController::class, 'getDependencias'])->name('sedes.dependencias.get');
    Route::post('/sedes/{id}/dependencias', [SedeController::class, 'syncDependencias'])->name('sedes.dependencias.sync');

    Route::get('/dependencias', [DependenciaController::class, 'index'])->name('dependencias.index');
    Route::post('/dependencias', [DependenciaController::class, 'store'])->name('dependencias.store');
    Route::put('/dependencias/{id}', [DependenciaController::class, 'update'])->name('dependencias.update');
    Route::post('/dependencias/{id}/toggle-estado', [DependenciaController::class, 'toggleEstado'])->name('dependencias.toggle-estado');

    Route::get('/ubicaciones', [UbicacionController::class, 'index'])->name('ubicaciones.index');
    Route::post('/ubicaciones', [UbicacionController::class, 'store'])->name('ubicaciones.store');
    Route::put('/ubicaciones/{id}', [UbicacionController::class, 'update'])->name('ubicaciones.update');
    Route::post('/ubicaciones/{id}/toggle-estado', [UbicacionController::class, 'toggleEstado'])->name('ubicaciones.toggle-estado');

    // ── Configuración del sistema · SOLO ADMINISTRADOR ──────────────
    // Gestión de roles y permisos. El OPERADOR queda excluido.
    Route::middleware('role:ADMINISTRADOR')->group(function () {
      Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
      Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
      Route::put('/roles/{id}', [RolController::class, 'update'])->name('roles.update');
      Route::post('/roles/{id}/toggle-estado', [RolController::class, 'toggleEstado'])->name('roles.toggle-estado');
    });

    Route::get('/colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
    Route::get('/colaboradores/crear', [ColaboradorController::class, 'create'])->name('colaboradores.create');
    Route::post('/colaboradores', [ColaboradorController::class, 'store'])->name('colaboradores.store');
    Route::get('/colaboradores/{id}/editar', [ColaboradorController::class, 'edit'])->name('colaboradores.edit');
    Route::put('/colaboradores/{id}', [ColaboradorController::class, 'update'])->name('colaboradores.update');
    Route::post('/colaboradores/{id}/toggle-estado', [ColaboradorController::class, 'toggleEstado'])->name('colaboradores.toggle-estado');

    // Catálogos
    Route::get('/categorias', [CategoriaActivoController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [CategoriaActivoController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{id}', [CategoriaActivoController::class, 'update'])->name('categorias.update');
    Route::post('/categorias/{id}/toggle-estado', [CategoriaActivoController::class, 'toggleEstado'])->name('categorias.toggle-estado');

    Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas.index');
    Route::post('/marcas', [MarcaController::class, 'store'])->name('marcas.store');
    Route::put('/marcas/{id}', [MarcaController::class, 'update'])->name('marcas.update');
    Route::post('/marcas/{id}/toggle-estado', [MarcaController::class, 'toggleEstado'])->name('marcas.toggle-estado');
    Route::post('/modelos', [MarcaController::class, 'storeModelo'])->name('modelos.store');
    Route::put('/modelos/{id}', [MarcaController::class, 'updateModelo'])->name('modelos.update');
    Route::post('/modelos/{id}/toggle-estado', [MarcaController::class, 'toggleEstadoModelo'])->name('modelos.toggle-estado');

    // Gestión de Activos TI
    Route::get('/activos', [ActivoController::class, 'index'])->name('activos.index');
    Route::get('/activos/etiquetas', [ActivoController::class, 'etiquetas'])->name('activos.etiquetas');
    Route::get('/activos/qr/{token}', [ActivoController::class, 'qrShow'])->name('activos.qr');
    Route::get('/activos/crear', [ActivoController::class, 'create'])->name('activos.create');
    Route::post('/activos', [ActivoController::class, 'store'])->name('activos.store');

    Route::get('/activos/{id}/editar', [ActivoController::class, 'edit'])->name('activos.edit');
    Route::put('/activos/{id}', [ActivoController::class, 'update'])->name('activos.update');
    Route::delete('/activos/{id}', [ActivoController::class, 'destroy'])->name('activos.destroy');

    Route::get('/activos/{id}/ver', [ActivoController::class, 'show'])->name('activos.ver');

    // Exportación del inventario completo a Excel (todas las columnas de detalle)
    Route::get('/activos/exportar/excel', [ActivoController::class, 'exportarExcel'])->name('activos.exportar.excel');

    // Importación masiva de activos desde Excel
    Route::get('/activos/importar/plantilla', [ActivoImportController::class, 'plantilla'])->name('activos.importar.plantilla');
    Route::post('/activos/importar', [ActivoImportController::class, 'store'])->name('activos.importar');

    // Documentos adjuntos (transversal: activos, movimientos, mantenimientos…)
    Route::post('/documentos', [DocumentoAdjuntoController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{id}/descargar', [DocumentoAdjuntoController::class, 'download'])->name('documentos.download');
    Route::delete('/documentos/{id}', [DocumentoAdjuntoController::class, 'destroy'])->name('documentos.destroy');

    // Gestión de Movimientos internos OTI (préstamo, transferencia, regularización)
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('/movimientos/{id}/ver', [MovimientoController::class, 'show'])->name('movimientos.ver');
    Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
    Route::put('/movimientos/{id}/devolver', [MovimientoController::class, 'devolver'])->name('movimientos.devolver');
    Route::delete('/movimientos/{id}', [MovimientoController::class, 'destroy'])->name('movimientos.destroy');
    Route::get(
      '/movimientos/{id}/devolucion/datos',
      [MovimientoController::class, 'datosDevolucion']
    )->name('movimientos.devolucion.datos');

    // Mantenimientos (preventivo, correctivo, garantía, revisión técnica)
    Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
    Route::post('/mantenimientos', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
    Route::put('/mantenimientos/{id}/avanzar', [MantenimientoController::class, 'avanzar'])->name('mantenimientos.avanzar');
    Route::put('/mantenimientos/{id}/finalizar', [MantenimientoController::class, 'finalizar'])->name('mantenimientos.finalizar');
    // Cierre administrativo eliminado: FINALIZADO es el estado terminal (ver MantenimientoController::cerrar comentado).
    // Route::put('/mantenimientos/{id}/cerrar', [MantenimientoController::class, 'cerrar'])->name('mantenimientos.cerrar');
    Route::put('/mantenimientos/{id}/cancelar', [MantenimientoController::class, 'cancelar'])->name('mantenimientos.cancelar');

    // Bajas de activos (flujo simplificado: registrar → ejecutar / rechazar)
    Route::get('/bajas', [BajaActivoController::class, 'index'])->name('bajas.index');
    Route::post('/bajas', [BajaActivoController::class, 'store'])->name('bajas.store');
    // Evaluación técnica eliminada del módulo de bajas (la hace mantenimientos):
    // Route::put('/bajas/{id}/evaluar', [BajaActivoController::class, 'evaluar'])->name('bajas.evaluar');
    // Route::put('/bajas/{id}/validar', [BajaActivoController::class, 'validar'])->name('bajas.validar');
    Route::put('/bajas/{id}/ejecutar', [BajaActivoController::class, 'ejecutar'])->name('bajas.ejecutar');
    Route::put('/bajas/{id}/rechazar', [BajaActivoController::class, 'rechazar'])->name('bajas.rechazar');

    Route::get(
      '/activos/{activo}/ocs',
      [OcsInventoryController::class, 'show']
    )->name('activos.ocs.show');

    Route::get(
      '/activos/{activo}/ocs/datos',
      [OcsInventoryController::class, 'datos']
    )->name('activos.ocs.datos');
  });

  // ── Reportes (ADMINISTRADOR + SERVICIOS_GENERALES + OPERADOR) ───────
  Route::middleware('role:ADMINISTRADOR,SERVICIOS_GENERALES,OPERADOR')->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
  });

  // ── Auditoría de cambios (ADMINISTRADOR + SERVICIOS_GENERALES) ──────
  // El OPERADOR queda excluido de la auditoría (supervisión/lectura).
  Route::middleware('role:ADMINISTRADOR,SERVICIOS_GENERALES')->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
  });
});
