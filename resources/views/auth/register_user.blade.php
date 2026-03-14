@extends('portal_login')

@section('content')
<div class="auth-container">
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="nombre" id="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-light">Ingrese su nombre completo.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo"
                           class="form-control @error('correo') is-invalid @enderror"
                           value="{{ old('correo') }}" required>
                    @error('correo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-light" id="correo_hint"></small>
                </div>
            </div>

            <div class="row">
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

            @if(!$tipoFijo)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de usuario</label>
                        <select name="tipo_usuario" id="tipo_usuario" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="estudiante" @selected(old('tipo_usuario')==='estudiante')>Estudiante</option>
                            <option value="empleado" @selected(old('tipo_usuario')==='empleado')>Empleado</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3 hidden-field" id="rol_auto_box">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control" value="estudiante" disabled>
                        <input type="hidden" name="id_rol" id="id_rol_auto" value="2">
                    </div>

                    <div class="col-md-6 mb-3 hidden-field" id="rol_manual_box">
                        <label class="form-label">Rol</label>
                        <select name="id_rol" id="id_rol_manual" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($roles as $rol)
                                @if(in_array((int)$rol->id_rol, [4,5]))
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
                </div>
            @else
                <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="{{ $tipoFijo }}">

                @if($tipoFijo === 'empleado')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de usuario</label>
                            <input class="form-control" value="Empleado" disabled>
                        </div>

                        <div class="col-md-6 mb-3" id="rol_manual_box">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="id_rol_manual" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($roles as $rol)
                                    @if(in_array((int)$rol->id_rol, [4,5]))
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
                    </div>
                @else
                    <input type="hidden" name="id_rol" value="2">
                @endif
            @endif

            <div class="row hidden-field" id="box_departamento">
                <div class="col-md-12 mb-3">
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

            <div id="bloque_estudiante" class="border rounded p-3 mb-3 hidden-field">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Número de cuenta</label>
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
                        <label>Carrera</label>
                        <select name="id_carrera" id="id_carrera" class="form-select">
                            <option value="">Seleccione...</option>
                        </select>
                        @error('id_carrera')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div id="bloque_empleado" class="border rounded p-3 mb-3 hidden-field">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Código empleado</label>
                        <input name="cod_empleado" id="cod_empleado"
                               class="form-control @error('cod_empleado') is-invalid @enderror"
                               value="{{ old('cod_empleado') }}">
                        @error('cod_empleado')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tipo empleado</label>
                        <input name="tipo_empleado" id="tipo_empleado"
                               class="form-control @error('tipo_empleado') is-invalid @enderror"
                               value="{{ old('tipo_empleado') }}" readonly>
                        @error('tipo_empleado')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <button class="btn btn-primary w-100">Registrar</button>
        </form>

        <a href="{{ route('portal') }}" class="btn btn-outline-secondary w-100 mt-2">
            Volver al portal
        </a>
    </div>
</div>
@endsection
