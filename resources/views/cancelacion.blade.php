@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional')

@section('content')
    @vite(['resources/css/cancelacion.css', 'resources/js/cancelacion.js'])

    <div class="puma-container">

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

        {{-- PANTALLA 1 --}}
        <div id="step-intro"
             class="puma-card animate-in"
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
                    <p>
                        La <strong>Cancelación Excepcional</strong> no es un proceso automático.
                        Para que su solicitud sea válida, debe cumplir con los requisitos establecidos
                        en las Normas Académicas.
                    </p>

                    <div class="puma-info-grid">

                        <div class="info-item">
                            <div class="info-icon">📄</div>
                            <h6>Documentación Digital</h6>
                            <p>
                                Adjunte constancias médicas, laborales o de calamidad
                                en formato PDF o imagen.
                            </p>
                        </div>

                        <div class="info-item info-item--legal">
                            <div class="info-icon">⚖️</div>

                            <div class="info-item__top">
                                <h6>Sustento Legal</h6>

                                <button type="button"
                                        class="info-pop-btn"
                                        onclick="toggleLegalPopover(event)"
                                        aria-label="Ver artículos legales">
                                    <i class="fas fa-circle-info"></i>
                                </button>
                            </div>

                            <p>
                                Se evaluará bajo los Artículos 222, 223 y 224 de la UNAH.
                            </p>

                            <div id="legalPopover" class="info-popover">
                                <div class="info-popover__arrow"></div>

                                <div class="info-popover__header">
                                    <i class="fas fa-scale-balanced"></i>
                                    <span>Base normativa</span>
                                </div>

                                <div class="info-popover__body">
                                    <div class="info-popover__item">
                                        <strong>Artículo 222</strong>
                                        <p>
                                            Regula la cancelación excepcional cuando existan
                                            causas justificadas debidamente acreditadas por el estudiante.
                                        </p>
                                    </div>

                                    <div class="info-popover__item">
                                        <strong>Artículo 223</strong>
                                        <p>
                                            Establece la revisión de la documentación presentada
                                            y la valoración académica correspondiente.
                                        </p>
                                    </div>

                                    <div class="info-popover__item">
                                        <strong>Artículo 224</strong>
                                        <p>
                                            Define la resolución del trámite conforme a las normas
                                            académicas vigentes de la UNAH.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">⏱️</div>
                            <h6>Tiempo de Respuesta</h6>
                            <p>
                                El dictamen se emitirá en un máximo de 3 días hábiles.
                            </p>
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

        {{-- PANTALLA 2 --}}
        <div id="step-form"
             class="animate-in"
             style="{{ session('show_form') || $errors->any() || old('motivo_id') ? '' : 'display:none;' }}">

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

                        <div class="puma-section">
                            <span>Identificación del Estudiante</span>
                        </div>

                        <div class="puma-grid">
                            <div class="puma-field">
                                <label class="puma-label">Nombre del Estudiante</label>
                                <input type="text"
                                       class="puma-input readonly"
                                       value="{{ auth()->user()?->persona?->nombre_persona ?? 'Usuario' }}"
                                       readonly>
                            </div>

                            <div class="puma-field">
                                <label class="puma-label">Número de Cuenta</label>
                                <input type="text"
                                       class="puma-input readonly"
                                       value="{{ auth()->user()?->estudiante?->numero_cuenta ?? '20XXXXXXXXX' }}"
                                       readonly>
                            </div>
                        </div>

                        <div class="puma-section">
                            <span>Detalles de la Solicitud</span>
                        </div>

                        <div class="puma-grid">
                            <div class="puma-field">
                                <label class="puma-label">Tipo de Solicitud <span class="req">*</span></label>
                                <select name="tipo" id="tipo" class="puma-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Parcial" {{ old('tipo') == 'Parcial' ? 'selected' : '' }}>
                                        Cancelación Parcial
                                    </option>
                                    <option value="Total" {{ old('tipo') == 'Total' ? 'selected' : '' }}>
                                        Cancelación Total del Período
                                    </option>
                                </select>
                                @error('tipo')
                                    <span class="puma-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="puma-field">
                                <label class="puma-label">Causa Justificada <span class="req">*</span></label>
                                <select name="motivo_id" class="puma-select" required>
                                    <option value="">Seleccione el motivo...</option>
                                    <option value="1" {{ old('motivo_id') == '1' ? 'selected' : '' }}>
                                        Enfermedad o Accidente
                                    </option>
                                    <option value="2" {{ old('motivo_id') == '2' ? 'selected' : '' }}>
                                        Calamidad Doméstica
                                    </option>
                                    <option value="3" {{ old('motivo_id') == '3' ? 'selected' : '' }}>
                                        Problemas Laborales / Cambio de Horario
                                    </option>
                                </select>
                                @error('motivo_id')
                                    <span class="puma-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="puma-tipo-info" id="tipoInfoBox">
                            <div class="puma-tipo-info__header">
                                <i class="fas fa-circle-info"></i>
                                <span>Aclaración sobre el tipo de solicitud</span>
                            </div>

                            <div class="puma-tipo-info__grid">
                                <div class="puma-tipo-info__item" data-tipo-card="Parcial">
                                    <div class="puma-tipo-info__icon">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div class="puma-tipo-info__content">
                                        <strong>Cancelación Parcial</strong>
                                        <p>
                                            Úsela cuando desea cancelar únicamente una o varias asignaturas
                                            y continuar con las demás clases matriculadas.
                                        </p>
                                    </div>
                                </div>

                                <div class="puma-tipo-info__item" data-tipo-card="Total">
                                    <div class="puma-tipo-info__icon">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                    <div class="puma-tipo-info__content">
                                        <strong>Cancelación Total</strong>
                                        <p>
                                            Úsela cuando desea cancelar toda la carga académica
                                            inscrita en el período actual.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="puma-field puma-field--full">
                            <label class="puma-label">
                                Exposición de Motivos <span class="req">*</span>
                            </label>

                            <textarea name="justificacion"
                                      class="puma-textarea"
                                      rows="5"
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
                            Continuar al Paso 2
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection