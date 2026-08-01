@extends('layout.app')
 
@section('titulo')
Externo
@endsection

@section('content')
{{$url}}
<iframe src="{{ $url }}" width="100%" height="100%" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>

@endsection