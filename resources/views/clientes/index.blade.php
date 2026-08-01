<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Esterilização</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts e Bootstrap CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: url('https://gbringel.com/wp-content/uploads/2020/09/o-que-e-CME-1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #3DAD96; /* verde do logo */
        }

        .logo-img {
            max-width: 100px;
            margin-bottom: 20px;
        }

        .btn-steriliza {
            background-color: #3DAD96;
            border-color: #3DAD96;
            color: white;
        }

        .btn-steriliza:hover {
            background-color: #369e89;
            border-color: #369e89;
        }
    </style>
</head>
<body>
    <div class="container h-100 d-flex justify-content-center align-items-center">
        <div class="col-md-4 login-box">
            <!-- Logo -->
            <img src="{{ asset('images/logo-steriliza25.png') }}" alt="Logo" class="logo-img">

            <h4 class="mb-4">Acesso ao Sistema de Notas</h4>

            @if (session('error'))
                <div class="alert alert-danger text-start">
                    {{ session('error') }}
                </div>
            @endif


            <form method="POST" action="{{ route('clientes.login') }}">
                @csrf

                <div class="mb-3 text-start">
                    <label for="login" class="form-label">Usuário</label>
                    <input type="text" name="login" id="login" class="form-control" required autofocus>
                </div>

                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-steriliza w-100">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
