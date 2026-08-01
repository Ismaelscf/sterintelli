@extends('layout.app')
 
@section('titulo')
Cadastro de Clientes
@endsection

@section('content')
<!--<div style="text-align: right;">
<b>Legenda</b>
<a class="btn btn-info btn-link btn-icon btn-sm"> <i class="fa fa-send "></i></a> Emitir NFSe
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-print"></i></a> Faturamento
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-bars"></i></a> Detalhar Fat.
<a class="btn btn-info btn-link btn-icon btn-sm"><i class="fa fa-file"></i></a> Imprimir NFSe
</div>-->

<div class="card">
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>CNPJ</th>
                        <th>Razão</th>
                        <th>Fantasia</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                        @if(isset($dados))
                            @foreach ($dados as $l)
                            <tr>
                                <td>{{ $l->CNPJ }}</td>
                                
                                <td>{{ $l->NOME }}
                                <br><small>{{ $l->DADOS }}</small>
                               
                                </td>

                                <td>{{ $l->FANTASIA }}
                                     @if(isset($l->DESCONTO))
                                        <br><small>Desconto: {{ $l->DESCONTO }}</small>
                                    @endif
                                    @if(isset($l->TAXA))
                                        <br><small>Tx. Transporte: {{ $l->TAXA }}</small>
                                    @endif

                                </td>
                                <td>
                                    <form action="" method="POST">

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

$('#datatable').DataTable( {
    dom: 'Bfrtip',
    buttons: [
        'csv', 'excel', 'pdf'
    ],
     "order": [[ 1, "asc" ]]
} );
</script>  


@endsection