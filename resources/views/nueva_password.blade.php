@extends('portal_login')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 520px;">

        <h3 class="mb-4 text-center">Restablecer contraseña</h3>

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

        <form method="POST" action="{{ route('custom.password.update') }}" id="resetPasswordForm">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <div class="input-group">
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control"
                           required
                           autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                            type="button"
                            id="btn_toggle_pass"
                            aria-label="Mostrar contraseña">👁️</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <div class="input-group">
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control"
                           required
                           autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                            type="button"
                            id="btn_toggle_pass2"
                            aria-label="Mostrar confirmación">👁️</button>
                </div>

                <div id="pass_mismatch" class="text-danger mt-1 hidden-field">
                    Las contraseñas no coinciden.
                </div>
            </div>

            <small class="text-light d-block mb-3">
                Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
            </small>

            <button type="submit" class="btn btn-primary w-100">
                Restablecer contraseña
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('portal') }}">← Volver al portal</a>
            </div>
        </form>

    </div>
</div>
@endsection
