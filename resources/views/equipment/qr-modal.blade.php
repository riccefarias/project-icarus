<div class="p-4 text-center">
    <div class="mb-4">
        {!! $equipment->getQrCode(250) !!}
    </div>
    
    <p class="mb-3">
        Escaneie o QR code acima para acessar os detalhes do equipamento.
    </p>
    
    <div class="mb-3">
        <h5>Informações do Equipamento</h5>
        <table class="table table-sm">
            <tr>
                <th>Número de Série:</th>
                <td>{{ $equipment->serial_number }}</td>
            </tr>
            <tr>
                <th>Modelo:</th>
                <td>{{ $equipment->model }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>{{ $equipment->status_name }}</td>
            </tr>
        </table>
    </div>
    
    <div class="mt-3">
        <a href="{{ route('equipment.qrcode', $equipment) }}" class="btn btn-primary" target="_blank">
            Download QR Code
        </a>
        <a href="{{ route('equipment.show', $equipment) }}" class="btn btn-secondary ms-2" target="_blank">
            Ver página pública
        </a>
    </div>
</div>