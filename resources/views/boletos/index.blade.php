@extends('layout.app')




@section('titulo')
Emitir Boleto
@endsection

@section('content')
<div class="container">

@if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
@endif
    <div class="row justify-content-center">
        <div class="col-md-12">
        @if($boletos && count($boletos) > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        Lista de Boletos
                    </h3>
                </div>
                <div class="card-body">
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
                                            <span class="badge badge-success">{{ $boleto->status_boleto }}</span>
                                        @endif

                                        @if(trim(strtoupper($boleto->status_boleto)) == 'BAIXADA')
                                            <span class="badge badge-danger">{{ $boleto->status_boleto }}</span>
                                        @endif

                                        @if(trim(strtoupper($boleto->status_boleto)) == 'EM ABERTO' && trim(strtoupper($boleto->situacao_vencimento)) == 'A VENCER')
                                            <span class="badge badge-info">{{ $boleto->situacao_vencimento }}</span>
                                        @endif

                                        @if(trim(strtoupper($boleto->status_boleto)) == 'EM ABERTO' && trim(strtoupper($boleto->situacao_vencimento)) == 'VENCIDA')
                                            <span class="badge badge-danger">{{ $boleto->situacao_vencimento }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('boleto.consultar', ['nossoNumero' => $boleto->nosso_numero]) }}" class="btn btn-primary btn-sm">Imprimir Boleto</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p>Nenhum boleto encontrado.</p>
        @endif

        <!-- Modal -->
        <div class="modal fade" id="editarDadosModal" tabindex="-1" aria-labelledby="editarDadosModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarDadosModalLabel">Editar Dados do Boleto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarDadosForm" method="POST" action="#">
                            @csrf
                            <div class="mb-3">
                                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                                <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required>
                            </div>
                            <input type="hidden" id="nosso_numero" name="nosso_numero">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" form="editarDadosForm">Editar</button>
                    </div>
                </div>
            </div>
        </div>


    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('boleto.gerar') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">Dados do Beneficiario</div>

                <div class="card-body">
                    <input type="hidden" value="{{$dadosNota->numeronota}}" id="numeronota" name="numeronota">
                    <input type="hidden" value="908400080952" id="id_beneficiario" name="id_beneficiario">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">CNPJ<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cnpjBeneficiario" id="cnpjBeneficiario" required="required" aria-required="true" value="01469892000137" readonly>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="fb-text form-group field-razao">
                                <input class="form-check-input" type="checkbox" value="1" id="pf" name="pf">
                                <label for="pf" class="fb-text-label" data-toggle="tooltip" title="Adicionar em caso de Pessoa Física">Razão Social<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaoSocialBeneficiario" id="razaoSocialBeneficiario" required="required" aria-required="true" value="BRITO SOARES LTDA" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Rua<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="ruaBeneficiario" id="ruaBeneficiario" minLength="6" required="required" aria-required="true" value="RUA DOS FLAMINGOS" readonly>
                            </div>                            
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Bairro<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="bairroBeneficiario" id="bairroBeneficiario" required="required" aria-required="true" value="PARQUE ATLANTICO" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Cidade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cidadeBeneficiario" id="cidadeBeneficiario" required="required" aria-required="true" value="SAO LUIS" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Estado<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="estadoBeneficiario" id="estadoBeneficiario" required="required" aria-required="true" value="MA" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Cep<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cepBeneficiario" id="cepBeneficiario" required="required" aria-required="true" value="65066060" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Dados do Pagador</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">CNPJ<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cnpjPagador" id="cnpjPagador" required="required" aria-required="true" value="{{$dadosNota->cnpj}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Razão Social<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaoSocialPagador" id="razaoSocialPagador" required="required" aria-required="true" value="{{$dadosNota->nome}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Estado<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="estadoPagador" id="estadoPagador" required="required" aria-required="true" value="{{$dadosNota->uf}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Cidade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cidadePagador" id="cidadePagador" required="required" aria-required="true" value="{{$dadosNota->municipio}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Cep<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cepPagador" id="cepPagador" required="required" aria-required="true" value="{{$dadosNota->cep}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Rua<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="ruaPagador" id="ruaPagador" minLength="6" required="required" aria-required="true" value="{{$dadosNota->endereco}}" readonly>
                            </div>                            
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Bairro<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="bairroPagador" id="bairroPagador" required="required" aria-required="true" value="{{$dadosNota->bairro}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Email<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="emailPagador" id="emailPagador" minLength="6" required="required" aria-required="true" value="{{$dadosNota->email}}" readonly>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Dados do Boleto</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">Nota Fiscal<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="nf" id="nf" required="required" aria-required="true" value="NF{{$dadosNota->numeronota}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Valor do Boleto<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorBoleto" id="valorBoleto" required="required" aria-required="true" value="{{$dadosNota->valornota}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Descontos<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="descontos" id="descontos" required="required" aria-required="true" value="{{$dadosNota->valorpis + $dadosNota->valorcofins +$dadosNota->valorinss + $dadosNota->valorir + $dadosNota->valorcsll}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Nosso  número<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="nosso_numero" id="nosso_numero" required="required" aria-required="true" value="{{ is_null($nossoNumero) ? 10000006 : ($nossoNumero->nosso_numero + 1) }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Vencimento<span class="fb-required">*</span></label>
                                <input type="date" class="form-control" name="dt_vencimento" id="dt_vencimento" value="{{substr($dadosNota->dtavencimento, 0, 10)}}" required="required" aria-required="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Juros<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="juros" id="juros" required="required" aria-required="true" value="2.0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Multa<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="multa" id="multa" required="required" aria-required="true" value="2.0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <input class="form-check-input" type="checkbox" value="1" id="protestar" name="protestar">
                                <label for="protestar" class="fb-text-label">Protestar</label>
                            </div>
                            <!-- <div class="form-group">
                                <input class="form-check-input" type="checkbox" value="1" id="juros_ativacao" name="juros_ativacao">
                                <label for="juros_ativacao" class="fb-text-label">Juros</label>
                            </div> -->
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Dias<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="day" id="day" aria-required="true" value="10">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Bairro<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="bairroPagador2" id="bairroPagador2" required="required" aria-required="true" value="{{$dadosNota->bairro}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Email<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="emailPagador2" id="emailPagador2" minLength="6" required="required" aria-required="true" value="{{$dadosNota->email}}" readonly>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Descontos Aplicados</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="fb-text form-group field-cnpj">
                                <label for="pis" class="fb-text-label">PIS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="pis" id="pis" required="required" aria-required="true" value="{{$dadosNota->valorpis}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-razao">
                                <label for="cofins" class="fb-text-label">COFINS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cofins" id="cofins" required="required" aria-required="true" value="{{$dadosNota->valorcofins}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-razao">
                                <label for="inss" class="fb-text-label">INSS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="inss" id="inss" required="required" aria-required="true" value="{{$dadosNota->valorinss}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-razao">
                                <label for="ir" class="fb-text-label">IR<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="ir" id="ir" required="required" aria-required="true" value="{{$dadosNota->valorir}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-razao">
                                <label for="cll" class="fb-text-label">CSLL<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cll" id="cll" required="required" aria-required="true" value="{{$dadosNota->valorcsll}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-razao">
                                <input class="form-check-input" type="checkbox" value="1" id="buttomIss" name="buttomIss">
                                <label for="buttomIss" class="fb-text-label" data-toggle="tooltip" title="Adicionar Desconto do ISS">ISS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="iss" id="iss" required="required" aria-required="true" value="{{ number_format($dadosNota->valornota * ($dadosNota->perc_iss / 100), 2, '.', '') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Gerar Boleto</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    $('#editarDadosModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botão que acionou o modal
        var nossoNumero = button.data('nossonumero'); // Extrair informação dos atributos data-*

        // Atualizar os campos do modal
        var modal = $(this);
        modal.find('.modal-body #nosso_numero').val(nossoNumero);
    });
});
</script>

<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
@endsection


