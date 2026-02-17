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

            {{-- DATOS BÁSICOS --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Documento</label>
                    <input name="documento" class="form-control" value="{{ old('documento') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                     <label class="form-label">Contraseña</label>

                     <input type="password"
                     name="contrasena"
                     class="form-control"
                    required>

        {{-- error de validación --}}
                    @error('contrasena')
                     <div class="text-danger mt-1">
                     {{ $message }}
                     </div>
              @enderror

        {{-- ayuda visual --}}
                    <small class="text-light">
                  Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.
                 </small>
            </div>

            </div>

            {{-- TIPO + ROL --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de usuario</label>
                    <select name="tipo_usuario" id="tipo_usuario" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="estudiante" @selected(old('tipo_usuario')==='estudiante')>Estudiante</option>
                        <option value="empleado" @selected(old('tipo_usuario')==='empleado')>Empleado</option>
                    </select>
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
                            @if(strtolower($rol->nombre_rol) !== 'estudiante')
                                <option value="{{ $rol->id_rol }}" @selected(old('id_rol')==$rol->id_rol)>
                                    {{ $rol->nombre_rol }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- DEPARTAMENTO --}}
            <div class="row" id="box_departamento">
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
                </div>
            </div>

            {{-- BLOQUE ESTUDIANTE --}}
            <div id="bloque_estudiante" class="border rounded p-3 mb-3" style="display:none; border-color: rgba(255,255,255,.2)!important;">
                <h6 class="mb-3">Datos de estudiante</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Número de cuenta</label>
                        <input name="numero_cuenta" class="form-control" value="{{ old('numero_cuenta') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Carrera</label>
                        <select name="id_carrera" id="id_carrera" class="form-select">
                            <option value="">Seleccione departamento primero...</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- BLOQUE EMPLEADO --}}
            <div id="bloque_empleado" class="border rounded p-3 mb-3" style="display:none; border-color: rgba(255,255,255,.2)!important;">
                <h6 class="mb-3">Datos de empleado</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Código empleado</label>
                        <input name="cod_empleado" class="form-control" value="{{ old('cod_empleado') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tipo empleado</label>
                        <input name="tipo_empleado" class="form-control" value="{{ old('tipo_empleado') }}">
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary w-100">Registrar</button>
                <div class="text-center mt-3">
                    <a href="/login">Ya tengo cuenta</a>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tipoSel = document.getElementById('tipo_usuario');

    const bloqueEst = document.getElementById('bloque_estudiante');
    const bloqueEmp = document.getElementById('bloque_empleado');

    const depSel = document.getElementById('id_departamento');
    const carSel = document.getElementById('id_carrera');

    const rolAutoBox = document.getElementById('rol_auto_box');
    const rolManualBox = document.getElementById('rol_manual_box');
    const rolManualSel = document.getElementById('id_rol_manual');

    const carreras = @json($carreras);
    const oldCarrera = "{{ old('id_carrera') ?? '' }}";

    function cargarCarreras() {
        if (!carSel) return;

        const depId = parseInt(depSel.value || '0', 10);
        carSel.innerHTML = '';

        if (!depId) {
            carSel.innerHTML = '<option value="">Seleccione departamento primero...</option>';
            return;
        }

        const filtradas = carreras.filter(c => parseInt(c.id_departamento, 10) === depId);

        if (filtradas.length === 0) {
            carSel.innerHTML = '<option value="">No hay carreras para este departamento</option>';
            return;
        }

        carSel.innerHTML = '<option value="">Seleccione...</option>';

        filtradas.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id_carrera;
            opt.textContent = c.nombre_carrera;

            if (oldCarrera && String(oldCarrera) === String(c.id_carrera)) {
                opt.selected = true;
            }

            carSel.appendChild(opt);
        });
    }

    function renderTipo() {
        const tipo = tipoSel.value;

        // ocultar todo
        bloqueEst.style.display = 'none';
        bloqueEmp.style.display = 'none';
        rolAutoBox.style.display = 'none';
        rolManualBox.style.display = 'none';

        if (tipo === 'estudiante') {
            bloqueEst.style.display = 'block';
            rolAutoBox.style.display = 'block';
            cargarCarreras();
        } else if (tipo === 'empleado') {
            bloqueEmp.style.display = 'block';
            rolManualBox.style.display = 'block';

            // por defecto id_rol=1 (cambia si tu rol empleado es otro)
            if (rolManualSel && !rolManualSel.value) {
                rolManualSel.value = "1";
            }
        }
    }

    tipoSel.addEventListener('change', function () {
        renderTipo();
        if (tipoSel.value === 'estudiante') cargarCarreras();
    });

    depSel.addEventListener('change', () => {
        if (tipoSel.value === 'estudiante') cargarCarreras();
    });

    // init
    renderTipo();
});
</script>
@endsection
