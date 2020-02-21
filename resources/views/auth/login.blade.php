@extends('layout.app_nomenu')
 
@section('titulo')
Consulta de Notas Emitidas
@endsection

@section('content')
          <div class="col-lg-4 col-md-6 ml-auto mr-auto">
            <form class="form" method="POST" action="{{url('post-login')}}">
              {{ csrf_field() }}

              <div class="card card-login">
                <div class="card-header ">
                  <div class="card-header ">
                    <h3 class="header text-center">Login</h3>
                  </div>
                </div>
                <div class="card-body ">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="nc-icon nc-single-02"></i>
                      </span>
                    </div>
                    <input type="text"  name="loginname" id="loginname" class="form-control" placeholder="Usuário" required>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="nc-icon nc-key-25"></i>
                      </span>
                    </div>
                    <input type="password" name="senha" id="senha" placeholder="Senha" class="form-control" required>
                  </div>

                </div>
                <div class="card-footer ">
                  <button class="btn btn-warning btn-round btn-block mb-3" type="submit">Entrar</button>
                </div>
              </div>
            </form>
          </div>
@endsection

@section('scripts')




@endsection          