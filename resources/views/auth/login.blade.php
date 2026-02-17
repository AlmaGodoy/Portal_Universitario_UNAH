@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 520px;">

        <h3 class="mb-4 text-center">Iniciar Sesión</h3>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Iniciar Sesión</button>

            <div class="text-center mt-3">
                <a href="{{ route('register') }}">¿No tienes cuenta? Regístrate</a>
            </div>
        </form>

    </div>
</div>
@endsection
