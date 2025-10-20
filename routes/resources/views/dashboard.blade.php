@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- KPIs Row -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>150</h3>
                <p>Pedidos Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="{{ route('gestion-pedidos') }}" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>53</h3>
                <p>Lotes Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="{{ route('gestion-lotes') }}" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning ">
            <div class="inner ">
                <h3>44</h3>
                <p>Pedidos Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('gestion-pedidos') }}" class=" small-box-footer">
                Más información <i class="fas fa-arrow-circle-right "></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>65</h3>
                <p>Lotes Certificados</p>
            </div>
            <div class="icon">
                <i class="fas fa-certificate"></i>
            </div>
            <a href="{{ route('certificados') }}" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfica de Estado de Pedidos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Estado de Pedidos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="pedidosChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfica de Estado de Lotes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Estado de Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="lotesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimos Pedidos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Últimos Pedidos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#001</td>
                                <td>Cliente A</td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>2024-01-15</td>
                            </tr>
                            <tr>
                                <td>#002</td>
                                <td>Cliente B</td>
                                <td><span class="badge badge-info">En Proceso</span></td>
                                <td>2024-01-14</td>
                            </tr>
                            <tr>
                                <td>#003</td>
                                <td>Cliente C</td>
                                <td><span class="badge badge-success">Completado</span></td>
                                <td>2024-01-13</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Lotes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes mr-1"></i>
                    Últimos Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#L001</td>
                                <td>Lote Producción A</td>
                                <td><span class="badge badge-success">Certificado</span></td>
                                <td>2024-01-15</td>
                            </tr>
                            <tr>
                                <td>#L002</td>
                                <td>Lote Producción B</td>
                                <td><span class="badge badge-warning">En Proceso</span></td>
                                <td>2024-01-14</td>
                            </tr>
                            <tr>
                                <td>#L003</td>
                                <td>Lote Producción C</td>
                                <td><span class="badge badge-info">Pendiente</span></td>
                                <td>2024-01-13</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfica de Pedidos (Doughnut)
var pedidosCtx = document.getElementById('pedidosChart').getContext('2d');
var pedidosChart = new Chart(pedidosCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pendiente', 'Materia Prima Solicitada', 'En Proceso', 'Producción Finalizada', 'Almacenado', 'Cancelado'],
        datasets: [{
            data: [44, 23, 35, 28, 15, 5],
            backgroundColor: [
                '#facc15',
                '#fb923c',
                '#60a5fa',
                '#22c55e',
                '#a78bfa',
                '#f87171'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica de Lotes (Bar)
var lotesCtx = document.getElementById('lotesChart').getContext('2d');
var lotesChart = new Chart(lotesCtx, {
    type: 'bar',
    data: {
        labels: ['Pendiente', 'En Proceso', 'Certificado', 'No Certificado', 'Almacenado'],
        datasets: [{
            label: 'Cantidad',
            data: [12, 8, 25, 5, 3],
            backgroundColor: [
                '#facc15',
                '#60a5fa',
                '#22c55e',
                '#f87171',
                '#a78bfa'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush

