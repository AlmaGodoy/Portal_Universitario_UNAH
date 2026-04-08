@extends('portal_login')

@section('content')
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

            <button class="btn btn-primary w-100">Registrar</button>
        </form>
    </div>
</div>
@endsection
