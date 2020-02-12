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
                <tr>
                    <th>Numero</th>
                    <th>Razão Social Tomador</th>
                    <th>Data</th>
                    <th>Ação</th>
                </tr>
                @foreach ($notas as $nota)
                
                <tr>
                    <td>{{ $nota->NumeroNota }}<br><small>{{ $nota->CodigoVerificao }}</small></td>
                    <td>{{ $nota->RazaoSocialTomador }}</td>
                    <td>{{ $nota->DataProcessamento }}</td>
                    <td>
                        <form action="" method="POST">
           
                            <a href="/notas/consultarnfse/{{ $nota->NumeroNota }}/{{ $nota->CodigoVerificao }}/" class="btn btn-info btn-link btn-icon btn-sm print" target="_blank"><i class="fa fa-print"></i></a>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
    </div>
</div>

      
@endsection

@section('scripts')

<script>
    $(document).ready( function () {
        $('#datatable').DataTable();
    } );

</script>  


@endsection