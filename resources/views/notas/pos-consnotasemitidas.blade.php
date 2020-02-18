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
                    <td>{{ $nota->NUMERONOTA }}<br><small>{{ date('d/m/Y', strtotime($nota->DTANOTA)) }}</small></td>
                    <td>{{ $nota->VALPAGO }}</td>
                    <td>{{ date('d/m/Y', strtotime($nota->DTAINICIAL)) }} - {{ date('d/m/Y', strtotime($nota->DTAFINAL)) }}</td>
                    <td>{{ $nota->VALPAGO }}<br><small>{{ date('d/m/Y', strtotime($nota->DTAPAGO)) }}</small></td>
                    <td>
                        <form action="" method="POST">
           

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