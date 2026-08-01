@extends('layout.app') 

@section('titulo')
Editar nota cadastrada
@endsection

@section('content')
 <form action="{{ url('notas/poseditarnota') }}" method="POST">
   @csrf 

 <input type="hidden"   id="idcliente" name="idcliente"  value= "{{$dados->CODCLIENTE}}">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dados da nota</div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">CNPJ<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cnpj" id="cnpj" readonly aria-required="true" value="{{ $dados->CNPJ }}">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Razão Social<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razao" id="razao" readonly aria-required="true" value="{{ $dados->RAZAO }}">
                            </div>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-md-3">

                            <div class="fb-text form-group field-numeronota">
                                <label for="numeronota" class="fb-text-label">Numero nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="numeronota" id="numeronota" readonly aria-required="true" value="{{ $dados->NUMERONOTA }}" >
                            </div>                            
                        </div>

                        <div class="col-md-3">
                            <div class="fb-text form-group field-valornota">
                                <label for="valornota" class="fb-text-label">Valor<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valornota" id="valornota" readonly aria-required="true" value="{{ $dados->VALORNOTA }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-dtanota">
                                <label for="dtanota" class="fb-text-label">Data Nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtanota" id="dtanota"  aria-required="true" value="{{ $dados->DTANOTA }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-dtavencimento">
                                <label for="dtavencimento" class="fb-text-label">Data Vencimento<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtavencimento" id="dtavencimento" readonly aria-required="true" value="{{ $dados->DTAVENCIMENTO }}">
                            </div>
                        </div>
                    </div>
                        
                    <div class="row">
                        <div class="col-md-3">

                            <div class="fb-text form-group field-dtaInicial">
                                <label for="dtaInicial" class="fb-text-label">Data início<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtaInicial" id="dtaInicial" readonly aria-required="true" value="{{ $dados->DTAINICIAL }}">
                            </div>                            
                        </div>

                        <div class="col-md-3">
                            <div class="fb-text form-group field-dtafinal">
                                <label for="dtafinal" class="fb-text-label">Data final<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtafinal" id="dtafinal" readonly aria-required="true" value="{{ $dados->DTAFINAL }}" >
                            </div>
                        </div>
                        <div class="col-md-3">

                            <div class="fb-text form-group field-dtapago">
                                <label for="dtapago" class="fb-text-label">Data pagamento<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtapago" id="dtapago" required="required" aria-required="true" value="{{ $dados->DTAPAGO }}">
                            </div>                            
                        </div>

                        <div class="col-md-3">
                            <div class="fb-text form-group field-valpago">
                                <label for="valpago" class="fb-text-label">Valor Pago<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valpago" id="valpago" required="required" aria-required="true" value="{{ $dados->VALPAGO }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>  

    <div class="row">
        <div class="col-md-12">
            <div class="rendered-form">
                <div class="fb-button form-group field-emitir">
                    <button type="submit" class="btn-success btn" name="emitir" style="success" id="emitir">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection


@section('scripts')

<script type="text/javascript">

$('#dtapago').datepicker({locale:'pt-br', format:'dd/mm/yyyy'});

$("#valpago").on("keyup", function(){
    var valid = /^\d{0,10}(\,\d{0,2})?$/.test(this.value),
        val = this.value;
    
    if(!valid){
        console.log("Numero invalido!");
        this.value = val.substring(0, val.length - 1);
    }
});

</script>  
@endsection