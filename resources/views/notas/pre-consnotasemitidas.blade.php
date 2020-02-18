@extends('layout.app') 

@section('titulo')
Consulta de Notas Emitidas
@endsection

@section('content')
 <form action="{{ url('notas/posconsnotasemitidas') }}" method="POST">
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
                                <label for="dtFim" class="fb-text-label">Clientes</label>
                                <select id="cmbCliente" name="cmbCliente" class="form-control">
                                    <option selected value="-1">Selecione ...</option>
                                    @foreach($clientes as $c)
                                      <option value="{{$c->COD}}">{{ $c->NOME}}</option>
                                    @endforeach
                                </select>
                              
                            </div> 
                        </div>

                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info btn-round text-right" name="emitir" style="success" id="emitir">Consultar</button>
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


</script>  
@endsection
