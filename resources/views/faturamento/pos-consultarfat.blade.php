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
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-barcode"></i></a> Emitir Boleto
</div>

<div class="card">
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Clientes</th>
                        <th>Tipo Esterilização</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Transporte</th>
                        <th>Total</th>
                        <th style="width: 100px">Ação</th>
                    </tr>
                </thead>
                <tbody>
                        @if(isset($lista))
                            @foreach ($lista as $l)
                            <tr>
                                <td>{{ $l->RAZAO }}<br><small>{{ $l->CLICOD }} -{{ $l->FANTASIA }}</small></td>
                                <td>{{ $l->DESC_TIPO_EST }}</td>
                                <td>{{ $l->QTD }}</td>
                                <td align="right">{{ $l->TOTAL }}</td>
                                <td align="right">{{ $l->TRANSPORTE }}</td>
                                <td align="right">{{ $l->TOT_C_TRANSPORTE }}</td>
                                <td>
                                    <form action="" method="POST">
                                        <a href="{{ url('notas/preemitir/'.$l->CLICOD.'?dtini='.$dtIni.
                                                        '&dtfim='.$dtFim.'&searchOption='.
                                                        $searchOption.'&tipoEste='.$l->TIPO_ESTE.'')}}" 
                                                        class="btn btn-info btn-link btn-icon btn-sm print" 
                                                        target="_blank" title="Emitir NFSe"><i class="fa fa-send" ></i></a>

                                        <a href="{{ url('faturamento/imprimirfaturamento/'.$l->CLICOD.'?dtini='.$dtIni.
                                                        '&dtfim='.$dtFim.'&searchOption='.$searchOption.'&tipoEste='. $l->TIPO_ESTE .'')}}" 
                                                        class="btn btn-info btn-link btn-icon btn-sm print" 
                                                        target="_blank" title="Imprimir Faturamento"><i class="fa fa-print"></i></a>
                                        
                                        <a href="{{ url('faturamento/detalharfaturamento/'.$l->CLICOD.'?dtini='.$dtIni.
                                                        '&dtfim='.$dtFim.'&searchOption='.$searchOption.'&tipoEste='. $l->TIPO_ESTE .'')}}" 
                                                        class="btn btn-info btn-link btn-icon btn-sm print" 
                                                        target="_blank"  title="Detalhar Faturamento"><i class="fa fa-bars"></i></a>
                                                        

                                    @if(isset($l->CAMINHO))
                                    <form action="" method="POST">
                                        @foreach(explode(";",$l->CAMINHO) as $n)
                                            @if($n != "")
                                                <a href="{{ url('notas/imprimirnfse')}}{{$n}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank" title="ImprimirNFSe"><i class="fa fa-file"></i></a>
                                                <a href="{{ url('notas/imprimirnfse')}}{{$n}}?email=S" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank" title="Nota Fiscal e Boletos enviados para o email: {{$l->EMAIL}}
                                                Clique para enviar novamente."><i class="fa fa-envelope"></i></a>
                                                <a href="{{ url('boleto')}}{{$n}}" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank" title="Emitir boleto"><i class="fa fa-barcode"></i></a>
                                            @endif
                                        @endforeach
                                    
                                                
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