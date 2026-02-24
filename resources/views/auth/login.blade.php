@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 520px;">

        @php
            $tipo = $tipo ?? session('login_tipo'); // estudiante | empleado | null
            $titulo = $tipo === 'estudiante'
                        ? 'Iniciar Sesión - Estudiantes'
                        : ($tipo === 'empleado' ? 'Iniciar Sesión - Empleados' : 'Iniciar Sesión');
        @endphp

        <h3 class="mb-4 text-center">{{ $titulo }}</h3>

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

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- ✅ IMPORTANTE: si no hay tipo, mandamos a /portal --}}
        @if(!$tipo)
            <div class="alert alert-warning">
                Selecciona primero un portal.
            </div>
            <div class="d-grid gap-2">
                <a class="btn btn-primary" href="{{ route('portal') }}">Ir al Portal</a>
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
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">Iniciar Sesión</button>

                <div class="text-center mt-3">
                    <a href="{{ route('register.tipo', ['tipo' => $tipo]) }}">
                        ¿No tienes cuenta? Regístrate
                    </a>
                </div>

                <div class="text-center mt-2">
                    <a href="{{ route('portal') }}">← Volver al portal</a>
                </div>
            </form>

        @endif

    </div>
</div>
@endsection
