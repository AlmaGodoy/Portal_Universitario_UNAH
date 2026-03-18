@extends('portal_login')

@section('content')
@vite(['resources/css/cancelacion.css', 'resources/js/cancelacion.js'])

<div class="puma-page">

    {{-- ─── Topbar ─── --}}
    <nav class="puma-topbar">
        <a href="/" class="puma-logo-wrap">
            <div class="nav-bee-circle">🐝</div>
            <div class="puma-logo-text">
                <span class="brand">Puma<span>Gestión</span></span>
                <span class="sub">FCEAC · UNAH</span>
            </div>
        </a>
    </nav>

    <div class="puma-container">

        {{-- ─── Mensaje de error global ─── --}}
        @if ($errors->any())
            <div class="puma-alert puma-alert--error">
                <span>⚠️</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ─── PANTALLA 1: INTRODUCCIÓN ─── --}}
        {{-- Se oculta si hay errores o si se envió el form (show_form = true) --}}
        <div id="step-intro" class="puma-card animate-in"
             style="{{ session('show_form') || $errors->any() || old('motivo_id') ? 'display:none;' : '' }}">
            <div class="puma-card__header">
                <div class="puma-card__header-icon">📢</div>
                <div>
                    <h4 class="puma-card__header-title">Información Importante</h4>
                    <p class="puma-card__header-sub">Lea antes de iniciar su solicitud</p>
                </div>
            </div>
            <div class="puma-card__body">
                <div class="puma-notice-box">
                    <p>La <strong>Cancelación Excepcional</strong> no es un proceso automático. Para que su solicitud sea válida, debe cumplir con los requisitos establecidos en las Normas Académicas.</p>
                    <div class="puma-info-grid">
                        <div class="info-item">
                            <span class="info-icon">📑</span>
                            <h6>Documentación Digital</h6>
                            <p>Deberá adjuntar constancias médicas, laborales o de calamidad en formato PDF o imagen.</p>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">⚖️</span>
                            <h6>Sustento Legal</h6>
                            <p>Su caso será analizado bajo los Artículos 222, 223 y 224 de la UNAH.</p>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">⏱️</span>
                            <h6>Tiempo de Respuesta</h6>
                            <p>El dictamen final se emitirá en un máximo de 3 días hábiles.</p>
                        </div>
                    </div>
                </div>
                <div class="puma-actions-center">
                    <button type="button" class="puma-btn" onclick="startProcess()">
                        Entendido, iniciar solicitud
                    </button>
                </div>
            </div>
        </div>

        {{-- ─── PANTALLA 2: FORMULARIO PASO 1 ─── --}}
        {{-- Visible si hay errores, old input, o show_form --}}
        <div id="step-form" class="animate-in"
             style="{{ session('show_form') || $errors->any() || old('motivo_id') ? '' : 'display:none;' }}">

            {{-- Stepper --}}
            <div class="puma-steps">
                <div class="puma-step puma-step--active">
                    <div class="puma-step__circle">1</div>
                    <div class="puma-step__label">Datos y Motivo</div>
                </div>
                <div class="puma-step-connector"></div>
                <div class="puma-step">
                    <div class="puma-step__circle">2</div>
                    <div class="puma-step__label">Documentación</div>
                </div>
                <div class="puma-step-connector"></div>
                <div class="puma-step">
                    <div class="puma-step__circle">3</div>
                    <div class="puma-step__label">Finalizar</div>
                </div>
            </div>

            <div class="puma-card">
                <div class="puma-card__body">
                    <form action="{{ route('cancelacion.subir') }}" method="POST">
                        @csrf

                        <div class="puma-section"><span>Identificación (Automática)</span></div>
                        <div class="puma-grid">
                            <div class="puma-field">
                                <label class="puma-label">Nombre del Estudiante</label>
                                <input type="text" class="puma-input readonly"
                                       value="{{ auth()->user()->name ?? 'Alma Patricia Godoy' }}" readonly>
                            </div>
                            <div class="puma-field">
                                <label class="puma-label">Número de Cuenta</label>
                                <input type="text" class="puma-input readonly"
                                       value="{{ auth()->user()->numero_cuenta ?? '20XXXXXXXXX' }}" readonly>
                            </div>
                        </div>

                        <div class="puma-section" style="margin-top:25px">
                            <span>Detalles de la Solicitud (FCEAC)</span>
                        </div>
                        <div class="puma-grid">
                            <div class="puma-field">
                                <label class="puma-label">Tipo de Solicitud <span class="req">*</span></label>
                                <select name="tipo" class="puma-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Parcial"  {{ old('tipo') == 'Parcial'  ? 'selected' : '' }}>Cancelación Parcial</option>
                                    <option value="Total"    {{ old('tipo') == 'Total'    ? 'selected' : '' }}>Cancelación Total del Período</option>
                                </select>
                                @error('tipo')
                                    <span class="puma-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="puma-field">
                                <label class="puma-label">Causa Justificada <span class="req">*</span></label>
                                <select name="motivo_id" class="puma-select" required>
                                    <option value="">Seleccione el motivo...</option>
                                    <option value="1" {{ old('motivo_id') == '1' ? 'selected' : '' }}>Enfermedad o Accidente</option>
                                    <option value="2" {{ old('motivo_id') == '2' ? 'selected' : '' }}>Calamidad Doméstica</option>
                                    <option value="3" {{ old('motivo_id') == '3' ? 'selected' : '' }}>Problemas Laborales / Cambio de Horario</option>
                                    <option value="4" {{ old('motivo_id') == '4' ? 'selected' : '' }}>Separación de la UNAH</option>
                                </select>
                                @error('motivo_id')
                                    <span class="puma-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="puma-field" style="margin-top:20px">
                            <label class="puma-label">
                                Exposición de Motivos (Justificación Breve) <span class="req">*</span>
                            </label>
                            <textarea name="justificacion" class="puma-textarea" rows="4"
                                      placeholder="Resuma brevemente los hechos que motivan su solicitud..."
                                      required>{{ old('justificacion') }}</textarea>
                            <div class="puma-helper">
                                Explique la razón principal. En el siguiente paso podrá adjuntar sus documentos de respaldo.
                            </div>
                            @error('justificacion')
                                <span class="puma-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="puma-btn-full">
                            Continuar al Paso 2 (Subir Requisitos) →
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /puma-container --}}
</div>{{-- /puma-page --}}

<script>
    function startProcess() {
        document.getElementById('step-intro').style.display = 'none';
        document.getElementById('step-form').style.display  = 'block';
    }
</script>

@endsection
