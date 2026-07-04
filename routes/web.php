<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DocumentoAdjuntoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\CategoriaActivoController;
use App\Http\Controllers\EstadoActivoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\ImportacionSigaController;

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

  // ── Solo ADMINISTRADOR ─────────────────────────────────────────────
  Route::middleware('role:ADMINISTRADOR')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::post('/usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
    Route::post('/usuarios/{id}/change-password', [UsuarioController::class, 'changePassword'])->name('usuarios.change-password');

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

    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
    Route::put('/roles/{id}', [RolController::class, 'update'])->name('roles.update');
    Route::post('/roles/{id}/toggle-estado', [RolController::class, 'toggleEstado'])->name('roles.toggle-estado');

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

    Route::get('/estados', [EstadoActivoController::class, 'index'])->name('estados.index');
    Route::post('/estados', [EstadoActivoController::class, 'store'])->name('estados.store');
    Route::put('/estados/{id}', [EstadoActivoController::class, 'update'])->name('estados.update');
    Route::post('/estados/{id}/toggle-estado', [EstadoActivoController::class, 'toggleEstado'])->name('estados.toggle-estado');

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

    // Documentos adjuntos (transversal: activos, movimientos, mantenimientos…)
    Route::post('/documentos', [DocumentoAdjuntoController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{id}/descargar', [DocumentoAdjuntoController::class, 'download'])->name('documentos.download');
    Route::delete('/documentos/{id}', [DocumentoAdjuntoController::class, 'destroy'])->name('documentos.destroy');

    // Gestión de Movimientos (asignar, transferir, prestar, devolver, reubicar, baja)
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');

    // Mantenimientos (preventivo, correctivo, garantía, revisión técnica)
    Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
    Route::post('/mantenimientos', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
    Route::put('/mantenimientos/{id}/avanzar', [MantenimientoController::class, 'avanzar'])->name('mantenimientos.avanzar');
    Route::put('/mantenimientos/{id}/finalizar', [MantenimientoController::class, 'finalizar'])->name('mantenimientos.finalizar');
    Route::put('/mantenimientos/{id}/cerrar', [MantenimientoController::class, 'cerrar'])->name('mantenimientos.cerrar');
    Route::put('/mantenimientos/{id}/cancelar', [MantenimientoController::class, 'cancelar'])->name('mantenimientos.cancelar');

    // Importación SIGA (padrón patrimonial → base operativa)
    Route::get('/importaciones', [ImportacionSigaController::class, 'index'])->name('importaciones.index');
    Route::get('/importaciones/plantilla', [ImportacionSigaController::class, 'plantilla'])->name('importaciones.plantilla');
    Route::post('/importaciones', [ImportacionSigaController::class, 'store'])->name('importaciones.store');
    Route::get('/importaciones/{id}', [ImportacionSigaController::class, 'show'])->name('importaciones.show');
  });
});

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
