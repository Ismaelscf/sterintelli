@extends('layout.app')


@section('titulo')
Sistema de Gestão de Esterilização
@endsection

@section('content')
{{ csrf_field() }}

<div class="row">
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-globe text-warning"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">NFSe Emitidas</p>
                      <p class="card-title">{{$dados->qtd_notas}}
                        <p>
                      
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-calendar-o"></i> Emitidas no mês {{$dados->mes}}
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-money-coins text-success"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Total $</p>
                      <p class="card-title">{{$dados->tot_notas}}
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-calendar-o"></i>Emitidas no mês {{$dados->mes}}
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-vector text-danger"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Clientes com NFSe</p>
                      <p class="card-title">{{$dados->qtd_clientes}}
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <!--<i class="fa fa-clock-o"></i> In the last hour-->
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6 col-sm-6">
            <div class="card">
              <!--<div class="card-header">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="numbers pull-left">
                     {{$dados->tot_notas}}
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                     <!-- <span class="badge badge-pill badge-success">
                        +18%
                      </span>
                    </div>
                  </div>
                </div>
              </div>-->
              <div class="card-body">
                <h6 class="big-title">Valor emitido por mês</h6>
                <div id="grafico1"></div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="row">
                  <div class="col-sm-7">
                    <div class="footer-title">Estatistica Financeira</div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <!--<button class="btn btn-success btn-round btn-icon btn-sm">
                        <i class="nc-icon nc-simple-add"></i>-->
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-sm-6">
            <div class="card">
              <!--<div class="card-header">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="numbers pull-left">
                      {{$dados->qtd_notas}}
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <!--<span class="badge badge-pill badge-danger">
                        -14%
                      </span>--
                    </div>
                  </div>
                </div>
              </div>-->
              <div class="card-body">
                <h6 class="big-title">Emissões por mes</h6>
                <div id="grafico2"></div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="row">
                  <div class="col-sm-7">
                    <div class="footer-title">Quantitativos</div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <!--<button class="btn btn-danger btn-round btn-icon btn-sm">
                        <i class="nc-icon nc-button-play"></i>-->
                        

                        </script>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">Últimas notas emitidas</div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Cliente</th>
                        <th>Número</th>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($notasRecentes as $n)
                        <tr>
                          <td>{{ $n->FANTASIA ?: $n->CLIENTE }}</td>
                          <td>{{ $n->NUMERONOTA }}</td>
                          <td>{{ $n->DTANOTA }}</td>
                          <td>{{ $n->VALORNOTA }}</td>
                          <td>
                            @if($n->STATUS_FOCUS == 'autorizado')
                              <span class="badge badge-success">Autorizado</span>
                            @elseif($n->STATUS_FOCUS == 'processando_autorizacao')
                              <span class="badge badge-warning">Processando</span>
                            @elseif($n->STATUS_FOCUS == 'erro_autorizacao')
                              <span class="badge badge-danger">Erro</span>
                            @else
                              <span class="badge badge-secondary">{{ $n->STATUS_FOCUS ?: '—' }}</span>
                            @endif
                          </td>
                          <td>
                            <a href="{{ url('notas/imprimirnfse/' . $n->NUMERONOTA . '/0/') }}" class="btn btn-info btn-link btn-icon btn-sm" target="_blank" title="Imprimir NFSe"><i class="fa fa-file"></i></a>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="6">Nenhuma nota emitida ainda.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

@endsection

@section('scripts')
<style type="text/css">
  
  .ct-horizontal,  .ct-vertical{
   font-size: 10px !important;
   white-space:nowrap;
 }
</style>

<script type="text/javascript">
                  
arr_labels = [];
arr_qdt = [];
arr_tot = [];
@foreach($dados->dados_grafico as $dado)
  arr_labels.push( '{{$dado->PERIODO}}' );
  arr_qdt.push( '{{$dado->QTD_NOTAS}}' );
  arr_tot.push( ('{{$dado->TOT_NOTAS}}').replace(",", ".") );

@endforeach


new Chartist.Line('#grafico1', {labels: arr_labels,
    series: [arr_tot]}, {
  fullWidth: true,
  chartPadding: {
    right: 40
  }
});

new Chartist.Line('#grafico2', {labels: arr_labels,
    series: [arr_qdt]}, {
  fullWidth: true,
  chartPadding: {
    right: 40
  }
});
 </script>
@endsection