<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DashboardClienteController;
use App\Http\Controllers\Web\GestionLotesController;
use App\Http\Controllers\Web\MateriaPrimaBaseController;
use App\Http\Controllers\Web\SolicitarMateriaPrimaController;
use App\Http\Controllers\Web\RecepcionMateriaPrimaController;
use App\Http\Controllers\Web\ProveedorWebController;
use App\Http\Controllers\Web\MaquinaWebController;
use App\Http\Controllers\Web\ProcesoWebController;
use App\Http\Controllers\Web\OperadorWebController;
use App\Http\Controllers\Web\VariablesEstandarController;
use App\Http\Controllers\Web\CertificarLoteController;
use App\Http\Controllers\Web\CertificadosController;
use App\Http\Controllers\Web\AlmacenajeController;
use App\Http\Controllers\Web\LotesAlmacenadosController;
use App\Http\Controllers\Web\PedidosController;
use App\Http\Controllers\Web\GestionPedidosController;
use App\Http\Controllers\Web\UsuariosController;
use App\Http\Controllers\Web\ProcesoTransformacionController;

Route::redirect('/', '/login');

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta pública para ver certificados (accesible desde QR)
Route::get('/certificado-publico/{id}', [CertificadosController::class, 'showPublic'])->name('certificado.publico');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    // Dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-cliente', [DashboardClienteController::class, 'index'])->name('dashboard-cliente');
    Route::get('/dashboard-cliente/pedido/{orderId}', [DashboardClienteController::class, 'obtenerDetallesPedido'])->name('dashboard-cliente.pedido.detalles');

    // Materia Prima
    Route::get('/materia-prima-base', [MateriaPrimaBaseController::class, 'index'])->name('materia-prima-base');
    Route::post('/materia-prima-base', [MateriaPrimaBaseController::class, 'store']);
    Route::get('/materia-prima-base/{id}', [MateriaPrimaBaseController::class, 'show'])->name('materia-prima-base.show');
    Route::put('/materia-prima-base/{id}', [MateriaPrimaBaseController::class, 'update'])->name('materia-prima-base.update');
    
    Route::get('/solicitar-materia-prima', [SolicitarMateriaPrimaController::class, 'index'])->name('solicitar-materia-prima');
    Route::post('/solicitar-materia-prima', [SolicitarMateriaPrimaController::class, 'store']);
    
    Route::get('/recepcion-materia-prima', [RecepcionMateriaPrimaController::class, 'index'])->name('recepcion-materia-prima');
    Route::post('/recepcion-materia-prima', [RecepcionMateriaPrimaController::class, 'store']);

    // Proveedores
    Route::resource('proveedores', ProveedorWebController::class);

    // Gestión de Lotes
    Route::get('/gestion-lotes', [GestionLotesController::class, 'index'])->name('gestion-lotes');
    Route::post('/gestion-lotes', [GestionLotesController::class, 'store']);
    Route::get('/gestion-lotes/{id}', [GestionLotesController::class, 'show'])->name('gestion-lotes.show');
    Route::get('/gestion-lotes/{id}/edit', [GestionLotesController::class, 'edit'])->name('gestion-lotes.edit');
    Route::put('/gestion-lotes/{id}', [GestionLotesController::class, 'update'])->name('gestion-lotes.update');

    // Máquinas
    Route::resource('maquinas', MaquinaWebController::class);

    // Procesos
    Route::resource('procesos', ProcesoWebController::class);

    // Variables Estándar
    Route::get('/variables-estandar', [VariablesEstandarController::class, 'index'])->name('variables-estandar');
    Route::post('/variables-estandar', [VariablesEstandarController::class, 'store']);
    Route::get('/variables-estandar/{id}', [VariablesEstandarController::class, 'show'])->name('variables-estandar.show');
    Route::put('/variables-estandar/{id}', [VariablesEstandarController::class, 'update'])->name('variables-estandar.update');
    Route::delete('/variables-estandar/{id}', [VariablesEstandarController::class, 'destroy'])->name('variables-estandar.destroy');

    // Proceso de Transformación
    Route::get('/proceso/{batchId}', [ProcesoTransformacionController::class, 'index'])->name('proceso-transformacion');
    Route::post('/proceso/{batchId}/asignar', [ProcesoTransformacionController::class, 'asignarProceso'])->name('proceso-transformacion.asignar');
    Route::get('/proceso/{batchId}/maquina/{processMachineId}', [ProcesoTransformacionController::class, 'mostrarFormulario'])->name('proceso-transformacion.mostrar-formulario');
    Route::post('/proceso/{batchId}/maquina/{processMachineId}', [ProcesoTransformacionController::class, 'registrarFormulario'])->name('proceso-transformacion.registrar');
    Route::get('/proceso/{batchId}/maquina/{processMachineId}/formulario', [ProcesoTransformacionController::class, 'obtenerFormulario'])->name('proceso-transformacion.formulario');
    Route::get('/proceso/{processId}/maquinas', [ProcesoTransformacionController::class, 'obtenerMaquinasProceso'])->name('proceso-transformacion.maquinas');

    // Certificación
    Route::get('/certificar-lote', [CertificarLoteController::class, 'index'])->name('certificar-lote');
    Route::post('/certificar-lote/{batchId}', [CertificarLoteController::class, 'finalizar'])->name('certificar-lote.finalizar');
    Route::get('/certificar-lote/{batchId}/log', [CertificarLoteController::class, 'obtenerLog'])->name('certificar-lote.log');
    
    Route::get('/certificados', [CertificadosController::class, 'index'])->name('certificados');
    Route::get('/certificado/{id}', [CertificadosController::class, 'show'])->name('certificado.show');
    Route::get('/certificado/{id}/qr', [CertificadosController::class, 'qr'])->name('certificado.qr');

    // Almacenaje
    Route::get('/almacenaje', [AlmacenajeController::class, 'index'])->name('almacenaje');
    Route::post('/almacenaje', [AlmacenajeController::class, 'almacenar'])->name('almacenaje.store');
    Route::get('/almacenaje/lote/{batchId}', [AlmacenajeController::class, 'obtenerAlmacenajesPorLote'])->name('almacenaje.por-lote');
    
    Route::get('/lotes-almacenados', [LotesAlmacenadosController::class, 'index'])->name('lotes-almacenados');
    Route::get('/lotes-almacenados/lote/{batchId}', [LotesAlmacenadosController::class, 'obtenerAlmacenajesPorLote'])->name('lotes-almacenados.por-lote');

    // Pedidos
    Route::get('/mis-pedidos', [PedidosController::class, 'misPedidos'])->name('mis-pedidos');
    Route::post('/mis-pedidos', [PedidosController::class, 'crearPedido'])->name('mis-pedidos.store');
    Route::get('/mis-pedidos/{id}', [PedidosController::class, 'show'])->name('mis-pedidos.show');
    
    Route::get('/gestion-pedidos', [GestionPedidosController::class, 'index'])->name('gestion-pedidos');
    Route::put('/gestion-pedidos/{id}', [GestionPedidosController::class, 'update'])->name('gestion-pedidos.update');

    // Usuarios/Operadores
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
    Route::post('/usuarios', [UsuariosController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update']);

    // Operadores (CRUD completo)
    Route::resource('operadores', OperadorWebController::class);

    // Carga de imágenes
    Route::post('/upload-image', [\App\Http\Controllers\Web\ImageUploadController::class, 'upload'])->name('upload-image');
    Route::delete('/delete-image', [\App\Http\Controllers\Web\ImageUploadController::class, 'delete'])->name('delete-image');
});
