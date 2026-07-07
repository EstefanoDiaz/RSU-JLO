<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\VehicleColorController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\UserTypeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\VehicleController;
use App\Http\Controllers\admin\ContractController;
use App\Http\Controllers\admin\ScheduleController;
use App\Http\Controllers\admin\AttendanceController;
use App\Http\Controllers\admin\VacationController;
use App\Http\Controllers\admin\ZoneController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\admin\HolidayController;
use App\Http\Controllers\admin\PersonalGroupController;
use App\Http\Controllers\admin\ProgramacionController;
use App\Http\Controllers\admin\CambioController;
use App\Http\Controllers\admin\CambioMasivoController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\MantenimientoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirección inicial pública
Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Ruta base del Dashboard de Jetstream
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');



    // MÓDULO ADMINISTRATIVO (PROTEGIDO)
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // CRUDs de Configuración y Tablas Maestras
    Route::resource('color', VehicleColorController::class)->names('admin.color');
    Route::resource('brandmodel', BrandModelController::class)->names('admin.brandmodel');
    Route::resource('tipo-vehiculo', VehicleTypeController::class)->names('admin.tipo-vehiculo');
    Route::resource('brand', BrandController::class)->names('admin.brand');
    Route::resource('user-type', UserTypeController::class)->names('admin.usertype');

    // CRUD de Personal / Usuarios (El que acabamos de armar con estado y foto)
    Route::resource('user', UserController::class)->names('admin.user');

    // Módulo de Vehículos y su ruta auxiliar AJAX para los modelos
    Route::get('vehicle/models-by-brand', [VehicleController::class, 'modelsByBrand'])->name('admin.vehicle.modelsByBrand');
    Route::resource('vehicle', VehicleController::class)->names('admin.vehicle');


    // Rutas auxiliares sin parámetros
    Route::get('vehicle/models-by-brand', [VehicleController::class, 'modelsByBrand'])->name('admin.vehicle.modelsByBrand');

    // Rutas de imágenes
    Route::get('vehicle/{id}/images', [VehicleController::class, 'getImages'])->name('admin.vehicle.images');
    Route::post('vehicle/{id}/upload-image', [VehicleController::class, 'uploadImage'])->name('admin.vehicle.upload-image');
    Route::delete('vehicle/image/{imageId}', [VehicleController::class, 'deleteImage'])->name('admin.vehicle.delete-image');
    Route::put('vehicle/image/{imageId}/profile', [VehicleController::class, 'setProfile'])->name('admin.vehicle.set-profile');

    // Resource al final
    Route::resource('vehicle', VehicleController::class)->names('admin.vehicle');


    Route::post('contract/{id}/toggle', [ContractController::class, 'toggle'])->name('admin.contract.toggle');
    Route::resource('contract', ContractController::class)->names('admin.contract');


    // Turnos
    Route::resource('schedule', ScheduleController::class)->names('admin.schedule');

    // Asistencias
    Route::get('attendance/schedule-by-time', [AttendanceController::class, 'getScheduleByTime'])->name('admin.attendance.scheduleByTime');
    Route::get('attendance/type', [AttendanceController::class, 'getAttendanceType'])->name('admin.attendance.type');
    Route::get('attendance/user-info', [AttendanceController::class, 'getUserInfo'])->name('admin.attendance.userInfo');
    Route::resource('attendance', AttendanceController::class)->names('admin.attendance');


    //RUTA VACACIONES
    Route::resource('admin/vacation', VacationController::class)->names('admin.vacation');
    Route::post('admin/vacation/{id}/approve', [VacationController::class, 'approve'])->name('admin.vacation.approve');
    Route::post('admin/vacation/{id}/reject', [VacationController::class, 'reject'])->name('admin.vacation.reject');
    Route::get('admin/vacation-check-live', [VacationController::class, 'checkLive'])->name('admin.vacation.checkLive');

    //RUTA ZONAS
    Route::resource('admin/zone', ZoneController::class)->names('admin.zone');
    // Ruta para obtener los datos de las zonas en formato GeoJSON para el mapa
    Route::get('zones/map-data', [ZoneController::class, 'getZonesForMap'])->name('admin.zone.mapdata');
    // Ruta para obtener los detalles de una zona específica para mostrar en el mapa
    Route::get('zones/{id}/map-details', [ZoneController::class, 'getSingleZoneMapDetails'])->name('admin.zones.mapDetails');
    // Rutas para los combobox encadenados
    Route::get('locations/departments/{id}/provinces', [ProvinceController::class, 'getProvinces'])->name('admin.locations.provinces');
    Route::get('locations/provinces/{id}/districts', [DistrictController::class, 'getDistricts'])->name('admin.locations.districts');

    // Ruta Feriados
    Route::resource('admin/holiday', HolidayController::class)->names('admin.holiday');

    // GRUPOS DE PERSONAL
    // Rutas específicas primero
    Route::get('programacion/grupos/search-users', [PersonalGroupController::class, 'searchUsers'])
        ->name('admin.personal-group.search-users');

    Route::get('programacion/grupos/vehicle-info/{id}', [PersonalGroupController::class, 'vehicleInfo'])
        ->name('admin.personal-group.vehicle-info');

    Route::get('programacion/grupos/{id}/data', [PersonalGroupController::class, 'getGroupData'])
        ->name('admin.personal-group.data');

    // Resource al final
    Route::resource('programacion/grupos', PersonalGroupController::class)
        ->except('show')
        ->names('admin.personal-group');





    // ── PROGRAMACIONES ─────────────────────────────────────────────────────────
    // IMPORTANTE: las rutas estáticas deben ir ANTES del Route::resource
    // para evitar que Laravel interprete 'validate', 'search-users', etc. como {programacion}

    Route::post(
        'admin/programacion/validate',
        [ProgramacionController::class, 'validateAvailability']
    )->name('admin.programacion.validate');

    Route::get(
        'admin/programacion/search-users',
        [ProgramacionController::class, 'searchUsers']
    )->name('admin.programacion.search-users');

    Route::post(
        'admin/programacion/{id}/finalizar',
        [ProgramacionController::class, 'finalize']
    )->name('admin.programacion.finalize');

    Route::get(
        'admin/programacion/{id}/historial',
        [ProgramacionController::class, 'historial']
    )->name('admin.programacion.historial');

    // ══════════════════════════════════════════════════════════════════════════════
    // RUTAS A AGREGAR — pegar ANTES del Route::resource de programacion
    // ══════════════════════════════════════════════════════════════════════════════

    // Programación Masiva
    Route::get(
        'admin/programacion/masivo/create',
        [ProgramacionController::class, 'createMasivo']
    )->name('admin.programacion.create-masivo');

    Route::post(
        'admin/programacion/masivo/store',
        [ProgramacionController::class, 'storeMasivo']
    )->name('admin.programacion.store-masivo');

    Route::post(
        'admin/programacion/masivo/validate',
        [ProgramacionController::class, 'validateMasivo']
    )->name('admin.programacion.validate-masivo');

    Route::get(
        'admin/programacion/feriados',
        [ProgramacionController::class, 'getFeriados']
    )->name('admin.programacion.feriados');

    // Resource (genera index, create, store, edit, update, destroy)
    Route::resource('admin/programacion', ProgramacionController::class)
        ->except('show')
        ->names('admin.programacion');

    // Show aparte porque devuelve JSON (no vista)
    Route::get(
        'admin/programacion/{programacion}',
        [ProgramacionController::class, 'show']
    )->name('admin.programacion.show');




    // ── MOTIVOS DE CAMBIO ──────────────────────────────────────────────────────
    Route::resource('admin/cambio', CambioController::class)
        ->except('show')
        ->names('admin.cambio');





    // CAMBIOS MASIVOS
    Route::get('admin/cambios-masivos/create-form', [CambioMasivoController::class, 'createForm'])->name('admin.cambios-masivos.create-form');
    Route::get('admin/cambios-masivos/search-users', [CambioMasivoController::class, 'searchUsers'])->name('admin.cambios-masivos.search-users');
    Route::get('admin/cambios-masivos/personas-rango', [CambioMasivoController::class, 'getPersonasEnRango'])->name('admin.cambios-masivos.personas-rango');
    Route::get('admin/cambios-masivos/recursos-rango', [CambioMasivoController::class, 'getRecursosEnRango'])->name('admin.cambios-masivos.recursos-rango');
    Route::post('admin/cambios-masivos/{id}/revertir', [CambioMasivoController::class, 'revertFila'])->name('admin.cambios-masivos.revertir');
    Route::resource('admin/cambios-masivos', CambioMasivoController::class)->only(['index', 'show', 'store'])->names('admin.cambios-masivos');





    //
    Route::get('admin/monitoreo', [DashboardController::class, 'index'])->name('admin.monitoreo.index');
    Route::get('admin/monitoreo/detalle/{id}', [DashboardController::class, 'detalle'])->name('admin.monitoreo.detalle');
    Route::get('admin/monitoreo/personal-disponible', [DashboardController::class, 'personalDisponible'])->name('admin.monitoreo.personal-disponible');
    Route::post('admin/monitoreo/reemplazar/{id}', [DashboardController::class, 'reemplazar'])->name('admin.monitoreo.reemplazar');
    Route::post('admin/monitoreo/cambiar-turno/{id}', [DashboardController::class, 'cambiarTurno'])->name('admin.monitoreo.cambiar-turno');
    Route::post('admin/monitoreo/cambiar-vehiculo/{id}', [DashboardController::class, 'cambiarVehiculo'])->name('admin.monitoreo.cambiar-vehiculo');
    Route::get('admin/monitoreo/verificar-asistencia', [DashboardController::class, 'verificarAsistencia'])->name('admin.monitoreo.verificar-asistencia');

    // MANTENIMIENTOS
    Route::get('admin/mantenimientos/form-data',                    [MantenimientoController::class, 'formData'])->name('admin.mantenimientos.form-data');
    Route::get('admin/mantenimientos/{id}/horarios',                [MantenimientoController::class, 'horarios'])->name('admin.mantenimientos.horarios');
    Route::post('admin/mantenimientos/{id}/horarios',               [MantenimientoController::class, 'storeHorario'])->name('admin.mantenimientos.horarios.store');
    Route::put('admin/mantenimientos/horarios/{horario}',           [MantenimientoController::class, 'updateHorario'])->name('admin.mantenimientos.horarios.update');
    Route::delete('admin/mantenimientos/horarios/{horario}',        [MantenimientoController::class, 'destroyHorario'])->name('admin.mantenimientos.horarios.destroy');
    Route::get('admin/mantenimientos/horarios/{horario}/detalles',  [MantenimientoController::class, 'detalles'])->name('admin.mantenimientos.detalles');
    Route::post('admin/mantenimientos/detalles/{detalle}',          [MantenimientoController::class, 'updateDetalle'])->name('admin.mantenimientos.detalles.update');
    Route::resource('admin/mantenimientos', MantenimientoController::class)->only(['index','store','edit','update','destroy'])->names('admin.mantenimientos');

});