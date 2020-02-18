@extends('layout.app')


@section('titulo')
Sistema de Gestão de Esterilização
@endsection

@section('content')


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
                      <p class="card-title">10
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <!--<i class="fa fa-refresh"></i> Update Now-->
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
                      <p class="card-title">$ 1200,00
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <!--<i class="fa fa-calendar-o"></i> Last day-->
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
                      <p class="card-title">23
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
              <div class="card-header">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="numbers pull-left">
                      $34.657,00
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                     <!-- <span class="badge badge-pill badge-success">
                        +18%
                      </span>-->
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <h6 class="big-title">Valor emitido por mês</h6>
                <canvas id="activeUsers" width="826" height="380"></canvas>
              </div>
              <div class="card-footer">
                <hr>
                <div class="row">
                  <div class="col-sm-7">
                    <div class="footer-title">Estatistica Financeira</div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <button class="btn btn-success btn-round btn-icon btn-sm">
                        <i class="nc-icon nc-simple-add"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-sm-6">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="numbers pull-left">
                      169
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <!--<span class="badge badge-pill badge-danger">
                        -14%
                      </span>-->
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <h6 class="big-title">Emissões por mes</h6>
                <canvas id="emailsCampaignChart" width="826" height="380"></canvas>
              </div>
              <div class="card-footer">
                <hr>
                <div class="row">
                  <div class="col-sm-7">
                    <div class="footer-title">Quantitativos</div>
                  </div>
                  <div class="col-sm-5">
                    <div class="pull-right">
                      <button class="btn btn-danger btn-round btn-icon btn-sm">
                        <i class="nc-icon nc-button-play"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


@endsection
