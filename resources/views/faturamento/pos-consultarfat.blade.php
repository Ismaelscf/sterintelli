@extends('layout.app')
 
@section('titulo')
Consulta de Notas Emitidas
@endsection

@section('content')
<div class="card">
      <div class="card-header">
        <h4 class="card-title">Notas no período</h4>
      </div>
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                        <th>Total c/Transporte</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                        @if(isset($lista))
                            @foreach ($lista as $l)
                            <tr>
                                <td>{{ $l->FANTASIA }}</td>
                                <td>{{ $l->QTD }}</td>
                                <td>{{ $l->TOTAL }}</td>
                                <td>{{ $l->TOT_C_TRANSPORTE }}</td>
                                <td>
                                    <form action="" method="POST">
                                                <a href="/notas/preemitir/{{ $l->CLICOD }}?dtini={{$dtIni }}&dtfim={{$dtFim}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-print"></i></a>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>

                            </tr>
                        @endif
                </tbody>
            </table>
    </div>
</div>

      
@endsection

@section('scripts')

<script>
    $('#datatable').DataTable();
    $(document).ready( function () {

    } );

</script>  


@endsection