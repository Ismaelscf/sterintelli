@extends('layout.app')
 
@section('titulo')
Faturamento no período - {{$dtIni}} - {{$dtFim}}
@endsection

@section('content')
<div style="text-align: right;">
<b>Legenda</b>
<a class="btn btn-info btn-link btn-icon btn-sm"> <i class="fa fa-send "></i></a> Emitir NFSe
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-print"></i></a> Faturamento
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-bars"></i></a> Detalhar Fat.
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-file"></i></a> Imprimir NFSe
</div>

<div class="card">
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Transporte</th>
                        <th>Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                        @if(isset($lista))
                            @foreach ($lista as $l)
                            <tr>
                                <td>{{ $l->FANTASIA }}</td>
                                <td>{{ $l->QTD }}</td>
                                <td align="right">{{ $l->TOTAL }}</td>
                                <td align="right">{{ $l->TRANSPORTE }}</td>
                                <td align="right">{{ $l->TOT_C_TRANSPORTE }}</td>
                                <td>
                                    <form action="" method="POST">
                                        <a href="/notas/preemitir/{{ $l->CLICOD }}?dtini={{$dtIni }}&dtfim={{$dtFim}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-send"></i></a>

                                        <a href="/faturamento/imprimirfaturamento/{{ $l->CLICOD }}?dtini={{$dtIni}}&dtfim={{$dtFim}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-print"></i></a>
                                        
                                        <a href="/faturamento/detalharfaturamento/{{ $l->CLICOD }}?dtini={{$dtIni}}&dtfim={{$dtFim}}&cliente={{$l->FANTASIA}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-bars"></i></a>

                                    @if(isset($l->CAMINHO))
                                    <form action="" method="POST">
                                                <a href="/notas/imprimirnfse{{$l->CAMINHO}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-file"></i></a>
                                    @endif
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


    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
<script>

$('#datatable').DataTable( {
    dom: 'Bfrtip',
    buttons: [
        'csv', 'excel', 'pdf'
    ]
} );
</script>  


@endsection