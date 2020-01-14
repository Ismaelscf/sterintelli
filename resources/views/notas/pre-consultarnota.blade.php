@extends('layout.app') @section('content')
 <form action="{{ url('notas/consultarnotas') }}" method="POST">
   @csrf 
<div class="container">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h1>
					Consultar notas fiscais de serviço
					</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Periodo da busca</div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-text form-group field-cnpj">
                                <label for="dtIni" class="fb-text-label">Incio<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtIni" id="dtIni" required="required" aria-required="true" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-im">
                                <label for="dtFim" class="fb-text-label">Fim<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dtFim" id="dtFim" required="required" aria-required="true" value="">
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
                    <button type="submit" class="btn-success btn" name="emitir" style="success" id="emitir">Emitir</button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection