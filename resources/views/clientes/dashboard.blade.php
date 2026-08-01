<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Fonts e Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://gbringel.com/wp-content/uploads/2020/09/o-que-e-CME-1.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(14px); /* tirado grayscale */
            z-index: -1;
        }

        .blur-overlay {
            height: 100vh;
            width: 100%;
            overflow-y: auto;
        }

        .topbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(6px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-img {
            max-height: 40px;
        }

        .content {
            padding: 40px 15px;
        }

        .card-custom {
            background-color: rgba(255, 255, 255, 0.75); /* translúcido real */
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }

        .btn-logout {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-logout:hover {
            background-color: #bb2d3b;
        }

        .btn-acompanhar {
            background-color: #3DAD96;
            border: none;
        }

        .btn-acompanhar:hover {
            background-color: #2e8d79;
        }

        .table thead {
            background-color: #3DAD96;
            color: #fff;
        }

        .titulo-dashboard {
            font-size: 1.4rem;
            font-weight: bold;
            color: #3DAD96;
        }

        @media (max-width: 768px) {
            .card-custom {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="blur-overlay">
        <!-- Topbar -->
        <div class="topbar">
            <img src="{{ asset('images/logo-steriliza25.png') }}" alt="Logo" class="logo-img">
            <a href="{{ route('clientes.logout') }}" class="btn btn-logout">Logout</a>
        </div>

        <!-- Conteúdo -->
        <div class="content">
            <div class="container-fluid px-3">
                <div class="card-custom">
                    <div class="mb-4">
                        <div class="titulo-dashboard">Bem-vindo!</div>
                        <p class="mb-0">Seu CNPJ: {{ session('cliente_login') }}</p>
                    </div>

                    <div class="table-responsive">
                        <table id="clientesTable" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Fantasia</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->id }}</td>
                                    <td>{{ $cliente->nome }}</td>
                                    <td>{{ $cliente->fantasia }}</td>
                                    <td>{{ $cliente->email }}</td>
                                    <td>{{ $cliente->telefone }}</td>
                                    <td>
                                        <a href="{{ route('clientes.buscafaturas', $cliente->codigo) }}" class="btn btn-sm btn-acompanhar">
                                            Acompanhar
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#clientesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                }
            });
        });
    </script>
</body>
</html>
