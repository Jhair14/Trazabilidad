@extends('layouts.app')

@section('page_title', 'Certificar Lote')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<!-- Estadísticas de Certificación -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $lotes->count() }}</h3>
                <p>Lotes Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lotes->where('latestFinalEvaluation', '!=', null)->filter(function($l) { return !str_contains(strtolower($l->latestFinalEvaluation->reason ?? ''), 'falló'); })->count() }}</h3>
                <p>Certificados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $lotes->where('start_time', '!=', null)->where('end_time', null)->count() }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $lotes->filter(function($l) { return $l->latestFinalEvaluation && str_contains(strtolower($l->latestFinalEvaluation->reason ?? ''), 'falló'); })->count() }}</h3>
                <p>No Certificados</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Lotes para Certificar -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Lotes Pendientes de Certificación
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID Lote</th>
                        <th>Nombre</th>
                        <th>Cliente</th>
                        <th>Fecha Producción</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                    <tr>
                        <td>#{{ $lote->batch_code ?? $lote->batch_id }}</td>
                        <td>{{ $lote->name ?? 'Sin nombre' }}</td>
                        <td>{{ $lote->order->customer->business_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($lote->creation_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($lote->latestFinalEvaluation)
                                @if(str_contains(strtolower($lote->latestFinalEvaluation->reason ?? ''), 'falló'))
                                    <span class="badge badge-danger">No Certificado</span>
                                @else
                                    <span class="badge badge-success">Certificado</span>
                                @endif
                            @elseif($lote->processMachineRecords->isNotEmpty())
                                <span class="badge badge-warning">Listo para Certificar</span>
                            @else
                                <span class="badge badge-info">Pendiente - Sin Proceso</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('gestion-lotes.show', $lote->batch_id) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(!$lote->latestFinalEvaluation)
                                @if($lote->processMachineRecords->isEmpty())
                                    <a href="{{ route('proceso-transformacion', $lote->batch_id) }}" class="btn btn-sm btn-warning" title="Ir a Proceso de Transformación">
                                        <i class="fas fa-cogs"></i> Proceso
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('certificar-lote.finalizar', $lote->batch_id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Certificar" onclick="return confirm('¿Desea certificar este lote?')">
                                            <i class="fas fa-check"></i> Certificar
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay lotes pendientes de certificación</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Certificar Lote -->
<div class="modal fade" id="modalCertificarLote" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Certificar Lote</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idLote">ID del Lote</label>
                                <input type="text" class="form-control" id="idLote" value="#L001" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaCertificacion">Fecha de Certificación</label>
                                <input type="date" class="form-control" id="fechaCertificacion">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones sobre la certificación..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="certificador">Certificador</label>
                        <select class="form-control" id="certificador">
                            <option>Seleccionar Certificador</option>
                            <option>Ana García - Supervisor</option>
                            <option>Carlos López - Inspector</option>
                            <option>María Rodríguez - Auditor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cumpleEstándares">
                            <label class="form-check-label" for="cumpleEstándares">
                                El lote cumple con todos los estándares de calidad
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success">Certificar Lote</button>
            </div>
        </div>
    </div>
</div>
@endsection
