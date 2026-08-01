@if($boletos && count($boletos) > 0)
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Nosso Número</th>
                <th scope="col">Nota Fiscal</th>
                <th scope="col">Data</th>
                <th scope="col">Status</th>
                <th scope="col">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boletos as $boleto)
                <tr>
                    <td>{{ $boleto->nosso_numero }}</td>
                    <td>{{ $boleto->notafiscal }}</td>
                    <td>{{ date('d/m/Y', strtotime($boleto->data_inclusao)) }}</td>
                    <td>
                        @if(trim(strtoupper($boleto->status_boleto)) == 'PAGA')
                            <span class="badge bg-success">{{ $boleto->status_boleto }}</span>
                        @elseif(trim(strtoupper($boleto->status_boleto)) == 'BAIXADA')
                            <span class="badge bg-danger">{{ $boleto->status_boleto }}</span>
                        @elseif(trim(strtoupper($boleto->status_boleto)) == 'EM ABERTO' && trim(strtoupper($boleto->situacao_vencimento)) == 'A VENCER')
                            <span class="badge bg-info">{{ $boleto->situacao_vencimento }}</span>
                        @elseif(trim(strtoupper($boleto->status_boleto)) == 'EM ABERTO' && trim(strtoupper($boleto->situacao_vencimento)) == 'VENCIDA')
                            <span class="badge bg-danger">{{ $boleto->situacao_vencimento }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('boleto.consultar', ['nossoNumero' => $boleto->nosso_numero]) }}" class="btn btn-primary btn-sm" target="_blank">Imprimir Boleto</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Nenhum boleto encontrado.</p>
@endif
