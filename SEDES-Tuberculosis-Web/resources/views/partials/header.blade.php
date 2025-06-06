<header>
    <nav class="navbar">
        <div class="container-header">
            <!-- Logo -->
            <a href="/">
                <img src="/images/logotuberculosis.png" alt="Logo" class="logo">
            </a>

            <!-- Botón de menú para móviles -->
            <button class="menu-toggle" id="menu-toggle">
                ☰
            </button>

            <!-- Menú de navegación -->
            <div class="menu" id="menu">
                 {{-- <ul>
                <li><a href="/">Inicio</a></li>
                <li><a href="/patients">Pacientes</a></li>
                <li><a href="/patients/create">Registrar Pacientes</a></li>
                <li><a href="/documentacion">Documentacion</a></li>
                <li><a href="/establecimiento/create">Nuevo Establecimiento</a></li>
                <li><a href="{{ route('establecimiento.index') }}">Lista de Establecimientos</a></li>
                <li><a href="/login" class="btn-primary">Login</a></li>
                </ul>  --}}
                 <ul>
                    
                                
                    @php
                        $rol = session('empleado_rol');
                    @endphp
                    @if($rol)
                        <li><a href="/">Inicio</a></li> 
                        <li><a href="/patients">Pacientes</a></li>
                        <li><a href="/patients/create">Registrar Pacientes</a></li> 
                        {{-- @if($rol !== 'ENFERMERO')
                          <li><a href="/documentacion">Documentación</a></li>
                        @endif --}}

                        @if($rol === 'ADMINISTRADOR')
                          <li><a href="/establecimiento/create">Nuevo establecimiento</a></li>
                          <li><a href="{{ route('establecimiento.index') }}">Lista de establecimientos</a></li>
                         @endif
                    @endif   
                    
                    
                    @php $rol = session('empleado_rol'); @endphp

                    @if ($rol)
                        <li>
                            <form action="{{ route('empleado.logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-primary" style="background: none; border: none; padding: 0; color: inherit; cursor: pointer;">
                                    Cerrar sesión
                                </button>
                            </form>
                        </li>
                    @else
                        <li><a href="/login" class="btn-primary">Iniciar sesión</a></li>
                    @endif

                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Script para menú móvil -->
<script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
        document.getElementById('menu').classList.toggle('active');
    });
</script>

<!-- Importar el CSS -->
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
