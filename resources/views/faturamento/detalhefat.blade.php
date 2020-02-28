@extends('layout.app')
 
@section('titulo')
Detalhe do Faturamento por Período
@endsection

@section('content')
<div style="text-align: right;">
<b>Legenda</b>
<a class="btn btn-info btn-link btn-icon btn-sm"> <i class="fa fa-send "></i></a> Emitir NFSe
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-print"></i></a> Faturamento
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-file"></i></a> Imprimir NFSe
</div>

<div class="card">
      <div class="card-header">
        <h4 class="card-title">Listas do período - {{$cliente}} - {{$dtIni}} - {{$dtFim}}</h4>
      </div>
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Transporte</th>
                        <th>Total</th>
                        <!--<th>Ação</th>-->
                    </tr>
                </thead>
                <tbody>
                        @if(isset($lista))
                            @foreach ($lista as $l)
                            <tr>
                                <td>{{ $l->FANTASIA }}</td>
                                <td>{{ $l->DATAESTE }}</td>
                                <td>{{ $l->QTD }}</td>
                                <td align="right">{{ $l->TOTAL }}</td>
                                <td align="right">{{ $l->TRANSPORTE }}</td>
                                <td align="right">{{ $l->TOT_C_TRANSPORTE }}</td>
                                <!--<td>
                                    <form action="" method="POST">
                                        
                                    </form>
                                </td>-->
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