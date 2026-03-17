@extends('portal_login')

@section('content')
@vite(['resources/css/cancelacion.css', 'resources/js/cancelacion.js'])

<div class="puma-page">

    {{-- ─── Topbar ──────────────────────────────── --}}
    <nav class="puma-topbar">
    <a href="/" class="puma-logo-wrap">
        {{-- AJUSTE: Reemplazamos <img> por este div con el emoji y estilo --}}
        <div class="nav-bee-circle">
            🐝
        </div>

        <div class="puma-logo-text">
            <span class="brand">Puma<span>Gestión</span></span>
            <span class="sub">FCEAC · UNAH</span>
        </div>
    </a>

        <nav class="puma-breadcrumb">
            <a href="/">Inicio</a>
            <span class="sep">›</span>
            <a href="#">Gestión Académica</a>
            <span class="sep">›</span>
            <span class="current">Cancelación Digital</span>
        </nav>
    </nav>

    {{-- ─── Main content ────────────────────────── --}}
    <div class="puma-container">

        {{-- Paso indicador --}}
        <div class="puma-steps">
            <div class="puma-step puma-step--active">
                <div class="puma-step__circle">1</div>
                <div class="puma-step__label">Información</div>
            </div>
            <div class="puma-step-connector"></div>

            <div class="puma-step puma-step--inactive">
                <div class="puma-step__circle">2</div>
                <div class="puma-step__label">Requisitos</div>
            </div>
            <div class="puma-step-connector"></div>

            <div class="puma-step puma-step--inactive">
                <div class="puma-step__circle">3</div>
                <div class="puma-step__label">Asignaturas</div>
            </div>
            <div class="puma-step-connector"></div>

            <div class="puma-step puma-step--inactive">
                <div class="puma-step__circle">4</div>
                <div class="puma-step__label">Confirmación</div>
            </div>
        </div>

        {{-- Card principal --}}
        <div class="puma-card" id="cardPaso1">

            {{-- Header --}}
            <div class="puma-card__header">
                <div class="puma-card__header-icon">📋</div>
                <div>
                    <h4 class="puma-card__header-title">Solicitud de Cancelación Digital</h4>
                    <p class="puma-card__header-sub">Gestión Académica FCEAC</p>
                </div>
                <span class="puma-card__header-badge">Art. 222–224</span>
            </div>

            {{-- Body --}}
            <div class="puma-card__body">

                {{-- Alerta informativa --}}
                <div class="puma-alert">
                    <span class="puma-alert__icon">⚠️</span>
                    <p class="puma-alert__text">
                        <strong>Artículos 222-224:</strong>
                        Su solicitud será evaluada por la Coordinación en un máximo de
                        <strong>3 días hábiles</strong> a partir de su envío.
                    </p>
                </div>

                <form id="formCancelacion" action="/cancelacion-excepcional" method="POST">
                    @csrf

                    {{-- ── Datos del solicitante ──────────── --}}
                    <div class="puma-section"><span>Datos del Solicitante</span></div>

                    <div class="puma-grid">
                        <div>
                            <label class="puma-label">Nombre Completo</label>
                            <div class="puma-input-wrap">
                                <span class="puma-input-icon">👤</span>
                                <input type="text"
                                       class="puma-input"
                                       value="{{ auth()->user()->name ?? 'Alma Patricia Godoy Cruz' }}"
                                       readonly>
                            </div>
                        </div>

                        <div>
                            <label class="puma-label">Correo Institucional</label>
                            <div class="puma-input-wrap">
                                <span class="puma-input-icon">✉️</span>
                                <input type="email"
                                       class="puma-input"
                                       value="{{ auth()->user()->email ?? 'alma.godoy@unah.hn' }}"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    {{-- ── Detalles de la solicitud ───────── --}}
                    <div class="puma-section" style="margin-top:24px"><span>Detalles de la Solicitud</span></div>

                    <div class="puma-grid">
                        <div>
                            <label class="puma-label">
                                Prioridad <span class="req">*</span>
                            </label>
                            <div class="puma-input-wrap">
                                <span class="puma-input-icon">🚦</span>
                                <select name="prioridad" id="prioridad" class="puma-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Alta"  {{ old('prioridad') === 'Alta'  ? 'selected' : '' }}>🔴 Alta (Emergencia)</option>
                                    <option value="Media" {{ old('prioridad') === 'Media' ? 'selected' : '' }}>🟡 Media</option>
                                    <option value="Baja"  {{ old('prioridad') === 'Baja'  ? 'selected' : '' }}>🟢 Baja</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="puma-label">
                                Tipo de Cancelación <span class="req">*</span>
                            </label>
                            <div class="puma-input-wrap">
                                <span class="puma-input-icon">📂</span>
                                <select name="tipo_cancelacion" id="tipo_cancelacion" class="puma-select" required>
                                    <option value=" Parcial" {{ old('tipo_cancelacion', 'Parcial') === 'Parcial' ? 'selected' : '' }}>Parcial (Asignaturas)</option>
                                    <option value="Total"   {{ old('tipo_cancelacion') === 'Total' ? 'selected' : '' }}>Total (Período)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── Justificación ───────────────────── --}}
                    <div class="puma-section" style="margin-top:24px"><span>Justificación</span></div>

                    <div>
                        <label class="puma-label">
                            Explicación de los Hechos
                            <span class="req">*</span>
                            <span class="art">(Art. 224)</span>
                        </label>
                        <div class="puma-input-wrap">
                            <span class="puma-input-icon puma-input-icon--top">📝</span>
                            <textarea name="observacion_inicial"
                                      id="observacion"
                                      class="puma-textarea"
                                      rows="5"
                                      maxlength="500"
                                      placeholder="Describa el motivo de la solicitud con el mayor detalle posible..."
                                      required>{{ old('observacion_inicial') }}</textarea>
                        </div>
                        <div class="puma-char-count" id="charCount">0 / 500</div>
                    </div>

                    {{-- ── Submit ──────────────────────────── --}}
                    <button type="submit" id="btnSubmit" class="puma-btn">
                        <div class="puma-spinner"></div>
                        <span class="puma-btn__text">Continuar a Requisitos</span>
                        <span class="puma-btn__arrow">›</span>
                    </button>

                </form>

                <p class="puma-footer-note">🔒 Sus datos están protegidos · Solicitud cifrada con SSL</p>

            </div>{{-- /.puma-card__body --}}
        </div>{{-- /.puma-card --}}

    </div>{{-- /.puma-container --}}
</div>{{-- /.puma-page --}}

@endsection
