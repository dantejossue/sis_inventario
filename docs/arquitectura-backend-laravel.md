# Arquitectura Técnica del Backend (Laravel) — Sistema de Control Operativo de Activos TI

> Guía técnica para desarrolladores: **cómo está construido el backend y dónde codificar
> cada cosa**. Incluye la base teórica mínima (MVC, ciclo de request, Eloquent) y el mapa
> práctico de archivos por módulo. Complementa a
> [funcionalidades-y-flujos.md](funcionalidades-y-flujos.md) (el "qué hace"); este documento
> es el "cómo y dónde se programa".
>
> Stack: **PHP 8.x · Laravel 12 · MySQL · Blade + Vite (Bootstrap/Sneat)**.
> Última actualización: 2026-08-08.

---

## Índice

1. [Cómo usar este documento](#1-cómo-usar-este-documento)
2. [Base teórica: MVC y ciclo de vida de un request](#2-base-teórica)
3. [Estructura de carpetas (dónde vive cada cosa)](#3-estructura-de-carpetas)
4. [Capa de rutas](#4-capa-de-rutas)
5. [Capa de middleware](#5-capa-de-middleware)
6. [Capa de controladores](#6-capa-de-controladores)
7. [Capa de modelos (Eloquent)](#7-capa-de-modelos-eloquent)
8. [Persistencia: migraciones y seeders](#8-persistencia-migraciones-y-seeders)
9. [Servicios e integraciones externas](#9-servicios-e-integraciones-externas)
10. [Almacenamiento de archivos y documentos](#10-almacenamiento-de-archivos-y-documentos)
11. [Patrones transversales](#11-patrones-transversales)
12. [Puente con el frontend (Vite)](#12-puente-con-el-frontend-vite)
13. [Convenciones del proyecto](#13-convenciones-del-proyecto)
14. [Guía práctica: "¿dónde codifico X?"](#14-guía-práctica-dónde-codifico-x)
15. [Mapa de archivos por módulo](#15-mapa-de-archivos-por-módulo)
16. [Entorno y ejecución](#16-entorno-y-ejecución)

---

## 1. Cómo usar este documento

- ¿Vas a **agregar/cambiar una funcionalidad**? Ve directo a la §14 ("¿dónde codifico X?")
  y a la §15 (mapa por módulo).
- ¿Necesitas **entender la arquitectura**? §2 (teoría) + §3 (estructura) + §6/§7 (capas).
- ¿Vas a tocar **datos o esquema**? §7 (Eloquent) + §8 (migraciones).
- ¿Integración externa o archivos? §9 y §10.

---

## 2. Base teórica

### 2.1 Patrón MVC en Laravel

El sistema sigue el patrón **Modelo–Vista–Controlador**, que separa responsabilidades:

- **Modelo** (`app/Models`) — representa una entidad del dominio y encapsula el acceso a
  datos. Aquí se usa **Eloquent** (ORM de Laravel, patrón *Active Record*): cada modelo es
  una clase que mapea a una tabla y cada instancia a una fila.
- **Vista** (`resources/views/**.blade.php`) — la capa de presentación (plantillas Blade).
- **Controlador** (`app/Http/Controllers`) — orquesta: recibe el request, valida, invoca
  modelos/servicios y devuelve una respuesta (vista, redirect o JSON).

A esto se suman capas de **routing** (mapea URL → controlador), **middleware** (filtros
que envuelven el request) y **servicios** (`app/Services`, lógica reutilizable e
integraciones).

### 2.2 Ciclo de vida de un request

```
Navegador
   │  HTTP request
   ▼
public/index.php  →  bootstrap/app.php  (arranque de la aplicación)
   ▼
routes/web.php  (¿qué controlador atiende esta URL?)
   ▼
Pipeline de middleware:  auth → activo → no.cache → role:...
   ▼
Método del Controlador  (valida, aplica reglas, usa Modelos/Servicios)
   ▼
Eloquent  ⇄  Base de datos (MySQL)
   ▼
Respuesta:  view()  |  redirect()->with(...)  |  response()->json(...)
   ▼
Navegador
```

Conceptos clave que este proyecto aprovecha:

- **Service Container / Inyección de dependencias**: p. ej. `OcsInventoryController::datos(Activo $activo, OcsInventoryService $ocs)` recibe el servicio ya instanciado por el contenedor.
- **Route Model Binding**: `Route::get('/activos/{activo}/ocs', ...)` inyecta el modelo `Activo` resuelto por su id automáticamente.
- **Facades**: `Auth::id()`, `DB::transaction()`, `Storage::disk('local')`, `Http::get()` son fachadas de servicios del contenedor.
- **Migrations**: control de versiones del esquema de BD como código.

---

## 3. Estructura de carpetas

Dónde vive cada responsabilidad (solo lo relevante del dominio):

```
sis_inventario/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/        ← lógica de cada módulo (dominio) + scaffolding de plantilla
│  │  │  └─ dashboard/Analytics.php   ← KPIs del tablero
│  │  └─ Middleware/         ← CheckRole, CheckActivo, PreventBackHistory
│  ├─ Models/                ← entidades Eloquent (Activo, Movimiento, ...)
│  ├─ Observers/             ← ActivoObserver (historial de condición)
│  └─ Services/              ← OcsInventoryService (integraciones/lógica reutilizable)
├─ bootstrap/app.php         ← arranque + registro de alias de middleware
├─ config/                   ← configuración (services.php, filesystems.php, variables.php, ...)
├─ database/
│  ├─ migrations/            ← esquema de BD versionado
│  └─ seeders/               ← datos base (RolSeeder, ...)
├─ resources/
│  ├─ views/content/<módulo> ← Blade por módulo
│  ├─ js/pages/<módulo>      ← JS por página (Vite)
│  └─ menu/verticalMenu.json ← definición del menú lateral
├─ routes/web.php            ← todas las rutas
├─ storage/app/private/      ← documentos privados (disco 'local')
└─ public/                   ← index.php + assets compilados (build)
```

> Los controladores dentro de `layouts/`, `pages/`, `user_interface/`, `form_*`, `tables/`,
> `icons/`, `cards/`, `extended_ui/` son **demos de la plantilla Sneat** y no forman parte
> del dominio; se pueden ignorar (o eliminar) al programar funcionalidades reales.

---

## 4. Capa de rutas

Archivo único: **`routes/web.php`**. Organizado en grupos por autenticación y rol.

```php
// Invitados
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginBasic::class, 'index'])->name('login');
    Route::post('/login', [LoginBasic::class, 'authenticate'])->name('login.post');
});

// Protegido: sesión + cuenta activa + sin caché
Route::middleware(['auth', 'activo', 'no.cache'])->group(function () {

    // Operación y maestros: ADMINISTRADOR + OPERADOR
    Route::middleware('role:ADMINISTRADOR,OPERADOR')->group(function () {
        Route::get('/activos', [ActivoController::class, 'index'])->name('activos.index');
        // ... configuración (usuarios/roles) anidada en role:ADMINISTRADOR
    });

    // Reportes: ADMINISTRADOR + SERVICIOS_GENERALES + OPERADOR
    // Auditoría: ADMINISTRADOR + SERVICIOS_GENERALES
});
```

**Convenciones:**
- Nombres de ruta con punto: `activos.index`, `movimientos.devolver`, `bajas.ejecutar`.
- Acciones AJAX suelen ser `POST/PUT/DELETE` que devuelven JSON; las de formulario
  completo devuelven `redirect()`.
- Para **restringir por rol**, envuelve en `Route::middleware('role:ROL1,ROL2')`.

**Para agregar una ruta:** decláralas dentro del grupo `['auth','activo','no.cache']` y del
subgrupo de rol adecuado; nómbrala con la convención `modulo.accion`.

---

## 5. Capa de middleware

Los alias se registran en **`bootstrap/app.php`** (Laravel 12 ya no usa `Kernel.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'     => \App\Http\Middleware\CheckRole::class,
        'activo'   => \App\Http\Middleware\CheckActivo::class,
        'no.cache' => \App\Http\Middleware\PreventBackHistory::class,
    ]);
})
```

| Alias | Clase | Qué hace |
|---|---|---|
| `role:...` | `CheckRole` | Exige que `user->rol->nombre` esté en la lista; si no, aborta 403. |
| `activo` | `CheckActivo` | Si la cuenta es `INACTIVO`, cierra sesión y redirige a login. |
| `no.cache` | `PreventBackHistory` | Cabeceras anti-caché (evita "atrás" tras logout). |

**Para un middleware nuevo:** créalo en `app/Http/Middleware`, regístralo con alias en
`bootstrap/app.php` y aplícalo en `routes/web.php`.

---

## 6. Capa de controladores

Un controlador por módulo, en `app/Http/Controllers`. Patrón típico de una acción:

```php
public function store(Request $request)
{
    // 1) Validación (reglas + mensajes en español)
    $request->validate([...], [...]);

    // 2) Reglas de negocio (situación de origen, regla OTI, duplicados...)
    // ...

    // 3) Persistencia atómica
    DB::transaction(function () use (...) {
        // create/update de modelos
    });

    // 4) Efectos transversales (traza de condición, documentos)
    // 5) Auditoría
    AuditoriaCambio::registrar('ENTIDAD', $id, 'ACCION', $antes, $despues, $motivo);

    // 6) Respuesta: JSON (AJAX) o redirect()->with('success', ...)
    return response()->json([...]);  // o redirect()->route(...)->with(...);
}
```

**Controladores del dominio:**

| Controlador | Módulo |
|---|---|
| `ActivoController` | Activos (CRUD, ficha, QR, etiquetas, export, condición) |
| `ActivoImportController` | Importación Excel (plantilla + procesamiento) |
| `MovimientoController` | Movimientos + devolución |
| `MantenimientoController` | Mantenimientos (registrar/avanzar/finalizar/cancelar) |
| `BajaActivoController` | Bajas (registrar/ejecutar/rechazar) |
| `DocumentoAdjuntoController` | Documentos transversales |
| `OcsInventoryController` | Consulta OCS |
| `Colaborador/Sede/Dependencia/Ubicacion/CategoriaActivo/Marca/Usuario/Rol Controller` | Datos maestros y catálogos |
| `ReporteController`, `AuditoriaController`, `dashboard\Analytics`, `ProveedorController` | Reportes, auditoría, tablero, portal |

**Convenciones útiles:**
- Métodos privados `format<Entidad>()` normalizan un modelo a array para el frontend
  (p. ej. `ActivoController::formatActivo`, `MovimientoController::formatMovimiento`).
- Reglas y catálogos suelen extraerse a métodos privados (`reglasTecnicas()`,
  `reglasPatrimoniales()`, `catalogos()`).
- Helpers de dominio reutilizados entre controladores (p. ej. `situarActivo`) se repiten
  por controlador de forma deliberada (cada uno ajusta el `estado_operativo`).

---

## 7. Capa de modelos (Eloquent)

Cada tabla del dominio tiene su modelo en `app/Models`. Ejemplo canónico (`Activo`):

```php
#[ObservedBy([ActivoObserver::class])]      // engancha el historial de condición
class Activo extends Model
{
    use SoftDeletes;                         // borrado lógico (deleted_at)

    protected $table = 'activo';
    protected $primaryKey = 'id_activo';     // PK no estándar
    public $timestamps = true;
    const CREATED_AT = 'creado_en';          // timestamps en español
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'deleted_at';

    public const CONDICIONES = ['NUEVO','BUENO','REGULAR','MALO'];   // enums como constantes
    public const CONDICION_LABELS = ['NUEVO' => 'Nuevo', ...];       // + etiquetas legibles

    protected $fillable = [...];             // asignación masiva permitida
    protected $casts = ['fecha_adquisicion' => 'date', ...];

    public function modelo() { return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo'); }
    public function documentos() { return $this->hasMany(DocumentoAdjunto::class, 'entidad_id', 'id_activo')->where('entidad_tipo','ACTIVO'); }
}
```

**Convenciones de modelo (importantes):**
- **PK personalizada** por modelo (`id_activo`, `id_movimiento`, …) → declara `$primaryKey`
  y usa las llaves explícitas en las relaciones.
- **Timestamps en español**: unos modelos usan `CREATED_AT/UPDATED_AT` custom; muchos usan
  `public $timestamps = false` y la BD gestiona `creado_en` con `DEFAULT CURRENT_TIMESTAMP`.
- **Enums como `public const`** + su mapa `*_LABELS`. No hay tablas de catálogo para
  condición/situación (son ENUM directos).
- **Relaciones polimórficas "manuales"**: `DocumentoAdjunto` y `AuditoriaCambio` usan
  `entidad_tipo` + `entidad_id` (no la relación polimórfica nativa de Laravel).

**Catálogo de modelos:**

| Modelo | Tabla | Notas |
|---|---|---|
| `Activo` | `activo` | SoftDeletes, Observer, condición/situación |
| `ActivoTecnico` | `activo_tecnico` | Ficha técnica 1‑a‑1 |
| `HistorialCondicionActivo` | `historial_condicion_activo` | Transiciones de condición |
| `Movimiento` / `DetalleMovimientoActivo` | `movimientos` / `detalle_movimiento_activo` | Cabecera + detalle por activo |
| `Mantenimiento` / `MantenimientoAvance` | `mantenimientos` / `mantenimiento_avances` | Proceso + historial de avances |
| `BajaActivo` | `bajas_activo` | Propuesta/ejecución de baja |
| `DocumentoAdjunto` | `documentos_adjuntos` | Polimórfico (entidad_tipo/id) |
| `AuditoriaCambio` | `auditoria_cambios` | Traza polimórfica |
| `TramiteReferencia` | `tramites_referencias` | Referencias a trámite documentario |
| `Colaborador` | `colaboradores` | Personas (CARGOS) |
| `Sede`, `Dependencia`, `Sede_Dependencia` | `sedes`, `dependencias`, `sede_dependencia` | Organización |
| `Ubicacion` | `ubicaciones` | Árbol (id_ubicacion_padre) |
| `CategoriaActivo`, `Marca`, `Modelo` | `categoria_activo`, `marca`, `modelo` | Catálogos |
| `User`, `Rol`, `Permiso` | `usuarios`, `roles`, `permisos` | Accesos |

### 7.1 Observer del activo

`ActivoObserver` (registrado con `#[ObservedBy]` en `Activo`) es la **única vía de
escritura** del historial de condición. Reacciona a `created`/`updated`, lee el "contexto"
que el flujo marcó con `Activo::marcarOrigenCondicion(origen, entidadTipo, entidadId, motivo)`
y escribe una fila por transición. Es *best-effort* (try/catch): nunca rompe la operación.

---

## 8. Persistencia: migraciones y seeders

Migraciones en `database/migrations` (una por tabla/cambio). Estilo del proyecto:

```php
Schema::create('historial_condicion_activo', function (Blueprint $table) {
    $table->bigIncrements('id_historial');
    $table->integer('id_activo');                          // FKs como integer
    $table->enum('condicion_nueva', ['NUEVO','BUENO','REGULAR','MALO']);
    $table->dateTime('creado_en')->useCurrent();           // timestamp gestionado por BD
    $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
    $table->index('id_activo');
});
```

**Convenciones:**
- Nombre `AAAA_MM_DD_NNNNNN_descripcion.php`; PK `bigIncrements` o `integer()->autoIncrement()`.
- FKs `integer` apuntando a la PK real (`references('id_activo')->on('activo')`).
- `creado_en`/`actualizado_en` con `useCurrent()` / `useCurrentOnUpdate()`.
- Enums de dominio replicados en la migración **y** como `const` en el modelo (mantenerlos
  sincronizados a mano).

**Seeders** en `database/seeders` (p. ej. `RolSeeder` es idempotente vía `updateOrInsert`).

**Para agregar tabla/columna:** crea una migración (`php artisan make:migration`), aплícala
con `php artisan migrate`, y refleja los cambios en el `$fillable`/`$casts`/consts del modelo.

---

## 9. Servicios e integraciones externas

`app/Services` alberga lógica reutilizable e integraciones. Único servicio actual:

**`OcsInventoryService`** — cliente HTTP a la API de OCS Inventory:

```php
$response = Http::acceptJson()
    ->timeout(config('services.ocs.timeout', 15))
    ->get(config('services.ocs.url') . '/api/activos/' . rawurlencode($codigo));
```

- Configuración en `config/services.php` → `services.ocs.url` / `services.ocs.timeout`,
  leídas de `.env` (`OCS_API_URL`, `OCS_API_TIMEOUT`).
- Traduce errores (sin URL, 404, conexión, HTTP no exitoso) a `RuntimeException`, que el
  controlador convierte en 422/503.

**PhpSpreadsheet** se usa directamente en los controladores para Excel:
export completo (`ActivoController::exportarExcel`) e import con plantilla
(`ActivoImportController`).

**Para una integración nueva:** crea `app/Services/MiIntegracionService.php`, agrega su
config en `config/services.php` + claves en `.env`, e inyéctalo por el constructor del
método del controlador (el contenedor lo resuelve).

---

## 10. Almacenamiento de archivos y documentos

- Los adjuntos se guardan en el **disco privado `local`** (`storage/app/private`, ver
  `config/filesystems.php`), nunca en el público.
- Patrón de guardado: `$file->store('documentos/<entidad>', 'local')`.
- Descarga solo por ruta autenticada: `Storage::disk('local')->download($doc->archivo, $nombre)`.
- La **imagen** del activo sí va al disco `public` (`store('activos','public')`) para
  mostrarse; requiere `php artisan storage:link`.
- Toda la metadata se centraliza en `documentos_adjuntos` (polimórfico). Cada módulo tiene
  su propio método `guardarDocumento/guardarEvidencia/guardarSustento`.

---

## 11. Patrones transversales

| Patrón | Cómo se aplica | Dónde |
|---|---|---|
| **Validación** | `$request->validate([reglas], [mensajes])`; reglas condicionales con closures y `ValidationException::withMessages()` | Todos los controladores |
| **Transacciones** | `DB::transaction(fn () => ...)` para operaciones multi-tabla | store/devolver/finalizar/ejecutar |
| **Auditoría** | `AuditoriaCambio::registrar(tipo, id, accion, antes, nuevos, motivo)` — *best-effort* | Tras cada operación sensible |
| **Historial de condición** | `Activo::marcarOrigenCondicion(...)` antes del `update` + `ActivoObserver` | Flujos que cambian `condicion_actual` |
| **Respuesta AJAX** | `response()->json(['success','message','data'])` | Movimientos, mantenimientos, bajas, import |
| **Respuesta formulario** | `redirect()->route(...)->with('success', ...)` | Activos (store/update) |
| **Sincronización derivada** | `situarActivo()` actualiza situación + `estado_operativo` de la ficha técnica | Mantenimientos y bajas |

> Regla de oro: la auditoría y el historial **nunca** deben romper la operación principal
> (van envueltos en try/catch o se registran fuera del camino crítico).

---

## 12. Puente con el frontend (Vite)

- Los assets viven en `resources/js/pages/<módulo>/*.js` y `resources/css`, compilados con
  **Vite** (`npm run dev` en desarrollo, `npm run build` para producción).
- Cada Blade carga sus assets con `@vite([...])` en `@section('page-script')`.
- El backend expone datos al JS mediante `@json(...)` o variables `window.routes = {...}`.
- Librerías cliente: jQuery, Select2, DataTables, ApexCharts, SweetAlert2, JsBarcode/QRCode.

Backend y frontend se comunican por **rutas JSON** (AJAX) para las acciones dinámicas
(tablas, modales de movimiento/mantenimiento/baja, import) y por **formularios Blade**
para altas/ediciones completas.

---

## 13. Convenciones del proyecto

- **Idioma del dominio en español**: tablas, columnas, rutas, variables y enums
  (`activo`, `situacion_actual`, `movimientos.devolver`). Mantenerlo por coherencia.
- **Enums** como `public const` en el modelo + mapa `*_LABELS`; replicados en la migración.
- **Mensajes de validación** siempre personalizados en español.
- **PK e IDs** con prefijo `id_` y nombre de entidad (`id_activo`).
- **Timestamps** `creado_en` / `actualizado_en` (no `created_at`).
- **Comentarios** explican el "porqué" (reglas OTI, decisiones de negocio), no el "qué".
- **Código de folios** derivado por código (`MOV-000001`, `MANT-000001`).

---

## 14. Guía práctica: "¿dónde codifico X?"

| Quiero… | Archivos a tocar |
|---|---|
| **Agregar un campo al activo** | Migración (add column) → `Activo::$fillable`/`$casts` → validación en `ActivoController@store/@update` → `resources/views/content/activos/partials/form-fields.blade.php` → (export/import y ficha si aplica) |
| **Nuevo valor de condición/situación** | `Activo` consts + `*_LABELS` → migración (modificar ENUM en `activo` y en `detalle_movimiento_activo`) → lógica de flujos afectados → badges en las vistas |
| **Nuevo tipo de movimiento** | `Movimiento::TIPOS` → `MovimientoController::OPERACIONES` (regla del tipo) → migración (ENUM `movimientos.tipo`) → UI del modal en `activos/index` + JS |
| **Nuevo estado en mantenimiento/baja** | consts del modelo (`ESTADOS*`) → migración (ENUM) → transiciones en el controlador → badges en vistas/JS |
| **Enganchar un cambio de condición desde un flujo nuevo** | `$activo->marcarOrigenCondicion('ORIGEN','ENTIDAD',$id,$motivo)` **antes** del `update` que cambia `condicion_actual` (el Observer registra) |
| **Registrar un evento de auditoría** | `AuditoriaCambio::registrar('ENTIDAD',$id,'ACCION',$antes,$nuevos,$motivo)` en el punto del flujo |
| **Nuevo módulo CRUD maestro** | Migración + Modelo + Controlador + rutas (grupo `role:`) + vistas `content/<módulo>` + JS `pages/<módulo>` + entrada en `resources/menu/verticalMenu.json` |
| **Nuevo rol** | `RolSeeder` → aplicar `role:NUEVO_ROL` en las rutas → (helper en `User` si se usa en Blade) → menú/permisos |
| **Nueva integración externa** | `app/Services/XService.php` + `config/services.php` + `.env` + inyección en el controlador |
| **Nuevo tipo de documento (etiqueta)** | Opciones del `<select>` del modal correspondiente (Blade); el backend ya acepta cualquier string en `tipo_documento` |
| **Nuevo reporte/KPI** | `dashboard\Analytics` (consulta + arreglo para la vista) o `ReporteController` |

---

## 15. Mapa de archivos por módulo

| Módulo | Controlador | Modelo(s) | Rutas | Vistas | JS |
|---|---|---|---|---|---|
| Activos | `ActivoController`, `ActivoImportController` | `Activo`, `ActivoTecnico`, `HistorialCondicionActivo` | `activos.*` | `content/activos/*` | `pages/activos/*` |
| Movimientos | `MovimientoController` | `Movimiento`, `DetalleMovimientoActivo` | `movimientos.*` | `content/movimientos/*` | `pages/movimientos/*` |
| Mantenimientos | `MantenimientoController` | `Mantenimiento`, `MantenimientoAvance` | `mantenimientos.*` | `content/mantenimientos/*` | `pages/mantenimientos/*` |
| Bajas | `BajaActivoController` | `BajaActivo` | `bajas.*` | `content/bajas/*` | `pages/bajas/*` |
| Documentos | `DocumentoAdjuntoController` | `DocumentoAdjunto` | `documentos.*` | (modales embebidos) | (en el JS de cada módulo) |
| OCS | `OcsInventoryController` + `OcsInventoryService` | `Activo` | `activos.ocs.*` | `content/activos/ocs*` | `pages/activos/*` |
| Auditoría | `AuditoriaController` | `AuditoriaCambio` | `auditoria.index` | `content/auditoria/*` | — |
| Dashboard | `dashboard\Analytics` | (varios) | `home` | `content/dashboard/*` | — |
| Datos maestros | `Sede/Dependencia/Ubicacion/Colaborador/CategoriaActivo/Marca/Usuario/Rol Controller` | respectivos | `<modulo>.*` | `content/<modulo>/*` | `pages/<modulo>/*` |

---

## 16. Entorno y ejecución

**Comandos habituales:**

```bash
php artisan migrate            # aplica migraciones pendientes
php artisan migrate:status     # ver estado
php artisan db:seed            # ejecutar seeders (o --class=RolSeeder)
php artisan storage:link       # enlaza storage/app/public → public/storage
npm run dev                    # servidor Vite (desarrollo, HMR)
npm run build                  # compila assets (producción)
```

**Variables `.env` clave:** `APP_URL`, `DB_*` (conexión MySQL), `FILESYSTEM_DISK`,
`OCS_API_URL`, `OCS_API_TIMEOUT`.

**Discos (`config/filesystems.php`):** `local` = `storage/app/private` (privado, documentos);
`public` = `storage/app/public` (imágenes, requiere symlink).

---

*Documento técnico elaborado a partir del análisis del código (rutas, controladores,
modelos, middleware, servicios y configuración). Para el detalle funcional de cada flujo,
ver [funcionalidades-y-flujos.md](funcionalidades-y-flujos.md).*
