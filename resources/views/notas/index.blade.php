@extends('layout.layout')
 
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Notas</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('notas.emitir') }}">Nova nota</a>
            </div>
        </div>
    </div>
   
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
   
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Numero</th>
            <th>Razão Social Tomador</th>
            <th>Data</th>
            <th width="300px">Ação</th>
        </tr>
        @foreach ($notas as $nota)
        <tr>
            <td>{{ $nota->id }}</td>
            <td>{{ $nota->serierps}} - {{ $nota->numerorps }}</td>
            <td>{{ $nota->razaosocialtomador }}</td>
            <td>{{ $nota->dataemissaorps }}</td>
            <td>
                <form action="{{ route('notas.destroy',$nota->id) }}" method="POST">
   
   <a class="btn btn-info" href="{{ route('notas.danfse',$nota) }}">print</a>
                    <a class="btn btn-info" href="{{ route('notas.show',$nota->id) }}">Detalhar</a>
    
                    <a class="btn btn-primary" href="{{ route('notas.edit',$nota->id) }}">Editar</a>
   
                    @csrf
                    @method('DELETE')
      
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
  
    {!! $notas->links() !!}
      
@endsection