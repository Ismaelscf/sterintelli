@extends('layout.app')

@section('titulo')
Consulta de NFSe Emitidas
@endsection

@section('content')
<div class="card">
      <div class="card-header">
        <h4 class="card-title">NFSe no período</h4>
      </div>
      <div class="card-body">
            <div class="table-responsive">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Número<br><small>Cód. Verificação</small></th>
                    <th>Cliente<br><small>CNPJ</small></th>
                    <th>Emissão</th>
                    <th>Período de referência</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Boleto<br><small>Nosso número</small></th>
                    <th>Alíq. ISS</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($notas as $nota)
                    <tr>
                        <td>{{ $nota->NUMERONOTA }}<br><small>{{ $nota->CODIGOVERIFICACAO }}</small></td>
                        <td>{{ $nota->FANTASIA ?: $nota->NOME }}<br><small>{{ $nota->CNPJ }}</small></td>
                        <td>{{ $nota->DTANOTA }}</td>
                        <td>{{ $nota->DTAINICIAL }} a {{ $nota->DTAFINAL }}</td>
                        <td>{{ $nota->DTAVENCIMENTO }}</td>
                        <td>{{ $nota->VALORNOTA }}</td>
                        <td>
                            @if($nota->DTAPAGO)
                                <span class="badge badge-success">Pago</span><br>
                                <small>{{ $nota->DTAPAGO }} - {{ $nota->VALPAGO }}</small>
                            @else
                                <span class="badge badge-secondary">Em aberto</span>
                            @endif
                        </td>
                        <td>
                            @if($nota->NOSSO_NUMERO)
                                {{ $nota->NOSSO_NUMERO }}<br><small>{{ $nota->DTABOLETO }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $nota->PERC_ISS }}%</td>
                        <td>
                            @if($nota->STATUS_FOCUS == 'autorizado')
                                <span class="badge badge-success">Autorizado</span>
                            @elseif($nota->STATUS_FOCUS == 'processando_autorizacao')
                                <span class="badge badge-warning">Processando</span>
                            @elseif($nota->STATUS_FOCUS == 'erro_autorizacao')
                                <span class="badge badge-danger">Erro</span>
                            @elseif($nota->STATUS_FOCUS == 'cancelado')
                                <span class="badge badge-dark">Cancelado</span>
                            @else
                                <span class="badge badge-secondary">{{ $nota->STATUS_FOCUS ?: '—' }}</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ url('notas/imprimirnfse/'.$nota->NUMERONOTA.'/'.$nota->CODIGOVERIFICACAO.'/') }}" class="btn btn-info btn-link btn-icon btn-sm" target="_blank" title="Imprimir NFSe"><i class="fa fa-print"></i></a>
                            <a href="{{ url('notas/imprimirnfse/'.$nota->NUMERONOTA.'/'.$nota->CODIGOVERIFICACAO.'/?email=S') }}" class="btn btn-info btn-link btn-icon btn-sm" target="_blank" title="Enviar por email"><i class="fa fa-envelope"></i></a>
                            @if($nota->STATUS_FOCUS == 'autorizado')
                                <button type="button" class="btn btn-danger btn-link btn-icon btn-sm btn-abrir-cancelamento"
                                    data-toggle="modal" data-target="#modalCancelar"
                                    data-numeronota="{{ $nota->NUMERONOTA }}"
                                    title="Cancelar NFSe">
                                    <i class="fa fa-ban"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
    </div>
</div>

<div class="modal fade" id="modalCancelar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ url('notas/cancelarnota') }}" method="POST">
            @csrf
            <input type="hidden" name="numeronota" id="modalCancelarNumeroNota">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar NFSe</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você está cancelando a NFSe <b id="modalCancelarNumeroNotaTexto"></b>. Essa ação é enviada direto à Focus NFe e não pode ser desfeita pelo sistema.</p>
                    <div class="form-group">
                        <label for="motivo">Motivo do cancelamento<span class="fb-required">*</span></label>
                        <textarea class="form-control" name="motivo" id="motivo" rows="3" minlength="15" required placeholder="Descreva o motivo do cancelamento (mínimo 15 caracteres)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')

<script>

    $('#datatable').DataTable();

    //delegado no document (nao direto no botao): o DataTables pode re-renderizar as
    //linhas ao paginar/filtrar, e um bind direto se perderia nesse caso
    $(document).on('click', '.btn-abrir-cancelamento', function () {
        var numeronota = $(this).data('numeronota');
        $('#modalCancelarNumeroNota').val(numeronota);
        $('#modalCancelarNumeroNotaTexto').text(numeronota);
    });

</script>


@endsection
