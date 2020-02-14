<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SteriIntelli</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">


    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">


    <!-- Template -->

    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />

    <!-- Material Kit CSS -->
    <link href="{{ asset('paper-dashboard/css/bootstrap.min.css')}}" rel="stylesheet" />
    <link href="{{ asset('paper-dashboard/css/paper-dashboard.css?v=2.0.0')}}" rel="stylesheet" />

    <link href="{{ asset('plugins/datepicker/datepicker3.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet">

</head>
<body>

    <div class="wrapper">
      <div class="sidebar" data-color="white">

        <div class="logo">
        <a href="#" class="simple-text logo-mini">
          <div class="logo-image-small">
            <img src="{{asset('paper-dashboard/img/logo-small.png')}}">
          </div>
        </a>
        <a href="#" class="simple-text logo-normal">
          Sterintelli
          <!-- <div class="logo-image-big">
            <img src="../assets/img/logo-big.png">
          </div> -->
        </a>
      </div>
        <div class="sidebar-wrapper">
          <ul class="nav">

              <li class="active">
                  <a data-toggle="collapse" href="#mn_faturamento" class="collapsed" aria-expanded="false">
                      <i class="nc-icon nc-app"></i>
                      <p>Faturamento
                        <b class="caret"></b>
                      </p>
                  </a>
                  <div class="collapse" id="mn_faturamento">
                      <ul class="nav">
                          <li>
                              <a href="#">
                                  <span class="sidebar-mini-icon">PC</span>
                                  <span class="sidebar-normal">Por Cliente</span>
                              </a>
                          </li>
                          <li>
                              <a href="#">
                                  <span class="sidebar-mini-icon">PP</span>
                                  <span class="sidebar-normal">Ppr período</span>
                              </a>
                          </li>
                      </ul>
                  </div>
              </li>

              <li>
                  <a data-toggle="collapse" href="#mn_notas" class="collapsed" aria-expanded="false">
                      <i class="nc-icon nc-single-copy-04"></i>
                      <p>Notas Fiscais
                         <b class="caret"></b>
                      </p>
                  </a>

                  <div class="collapse" id="mn_notas">
                      <ul class="nav">
                          <li>
                              <a href="/notas">
                                  <span class="sidebar-mini-icon">CN</span>
                                  <span class="sidebar-normal">Consulta de Notas</span>
                              </a>
                          </li>
                          <li>
                              <a href="../examples/pages/timeline.html">
                                  <span class="sidebar-mini-icon">CC</span>
                                  <span class="sidebar-normal">Cancelamento</span>
                              </a>
                          </li>
                      </ul>
                  </div>
              </li>
          </ul>
        </div>
      </div>

    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-minimize">
              <button id="minimizeSidebar" class="btn btn-icon btn-round">
                <i class="nc-icon nc-minimal-right text-center visible-on-sidebar-mini"></i>
                <i class="nc-icon nc-minimal-left text-center visible-on-sidebar-regular"></i>
              </button>
            </div>

            <a class="navbar-brand" href="#pablo">@yield('titulo')</a>
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
          </button>
          <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
              
              <li class="nav-item">
                <a class="nav-link btn-rotate" href="#sair">
                  <i class="nc-icon nc-settings-gear-65"></i>
                  <p>
                    <span class="d-lg-none d-md-block">Sair</span>
                  </p>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <div class="content">

            @yield('content')

      </div>
      <footer class="footer footer-black  footer-white ">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <ul>
                <li>
                  <a href="https://sterintelli.com" target="_blank">SterIntelli Site</a>
                </li>
              </ul>
            </nav>
            <div class="credits ml-auto">
              <span class="copyright">
                © Sterintelli
              </span>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>

    <script src="{{ asset('paper-dashboard/js/core/jquery.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('paper-dashboard/js/core/popper.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('paper-dashboard/js/core/bootstrap.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('paper-dashboard/js/plugins/perfect-scrollbar.jquery.min.js')}}" type="text/javascript"></script>

    <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
    <script src="{{ asset('plugins/datepicker/bootstrap-datepicker.js')}}"></script>
    <!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
    <script src="{{ asset('plugins/datatables/datatables.min.js')}}"></script>

    <!-- Select 2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js')}}"></script>


    <!-- Chart JS -->
    <script src="{{ asset('paper-dashboard/js/plugins/chartjs.min.js')}}"></script>

    <!--  Notifications Plugin    -->
    <script src="{{ asset('paper-dashboard/js/plugins/bootstrap-notify.js')}}"></script>

    <!-- Control Center for Paper Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('paper-dashboard/js/paper-dashboard.js?v=2.0.0')}}" type="text/javascript"></script>


    @yield('scripts')
</body>
</html>
