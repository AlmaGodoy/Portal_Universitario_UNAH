@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional - Paso 2')

@section('content')
    @vite([
        'resources/css/cancelacion_paso2.css',
        'resources/js/cancelacion_paso2.js'
    ])

    @php
        $motivoTexto = $motivoActual ?? '';
        $esMedico = $motivoTexto === 'ENFERMEDAD_ACCIDENTE';
        $esLaboral = $motivoTexto === 'PROBLEMAS_LABORALES';
        $esCalamidad = $motivoTexto === 'CALAMIDAD_DOMESTICA';

        $mapaDocs = collect($documentos ?? [])->keyBy('tipo_documento');

        $docDniFrente = $mapaDocs->get('DNI_FRENTE');
        $docDniReverso = $mapaDocs->get('DNI_REVERSO');
        $docHistorial = $mapaDocs->get('HISTORIAL_ACADEMICO');
        $docForma003 = $mapaDocs->get('FORMA_003');
        $docMedica = $mapaDocs->get('CONSTANCIA_MEDICA');
        $docLaboral = $mapaDocs->get('CONSTANCIA_LABORAL');

        $docCalamidad = $mapaDocs->get('RESPALDO_CALAMIDAD')
            ?? $mapaDocs->get('ACTA_DEFUNCION')
            ?? $mapaDocs->get('TESTIMONIO_PADRES')
            ?? $mapaDocs->get('OTRO_RESPALDO');

        $urlBaseUpload   = route('cancelacion.paso2.base.upload', ['id_tramite' => $tramite->id_tramite]);
        $urlRiesgoUpload = route('cancelacion.paso2.riesgo.upload', ['id_tramite' => $tramite->id_tramite]);
        $urlFlexUpload   = route('cancelacion.paso2.flex.upload', ['id_tramite' => $tramite->id_tramite]);
        $urlValidarPaso2 = route('cancelacion.paso2.validar', ['id_tramite' => $tramite->id_tramite]);
        $urlEliminarBase = route('cancelacion.paso2.eliminar', [
            'id_tramite' => $tramite->id_tramite,
            'id_documento' => '__ID__'
        ]);
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc2-container"
         data-id-tramite="{{ $tramite->id_tramite }}"
         data-url-base-upload="{{ $urlBaseUpload }}"
         data-url-riesgo-upload="{{ $urlRiesgoUpload }}"
         data-url-flex-upload="{{ $urlFlexUpload }}"
         data-url-validar="{{ $urlValidarPaso2 }}"
         data-url-eliminar-template="{{ $urlEliminarBase }}"
         data-motivo="{{ $motivoActual ?? '' }}">

        <div id="cc2-global-alert"></div>

        @if (session('success'))
            <div class="cc2-alert cc2-alert--success">
                <span class="cc2-alert__icon">✅</span>
                <div class="cc2-alert__content">
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="cc2-alert cc2-alert--error">
                <span class="cc2-alert__icon">⚠️</span>
                <div class="cc2-alert__content">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="cc2-steps">
            <div class="cc2-step cc2-step--done">
                <div class="cc2-step__circle">✓</div>
                <div class="cc2-step__label">Datos y Motivo</div>
            </div>

            <div class="cc2-step__connector cc2-step__connector--done"></div>

            <div class="cc2-step cc2-step--active">
                <div class="cc2-step__circle">2</div>
                <div class="cc2-step__label">Documentación</div>
            </div>

            <div class="cc2-step__connector"></div>

            <div class="cc2-step">
                <div class="cc2-step__circle">3</div>
                <div class="cc2-step__label">Finalizar</div>
            </div>
        </div>

        <div class="cc2-card">
            <div class="cc2-card__header">
                <div class="cc2-card__icon">📎</div>
                <div>
                    <h1 class="cc2-card__title">Adjuntar Documentación</h1>
                    <p class="cc2-card__subtitle">
                        Trámite N.° {{ $tramite->id_tramite }}
                    </p>
                </div>
            </div>

            <div class="cc2-card__body">

                <div class="cc2-info-box">
                    <h3 class="cc2-info-box__title">Documentación requerida</h3>

                    <ul class="cc2-doc-list">
                        <li>Tarjeta de identidad: frente y reverso, en foto o escaneo legible.</li>
                        <li>Historial académico en PDF.</li>
                        <li>Forma 003 en PDF o captura completa y legible.</li>
                        <li>Documento de respaldo según la causa justificada seleccionada.</li>
                    </ul>

                    <p class="cc2-helper">
                        Formatos permitidos según documento:
                        <strong>PDF, JPG o PNG</strong>
                        <span class="cc2-dot">•</span>
                        Tamaño máximo general:
                        <strong>10 MB</strong>
                    </p>
                </div>

                <div class="cc2-section">
                    <span>Resumen del paso 2</span>
                </div>

                <div class="cc2-summary-grid">
                    <div class="cc2-summary-card">
                        <span class="cc2-summary-card__label">Motivo seleccionado</span>
                        <strong class="cc2-summary-card__value">
                            @if($esMedico)
                                Enfermedad o Accidente
                            @elseif($esLaboral)
                                Problemas Laborales / Cambio de Horario
                            @elseif($esCalamidad)
                                Calamidad Doméstica
                            @else
                                No identificado
                            @endif
                        </strong>
                    </div>

                    <div class="cc2-summary-card">
                        <span class="cc2-summary-card__label">Documento de respaldo</span>
                        <strong class="cc2-summary-card__value">
                            @if($esMedico)
                                Constancia médica
                            @elseif($esLaboral)
                                Constancia laboral
                            @elseif($esCalamidad)
                                Respaldo de calamidad
                            @else
                                Pendiente
                            @endif
                        </strong>
                    </div>
                </div>

                <div class="cc2-section">
                    <span>Documentos base obligatorios</span>
                </div>

                {{-- TARJETA DE IDENTIDAD UNIFICADA EN UNA SOLA SECCIÓN --}}
                <div class="cc2-doc-card">
                    <div class="cc2-doc-card__head">
                        <div>
                            <h3 class="cc2-doc-card__title">Tarjeta de identidad</h3>
                            <p class="cc2-doc-card__text">
                                Suba una foto o escaneo legible del frente y reverso de su tarjeta de identidad.
                            </p>
                        </div>
                        <span class="cc2-badge cc2-badge--required">Obligatorio</span>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:18px;">
                        
                        {{-- FRENTE --}}
                        <div style="border:1px solid #e5e7eb; border-radius:16px; padding:18px; background:#fff;">
                            <h4 style="margin:0 0 12px 0; font-size:1.05rem; font-weight:700; color:#0f2d52;">
                                Frente
                            </h4>

                            <form class="cc2-upload-form"
                                  data-upload-kind="base"
                                  data-tipo-documento="DNI_FRENTE">
                                @csrf

                                <div class="cc2-upload-area" data-upload-area>
                                    <div class="cc2-upload-area__icon">🪪</div>
                                    <p class="cc2-upload-area__title">Subir frente de identidad</p>
                                    <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                    <p class="cc2-file-name" data-file-name></p>
                                </div>

                                <input type="file"
                                       name="archivo"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       hidden
                                       data-file-input>

                                <div class="cc2-inline-actions">
                                    <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar frente</button>
                                </div>

                                <div class="cc2-form-msg" data-form-msg></div>
                            </form>

                            <div class="cc2-doc-state">
                                @if($docDniFrente)
                                    <div class="cc2-doc-state__ok">
                                        <span>✅ Cargado:</span>
                                        <strong>{{ $docDniFrente->nombre_documento }}</strong>
                                    </div>
                                    <div class="cc2-doc-state__actions">
                                        <a href="{{ asset('storage/' . $docDniFrente->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                        <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                                data-delete-doc
                                                data-id-documento="{{ $docDniFrente->id_documento }}">
                                            Quitar
                                        </button>
                                    </div>
                                @else
                                    <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                                @endif
                            </div>
                        </div>

                        {{-- REVERSO --}}
                        <div style="border:1px solid #e5e7eb; border-radius:16px; padding:18px; background:#fff;">
                            <h4 style="margin:0 0 12px 0; font-size:1.05rem; font-weight:700; color:#0f2d52;">
                                Reverso
                            </h4>

                            <form class="cc2-upload-form"
                                  data-upload-kind="base"
                                  data-tipo-documento="DNI_REVERSO">
                                @csrf

                                <div class="cc2-upload-area" data-upload-area>
                                    <div class="cc2-upload-area__icon">🪪</div>
                                    <p class="cc2-upload-area__title">Subir reverso de identidad</p>
                                    <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                    <p class="cc2-file-name" data-file-name></p>
                                </div>

                                <input type="file"
                                       name="archivo"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       hidden
                                       data-file-input>

                                <div class="cc2-inline-actions">
                                    <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar reverso</button>
                                </div>

                                <div class="cc2-form-msg" data-form-msg></div>
                            </form>

                            <div class="cc2-doc-state">
                                @if($docDniReverso)
                                    <div class="cc2-doc-state__ok">
                                        <span>✅ Cargado:</span>
                                        <strong>{{ $docDniReverso->nombre_documento }}</strong>
                                    </div>
                                    <div class="cc2-doc-state__actions">
                                        <a href="{{ asset('storage/' . $docDniReverso->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                        <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                                data-delete-doc
                                                data-id-documento="{{ $docDniReverso->id_documento }}">
                                            Quitar
                                        </button>
                                    </div>
                                @else
                                    <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- HISTORIAL + FORMA 003 --}}
                <div class="cc2-doc-grid">
                    <div class="cc2-doc-card" data-doc-card="HISTORIAL_ACADEMICO">
                        <div class="cc2-doc-card__head">
                            <div>
                                <h3 class="cc2-doc-card__title">Historial académico</h3>
                                <p class="cc2-doc-card__text">
                                    Este documento debe subirse en PDF.
                                </p>
                            </div>
                            <span class="cc2-badge cc2-badge--required">Obligatorio</span>
                        </div>

                        <form class="cc2-upload-form"
                              data-upload-kind="base"
                              data-tipo-documento="HISTORIAL_ACADEMICO">
                            @csrf

                            <div class="cc2-upload-area" data-upload-area>
                                <div class="cc2-upload-area__icon">📘</div>
                                <p class="cc2-upload-area__title">Subir historial académico</p>
                                <p class="cc2-upload-area__subtitle">PDF únicamente</p>
                                <p class="cc2-file-name" data-file-name></p>
                            </div>

                            <input type="file"
                                   name="archivo"
                                   accept=".pdf"
                                   hidden
                                   data-file-input>

                            <div class="cc2-inline-actions">
                                <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar documento</button>
                            </div>

                            <div class="cc2-form-msg" data-form-msg></div>
                        </form>

                        <div class="cc2-doc-state">
                            @if($docHistorial)
                                <div class="cc2-doc-state__ok">
                                    <span>✅ Cargado:</span>
                                    <strong>{{ $docHistorial->nombre_documento }}</strong>
                                </div>
                                <div class="cc2-doc-state__actions">
                                    <a href="{{ asset('storage/' . $docHistorial->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                    <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                            data-delete-doc
                                            data-id-documento="{{ $docHistorial->id_documento }}">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                            @endif
                        </div>
                    </div>

                    <div class="cc2-doc-card" data-doc-card="FORMA_003">
                        <div class="cc2-doc-card__head">
                            <div>
                                <h3 class="cc2-doc-card__title">Forma 003</h3>
                                <p class="cc2-doc-card__text">
                                    Puede subir PDF o una captura completa y legible del documento.
                                </p>
                            </div>
                            <span class="cc2-badge cc2-badge--required">Obligatorio</span>
                        </div>

                        <form class="cc2-upload-form"
                              data-upload-kind="base"
                              data-tipo-documento="FORMA_003">
                            @csrf

                            <div class="cc2-upload-area" data-upload-area>
                                <div class="cc2-upload-area__icon">🧾</div>
                                <p class="cc2-upload-area__title">Subir Forma 003</p>
                                <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                <p class="cc2-file-name" data-file-name></p>
                            </div>

                            <input type="file"
                                   name="archivo"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   hidden
                                   data-file-input>

                            <div class="cc2-inline-actions">
                                <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar documento</button>
                            </div>

                            <div class="cc2-form-msg" data-form-msg></div>
                        </form>

                        <div class="cc2-doc-state">
                            @if($docForma003)
                                <div class="cc2-doc-state__ok">
                                    <span>✅ Cargado:</span>
                                    <strong>{{ $docForma003->nombre_documento }}</strong>
                                </div>
                                <div class="cc2-doc-state__actions">
                                    <a href="{{ asset('storage/' . $docForma003->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                    <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                            data-delete-doc
                                            data-id-documento="{{ $docForma003->id_documento }}">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="cc2-section">
                    <span>Documento de respaldo según la causa</span>
                </div>

                @if($esMedico)
                    <div class="cc2-doc-card" data-doc-card="CONSTANCIA_MEDICA">
                        <div class="cc2-doc-card__head">
                            <div>
                                <h3 class="cc2-doc-card__title">Constancia médica</h3>
                                <p class="cc2-doc-card__text">
                                    Suba una foto o escaneo legible. Si la constancia contiene folio, referencia o código visible, puede ingresarlo abajo.
                                </p>
                            </div>
                            <span class="cc2-badge cc2-badge--required">Requerido</span>
                        </div>

                        <form class="cc2-upload-form"
                              data-upload-kind="riesgo"
                              data-tipo-documento="CONSTANCIA_MEDICA">
                            @csrf

                            <div class="cc2-field-grid">
                                <div class="cc2-field cc2-field--full">
                                    <div class="cc2-upload-area" data-upload-area>
                                        <div class="cc2-upload-area__icon">🏥</div>
                                        <p class="cc2-upload-area__title">Subir constancia médica</p>
                                        <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                        <p class="cc2-file-name" data-file-name></p>
                                    </div>

                                    <input type="file"
                                           name="archivo"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           hidden
                                           data-file-input>
                                </div>

                                <div class="cc2-field cc2-field--full">
                                    <label class="cc2-switch">
                                        <input type="checkbox" name="tiene_referencia" value="1" data-ref-toggle>
                                        <span>El documento contiene folio, referencia o código visible</span>
                                    </label>
                                </div>

                                <div class="cc2-field cc2-ref-field" data-ref-field hidden>
                                    <label class="cc2-label">Folio, referencia o código</label>
                                    <input type="text"
                                           name="numero_folio"
                                           class="cc2-input"
                                           maxlength="100"
                                           placeholder="Ejemplo: MED-2026-00125">
                                </div>
                            </div>

                            <div class="cc2-inline-actions">
                                <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar constancia médica</button>
                            </div>

                            <div class="cc2-form-msg" data-form-msg></div>
                        </form>

                        <div class="cc2-doc-state">
                            @if($docMedica)
                                <div class="cc2-doc-state__ok">
                                    <span>✅ Cargado:</span>
                                    <strong>{{ $docMedica->nombre_documento }}</strong>
                                </div>
                                <div class="cc2-doc-state__meta">
                                    @if(!empty($docMedica->numero_folio))
                                        <span>Referencia registrada: <strong>{{ $docMedica->numero_folio }}</strong></span>
                                    @endif
                                </div>
                                <div class="cc2-doc-state__actions">
                                    <a href="{{ asset('storage/' . $docMedica->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                    <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                            data-delete-doc
                                            data-id-documento="{{ $docMedica->id_documento }}">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($esLaboral)
                    <div class="cc2-doc-card" data-doc-card="CONSTANCIA_LABORAL">
                        <div class="cc2-doc-card__head">
                            <div>
                                <h3 class="cc2-doc-card__title">Constancia laboral</h3>
                                <p class="cc2-doc-card__text">
                                    Suba una foto o escaneo legible. Si la constancia contiene folio, referencia o código visible, puede ingresarlo abajo.
                                </p>
                            </div>
                            <span class="cc2-badge cc2-badge--required">Requerido</span>
                        </div>

                        <form class="cc2-upload-form"
                              data-upload-kind="riesgo"
                              data-tipo-documento="CONSTANCIA_LABORAL">
                            @csrf

                            <div class="cc2-field-grid">
                                <div class="cc2-field cc2-field--full">
                                    <div class="cc2-upload-area" data-upload-area>
                                        <div class="cc2-upload-area__icon">💼</div>
                                        <p class="cc2-upload-area__title">Subir constancia laboral</p>
                                        <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                        <p class="cc2-file-name" data-file-name></p>
                                    </div>

                                    <input type="file"
                                           name="archivo"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           hidden
                                           data-file-input>
                                </div>

                                <div class="cc2-field cc2-field--full">
                                    <label class="cc2-switch">
                                        <input type="checkbox" name="tiene_referencia" value="1" data-ref-toggle>
                                        <span>El documento contiene folio, referencia o código visible</span>
                                    </label>
                                </div>

                                <div class="cc2-field cc2-ref-field" data-ref-field hidden>
                                    <label class="cc2-label">Folio, referencia o código</label>
                                    <input type="text"
                                           name="numero_folio"
                                           class="cc2-input"
                                           maxlength="100"
                                           placeholder="Ejemplo: LAB-2026-00125">
                                </div>
                            </div>

                            <div class="cc2-inline-actions">
                                <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar constancia laboral</button>
                            </div>

                            <div class="cc2-form-msg" data-form-msg></div>
                        </form>

                        <div class="cc2-doc-state">
                            @if($docLaboral)
                                <div class="cc2-doc-state__ok">
                                    <span>✅ Cargado:</span>
                                    <strong>{{ $docLaboral->nombre_documento }}</strong>
                                </div>
                                <div class="cc2-doc-state__meta">
                                    @if(!empty($docLaboral->numero_folio))
                                        <span>Referencia registrada: <strong>{{ $docLaboral->numero_folio }}</strong></span>
                                    @endif
                                </div>
                                <div class="cc2-doc-state__actions">
                                    <a href="{{ asset('storage/' . $docLaboral->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                    <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                            data-delete-doc
                                            data-id-documento="{{ $docLaboral->id_documento }}">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($esCalamidad)
                    <div class="cc2-doc-card" data-doc-card="RESPALDO_CALAMIDAD">
                        <div class="cc2-doc-card__head">
                            <div>
                                <h3 class="cc2-doc-card__title">Documento de respaldo por calamidad doméstica</h3>
                                <p class="cc2-doc-card__text">
                                    Suba el documento de soporte que corresponda a su caso.
                                </p>
                            </div>
                            <span class="cc2-badge cc2-badge--required">Requerido</span>
                        </div>

                        <form class="cc2-upload-form"
                              data-upload-kind="flex"
                              data-tipo-documento="RESPALDO_CALAMIDAD">
                            @csrf

                            <div class="cc2-field-grid">
                                <div class="cc2-field">
                                    <label class="cc2-label">Tipo de respaldo</label>
                                    <select name="tipo_documento" class="cc2-input cc2-select" required>
                                        <option value="RESPALDO_CALAMIDAD">Documento general de calamidad</option>
                                        <option value="ACTA_DEFUNCION">Acta de defunción</option>
                                        <option value="TESTIMONIO_PADRES">Testimonio de padres</option>
                                        <option value="OTRO_RESPALDO">Otro documento de respaldo</option>
                                    </select>
                                </div>

                                <div class="cc2-field cc2-field--full">
                                    <div class="cc2-upload-area" data-upload-area>
                                        <div class="cc2-upload-area__icon">📂</div>
                                        <p class="cc2-upload-area__title">Subir documento de respaldo</p>
                                        <p class="cc2-upload-area__subtitle">PDF, JPG o PNG</p>
                                        <p class="cc2-file-name" data-file-name></p>
                                    </div>

                                    <input type="file"
                                           name="archivo"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           hidden
                                           data-file-input>
                                </div>
                            </div>

                            <div class="cc2-inline-actions">
                                <button type="submit" class="cc2-btn cc2-btn--primary-soft">Guardar respaldo</button>
                            </div>

                            <div class="cc2-form-msg" data-form-msg></div>
                        </form>

                        <div class="cc2-doc-state">
                            @if($docCalamidad)
                                <div class="cc2-doc-state__ok">
                                    <span>✅ Cargado:</span>
                                    <strong>{{ $docCalamidad->nombre_documento }}</strong>
                                </div>
                                <div class="cc2-doc-state__actions">
                                    <a href="{{ asset('storage/' . $docCalamidad->ruta_archivo) }}" target="_blank" class="cc2-link-btn">Ver archivo</a>
                                    <button type="button" class="cc2-link-btn cc2-link-btn--danger"
                                            data-delete-doc
                                            data-id-documento="{{ $docCalamidad->id_documento }}">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <div class="cc2-doc-state__pending">Pendiente de cargar</div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="cc2-section">
                    <span>Finalizar este paso</span>
                </div>

                <div class="cc2-finish-box">
                    <p class="cc2-finish-box__text">
                        Cuando termine de cargar los documentos obligatorios y el respaldo según su causa, valide el paso 2 para continuar.
                    </p>

                    <div class="cc2-actions">
                        <a href="{{ route('cancelacion.index') }}" class="cc2-btn cc2-btn--secondary">
                            ← Regresar al Paso 1
                        </a>

                        <button type="button" id="btn-validar-paso2" class="cc2-btn cc2-btn--primary">
                            Validar Documentación y Continuar ✓
                        </button>
                    </div>

                    <div id="cc2-validate-msg" class="cc2-form-msg cc2-form-msg--final"></div>
                </div>

            </div>
        </div>
    </div>
@endsection