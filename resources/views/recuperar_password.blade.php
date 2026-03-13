@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 520px;">

        <h3 class="mb-4 text-center">Recuperar contraseña</h3>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('custom.password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Correo institucional</label>
                <input type="email"
                       name="correo"
                       class="form-control"
                       value="{{ old('correo') }}"
                       required
                       autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Enviar enlace de recuperación
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('portal') }}">← Volver al portal</a>
            </div>
        </form>

    </div>
</div>
@endsection
