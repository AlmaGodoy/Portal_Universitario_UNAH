@extends('portal_login')

@section('content')
<style>
    .session-lock-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .session-lock-modal {
        width: 100%;
        max-width: 530px;
        background: rgba(8, 18, 45, 0.78);
        border: 1.5px solid rgba(255, 255, 255, 0.75);
        border-radius: 18px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #ffffff;
        padding: 28px 24px;
    }

    .session-lock-title {
        font-size: 1.8rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 18px;
        color: #ffffff;
    }

    .session-lock-body {
        font-size: 1.05rem;
        line-height: 1.65;
        color: #f3f5fa;
        margin-bottom: 22px;
        text-align: left;
    }

    .session-lock-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .session-btn-danger {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 12px 16px;
        font-weight: 600;
        width: 100%;
        transition: 0.2s ease;
    }

    .session-btn-danger:hover {
        background: #c82333;
        color: #fff;
    }

    .session-btn-secondary {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 10px;
        padding: 12px 16px;
        font-weight: 600;
        width: 100%;
        transition: 0.2s ease;
    }

    .session-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
    }

    .session-lock-note {
        font-size: 0.95rem;
        color: #d8deea;
        margin-top: 8px;
    }
</style>

<div class="auth-container">
    <div class="auth-card login-card">

        @php
            $tipo = $tipo ?? session('login_tipo');
            $titulo = 'INICIAR SESIÓN';
            $sesionDuplicada = session('sesion_duplicada');
        @endphp

        <div class="auth-card-top-login">
            <a href="{{ url('/portal') }}" class="btn btn-outline-light auth-back-btn">
                ← Volver al portal
            </a>
        </div>

        <h3 class="mb-4 text-center login-title">{{ $titulo }}</h3>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>⚠ Atención:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('status') && !$sesionDuplicada)
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if(!$tipo)
            <div class="alert alert-warning">
                Selecciona primero un portal.
            </div>

            <div class="d-grid gap-2">
                <a class="btn btn-primary" href="{{ url('/portal') }}">Ir al Portal</a>
            </div>
        @else
            <form method="POST" action="{{ route('login.tipo.post', ['tipo' => $tipo]) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           required
                           autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>

                <button class="btn btn-primary w-100">Iniciar Sesión</button>

                <div class="text-center mt-3">
                    <a href="{{ route('custom.password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('register.tipo', ['tipo' => $tipo]) }}">
                        ¿No tienes cuenta? <span class="register-word-gold">Regístrate</span>
                    </a>
                </div>
            </form>
        @endif

    </div>
</div>

@if($sesionDuplicada)
    <div class="session-lock-overlay">
        <div class="session-lock-modal">
            <div class="session-lock-title">Sesión activa detectada</div>

            <div class="session-lock-body">
                <p>
                    Esta cuenta ya tiene una sesión iniciada en otro dispositivo o navegador.
                </p>
                <p class="mb-0">
                    ¿Deseas continuar en este dispositivo? Si continúas, la sesión anterior será cerrada.
                </p>
            </div>

            <div class="session-lock-actions">
                <form method="POST" action="{{ route('login.confirmar-nueva-sesion') }}">
                    @csrf
                    <button type="submit" class="session-btn-danger">
                        Sí, continuar en este dispositivo
                    </button>
                </form>

                <form method="POST" action="{{ route('login.cancelar-nueva-sesion') }}">
                    @csrf
                    <button type="submit" class="session-btn-secondary">
                        No, conservar la sesión anterior
                    </button>
                </form>
            </div>

            <div class="session-lock-note">
                Debes seleccionar una opción para continuar.
            </div>
        </div>
    </div>
@endif
@endsection
