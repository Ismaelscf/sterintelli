@extends('layout.app')


@section('titulo')
Sistema de Gestão de Esterilização
@endsection

@section('content')

<div class="row">
  <a href="preconsultarfaturamento/P/"> Periodo</a><br>
  <a href="preconsultarfaturamento/C/"> Cliente</a>
</div>

<div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
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
                      <p class="card-category">Capacity</p>
                      <p class="card-title">150GB
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i> Update Now
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6">
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
                      <p class="card-category">Revenue</p>
                      <p class="card-title">$ 1,345
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-calendar-o"></i> Last day
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6">
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
                      <p class="card-category">Errors</p>
                      <p class="card-title">23
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-clock-o"></i> In the last hour
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-favourite-28 text-primary"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Followers</p>
                      <p class="card-title">+45K
                        <p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i> Update now
                </div>
              </div>
            </div>
          </div>
        </div>
<div class="row">
  <div class="col-lg-4 col-sm-6">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-7">
            <div class="numbers pull-left">
              $34,657
            </div>
          </div>
          <div class="col-sm-5">
            <div class="pull-right">
              <span class="badge badge-pill badge-success">
                +18%
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="big-title">total earnings in last ten quarters</h6>
        <canvas id="activeUsers" width="826" height="380"></canvas>
      </div>
      <div class="card-footer">
        <hr>
        <div class="row">
          <div class="col-sm-7">
            <div class="footer-title">Financial Statistics</div>
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
  <div class="col-lg-4 col-sm-6">
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
              <span class="badge badge-pill badge-danger">
                -14%
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="big-title">total subscriptions in last 7 days</h6>
        <canvas id="emailsCampaignChart" width="826" height="380"></canvas>
      </div>
      <div class="card-footer">
        <hr>
        <div class="row">
          <div class="col-sm-7">
            <div class="footer-title">View all members</div>
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
  <div class="col-lg-4 col-sm-6">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-7">
            <div class="numbers pull-left">
              8,960
            </div>
          </div>
          <div class="col-sm-5">
            <div class="pull-right">
              <span class="badge badge-pill badge-warning">
                ~51%
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="big-title">total downloads in last 6 years</h6>
        <canvas id="activeCountries" width="826" height="380"></canvas>
      </div>
      <div class="card-footer">
        <hr>
        <div class="row">
          <div class="col-sm-7">
            <div class="footer-title">View more details</div>
          </div>
          <div class="col-sm-5">
            <div class="pull-right">
              <button class="btn btn-warning btn-round btn-icon btn-sm">
                <i class="nc-icon nc-alert-circle-i"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>





@endsection
