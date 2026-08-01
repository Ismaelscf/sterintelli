@extends('layout.app') 

@section('titulo')
Consulta de Faturamento por {{$tipoDesc}}
@endsection

@section('content')
 <form action="{{ url('faturamento/posconsultarfaturamento/'.$tipo.'/') }}" method="POST" id="faturamentoForm">

<input type="hidden" name="tipo">
   @csrf 
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                    Periodo da busca</h4>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="dtIni" class="fb-text-label">Incio<span class="fb-required">*</span></label>
                                <input type="text" class="form-control datepicker" name="dtIni" id="dtIni" required="required" aria-required="true" value="" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-im">
                                <label for="dtFim" class="fb-text-label">Fim<span class="fb-required">*</span></label>
                                <input type="text" class="form-control datepicker" name="dtFim" id="dtFim" required="required" aria-required="true" value="" autocomplete="off">
                              
                            </div> 
                        </div>
                        <div class="col-md-6">
                            <div class="fb-text form-group field-im">
                                <label for="dtFim" class="fb-text-label">Fantasia</label>
                                <select id="cmbCliente" name="cmbCliente" class="form-control">
                                    <option selected value="-1">Selecione ...</option>
                                    @foreach($clientes as $c)
                                      <option value="{{$c->COD}}">{{ $c->FANTASIA}}</option>
                                    @endforeach
                                </select>
                              
                            </div> 
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="fb-text form-group field-im">
                                <label for="dtFim" class="fb-text-label">Estado</label>
                                <select id="cmbEstado" name="cmbEstado" class="form-control">
                                    <option selected value="-1">Selecione ...</option>
                                    @foreach($estados as $e)
                                      <option value="{{$e->COD}}">{{ $e->NOME}}</option>
                                    @endforeach
                                </select>
                              
                            </div> 
                        </div>
                        <div class="col-md-6">
                            <div class="fb-text form-group field-im">
                                <label for="dtFim" class="fb-text-label">Município</label>
                                <select id="cmbMunicipio" name="cmbMunicipio" class="form-control">
                                    <option selected value="-1">Selecione ...</option>
                                    @foreach($municipios as $m)
                                      <option value="{{$m->COD}}">{{ $m->NOME}}</option>
                                    @endforeach
                                </select>
                              
                            </div> 
                        </div>

                    </div>
                    <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <legend class="col-form-label col-sm-6 fb-text-label">Buscar por:</legend>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="searchOption" id="porFantasia" value="fantasia" checked>
                                        <label class="form-check-label" for="porFantasia">Por fantasia</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="searchOption" id="porRazaoSocial" value="razaoSocial">
                                        <label class="form-check-label" for="porRazaoSocial">Por Razão Social</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="searchOption" id="porTipoEsterilizacao" value="tipoEsterilizacao">
                                        <label class="form-check-label" for="porTipoEsterilizacao">Por tipo de Esterilização</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">

                                <div class="fb-text form-group field-im">
                                    <label for="TipoEste" class="fb-text-label">Tipo de Esterilização</label>
                                    <select id="cmbTipoEste" name="cmbTipoEste" class="form-control">
                                        <option selected value="0">Selecione ...</option>
                                            <option value="1">ÓXIDO ETILENO</option>
                                            <option value="2">VAPOR</option>
                                    </select>
                                
                                </div> 
                            </div>
                        </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info btn-round text-right" name="emitir" id="emitir">Consultar</button>
                    <button type="button" class="btn btn-secondary btn-round text-right float-right" id="btnCreateNotaAvulsa" >Criar nota avulsa</button>
                    </div>

            </div>
        </div>
    </div>

</div>
</form>
@endsection

@section('scripts')

<script type="text/javascript">

    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy'
    });
    
    $('#cmbCliente').select2();
    $('#cmbEstado').select2();
    $('#cmbMunicipio').select2();

    $(document).ready(function() {
            $('#btnCreateNotaAvulsa').click(function() {
                var clienteSelected = $('#cmbCliente').val();
                if (clienteSelected === "-1" || clienteSelected === null) {
                    alert('Por favor, selecione um cliente antes de criar uma nota avulsa.');
                } else {

                    var isRequired = $(this).val() === "-1";
                    $('#dtIni, #dtFim').prop('required', isRequired);
                    
                    var formAction = clienteSelected !== "-1" ? "{{ url('/notas/preemitir/') }}/" + clienteSelected : "{{ url('faturamento/posconsultarfaturamento/'.$tipo.'/') }}";
                    $('#faturamentoForm').attr('action', formAction).attr('method', 'GET');
                    // Submit the form if a client is selected and dates are not required
                    if (!isRequired) {
                        $('#faturamentoForm').submit();
                    }
                }
            });
        });
</script>  
@endsection
