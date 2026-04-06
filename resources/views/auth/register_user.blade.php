@extends('portal_login')

@section('content')
<div class="auth-container">

    <div class="auth-back-wrap">
        <a href="{{ route('portal') }}" class="btn btn-outline-light auth-back-btn">
            ← Volver al portal
        </a>
    </div>

    <div class="auth-card">

        <h3 class="mb-4 text-center">Registro de usuario</h3>

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
                    <input name="primer_nombre" id="primer_nombre"
                           class="form-control @error('primer_nombre') is-invalid @enderror"
                           value="{{ old('primer_nombre') }}"
                           style="text-transform: capitalize;"
                           required>
                    @error('primer_nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Segundo nombre</label>
                    <input name="segundo_nombre" id="segundo_nombre"
                           class="form-control @error('segundo_nombre') is-invalid @enderror"
                           value="{{ old('segundo_nombre') }}"
                           style="text-transform: capitalize;">
                    @error('segundo_nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Primer apellido</label>
                    <input name="primer_apellido" id="primer_apellido"
                           class="form-control @error('primer_apellido') is-invalid @enderror"
                           value="{{ old('primer_apellido') }}"
                           style="text-transform: capitalize;"
                           required>
                    @error('primer_apellido')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Segundo apellido</label>
                    <input name="segundo_apellido" id="segundo_apellido"
                           class="form-control @error('segundo_apellido') is-invalid @enderror"
                           value="{{ old('segundo_apellido') }}"
                           style="text-transform: capitalize;">
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
                        <input type="email" name="correo" id="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo') }}" required>
                        @error('correo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-light" id="correo_hint">Estudiante: solo correos @unah.hn</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password"
                                   name="contrasena"
                                   id="contrasena"
                                   class="form-control @error('contrasena') is-invalid @enderror"
                                   required
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                    type="button"
                                    id="btn_toggle_pass"
                                    aria-label="Mostrar contraseña">🔓</button>
                        </div>
                        @error('contrasena')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-light">
                            Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de cuenta</label>
                        <input name="numero_cuenta"
                               id="numero_cuenta"
                               class="form-control @error('numero_cuenta') is-invalid @enderror"
                               value="{{ old('numero_cuenta') }}"
                               maxlength="11"
                               inputmode="numeric">
                        @error('numero_cuenta')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
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
                        <input type="email" name="correo" id="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo') }}" required>
                        @error('correo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-light" id="correo_hint">Empleado: solo correos @unah.edu.hn</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password"
                                   name="contrasena"
                                   id="contrasena"
                                   class="form-control @error('contrasena') is-invalid @enderror"
                                   required
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-sm toggle-password-btn"
                                    type="button"
                                    id="btn_toggle_pass"
                                    aria-label="Mostrar contraseña">🔓</button>
                        </div>
                        @error('contrasena')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-light">
                            Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de empleado</label>
                        <select name="id_rol" id="id_rol_manual" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($roles as $rol)
                                @if(in_array((int)$rol->id_rol, [1,4,5]))
                                    <option value="{{ $rol->id_rol }}" @selected(old('id_rol')==$rol->id_rol)>
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
                        <label class="form-label">Número de empleado</label>
                        <input name="cod_empleado" id="cod_empleado"
                               class="form-control @error('cod_empleado') is-invalid @enderror"
                               value="{{ old('cod_empleado') }}">
                        @error('cod_empleado')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Departamento</label>
                        <select name="id_departamento" id="id_departamento" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($departamentos as $d)
                                <option value="{{ $d->id_departamento }}" @selected(old('id_departamento')==$d->id_departamento)>
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
