@extends('layouts.app')

@section('page_title', 'Código QR del Certificado')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-qrcode mr-1"></i>
                    Código QR del Certificado
                </h3>
                <div class="card-tools">
                    <a href="{{ route('certificados') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Certificados
                    </a>
                </div>
            </div>
            <div class="card-body text-center">
                <div class="bg-white flex flex-column justify-content-center align-items-center p-6 rounded-xl shadow max-w-md mx-auto">
                    <h2 class="text-xl font-bold mb-4 text-primary">
                        Código QR del Certificado
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Escanea este código para ver el certificado del lote #{{ $lote->batch_id }}
                    </p>
                    <div id="qrCode" style="min-height: 256px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <!-- El QR se generará aquí -->
                    </div>
                    <p class="mt-4 text-xs text-gray-400 break-all" id="urlCertificado"></p>
                </div>
            </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // En producción esto debería ser un enlace público al certificado
    const url = window.location.origin + '/certificado/{{ $lote->batch_id }}';
    document.getElementById('urlCertificado').textContent = url;
    
    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = '';
    
    QRCode.toCanvas(qrContainer, url, {
        width: 256,
        height: 256,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function (error) {
        if (error) {
            console.error('Error al generar QR:', error);
            qrContainer.innerHTML = '<p class="text-danger">Error al generar el código QR</p>';
        }
    });
});
</script>
@endpush

