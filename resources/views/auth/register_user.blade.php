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

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            {{-- ================= DATOS BÁSICOS ================= --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Documento</label>
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" class="form-control" required>

                    @error('contrasena')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror

                    <small class="text-light">
                        Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
                    </small>
                </div>
            </div>

            {{-- ================= TIPO + ROL ================= --}}
            @php
                $tipoFijo = session('register_tipo'); // estudiante|empleado|null
            @endphp

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de usuario</label>

                    {{-- Si viene desde /register/{tipo}, lo fijamos aquí --}}
                    @if($tipoFijo)
                        <input class="form-control"
                               value="{{ $tipoFijo === 'estudiante' ? 'Estudiante' : 'Empleado' }}"
                               disabled>
                        <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="{{ $tipoFijo }}">
                    @else
                        <select name="tipo_usuario" id="tipo_usuario" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="estudiante" @selected(old('tipo_usuario')==='estudiante')>Estudiante</option>
                            <option value="empleado" @selected(old('tipo_usuario')==='empleado')>Empleado</option>
                        </select>
                    @endif
                </div>

                {{-- ROL AUTOMÁTICO SOLO PARA ESTUDIANTE --}}
                <div class="col-md-6 mb-3" id="rol_auto_box" style="display:none;">
                    <label class="form-label">Rol</label>
                    <input type="text" class="form-control" value="estudiante" disabled>
                    <input type="hidden" name="id_rol" id="id_rol_auto" value="2">
                </div>

                {{-- ROL MANUAL PARA EMPLEADO --}}
                <div class="col-md-6 mb-3" id="rol_manual_box" style="display:none;">
                    <label class="form-label">Rol</label>
                    <select name="id_rol" id="id_rol_manual" class="form-select">
                        <option value="">Seleccione...</option>
                        @foreach($roles as $rol)
                            @if(in_array((int)$rol->id_rol, [4,5])) {{-- 4=coordinador, 5=secretario --}}
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
                        <input name="cod_empleado" id="cod_empleado" class="form-control" value="{{ old('cod_empleado') }}">
                        @error('cod_empleado')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tipo empleado</label>
                        <input name="tipo_empleado" id="tipo_empleado" class="form-control" value="{{ old('tipo_empleado') }}" readonly>
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

    soloNumerosMax(document.getElementById('documento'), 13);
    soloNumerosMax(document.getElementById('numero_cuenta'), 11);

    // ELEMENTOS
    const tipoSel = document.getElementById('tipo_usuario'); // puede ser hidden o select
    const boxDep = document.getElementById('box_departamento');
    const depSel = document.getElementById('id_departamento');

    const bloqueEst = document.getElementById('bloque_estudiante');
    const bloqueEmp = document.getElementById('bloque_empleado');

    const rolAutoBox = document.getElementById('rol_auto_box');
    const rolAutoInput = document.getElementById('id_rol_auto');

    const rolManualBox = document.getElementById('rol_manual_box');
    const rolManualSel = document.getElementById('id_rol_manual');

    const codEmp = document.getElementById('cod_empleado');
    const tipoEmp = document.getElementById('tipo_empleado');

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

            if (oldCarrera && String(oldCarrera) === String(c.id_carrera)) {
                opt.selected = true;
            }

            carSel.appendChild(opt);
        });
    }

    function getTipoActual() {
        return (tipoSel?.value || '').toLowerCase().trim();
    }

    function aplicarUI() {
        const tipo = getTipoActual();

        // ocultar todo
        if (bloqueEst) bloqueEst.style.display = 'none';
        if (bloqueEmp) bloqueEmp.style.display = 'none';
        if (rolAutoBox) rolAutoBox.style.display = 'none';
        if (rolManualBox) rolManualBox.style.display = 'none';
        if (boxDep) boxDep.style.display = 'none';

        // required base
        if (depSel) depSel.required = false;
        if (codEmp) codEmp.required = false;
        if (tipoEmp) tipoEmp.required = false;

        // roles: solo uno activo
        if (rolAutoInput) rolAutoInput.disabled = true;
        if (rolManualSel) rolManualSel.disabled = true;

        // ESTUDIANTE (SIN DEPARTAMENTO)
        if (tipo === 'estudiante') {
            if (rolAutoBox) rolAutoBox.style.display = 'block';
            if (bloqueEst) bloqueEst.style.display = 'block';

            if (rolAutoInput) rolAutoInput.disabled = false;

            // cargar todas las carreras
            cargarCarrerasTodas();
            return;
        }

        // EMPLEADO (CON DEPARTAMENTO)
        if (tipo === 'empleado') {
            if (rolManualBox) rolManualBox.style.display = 'block';
            if (rolManualSel) rolManualSel.disabled = false;

            const rolId = parseInt(rolManualSel?.value || '0', 10);
            const rolTexto = rolManualSel?.selectedOptions?.[0]?.textContent?.toLowerCase()?.trim() || '';

            if (!rolId) return;

            // solo 4 y 5
            if (![4,5].includes(rolId)) {
                if (rolManualSel) rolManualSel.value = '';
                return;
            }

            if (boxDep) boxDep.style.display = 'block';
            if (bloqueEmp) bloqueEmp.style.display = 'block';

            if (depSel) depSel.required = true;
            if (codEmp) codEmp.required = true;
            if (tipoEmp) tipoEmp.required = true;

            if (tipoEmp) tipoEmp.value = rolTexto;
        }
    }

    tipoSel?.addEventListener?.('change', aplicarUI);
    rolManualSel?.addEventListener('change', aplicarUI);

    // init
    aplicarUI();
    if (getTipoActual() === 'estudiante') cargarCarrerasTodas();
});
</script>
@endsection
