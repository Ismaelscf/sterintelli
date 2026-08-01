@extends('layout.app')
 
@section('titulo')
Consulta de Notas Emitidas
@endsection

@section('content')
<div class="card">
      <div class="card-header">
        <h4 class="card-title">Notas Emitidas no período - {{$dtIni}} - {{$dtFim}}</h4>
      </div>
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">

                <!-- codcliente, nome, numeronota, valornota,
                       dtaInicial, dtafinal, dtanota, 
                       dtapago, valpago, dtavencimento -->
                <thead>
                <tr>
                    <th>Razão Social</th>
                    <th>Numero<br><small>Dt. Nota</small></th>
                    <th>Valor</th>
                    <th>Período</th>
                    <th>Pagamento</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($notas as $nota)
                
                <tr>
                    <td>{{ $nota->NOME }}</td>
                    <td>{{ $nota->NUMERONOTA }}<br><small>{{ $nota->DTANOTA }}</small></td>
                    <td>{{ $nota->VALORNOTA }}</td>
                    <td>{{ $nota->DTAINICIAL }} - {{ $nota->DTAFINAL }}</td>
                    <td>{{ $nota->VALPAGO }}
                        @if(isset($nota->DTAPAGO))
                            <br><small>{{ $nota->DTAPAGO }}</small>
                        @endif</td>
                    <td>
                        <form action="" method="POST">
                             <a href="{{ url('notas/preeditarnota/'.$nota->CODCLIENTE.'/'.$nota->NUMERONOTA.'/') }}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-edit"></i></a>

                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
    </div>
</div>

      
@endsection

@section('scripts')

<script>

    $('#datatable').DataTable();    

</script>  


@endsection