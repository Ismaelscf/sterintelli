@extends('layout.app')
 
@section('content')
    <!--<div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Notas</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="#">Nova nota</a>
            </div>
        </div>
    </div>-->
   
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
   
    <table class="table table-bordered">
        <tr>
            <th>Numero</th>
            <th>Razão Social Tomador</th>
            <th>Data</th>
            <th width="300px">Ação</th>
        </tr>
        @foreach ($notas as $nota)
        
        <tr>
            <td>{{ $nota->NumeroNota }}<br><small>{{ $nota->CodigoVerificao }}</small></td>
            <td>{{ $nota->RazaoSocialTomador }}</td>
            <td>{{ $nota->DataProcessamento }}</td>
            <td>
                <form action="" method="POST">
   
                    <a class="btn btn-info" href="">print</a>
                    <!--<a class="btn btn-info" href="">Detalhar</a>
                    <a class="btn btn-primary" href="">Editar</a>
                    <button type="submit" class="btn btn-danger">Excluir</button>-->
                </form>
            </td>
        </tr>
        @endforeach
    </table>
  
      
@endsection