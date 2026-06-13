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
});