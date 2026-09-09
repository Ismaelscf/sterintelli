@extends('layout.app')

@section('titulo')
Conferência da Baixa
@endsection

@php
    $qtdPagamento = 0;
    $qtdInformativo = 0;
    $qtdNaoEncontrado = 0;
    $qtdAmbiguo = 0;
    foreach ($linhas as $l) {
        if (!empty($l['ambiguo'])) {
            $qtdAmbiguo++;
        } elseif (!$l['encontrado']) {
            $qtdNaoEncontrado++;
        } elseif ($l['eh_pagamento']) {
            $qtdPagamento++;
        } else {
            $qtdInformativo++;
        }
    }
@endphp

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Resumo</h4>
                </div>
                <div class="card-body">
                    <span class="badge badge-success">{{ $qtdPagamento }} com pagamento (código 06/15/17), encontradas e prontas pra baixar</span>
                    <span class="badge badge-secondary">{{ $qtdInformativo }} só informativas (não alteram nada)</span>
                    <span class="badge badge-danger">{{ $qtdNaoEncontrado }} não encontradas no sistema</span>
                    @if($qtdAmbiguo > 0)
                        <span class="badge badge-warning">{{ $qtdAmbiguo }} ambígua(s) - mais de uma nota bate com o número truncado, verifique manualmente</span>
                    @endif
                </div>
            </div>

            <form action="{{ url('notas/confirmarbaixa') }}" method="POST" id="formConfirmar">
                @csrf
                <input type="hidden" name="linhas_selecionadas" id="linhas_selecionadas" value="[]">

                <div class="card">
                    <div class="card-header">Conferência linha a linha</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Número da nota</th>
                                        <th>Situação</th>
                                        <th>Ocorrência</th>
                                        <th>Data pagamento</th>
                                        <th>Valor pago</th>
                                        <th>Pagador (retorno)</th>
                                        <th>Já tinha pagamento?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($linhas as $l)
                                        <tr>
                                            <td>
                                                @if($l['encontrado'] && $l['eh_pagamento'])
                                                    <input type="checkbox" class="chk-linha"
                                                        checked
                                                        data-numeronota="{{ $l['numeronota_real'] }}"
                                                        data-data-pagamento="{{ $l['data_pagamento'] }}"
                                                        data-valor-pago="{{ $l['valor_pago'] }}">
                                                @endif
                                            </td>
                                            <td>
                                                {{ $l['numeronota'] }}
                                                @if(!empty($l['truncado']))
                                                    <br><small class="text-muted">número no banco: {{ $l['numeronota_real'] }}</small>
                                                @endif
                                                @if(!empty($l['ambiguo']))
                                                    <br><small class="text-muted">candidatos: {{ implode(', ', $l['candidatos']) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($l['ambiguo']))
                                                    <span class="badge badge-warning">Ambíguo</span>
                                                @elseif(!$l['encontrado'])
                                                    <span class="badge badge-danger">Não encontrada</span>
                                                @elseif($l['eh_pagamento'])
                                                    <span class="badge badge-success">Pagamento</span>
                                                @else
                                                    <span class="badge badge-secondary">Informativo</span>
                                                @endif
                                            </td>
                                            <td>{{ $l['codigo_ocorrencia'] }} - {{ $l['descricao_ocorrencia'] }}</td>
                                            <td>{{ $l['eh_pagamento'] ? $l['data_pagamento'] : '-' }}</td>
                                            <td>{{ $l['eh_pagamento'] ? number_format($l['valor_pago'], 2, ',', '.') : '-' }}</td>
                                            <td>{{ $l['nome_pagador'] }}</td>
                                            <td>
                                                @if($l['encontrado'] && $l['ja_tinha_pagamento'])
                                                    <span class="badge badge-warning" title="Vai sobrescrever">Sim ({{ $l['dtapago_atual'] }} - {{ $l['valpago_atual'] }})</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info btn-round" id="btnConfirmar">Confirmar baixa das linhas marcadas</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
document.getElementById('formConfirmar').addEventListener('submit', function (e) {
    var linhas = [];
    document.querySelectorAll('.chk-linha:checked').forEach(function (chk) {
        linhas.push({
            numeronota: chk.getAttribute('data-numeronota'),
            data_pagamento: chk.getAttribute('data-data-pagamento'),
            valor_pago: parseFloat(chk.getAttribute('data-valor-pago'))
        });
    });

    if (linhas.length === 0) {
        e.preventDefault();
        alert('Nenhuma linha marcada para baixa.');
        return;
    }

    document.getElementById('linhas_selecionadas').value = JSON.stringify(linhas);
});
</script>
@endsection
