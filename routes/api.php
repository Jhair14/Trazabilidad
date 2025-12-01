<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductionBatchController;
use App\Http\Controllers\Api\ProcessTransformationController;
use App\Http\Controllers\Api\ProcessEvaluationController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\RawMaterialController;
use App\Http\Controllers\Api\RawMaterialBaseController;
use App\Http\Controllers\Api\MaterialMovementLogController;
use App\Http\Controllers\Api\UnitOfMeasureController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // CRUD Routes using apiResource (ibex pattern)
    Route::apiResource('unit-of-measures', UnitOfMeasureController::class);
    Route::apiResource('statuses', \App\Http\Controllers\Api\StatusController::class);
    Route::apiResource('movement-types', \App\Http\Controllers\Api\MovementTypeController::class);
    Route::apiResource('operator-roles', \App\Http\Controllers\Api\OperatorRoleController::class);
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class);
    Route::apiResource('raw-material-categories', \App\Http\Controllers\Api\RawMaterialCategoryController::class);
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);
    Route::apiResource('standard-variables', \App\Http\Controllers\Api\StandardVariableController::class);
    Route::apiResource('machines', \App\Http\Controllers\Api\MachineController::class);
    Route::apiResource('processes', \App\Http\Controllers\Api\ProcessController::class);
    Route::apiResource('operators', \App\Http\Controllers\Api\OperatorController::class);
    Route::apiResource('raw-material-bases', RawMaterialBaseController::class);
    Route::apiResource('raw-materials', RawMaterialController::class);
    Route::apiResource('customer-orders', CustomerOrderController::class);
    Route::apiResource('production-batches', ProductionBatchController::class);
    Route::apiResource('batch-raw-materials', \App\Http\Controllers\Api\BatchRawMaterialController::class);
    Route::apiResource('material-movement-logs', MaterialMovementLogController::class);
    Route::apiResource('process-machines', \App\Http\Controllers\Api\ProcessMachineController::class);
    Route::apiResource('process-machine-variables', \App\Http\Controllers\Api\ProcessMachineVariableController::class);
    Route::apiResource('process-machine-records', \App\Http\Controllers\Api\ProcessMachineRecordController::class);
    Route::apiResource('process-final-evaluations', \App\Http\Controllers\Api\ProcessFinalEvaluationController::class);
    Route::apiResource('storages', StorageController::class);
    Route::apiResource('material-requests', \App\Http\Controllers\Api\MaterialRequestController::class);
    Route::apiResource('material-request-details', \App\Http\Controllers\Api\MaterialRequestDetailController::class);
    Route::apiResource('supplier-responses', \App\Http\Controllers\Api\SupplierResponseController::class);

    // Custom routes for business logic
    // Process Transformation
    Route::prefix('process-transformation')->group(function () {
        Route::post('/batch/{batchId}/machine/{processMachineId}', [ProcessTransformationController::class, 'registerForm']);
        Route::get('/batch/{batchId}/machine/{processMachineId}', [ProcessTransformationController::class, 'getForm']);
        Route::get('/batch/{batchId}', [ProcessTransformationController::class, 'getBatchProcess']);
    });

    // Process Evaluation
    Route::prefix('process-evaluation')->group(function () {
        Route::post('/finalize/{batchId}', [ProcessEvaluationController::class, 'finalize']);
        Route::get('/log/{batchId}', [ProcessEvaluationController::class, 'getLog']);
    });

    // Storage custom routes
    Route::get('/storages/batch/{batchId}', [StorageController::class, 'getByBatch']);
    
    // Material Movement Log custom routes
    Route::get('/material-movement-logs/material/{materialId}', [MaterialMovementLogController::class, 'getByMaterial']);

    // Batch Certification routes
    Route::prefix('batches')->group(function () {
        Route::get('/pending-certification', [ProductionBatchController::class, 'getPendingCertification']);
        Route::post('/{batchId}/assign-process', [ProductionBatchController::class, 'assignProcess']);
        Route::get('/{batchId}/process-machines', [ProductionBatchController::class, 'getProcessMachines']);
        Route::post('/{batchId}/finalize-certification', [ProductionBatchController::class, 'finalizeCertification']);
        Route::get('/{batchId}/certification-log', [ProductionBatchController::class, 'getCertificationLog']);
    });

    // Image Upload
    Route::post('/upload', [\App\Http\Controllers\Web\ImageUploadController::class, 'upload']);
});

// Legacy routes (keeping for compatibility)
Route::apiResource('procesos', \App\Http\Controllers\ProcesoController::class);
Route::apiResource('operadores', \App\Http\Controllers\OperadorController::class);
Route::apiResource('proveedores', \App\Http\Controllers\ProveedorController::class);
Route::apiResource('maquinas', \App\Http\Controllers\MaquinaController::class);
