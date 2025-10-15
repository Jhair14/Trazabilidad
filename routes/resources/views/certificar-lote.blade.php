@extends('layouts.app')

@section('page_title', 'Certificar Lote')

@section('content')
<!-- Estadísticas de Certificación -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>15</h3>
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
                <h3>8</h3>
                <p>Certificados Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>3</h3>
                <p>En Revisión</p>
            </div>
            <div class="icon">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>2</h3>
                <p>Rechazados</p>
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
                    <tr>
                        <td>#L001</td>
                        <td>Lote Producción A</td>
                        <td>Cliente ABC</td>
                        <td>15/01/2024</td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" title="Certificar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Rechazar">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#L002</td>
                        <td>Lote Producción B</td>
                        <td>Cliente XYZ</td>
                        <td>16/01/2024</td>
                        <td><span class="badge badge-info">En Revisión</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" title="Certificar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Rechazar">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#L003</td>
                        <td>Lote Producción C</td>
                        <td>Cliente DEF</td>
                        <td>17/01/2024</td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" title="Certificar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Rechazar">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        <ul class="pagination pagination-sm m-0 float-right">
            <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
        </ul>
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
