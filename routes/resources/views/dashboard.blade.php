@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- KPIs Row -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_pedidos'] }}</h3>
                <p>Pedidos Totales</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_lotes'] }}</h3>
                <p>Lotes Totales</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pedidos_pendientes'] }}</h3>
                <p>Pedidos Pendientes</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['lotes_completados'] }}</h3>
                <p>Lotes Completados</p>
            </div>
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
                            @forelse($pedidos_recientes as $pedido)
                            <tr>
                                <td>#{{ $pedido->order_number ?? $pedido->order_id }}</td>
                                <td>{{ $pedido->customer->business_name ?? 'N/A' }}</td>
                                <td>
                                    @if($pedido->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($pedido->estado == 'completado')
                                        <span class="badge badge-success">Completado</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($pedido->estado) }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay pedidos recientes</td>
                            </tr>
                            @endforelse
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
                            @forelse($lotes_recientes as $lote)
                            <tr>
                                <td>#{{ $lote->batch_code ?? $lote->batch_id }}</td>
                                <td>{{ $lote->name ?? 'Sin nombre' }}</td>
                                <td>
                                    @if($lote->latestFinalEvaluation)
                                        @if(str_contains(strtolower($lote->latestFinalEvaluation->reason ?? ''), 'falló'))
                                            <span class="badge badge-danger">No Certificado</span>
                                        @else
                                            <span class="badge badge-success">Certificado</span>
                                        @endif
                                    @elseif($lote->start_time && !$lote->end_time)
                                        <span class="badge badge-warning">En Proceso</span>
                                    @else
                                        <span class="badge badge-info">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($lote->creation_date)->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay lotes recientes</td>
                            </tr>
                            @endforelse
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
            data: [
                {{ $pedidosPorEstado['pendiente'] ?? 0 }},
                {{ $pedidosPorEstado['materia_prima_solicitada'] ?? 0 }},
                {{ $pedidosPorEstado['en_proceso'] ?? 0 }},
                {{ $pedidosPorEstado['produccion_finalizada'] ?? 0 }},
                {{ $pedidosPorEstado['almacenado'] ?? 0 }},
                {{ $pedidosPorEstado['cancelado'] ?? 0 }}
            ],
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
            data: [
                {{ $lotesPorEstado['pendiente'] ?? 0 }},
                {{ $lotesPorEstado['en_proceso'] ?? 0 }},
                {{ $lotesPorEstado['certificado'] ?? 0 }},
                {{ $lotesPorEstado['no_certificado'] ?? 0 }},
                {{ $lotesPorEstado['almacenado'] ?? 0 }}
            ],
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

