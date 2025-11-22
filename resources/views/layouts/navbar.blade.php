<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/dashboard">
            <i class="bi bi-lightbulb"></i> ModoAhorro
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item">
                        <span class="nav-link">Hola, {{ auth()->user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('equipment.index') }}">Equipos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                    </li>
                @endauth
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Iniciar sesión</a>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
