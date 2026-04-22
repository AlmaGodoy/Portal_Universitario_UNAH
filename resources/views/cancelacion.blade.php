@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional')

@section('content')
    @vite(['resources/css/cancelacion.css', 'resources/js/cancelacion.js'])

    <div class="puma-container">

        <style>
            .cancel-type-box{
                margin-top: 12px;
                border: 1px solid #dbe5f2;
                border-radius: 18px;
                background: linear-gradient(180deg, #fbfdff 0%, #f7fbff 100%);
                padding: 18px;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            }

            .cancel-type-grid{
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
                margin-top: 14px;
            }

            .cancel-type-card{
                position: relative;
                border: 1px solid #dbe5f2;
                border-radius: 16px;
                background: #ffffff;
                padding: 16px 16px 14px;
                transition: all .22s ease;
                min-height: 140px;
            }

            .cancel-type-card:hover{
                transform: translateY(-2px);
                border-color: #9cb7e5;
                box-shadow: 0 12px 24px rgba(23, 74, 150, 0.08);
            }

            .cancel-type-radio{
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .cancel-type-label{
                display: block;
                cursor: pointer;
                margin: 0;
            }

            .cancel-type-radio:checked + .cancel-type-label .cancel-type-card{
                border-color: #2458a6;
                box-shadow: 0 0 0 3px rgba(36, 88, 166, 0.12);
                background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            }

            .cancel-type-head{
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 10px;
            }

            .cancel-type-icon{
                width: 42px;
                height: 42px;
                min-width: 42px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                background: linear-gradient(135deg, #eef4ff 0%, #fff4d1 100%);
                color: #173b7a;
                box-shadow: 0 6px 14px rgba(23, 74, 150, 0.10);
            }

            .cancel-type-title{
                margin: 0;
                font-size: 1rem;
                font-weight: 800;
                color: #173b7a;
            }

            .cancel-type-badge{
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 2px;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: .76rem;
                font-weight: 700;
                background: #f4f8ff;
                color: #2458a6;
                border: 1px solid #d8e5fb;
            }

            .cancel-type-text{
                margin: 0;
                color: #5f7190;
                font-size: .92rem;
                line-height: 1.6;
            }

            .cancel-type-note{
                margin-top: 14px;
                padding: 12px 14px;
                border-radius: 14px;
                background: #fff9e8;
                border: 1px solid #f3d57a;
                color: #7a5f00;
                font-size: .88rem;
                line-height: 1.55;
                font-weight: 700;
            }

            @media (max-width: 767.98px) {
                .cancel-type-grid{
                    grid-template-columns: 1fr;
                }

                .cancel-type-card{
                    min-height: auto;
                }
            }
        </style>

        @if (session('success'))
            <div class="puma-alert puma-alert--success">
                <span>✅</span>
                <div>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

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
                    <button
                        type="button"
                        class="puma-btn"
                        onclick="document.getElementById('step-intro').style.display='none'; document.getElementById('step-form').style.display='block'; document.getElementById('step-form').scrollIntoView({behavior:'smooth', block:'start'});">
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
                            <div class="puma-field puma-field--full">
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

                            <div class="puma-field puma-field--full">
                                <label class="puma-label">Tipo de cancelación <span class="req">*</span></label>

                                <div class="cancel-type-box">
                                    <select name="tipo_cancelacion" class="puma-select" required>
                                        <option value="">Seleccione el tipo...</option>
                                        <option value="parcial" {{ old('tipo_cancelacion') == 'parcial' ? 'selected' : '' }}>
                                            Parcial
                                        </option>
                                        <option value="total" {{ old('tipo_cancelacion') == 'total' ? 'selected' : '' }}>
                                            Total
                                        </option>
                                    </select>

                                    <div class="cancel-type-grid">
                                        <div>
                                            <input
                                                type="radio"
                                                id="tipo_parcial"
                                                name="tipo_cancelacion"
                                                value="parcial"
                                                class="cancel-type-radio"
                                                {{ old('tipo_cancelacion') == 'parcial' ? 'checked' : '' }}
                                            >
                                            <label for="tipo_parcial" class="cancel-type-label">
                                                <div class="cancel-type-card">
                                                    <div class="cancel-type-head">
                                                        <div class="cancel-type-icon">📚</div>
                                                        <div>
                                                            <h6 class="cancel-type-title">Cancelación parcial</h6>
                                                            <span class="cancel-type-badge">Solo algunas clases</span>
                                                        </div>
                                                    </div>
                                                    <p class="cancel-type-text">
                                                        Seleccione esta opción cuando desea cancelar únicamente
                                                        ciertas asignaturas matriculadas, manteniendo activas las demás.
                                                    </p>
                                                </div>
                                            </label>
                                        </div>

                                        <div>
                                            <input
                                                type="radio"
                                                id="tipo_total"
                                                name="tipo_cancelacion"
                                                value="total"
                                                class="cancel-type-radio"
                                                {{ old('tipo_cancelacion') == 'total' ? 'checked' : '' }}
                                            >
                                            <label for="tipo_total" class="cancel-type-label">
                                                <div class="cancel-type-card">
                                                    <div class="cancel-type-head">
                                                        <div class="cancel-type-icon">🗂️</div>
                                                        <div>
                                                            <h6 class="cancel-type-title">Cancelación total</h6>
                                                            <span class="cancel-type-badge">Todas las clases</span>
                                                        </div>
                                                    </div>
                                                    <p class="cancel-type-text">
                                                        Seleccione esta opción cuando necesita cancelar
                                                        la totalidad de las asignaturas matriculadas
                                                        en el período académico actual.
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="cancel-type-note">
                                        El tipo de cancelación debe corresponder con su situación académica actual
                                        y con la documentación que presentará en el siguiente paso.
                                    </div>
                                </div>

                                @error('tipo_cancelacion')
                                    <span class="puma-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="puma-field puma-field--full">
                            <label class="puma-label">
                                Exposición de Motivos <span class="req">*</span>
                            </label>

                            <textarea name="justificacion"
                                      class="puma-textarea"
                                      rows="5"
                                      maxlength="2000"
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

    <script>
        function toggleLegalPopover(event) {
            event.stopPropagation();
            const popover = document.getElementById('legalPopover');
            if (popover) {
                popover.classList.toggle('show');
            }
        }

        document.addEventListener('click', function (event) {
            const popover = document.getElementById('legalPopover');
            const trigger = document.querySelector('.info-pop-btn');

            if (!popover) return;

            const clickDentro = popover.contains(event.target);
            const clickBoton = trigger ? trigger.contains(event.target) : false;

            if (!clickDentro && !clickBoton) {
                popover.classList.remove('show');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const selectTipo = document.querySelector('select[name="tipo_cancelacion"]');
            const radioParcial = document.getElementById('tipo_parcial');
            const radioTotal = document.getElementById('tipo_total');

            if (selectTipo && radioParcial && radioTotal) {
                selectTipo.addEventListener('change', function () {
                    if (this.value === 'parcial') {
                        radioParcial.checked = true;
                    } else if (this.value === 'total') {
                        radioTotal.checked = true;
                    }
                });

                radioParcial.addEventListener('change', function () {
                    if (this.checked) selectTipo.value = 'parcial';
                });

                radioTotal.addEventListener('change', function () {
                    if (this.checked) selectTipo.value = 'total';
                });
            }
        });
    </script>
@endsection