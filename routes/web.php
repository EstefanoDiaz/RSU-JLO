<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\VehicleColorController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\UserTypeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\VehicleController;

Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
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
    
    // CRUD de Personal / Usuarios
    Route::resource('user', UserController::class)->names('admin.user');
    
    // Rutas auxiliares sin parámetros
    Route::get('vehicle/models-by-brand', [VehicleController::class, 'modelsByBrand'])->name('admin.vehicle.modelsByBrand');

    // Rutas de imágenes
    Route::get('vehicle/{id}/images',             [VehicleController::class, 'getImages'])->name('admin.vehicle.images');
    Route::post('vehicle/{id}/upload-image',      [VehicleController::class, 'uploadImage'])->name('admin.vehicle.upload-image');
    Route::delete('vehicle/image/{imageId}',      [VehicleController::class, 'deleteImage'])->name('admin.vehicle.delete-image');
    Route::put('vehicle/image/{imageId}/profile', [VehicleController::class, 'setProfile'])->name('admin.vehicle.set-profile');

    // Resource al final
    Route::resource('vehicle', VehicleController::class)->names('admin.vehicle');   
});