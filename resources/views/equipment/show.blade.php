<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamento {{ $equipment->serial_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        .equipment-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .qr-container {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code {
            max-width: 200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card equipment-card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Detalhes do Equipamento</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="mb-3">Informações Gerais</h4>
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th scope="row" style="width: 40%">Número de Série</th>
                                            <td>{{ $equipment->serial_number }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Modelo</th>
                                            <td>{{ $equipment->model }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Marca</th>
                                            <td>{{ $equipment->brand }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Status</th>
                                            <td>
                                                <span class="badge bg-{{ $equipment->status == 'defective' ? 'danger' : ($equipment->status == 'maintenance' ? 'warning' : 'success') }}">
                                                    {{ $equipment->status_name }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($equipment->imei)
                                        <tr>
                                            <th scope="row">IMEI</th>
                                            <td>{{ $equipment->imei }}</td>
                                        </tr>
                                        @endif
                                        @if($equipment->phone_number)
                                        <tr>
                                            <th scope="row">Número de Telefone</th>
                                            <td>{{ $equipment->phone_number }}</td>
                                        </tr>
                                        @endif
                                        @if($equipment->chip_provider)
                                        <tr>
                                            <th scope="row">Operadora</th>
                                            <td>{{ $equipment->chip_provider }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <div class="qr-container">
                                    <h5>QR Code</h5>
                                    <div class="qr-code">
                                        {!! $equipment->getQrCode() !!}
                                    </div>
                                    <p class="mt-2"><small>Escaneie para acessar este equipamento</small></p>
                                </div>
                            </div>
                        </div>
                        
                        @if($equipment->notes)
                        <div class="mt-4">
                            <h4 class="mb-3">Observações</h4>
                            <div class="alert alert-secondary">
                                {{ $equipment->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>