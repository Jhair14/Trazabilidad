<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MaquinaController;

Route::apiResource('procesos', ProcesoController::class);
Route::apiResource('operadores', OperadorController::class);
Route::apiResource('proveedores', ProveedorController::class);
Route::apiResource('maquinas', MaquinaController::class);
