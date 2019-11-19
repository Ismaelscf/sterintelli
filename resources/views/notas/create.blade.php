@extends('layout.layout')
  
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Add New nota</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('notas.index') }}"> Back</a>
        </div>
    </div>
</div>
   
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
   
<form action="{{ route('notas.store') }}" method="POST">
    @csrf
  
     <div class="row">
        <div class="col-xs-6">
            <div class="form-group">
                <strong>Numero:</strong>
                <input type="text" name="numero" class="form-control" placeholder="Name">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
                <strong>CNPJ tomador:</strong>
                <input type="text" name="cnpjtomador" class="form-control" placeholder="Name">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
                <strong>RPS:</strong>
                <input type="text" name="rps" class="form-control" placeholder="Name">
            </div>
        </div>
        <div class="col-xs-6">

        </div>        
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
   
</form>
@endsection