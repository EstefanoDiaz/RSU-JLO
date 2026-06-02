<?php

use App\Http\Controllers\MarcaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\VehicleColorController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\UserTypeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\VehicleController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::resource('color', VehicleColorController::class)->names('admin.color');
Route::resource('brandmodel', BrandModelController::class)->names('admin.brandmodel');
Route::resource('tipo-vehiculo', VehicleTypeController::class)->names('admin.tipo-vehiculo');
Route::resource('brand', BrandController::class)->names('admin.brand');
Route::resource('user-type', UserTypeController::class)->names('admin.usertype');
Route::resource('user', UserController::class)->names('admin.user');
Route::get('vehicle/models-by-brand', [VehicleController::class, 'modelsByBrand'])->name('admin.vehicle.modelsByBrand');
Route::resource('vehicle', VehicleController::class)->names('admin.vehicle');