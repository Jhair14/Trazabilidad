<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado de Calidad - Lote #{{ $lote->lote_id }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px 0;
        }
        .certificado-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-certificado {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-certificado h1 {
            color: #007bff;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section-title {
            color: #007bff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        .info-item {
            margin-bottom: 8px;
            font-size: 1rem;
        }
        .badge-certificado {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 20px 0;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
        }
        .error-message i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .btn-download {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        @media print {
            .btn-download {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Botón de descarga PDF -->
    <button type="button" class="btn btn-primary btn-download" onclick="descargarPDF()">
        <i class="fas fa-download mr-2"></i> Descargar PDF
    </button>

    <div class="container">
        <div class="certificado-container" id="certificado-pdf">
            @if(isset($error))
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h2>{{ $error }}</h2>
                    <p>El lote #{{ $lote->lote_id }} aún no ha sido certificado.</p>
                </div>
            @else
                <!-- Encabezado institucional -->
                <header class="header-certificado">
                    <h1>Certificado de Calidad</h1>
                    <p class="text-muted">Sistema de Trazabilidad y Producción</p>
                    <p class="text-muted small">
                        Fecha de evaluación: {{ $lote->latestFinalEvaluation && $lote->latestFinalEvaluation->fecha_evaluacion ? \Carbon\Carbon::parse($lote->latestFinalEvaluation->fecha_evaluacion)->format('d/m/Y') : 'N/A' }}
                    </p>
                </header>

                <!-- Información general -->
                <section>
                    <h2 class="section-title">Información del Lote</h2>
                    <div class="info-item"><strong>ID:</strong> {{ $lote->lote_id }}</div>
                    <div class="info-item"><strong>Código:</strong> {{ $lote->codigo_lote ?? 'N/A' }}</div>
                    <div class="info-item"><strong>Nombre:</strong> {{ $lote->nombre ?? 'Sin nombre' }}</div>
                    <div class="info-item"><strong>Fecha de creación:</strong> {{ $lote->fecha_creacion ? \Carbon\Carbon::parse($lote->fecha_creacion)->format('d/m/Y') : 'N/A' }}</div>
                    @if($lote->order && $lote->order->customer)
                    <div class="info-item"><strong>Cliente:</strong> {{ $lote->order->customer->razon_social }}</div>
                    @endif
                    @if($lote->order)
                    <div class="info-item"><strong>Pedido:</strong> {{ $lote->order->nombre }} ({{ $lote->order->numero_pedido }})</div>
                    @endif
                </section>

                <!-- Productos del Pedido -->
                @if($lote->order && $lote->order->orderProducts && $lote->order->orderProducts->count() > 0)
                <section>
                    <h2 class="section-title">Productos del Pedido</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lote->order->orderProducts as $orderProduct)
                                <tr>
                                    <td>{{ $orderProduct->product->nombre ?? 'N/A' }}</td>
                                    <td>{{ number_format($orderProduct->cantidad, 2) }} {{ $orderProduct->product->unit->codigo ?? '' }}</td>
                                    <td>${{ number_format($orderProduct->precio / $orderProduct->cantidad, 2) }}</td>
                                    <td>${{ number_format($orderProduct->precio, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif

                <!-- Materias primas -->
                @if($lote->rawMaterials && $lote->rawMaterials->count() > 0)
                <section>
                    <h2 class="section-title">Materias Primas Utilizadas</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Materia Prima</th>
                                    <th>Cantidad Planificada</th>
                                    <th>Cantidad Usada</th>
                                    <th>Unidad</th>
                                    <th>Proveedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lote->rawMaterials as $rawMaterial)
                                <tr>
                                    <td>{{ $rawMaterial->rawMaterial->materialBase->nombre ?? 'N/A' }}</td>
                                    <td>{{ number_format($rawMaterial->cantidad_planificada ?? 0, 2) }}</td>
                                    <td>{{ number_format($rawMaterial->cantidad_usada ?? 0, 2) }}</td>
                                    <td>{{ $rawMaterial->rawMaterial->materialBase->unit->codigo ?? 'N/A' }}</td>
                                    <td>{{ $rawMaterial->rawMaterial->supplier->razon_social ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif

                <!-- Proceso por máquinas -->
                @if($lote->processMachineRecords && $lote->processMachineRecords->count() > 0)
                <section>
                    <h2 class="section-title">
                        Proceso de Transformación
                        @if($lote->processMachineRecords->isNotEmpty() && $lote->processMachineRecords->first()->processMachine && $lote->processMachineRecords->first()->processMachine->process)
                        <span class="text-muted" style="font-size: 0.9rem;">({{ $lote->processMachineRecords->first()->processMachine->process->nombre }})</span>
                        @endif
                    </h2>
                    <div class="row">
                        @foreach($lote->processMachineRecords->sortBy(function($record) {
                            return $record->processMachine ? $record->processMachine->orden_paso : 999;
                        }) as $record)
                        @php
                            $cumpleEstandar = $record->cumple_estandar ?? false;
                            $processMachine = $record->processMachine;
                            $variables = $record->variables_ingresadas ?? [];
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card {{ $cumpleEstandar ? 'border-success' : 'border-danger' }}">
                                <div class="card-header {{ $cumpleEstandar ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                    <h5 class="mb-0">
                                        Paso {{ $processMachine ? $processMachine->orden_paso : 'N/A' }}. 
                                        {{ $processMachine ? $processMachine->nombre : 'N/A' }}
                                        @if($cumpleEstandar)
                                            <span class="badge badge-light ml-2">✓ Éxito</span>
                                        @else
                                            <span class="badge badge-light ml-2">✗ Error</span>
                                        @endif
                                    </h5>
                                    @if($processMachine && $processMachine->machine)
                                    <small class="d-block mt-1">Máquina: {{ $processMachine->machine->nombre }}</small>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($processMachine && $processMachine->variables && $processMachine->variables->count() > 0)
                                    <h6 style="font-weight: 600; margin-bottom: 10px;">Variables Registradas:</h6>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                        @foreach($processMachine->variables as $variable)
                                        @php
                                            $varName = $variable->standardVariable->codigo ?? $variable->standardVariable->nombre;
                                            $enteredValue = $variables[$varName] ?? null;
                                            $unit = $variable->standardVariable->unidad ?? '';
                                        @endphp
                                        <li style="margin-bottom: 8px;">
                                            <strong>{{ $variable->standardVariable->nombre ?? 'N/A' }}</strong>
                                            @if($unit): <span class="text-muted">({{ $unit }})</span>@endif
                                            <br>
                                            <span style="margin-left: 15px;">
                                                Valor: <strong>{{ $enteredValue ?? 'N/A' }}</strong>
                                                @if($enteredValue !== null)
                                                    | Rango: {{ $variable->valor_minimo }} - {{ $variable->valor_maximo }}
                                                    @if($enteredValue < $variable->valor_minimo || $enteredValue > $variable->valor_maximo)
                                                        <span class="badge badge-danger">Fuera de rango</span>
                                                    @else
                                                        <span class="badge badge-success">OK</span>
                                                    @endif
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @elseif(!empty($variables))
                                    <h6 style="font-weight: 600; margin-bottom: 10px;">Variables Registradas:</h6>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                        @foreach($variables as $key => $value)
                                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted mb-0">Sin variables registradas</p>
                                    @endif
                                    @if($record->operator)
                                    <p class="text-muted mt-2 mb-0"><small><strong>Operador:</strong> {{ $record->operator->nombre }} {{ $record->operator->apellido }}</small></p>
                                    @endif
                                    @if($record->fecha_registro)
                                    <p class="text-muted mb-0"><small><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($record->fecha_registro)->format('d/m/Y H:i') }}</small></p>
                                    @endif
                                    @if($record->observaciones)
                                    <p class="text-muted mt-2 mb-0"><small><strong>Observaciones:</strong> {{ $record->observaciones }}</small></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Resultado final -->
                <section class="text-center mt-5">
                    @php
                        $estado = str_contains(strtolower($lote->latestFinalEvaluation->razon ?? ''), 'falló') ? 'No Certificado' : 'Certificado';
                        $motivo = $lote->latestFinalEvaluation->razon ?? 'N/A';
                    @endphp
                    <div class="badge-certificado {{ $estado === 'Certificado' ? 'badge-success' : 'badge-danger' }}">
                        @if($estado === 'Certificado')
                            ✓ {{ $estado }}
                        @else
                            ✗ {{ $estado }}
                        @endif
                    </div>
                    <p class="mt-3">{{ $motivo }}</p>
                    @if($lote->latestFinalEvaluation && $lote->latestFinalEvaluation->observaciones)
                    <p class="mt-2 text-muted"><strong>Observaciones:</strong> {{ $lote->latestFinalEvaluation->observaciones }}</p>
                    @endif
                    @if($lote->latestFinalEvaluation && $lote->latestFinalEvaluation->inspector)
                    <p class="mt-3 text-muted small">
                        <strong>Inspector:</strong> {{ $lote->latestFinalEvaluation->inspector->nombre }} {{ $lote->latestFinalEvaluation->inspector->apellido }}
                    </p>
                    @endif
                </section>
            @endif
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- HTML2Canvas y jsPDF para generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <script>
    function descargarPDF() {
        // Ocultar el botón antes de generar el PDF
        const btnDownload = document.querySelector('.btn-download');
        btnDownload.style.display = 'none';
        
        const elemento = document.getElementById("certificado-pdf");

        html2canvas(elemento, {
            scale: 2,
            useCORS: true,
            scrollX: 0,
            scrollY: -window.scrollY,
            windowWidth: document.documentElement.scrollWidth,
            windowHeight: document.documentElement.scrollHeight,
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

            pdf.save(`certificado-lote-{{ $lote->lote_id }}.pdf`);
            
            // Mostrar el botón nuevamente después de descargar
            btnDownload.style.display = 'block';
        }).catch(function(err) {
            console.error("Error al generar PDF:", err);
            alert("Error al generar el certificado. Intenta nuevamente.");
            // Mostrar el botón nuevamente en caso de error
            btnDownload.style.display = 'block';
        });
    }
    </script>
</body>
</html>

