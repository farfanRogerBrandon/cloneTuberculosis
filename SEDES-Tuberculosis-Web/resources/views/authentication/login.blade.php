<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuberculosis-Sedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/Login.css?v=1.0') }}" rel="stylesheet">
</head>
<body>
<div class="container">
        <div class="curved-shape"></div>
        <div class="curved-shape2"></div>
        <div class="slider">
            <!-- Login Form -->
            <div class="form-box">
                <img src="/images/logotuberculosis.png" alt="Logo" class="logo">
                <div class="welcome-text">
                    <h4 id="tx2" class="animation" style="--D:0; --S:20">Bienvenido a Sedes!</h4>
                    <p id="tx2" class="animation" style="--D:1; --S:21">Estamos felices de verte de vuelta. Ingresa con tus credenciales para continuar.</p>
                </div>
                <h2>Iniciar Sesión</h2>
              
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('empleado.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="nombreUsuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="nombreUsuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </form>
            </div>

            <!-- Register Form -->
            <div class="form-box">
                <img src="/images/logotuberculosis.png" alt="Logo" class="logo">
                <h2>Registrarse</h2>
                <form>
                    <div class="input-group">
                        <label for="register-username">Usuario</label>
                        <input type="text" id="register-username" required>
                    </div>
                    <div class="input-group">
                        <label for="register-email">Correo Electrónico</label>
                        <input type="email" id="register-email" required>
                    </div>
                    <div class="input-group">
                        <label for="register-password">Contraseña</label>
                        <input type="password" id="register-password" required>
                    </div>
                    <button class="btn" type="submit">Registrarse</button>
                    <p>
                        ¿Ya tienes una cuenta? 
                        <a class="toggle-link" id="to-login">Inicia sesión</a>    
                    </p>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/Login.js') }}"></script>

</body>
</html>
