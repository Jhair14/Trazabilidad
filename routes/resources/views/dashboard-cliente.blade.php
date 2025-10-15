@extends('layouts.app')

@section('page_title', 'Panel de Cliente')

@section('content')
<div class="row">
    <!-- KPIs del Cliente -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>5</h3>
                <p>Mis Pedidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>2</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>2</h3>
                <p>Completados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>1</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Seguimiento del Último Pedido -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-timeline mr-1"></i>
                    Seguimiento de tu Último Pedido
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Pedido #P001</h5>
                        <p><strong>Descripción:</strong> Producto de alta calidad</p>
                        <p><strong>Fecha de creación:</strong> 15/01/2024</p>
                        <p><strong>Estado actual:</strong> <span class="badge badge-primary">En Proceso</span></p>
                    </div>
                    <div class="col-md-6">
                        <!-- Timeline de Estados -->
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-red">Progreso del Pedido</span>
                            </div>
                            
                            <div>
                                <i class="fas fa-check bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> 15/01/2024</span>
                                    <h3 class="timeline-header">Pedido Creado</h3>
                                    <div class="timeline-body">
                                        Tu pedido ha sido registrado exitosamente.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-check bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> 16/01/2024</span>
                                    <h3 class="timeline-header">Materia Prima Solicitada</h3>
                                    <div class="timeline-body">
                                        Se ha solicitado la materia prima necesaria.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-cog bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> 17/01/2024</span>
                                    <h3 class="timeline-header">En Proceso</h3>
                                    <div class="timeline-body">
                                        Tu pedido está siendo procesado actualmente.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                                <div class="timeline-item">
                                    <span class="time">Pendiente</span>
                                    <h3 class="timeline-header">Producción Finalizada</h3>
                                    <div class="timeline-body">
                                        Esperando que se complete la producción.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                                <div class="timeline-item">
                                    <span class="time">Pendiente</span>
                                    <h3 class="timeline-header">Almacenado</h3>
                                    <div class="timeline-body">
                                        El producto será almacenado una vez completado.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mis Pedidos Recientes -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Mis Pedidos Recientes
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#P001</td>
                                <td>Producto de alta calidad</td>
                                <td>15/01/2024</td>
                                <td><span class="badge badge-primary">En Proceso</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#P002</td>
                                <td>Producto estándar</td>
                                <td>10/01/2024</td>
                                <td><span class="badge badge-success">Completado</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar Certificado">
                                        <i class="fas fa-certificate"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#P003</td>
                                <td>Producto premium</td>
                                <td>05/01/2024</td>
                                <td><span class="badge badge-success">Completado</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar Certificado">
                                        <i class="fas fa-certificate"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    position: relative;
    margin-bottom: 15px;
    margin-right: 10px;
    margin-left: 60px;
}

.timeline > div:before,
.timeline > div:after {
    content: "";
    display: table;
}

.timeline > div:after {
    clear: both;
}

.timeline > div > .timeline-item {
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    background: #fff;
    color: #444;
    margin-left: 60px;
    padding: 0;
    position: relative;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    font-size: 12px;
    padding: 10px;
}

.timeline > div > .timeline-item > .timeline-header {
    border-bottom: 1px solid #f4f4f4;
    color: #444;
    font-size: 16px;
    line-height: 1.1;
    margin: 0;
    padding: 10px;
}

.timeline > div > .timeline-item > .timeline-body,
.timeline > div > .timeline-item > .timeline-footer {
    padding: 10px;
}

.timeline > div > i {
    background: #6c757d;
    border-radius: 50%;
    color: #fff;
    font-size: 12px;
    height: 30px;
    left: 18px;
    line-height: 30px;
    position: absolute;
    text-align: center;
    top: 0;
    width: 30px;
}

.timeline > div > .bg-green {
    background-color: #28a745 !important;
}

.timeline > div > .bg-blue {
    background-color: #007bff !important;
}

.timeline > div > .bg-gray {
    background-color: #6c757d !important;
}
</style>
@endpush
