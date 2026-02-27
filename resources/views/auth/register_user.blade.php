@extends('layouts.app')

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
            $tipoFijo = session('register_tipo'); // estudiante|empleado|null
        @endphp

        <form method="POST" action="{{ route('register.store') }}" autocomplete="off">
            @csrf

            {{-- ================= DATOS BÁSICOS ================= --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-light">Solo letras y espacios.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">DNI</label>
                    <input name="documento"
                           id="documento"
                           class="form-control @error('documento') is-invalid @enderror"
                           value="{{ old('documento') }}"
                           maxlength="13"
                           inputmode="numeric"
                           autocomplete="off"
                           required>
                    @error('documento')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Correo + Contraseña --}}
            <div class="row">
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

                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password"
                               name="contrasena"
                               id="contrasena"
                               class="form-control @error('contrasena') is-invalid @enderror"
                               required
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary btn-sm"
                                style="min-width:44px;"
                                type="button"
                                id="btn_toggle_pass"
                                aria-label="Mostrar contraseña">👁️</button>
                    </div>

                    @error('contrasena')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <small class="text-light">
                        Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
                    </small>
                </div>
            </div>

            {{-- Confirmar contraseña: abajo de contraseña (derecha) --}}
            <div class="row">
                <div class="col-md-6 offset-md-6 mb-3">
                    <label class="form-label">Confirmar contraseña</label>

                    <div class="input-group">
                        <input type="password"
                               name="contrasena_confirmation"
                               id="contrasena_confirmation"
                               class="form-control @error('contrasena_confirmation') is-invalid @enderror"
                               required
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary btn-sm"
                                style="min-width:44px;"
                                type="button"
                                id="btn_toggle_pass2"
                                aria-label="Mostrar confirmación">👁️</button>
                    </div>

                    <div id="pass_mismatch" class="text-danger mt-1" style="display:none;">
                        Las contraseñas no coinciden.
                    </div>

                    @error('contrasena_confirmation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- ================= TIPO + ROL ================= --}}
            {{-- ✅ Caso 1: NO hay tipo fijo -> mostrar selector tipo + UI completa --}}
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

                    {{-- Estudiante: rol automático --}}
                    <div class="col-md-6 mb-3" id="rol_auto_box" style="display:none;">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control" value="estudiante" disabled>
                        <input type="hidden" name="id_rol" id="id_rol_auto" value="2">
                    </div>

                    {{-- Empleado: rol manual --}}
                    <div class="col-md-6 mb-3" id="rol_manual_box" style="display:none;">
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
                {{-- ✅ Caso 2: hay tipo fijo -> mandamos hidden tipo_usuario --}}
                <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="{{ $tipoFijo }}">

                {{-- ✅ Si es EMPLEADO fijo: mostramos Tipo (solo lectura) + Rol (selector) --}}
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
                    {{-- ✅ Si es ESTUDIANTE fijo: NO mostrar tipo/rol, solo mandar rol 2 hidden --}}
                    <input type="hidden" name="id_rol" value="2">
                @endif
            @endif

            {{-- ================= DEPARTAMENTO (SOLO EMPLEADO) ================= --}}
            <div class="row" id="box_departamento" style="display:none;">
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

            {{-- ================= ESTUDIANTE ================= --}}
            <div id="bloque_estudiante" class="border rounded p-3 mb-3" style="display:none;">
                <h6>Datos de estudiante</h6>

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

            {{-- ================= EMPLEADO ================= --}}
            <div id="bloque_empleado" class="border rounded p-3 mb-3" style="display:none;">
                <h6>Datos de empleado</h6>

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

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function soloNumerosMax(input, max) {
        if (!input) return;
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g,'').slice(0,max);
        });
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const t = (e.clipboardData.getData('text') || '')
                .replace(/\D/g,'')
                .slice(0,max);
            this.value = t;
        });
    }

    // Nombre solo letras/espacios
    const nombreInput = document.getElementById('nombre');
    if (nombreInput) {
        const re = /[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g;
        nombreInput.addEventListener('input', function () {
            this.value = this.value.replace(re, '');
        });
        nombreInput.addEventListener('paste', function (e) {
            e.preventDefault();
            this.value = (e.clipboardData.getData('text') || '').replace(re, '');
        });
    }

    soloNumerosMax(document.getElementById('documento'), 13);
    soloNumerosMax(document.getElementById('numero_cuenta'), 11);

    // Mostrar / ocultar contraseñas
    function togglePassword(inputId, btnId) {
        const inp = document.getElementById(inputId);
        const btn = document.getElementById(btnId);
        if (!inp || !btn) return;

        btn.addEventListener('click', () => {
            const isPass = inp.type === 'password';
            inp.type = isPass ? 'text' : 'password';
            btn.textContent = isPass ? '🔒' : '🔓';
        });
    }
    togglePassword('contrasena', 'btn_toggle_pass');
    togglePassword('contrasena_confirmation', 'btn_toggle_pass2');

    // Validación en vivo: no mostrar “no coinciden” si está vacío
    const pass1 = document.getElementById('contrasena');
    const pass2 = document.getElementById('contrasena_confirmation');
    const mismatch = document.getElementById('pass_mismatch');

    function validarMatch() {
        if (!pass1 || !pass2 || !mismatch) return;
        const v1 = (pass1.value || '').trim();
        const v2 = (pass2.value || '').trim();
        mismatch.style.display = (v1 && v2 && v1 !== v2) ? 'block' : 'none';
    }
    pass1?.addEventListener('input', validarMatch);
    pass2?.addEventListener('input', validarMatch);
    setTimeout(() => { if (mismatch) mismatch.style.display = 'none'; validarMatch(); }, 50);

    // UI por tipo
    const tipoSel = document.getElementById('tipo_usuario'); // hidden o select
    const boxDep = document.getElementById('box_departamento');
    const depSel = document.getElementById('id_departamento');

    const bloqueEst = document.getElementById('bloque_estudiante');
    const bloqueEmp = document.getElementById('bloque_empleado');

    const rolAutoBox = document.getElementById('rol_auto_box');
    const rolAutoInput = document.getElementById('id_rol_auto');

    const rolManualSel = document.getElementById('id_rol_manual');

    const codEmp = document.getElementById('cod_empleado');
    const tipoEmp = document.getElementById('tipo_empleado');

    const correoHint = document.getElementById('correo_hint');
    const correoInput = document.getElementById('correo');

    // carreras
    const carSel = document.getElementById('id_carrera');
    const carreras = @json($carreras);
    const oldCarrera = "{{ old('id_carrera') ?? '' }}";

    function cargarCarrerasTodas() {
        if (!carSel) return;
        carSel.innerHTML = '<option value="">Seleccione...</option>';
        carreras.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id_carrera;
            opt.textContent = c.nombre_carrera;
            if (oldCarrera && String(oldCarrera) === String(c.id_carrera)) opt.selected = true;
            carSel.appendChild(opt);
        });
    }

    function getTipoActual() {
        return (tipoSel?.value || '').toLowerCase().trim();
    }

    function setCorreoRegla(tipo) {
        if (!correoHint) return;
        if (tipo === 'estudiante') correoHint.textContent = 'Estudiante: solo correos @unah.hn';
        else if (tipo === 'empleado') correoHint.textContent = 'Empleado: solo correos @unah.edu.hn';
        else correoHint.textContent = '';
    }

    function validarCorreoDominio(tipo) {
        if (!correoInput) return true;
        const v = (correoInput.value || '').trim().toLowerCase();
        if (!v) return true;
        if (tipo === 'estudiante') return v.endsWith('@unah.hn');
        if (tipo === 'empleado') return v.endsWith('@unah.edu.hn');
        return true;
    }

    function aplicarUI() {
        const tipo = getTipoActual();

        // ocultar bloques
        if (bloqueEst) bloqueEst.style.display = 'none';
        if (bloqueEmp) bloqueEmp.style.display = 'none';
        if (rolAutoBox) rolAutoBox.style.display = 'none';
        if (boxDep) boxDep.style.display = 'none';

        // required reset
        if (depSel) depSel.required = false;
        if (codEmp) codEmp.required = false;
        if (tipoEmp) tipoEmp.required = false;

        setCorreoRegla(tipo);

        // estudiante
        if (tipo === 'estudiante') {
            if (rolAutoBox) rolAutoBox.style.display = 'none'; // no lo mostramos
            if (bloqueEst) bloqueEst.style.display = 'block';
            cargarCarrerasTodas();
            return;
        }

        // empleado
        if (tipo === 'empleado') {
            if (boxDep) boxDep.style.display = 'block';
            if (bloqueEmp) bloqueEmp.style.display = 'block';

            if (depSel) depSel.required = true;
            if (codEmp) codEmp.required = true;
            if (tipoEmp) tipoEmp.required = true;

            const rolTexto = rolManualSel?.selectedOptions?.[0]?.textContent?.toLowerCase()?.trim() || '';
            if (tipoEmp) tipoEmp.value = rolTexto;
        }
    }

    rolManualSel?.addEventListener('change', aplicarUI);
    tipoSel?.addEventListener?.('change', aplicarUI);

    correoInput?.addEventListener('input', () => {
        const tipo = getTipoActual();
        correoInput.setCustomValidity(validarCorreoDominio(tipo) ? '' : 'Dominio inválido para este tipo.');
    });

    // init
    aplicarUI();
});
</script>
@endsection
