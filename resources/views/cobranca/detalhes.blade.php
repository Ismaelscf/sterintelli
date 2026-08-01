@extends('layout.app')


@section('titulo')
Sistema de Gestão de Esterilização
@endsection

@section('content')
{{ csrf_field() }}

<div class="col-sm-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Emails Enviados</h4>
      </div>
      <div class="card-body">
            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Código do Cliente</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nota Fiscal</th>
                    <th>Valor</th>
                    <th>Enviado em:</th>
                </tr>
                </thead>
                <tbody>
                @foreach($emails as $email)
                    <tr>
                        <td>{{ $email->codigo_cliente }}</td>
                        <td>{{ $email->nome_cliente }}</td>
                        <td>{{ $email->email_cliente }}</td>
                        <td>{{ $email->nf }}</td>
                        <td>{{ number_format($email->valor, 2, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($email->data_envio)->format('d/m/Y \à H:i:s') }}</td>
                    </tr>
                @endforeach 
                </tbody>
            </table>
    </div>
</div>
</div>

@endsection

@section('scripts')
<script>
  $('#datatable').DataTable();    
</script> 
@endsection