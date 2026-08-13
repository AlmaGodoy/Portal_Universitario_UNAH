@extends('portal_login')

@section('content')

<style>
    .password-strength-box {
        margin-top: 8px;
    }

    .password-strength-bar {
        width: 100%;
        height: 8px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        overflow: hidden;
    }

    #password_strength_fill {
        height: 100%;
        width: 0%;
        background: #dc3545;
        border-radius: 20px;
        transition: width 0.25s ease, background 0.25s ease;
    }

    .password-strength-text {
        font-size: 0.85rem;
        font-weight: 700;
        margin-top: 6px;
    }

    #btnRegistrar:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
</style>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <a href="{{ route('portal') }}" class="btn btn-outline-light auth-back-btn">
                ← Volver al portal
            </a>

            <h3 class="auth-card-title mb-0">Registro de usuario</h3>

            <div class="auth-card-header-spacer"></div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first('registro') ?? $errors->first() }}
            </div>
        @endif

        @php
            $tipoFijo = session('register_tipo');
        @endphp

        <form method="POST"
              action="{{ route('register.store') }}"
              autocomplete="off"
              id="registerForm"
              data-carreras='@json($carreras)'
              data-old-carrera="{{ old('id_carrera') ?? '' }}">
            @csrf

            @if($tipoFijo)
                <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="{{ $tipoFijo }}">
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Primer nombre</label>
                    <div class="input-hint-wrap">
                        <input name="primer_nombre" id="primer_nombre"
                               class="form-control input-with-help @error('primer_nombre') is-invalid @enderror"
                               value="{{ old('primer_nombre') }}"
                               style="text-transform: capitalize;"
                               required>
                        <span class="inside-hint field-hint left-hint">PRIMER NOMBRE</span>
                    </div>
                    @error('primer_nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Segundo nombre</label>
                    <div class="input-hint-wrap">
                        <input name="segundo_nombre" id="segundo_nombre"
                               class="form-control input-with-help @error('segundo_nombre') is-invalid @enderror"
                               value="{{ old('segundo_nombre') }}"
                               style="text-transform: capitalize;">
                        <span class="inside-hint field-hint left-hint">SEGUNDO NOMBRE</span>
                    </div>
                    @error('segundo_nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Primer apellido</label>
                    <div class="input-hint-wrap">
                        <input name="primer_apellido" id="primer_apellido"
                               class="form-control input-with-help @error('primer_apellido') is-invalid @enderror"
                               value="{{ old('primer_apellido') }}"
                               style="text-transform: capitalize;"
                               required>
                        <span class="inside-hint field-hint left-hint">PRIMER APELLIDO</span>
                    </div>
                    @error('primer_apellido')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Segundo apellido</label>
                    <div class="input-hint-wrap">
                        <input name="segundo_apellido" id="segundo_apellido"
                               class="form-control input-with-help @error('segundo_apellido') is-invalid @enderror"
                               value="{{ old('segundo_apellido') }}"
                               style="text-transform: capitalize;">
                        <span class="inside-hint field-hint left-hint">SEGUNDO APELLIDO</span>
                    </div>
                    @error('segundo_apellido')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if($tipoFijo === 'estudiante')
                <input type="hidden" name="id_rol" value="2">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo</label>
                        <div class="input-hint-wrap">
                            <input type="email"
                                   name="correo"
                                   id="correo"
                                   class="form-control input-with-hint @error('correo') is-invalid @enderror"
                                   value="{{ old('correo') }}"
                                   required>
                            <span class="inside-hint right-hint" id="correo_hint_inside">@UNAH.HN</span>
                        </div>
                        @error('correo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de cuenta</label>
                        <div class="input-hint-wrap">
                            <input name="numero_cuenta"
                                   id="numero_cuenta"
                                   class="form-control input-with-help @error('numero_cuenta') is-invalid @enderror"
                                   value="{{ old('numero_cuenta') }}"
                                   maxlength="11"
                                   inputmode="numeric">
                            <span class="inside-hint field-hint left-hint">NÚMERO DE CUENTA</span>
                        </div>
                        @error('numero_cuenta')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row password-row">
                    <div class="col-md-6 mb-3 password-field-col">
                        <label class="form-label">Contraseña</label>
                        <div class="input-hint-password-wrap">
                            <div class="input-group">
                                <input type="password"
                                       name="contrasena"
                                       id="contrasena"
                                       class="form-control input-with-inline-hint @error('contrasena') is-invalid @enderror"
                                       required
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                        type="button"
                                        id="btn_toggle_pass"
                                        aria-label="Mostrar contraseña">🔓</button>
                            </div>

                            <span class="inside-hint password-mask left-hint" id="password_hint_inside">**********</span>
                        </div>

                        <div class="password-help-text">
                            CARACTERES, MAYÚSCULAS, MINÚSCULAS, NÚMEROS Y SÍMBOLOS.
                        </div>

                        <div class="password-strength-box">
                            <div class="password-strength-bar">
                                <div id="password_strength_fill"></div>
                            </div>

                            <div id="password_strength_text" class="password-strength-text text-danger">
                                Nivel de seguridad: Débil
                            </div>
                        </div>

                        @error('contrasena')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 confirm-password-field-col">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password"
                                   name="contrasena_confirmation"
                                   id="contrasena_confirmation"
                                   class="form-control @error('contrasena_confirmation') is-invalid @enderror"
                                   required
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                    type="button"
                                    id="btn_toggle_pass2"
                                    aria-label="Mostrar confirmación">🔓</button>
                        </div>

                        <div id="pass_mismatch" class="text-danger mt-1 hidden-field">
                            Las contraseñas no coinciden.
                        </div>

                        @error('contrasena_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Carrera</label>
                        <select name="id_carrera" id="id_carrera" class="form-select">
                            <option value="">Seleccione...</option>
                        </select>
                        @error('id_carrera')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            @elseif($tipoFijo === 'empleado')
                <input type="hidden" name="tipo_empleado" id="tipo_empleado" value="{{ old('tipo_empleado') }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo</label>
                        <div class="input-hint-wrap">
                            <input type="email"
                                   name="correo"
                                   id="correo"
                                   class="form-control input-with-hint @error('correo') is-invalid @enderror"
                                   value="{{ old('correo') }}"
                                   required>
                            <span class="inside-hint right-hint" id="correo_hint_inside">@UNAH.EDU.HN</span>
                        </div>
                        @error('correo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de empleado</label>
                        <div class="input-hint-wrap">
                            <input name="cod_empleado" id="cod_empleado"
                                   class="form-control input-with-help @error('cod_empleado') is-invalid @enderror"
                                   value="{{ old('cod_empleado') }}">
                            <span class="inside-hint field-hint left-hint">NÚMERO DE EMPLEADO</span>
                        </div>
                        @error('cod_empleado')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row password-row">
                    <div class="col-md-6 mb-3 password-field-col">
                        <label class="form-label">Contraseña</label>
                        <div class="input-hint-password-wrap">
                            <div class="input-group">
                                <input type="password"
                                       name="contrasena"
                                       id="contrasena"
                                       class="form-control input-with-inline-hint @error('contrasena') is-invalid @enderror"
                                       required
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                        type="button"
                                        id="btn_toggle_pass"
                                        aria-label="Mostrar contraseña">🔓</button>
                            </div>

                            <span class="inside-hint password-mask left-hint" id="password_hint_inside">**********</span>
                        </div>

                        <div class="password-help-text">
                            CARACTERES, MAYÚSCULAS, MINÚSCULAS, NÚMEROS Y SÍMBOLOS.
                        </div>

                        <div class="password-strength-box">
                            <div class="password-strength-bar">
                                <div id="password_strength_fill"></div>
                            </div>

                            <div id="password_strength_text" class="password-strength-text text-danger">
                                Nivel de seguridad: Débil
                            </div>
                        </div>

                        @error('contrasena')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 confirm-password-field-col">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password"
                                   name="contrasena_confirmation"
                                   id="contrasena_confirmation"
                                   class="form-control @error('contrasena_confirmation') is-invalid @enderror"
                                   required
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                    type="button"
                                    id="btn_toggle_pass2"
                                    aria-label="Mostrar confirmación">🔓</button>
                        </div>

                        <div id="pass_mismatch" class="text-danger mt-1 hidden-field">
                            Las contraseñas no coinciden.
                        </div>

                        @error('contrasena_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de empleado</label>
                        <select name="id_rol" id="id_rol_manual" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($roles as $rol)
                                @if(in_array((int)$rol->id_rol, [1,4,5]))
                                    <option value="{{ $rol->id_rol }}" @selected(old('id_rol') == $rol->id_rol)>
                                        {{ $rol->nombre_rol }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('id_rol')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Departamento</label>
                        <select name="id_departamento" id="id_departamento" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($departamentos as $d)
                                <option value="{{ $d->id_departamento }}" @selected(old('id_departamento') == $d->id_departamento)>
                                    {{ $d->nombre_departamento }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_departamento')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <button class="btn btn-primary w-100" id="btnRegistrar" disabled>
                Registrar
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('contrasena');
    const confirmInput = document.getElementById('contrasena_confirmation');
    const btnRegistrar = document.getElementById('btnRegistrar');
    const strengthFill = document.getElementById('password_strength_fill');
    const strengthText = document.getElementById('password_strength_text');
    const passMismatch = document.getElementById('pass_mismatch');

    function evaluatePassword(password) {
        const checks = {
            length: password.length >= 8,
            upper: /[A-ZÁÉÍÓÚÑ]/.test(password),
            lower: /[a-záéíóúñ]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9]/.test(password),
        };

        const score = Object.values(checks).filter(Boolean).length;

        return { checks, score };
    }

    function updatePasswordStrength() {
        const password = passwordInput ? passwordInput.value : '';
        const confirmation = confirmInput ? confirmInput.value : '';
        const result = evaluatePassword(password);

        let level = 'Débil';
        let width = '25%';
        let color = '#dc3545';
        let textClass = 'text-danger';

        if (result.score >= 3 && result.score < 5) {
            level = 'Medio';
            width = '60%';
            color = '#ffc107';
            textClass = 'text-warning';
        }

        if (result.score === 5) {
            level = 'Alta';
            width = '100%';
            color = '#198754';
            textClass = 'text-success';
        }

        if (strengthFill) {
            strengthFill.style.width = password.length === 0 ? '0%' : width;
            strengthFill.style.background = color;
        }

        if (strengthText) {
            strengthText.classList.remove('text-danger', 'text-warning', 'text-success');
            strengthText.classList.add(textClass);
            strengthText.textContent = 'Nivel de seguridad: ' + level;
        }

        const passwordIsHigh = result.score === 5;
        const passwordsMatch = password !== '' && password === confirmation;

        if (passMismatch) {
            if (confirmation !== '' && !passwordsMatch) {
                passMismatch.classList.remove('hidden-field');
            } else {
                passMismatch.classList.add('hidden-field');
            }
        }

        if (btnRegistrar) {
            btnRegistrar.disabled = !(passwordIsHigh && passwordsMatch);
        }

        return passwordIsHigh && passwordsMatch;
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', updatePasswordStrength);
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', updatePasswordStrength);
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!updatePasswordStrength()) {
                event.preventDefault();
                alert('La contraseña debe tener nivel de seguridad alto y coincidir con la confirmación.');
            }
        });
    }

    updatePasswordStrength();
});
</script>

@endsection
