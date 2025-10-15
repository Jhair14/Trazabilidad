<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProveedorWebController;
use App\Http\Controllers\Web\OperadorWebController;
use App\Http\Controllers\Web\ProcesoWebController;
use App\Http\Controllers\Web\MaquinaWebController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Auth;

Route::redirect('/', '/login');

// Login
Route::get('/login', function () {
    return view('login');
})->name('login');

// Dashboard Administrativo
Route::view('/dashboard', 'dashboard')->name('dashboard');

// Dashboard Cliente
Route::view('/dashboard-cliente', 'dashboard-cliente')->name('dashboard-cliente');

// MATERIA PRIMA
Route::view('/materia-prima-base', 'materia-prima-base')->name('materia-prima-base');
Route::view('/solicitar-materia-prima', 'solicitar-materia-prima')->name('solicitar-materia-prima');
Route::view('/recepcion-materia-prima', 'recepcion-materia-prima')->name('recepcion-materia-prima');

// Proveedores (subcarpeta)
Route::view('/proveedores', 'proveedores')->name('proveedores.index');Route::get('/proveedores/create', function() { return view('proveedores.create'); })->name('proveedores.create');

// Gestión de Lotes
Route::view('/gestion-lotes', 'gestion-lotes')->name('gestion-lotes');

// PROCESOS
Route::view('/maquinas', 'maquinas')->name('maquinas');
Route::view('/procesos', 'procesos')->name('procesos.index');
Route::view('/variables-estandar', 'variables-estandar')->name('variables-estandar');

// CERTIFICACIÓN
Route::view('/certificar-lote', 'certificar-lote')->name('certificar-lote');
Route::view('/certificados', 'certificados')->name('certificados');

// ALMACEN
Route::view('/lotes-almacenados', 'lotes-almacenados')->name('lotes-almacenados');

// PEDIDOS
Route::view('/mis-pedidos', 'mis-pedidos')->name('mis-pedidos');
Route::view('/gestion-pedidos', 'gestion-pedidos')->name('gestion-pedidos');
// ADMINISTRACIÓN
Route::view('/usuarios', 'usuarios')->name('usuarios');
Route::view('/usuarios', 'usuarios')->name('usuarios');
// CERRAR SESIÓN
Route::get('/logout', function() {
    Auth::logout();
    return redirect('/login');
})->name('logout');
