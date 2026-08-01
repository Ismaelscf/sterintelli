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
                    <th>Emails Enviados</th>
                    <th>Notas Distintas</th>
                </tr>
                </thead>
                <tbody>
                @foreach($emails as $email)
                    <tr>
                        <td>
                            <a href="{{ route('detalhesEmail', ['codigo' => $email->codigo_cliente]) }}">
                                {{ $email->codigo_cliente }}
                            </a>
                        </td>
                        <td>{{ $email->nome_cliente }}</td>
                        <td>{{ $email->numero_emails_enviados }}</td>
                        <td>{{ $email->numero_nf }}</td>
                        
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