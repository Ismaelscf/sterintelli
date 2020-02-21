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


    <!-- Template -->

    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />



    <!-- Material Kit CSS -->

    <link href="{{ asset('paper-dashboard/css/bootstrap.min.css')}}" rel="stylesheet" />
    <link href="{{ asset('paper-dashboard/css/paper-dashboard.css?v=2.0.0')}}" rel="stylesheet" />


    <link href="{{ asset('plugins/datepicker/datepicker3.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet">

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />    

</head>
<body class="login-page">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
    <div class="container">
      <div class="navbar-wrapper">
        <div class="navbar-toggle">
          <button type="button" class="navbar-toggler">
            <span class="navbar-toggler-bar bar1"></span>
            <span class="navbar-toggler-bar bar2"></span>
            <span class="navbar-toggler-bar bar3"></span>
          </button>
        </div>
        <a class="navbar-brand" href="#pablo">SterIntelli</a>
      </div>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-bar navbar-kebab"></span>
        <span class="navbar-toggler-bar navbar-kebab"></span>
        <span class="navbar-toggler-bar navbar-kebab"></span>
      </button>
      <!--<div class="collapse navbar-collapse justify-content-end" id="navigation">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="../dashboard.html" class="nav-link">
              <i class="nc-icon nc-layout-11"></i> Dashboard
            </a>
          </li>
          <li class="nav-item ">
            <a href="register.html" class="nav-link">
              <i class="nc-icon nc-book-bookmark"></i> Register
            </a>
          </li>
          <li class="nav-item  active ">
            <a href="login.html" class="nav-link">
              <i class="nc-icon nc-tap-01"></i> Login
            </a>
          </li>
          <li class="nav-item ">
            <a href="user.html" class="nav-link">
              <i class="nc-icon nc-satisfied"></i> User
            </a>
          </li>
          <li class="nav-item ">
            <a href="lock.html" class="nav-link">
              <i class="nc-icon nc-key-25"></i> Lock
            </a>
          </li>
        </ul>
      </div>-->
    </div>
  </nav>
  <!-- End Navbar -->

  <div class="wrapper wrapper-full-page ">
    <div class="full-page section-image" filter-color="black" data-image="{{asset('images/bg.jpg')}}">
      <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
      <div class="content">
        <div class="container">
           @if ($errors->any())
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Whoops! </strong>Ocorreram Erros.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif    

        @if (isset($msgAlerta))
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <ul>
                    @foreach ($msgAlerta as $m)
                        <li>{!! $m !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif    

        @if (isset($msgInforma))
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <ul>
                    @foreach ($msgInforma as $m)
                        <li>{!! $m !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif   

        @yield('content')
        </div>
      </div>
      <footer class="footer footer-black  footer-white ">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <ul>
                <li>
                  <a href="https://www.creative-tim.com" target="_blank">SterIntelli Site</a>
                </li>
              </ul>
            </nav>
            <div class="credits ml-auto">
              <span class="copyright">
                ©
                <script>
                  document.write(new Date().getFullYear())
                </script>
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

