@extends('layouts.app')

@section('page_title', 'Certificado de Calidad')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-certificate mr-1"></i>
                    Certificado de Calidad
                </h3>
                <div class="card-tools">
                    <a href="{{ route('certificados') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Certificados
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="descargarPDF()">
                        <i class="fas fa-download mr-1"></i> Descargar PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="certificado-pdf" class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-8 relative">
                    <!-- Encabezado institucional -->
                    <header class="text-center border-b pb-4 mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 uppercase">
                            Certificado de Calidad
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Sistema de Trazabilidad y Producción
                        </p>
                        <p class="text-xs text-gray-400">
                            Fecha de evaluación: {{ $lote->latestFinalEvaluation ? \Carbon\Carbon::parse($lote->latestFinalEvaluation->evaluation_date)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </header>

                    <!-- Información general -->
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-2">
                            Información del Lote
                        </h2>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p><strong>ID:</strong> {{ $lote->batch_id }}</p>
                            <p><strong>Código:</strong> {{ $lote->batch_code ?? 'N/A' }}</p>
                            <p><strong>Nombre:</strong> {{ $lote->name ?? 'Sin nombre' }}</p>
                            <p><strong>Fecha de creación:</strong> {{ \Carbon\Carbon::parse($lote->creation_date)->format('d/m/Y') }}</p>
                            @if($lote->order && $lote->order->customer)
                            <p><strong>Cliente:</strong> {{ $lote->order->customer->business_name }}</p>
                            @endif
                        </div>
                    </section>

                    <!-- Materias primas -->
                    @if($lote->rawMaterials && $lote->rawMaterials->count() > 0)
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-2">
                            Materias Primas Utilizadas
                        </h2>
                        <ul class="list-disc ml-6 text-sm text-gray-700 space-y-1">
                            @foreach($lote->rawMaterials as $rawMaterial)
                            <li>
                                {{ $rawMaterial->rawMaterial->materialBase->name ?? 'N/A' }} – 
                                {{ $rawMaterial->quantity ?? 'N/A' }} 
                                {{ $rawMaterial->rawMaterial->materialBase->unit ?? '' }}
                            </li>
                            @endforeach
                        </ul>
                    </section>
                    @endif

                    <!-- Proceso por máquinas -->
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-4">
                            Proceso de Transformación
                        </h2>
                        <div class="row">
                            @foreach($lote->processMachineRecords->sortBy(function($record) {
                                return $record->processMachine ? $record->processMachine->step_order : 999;
                            }) as $record)
                            @php
                                $cumpleEstandar = $record->meets_standard ?? false;
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card {{ $cumpleEstandar ? 'border-success' : 'border-danger' }}">
                                    <div class="card-header {{ $cumpleEstandar ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        <h3 class="font-semibold mb-0">
                                            {{ $record->processMachine ? $record->processMachine->step_order : 'N/A' }}. 
                                            {{ $record->processMachine ? $record->processMachine->name : 'N/A' }}
                                            @if($cumpleEstandar)
                                                <span class="badge badge-light ml-2">✓ Éxito</span>
                                            @else
                                                <span class="badge badge-light ml-2">✗ Error</span>
                                            @endif
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        @if($record->entered_variables && is_array($record->entered_variables))
                                        <ul class="list-unstyled mb-0 text-sm">
                                            @foreach($record->entered_variables as $key => $value)
                                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <p class="text-muted mb-0">Sin variables registradas</p>
                                        @endif
                                        @if($record->observations)
                                        <p class="text-muted mt-2 mb-0"><small><strong>Observaciones:</strong> {{ $record->observations }}</small></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>

                    <!-- Resultado final -->
                    <section class="text-center mt-10">
                        @php
                            $estado = str_contains(strtolower($lote->latestFinalEvaluation->reason ?? ''), 'falló') ? 'No Certificado' : 'Certificado';
                            $motivo = $lote->latestFinalEvaluation->reason ?? 'N/A';
                        @endphp
                        <div class="inline-block px-6 py-3 rounded-full font-semibold text-white {{ $estado === 'Certificado' ? 'bg-success' : 'bg-danger' }} shadow">
                            @if($estado === 'Certificado')
                                ✓ {{ $estado }}
                            @else
                                ✗ {{ $estado }}
                            @endif
                        </div>
                        <p class="mt-3 text-sm text-gray-600">{{ $motivo }}</p>
                        @if($lote->latestFinalEvaluation->observations)
                        <p class="mt-2 text-sm text-gray-500"><strong>Observaciones:</strong> {{ $lote->latestFinalEvaluation->observations }}</p>
                        @endif
                    </section>

                    <!-- Sello visual (simulado) -->
                    <div class="absolute right-6 bottom-6 opacity-20 rotate-[-15deg]">
                        <p class="text-5xl font-extrabold text-primary">CERTIFICADO</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function descargarPDF() {
    const elemento = document.getElementById("certificado-pdf");

    html2canvas(elemento, {
        scale: 2,
        useCORS: true,
        scrollX: 0,
        scrollY: 0,
        allowTaint: false,
    }).then(function(canvas) {
        const imgData = canvas.toDataURL("image/jpeg", 1.0);
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF("p", "mm", "a4");

        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const imgWidth = pageWidth;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        let position = 0;

        if (imgHeight < pageHeight) {
            pdf.addImage(imgData, "JPEG", 0, 0, imgWidth, imgHeight);
        } else {
            let heightLeft = imgHeight;
            while (heightLeft > 0) {
                pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                position -= pageHeight;
                if (heightLeft > 0) pdf.addPage();
            }
        }

        pdf.save(`certificado-lote-{{ $lote->batch_id }}.pdf`);
    }).catch(function(err) {
        console.error("Error al generar PDF:", err);
        alert("Error al generar el certificado. Intenta nuevamente.");
    });
}
</script>
@endpush

