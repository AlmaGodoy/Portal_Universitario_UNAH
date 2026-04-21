@php
    $idTramite = $tramite->id_tramite ?? session('id_tramite');

    $urlSubirIdentidad = route('cancelacion.paso2.subir-identidad', ['id_tramite' => $idTramite]);
    $urlSubirBase      = route('cancelacion.paso2.subir-base', ['id_tramite' => $idTramite]);
    $urlSubirRiesgo    = route('cancelacion.paso2.subir-riesgo', ['id_tramite' => $idTramite]);
    $urlSubirFlex      = route('cancelacion.paso2.subir-flexible', ['id_tramite' => $idTramite]);
    $urlValidar        = route('cancelacion.paso2.validar', ['id_tramite' => $idTramite]);

    $docs = collect($documentos ?? []);

    $nombreMotivo = match($motivoActual) {
        'ENFERMEDAD_ACCIDENTE' => 'Enfermedad o Accidente',
        'CALAMIDAD_DOMESTICA'  => 'Calamidad Doméstica',
        'PROBLEMAS_LABORALES'  => 'Problemas Laborales / Cambio de Horario',
        default                => 'No identificado',
    };

    $docPorTipo = fn($tipo) => $docs->firstWhere('tipo_documento', $tipo);

    $dniFrente  = $docPorTipo('DNI_FRENTE');
    $dniReverso = $docPorTipo('DNI_REVERSO');
    $historial  = $docPorTipo('HISTORIAL_ACADEMICO');
    $forma003   = $docPorTipo('FORMA_003');

    $constanciaMedica  = $docPorTipo('CONSTANCIA_MEDICA');
    $constanciaLaboral = $docPorTipo('CONSTANCIA_LABORAL');

    $tiposCalamidad = [
        'RESPALDO_CALAMIDAD' => 'Respaldo de calamidad',
        'ACTA_DEFUNCION'     => 'Acta de defunción',
        'TESTIMONIO_PADRES'  => 'Testimonio de padres',
        'OTRO_RESPALDO'      => 'Otro documento de respaldo',
    ];

    $iconoPorTipo = [
        'DNI_FRENTE'          => '🪪',
        'DNI_REVERSO'         => '🪪',
        'HISTORIAL_ACADEMICO' => '📋',
        'FORMA_003'           => '📄',
        'CONSTANCIA_MEDICA'   => '🏥',
        'CONSTANCIA_LABORAL'  => '💼',
        'RESPALDO_CALAMIDAD'  => '📁',
        'ACTA_DEFUNCION'      => '📜',
        'TESTIMONIO_PADRES'   => '👨‍👩‍👧',
        'OTRO_RESPALDO'       => '📎',
    ];

    $etiquetaPorTipo = [
        'DNI_FRENTE'          => 'Identidad · Frente',
        'DNI_REVERSO'         => 'Identidad · Reverso',
        'HISTORIAL_ACADEMICO' => 'Historial académico',
        'FORMA_003'           => 'Forma 003',
        'CONSTANCIA_MEDICA'   => 'Constancia médica',
        'CONSTANCIA_LABORAL'  => 'Constancia laboral',
        'RESPALDO_CALAMIDAD'  => 'Respaldo de calamidad',
        'ACTA_DEFUNCION'      => 'Acta de defunción',
        'TESTIMONIO_PADRES'   => 'Testimonio de padres',
        'OTRO_RESPALDO'       => 'Otro respaldo',
    ];
@endphp

@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional — Paso 2')

@section('content')
@vite(['resources/css/cancelacion.css'])

<div class="cx-wrap">

    <div id="cx-alert" class="cx-alert">
        <span class="cx-alert__icon" id="cx-alert-icon"></span>
        <div id="cx-alert-msg"></div>
    </div>

    @if (session('success'))
        <div class="cx-alert cx-alert--ok show">
            <span class="cx-alert__icon">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="cx-alert cx-alert--err show">
            <span class="cx-alert__icon">⚠️</span>
            <div>@foreach ($errors->all() as $e)<p style="margin:2px 0">{{ $e }}</p>@endforeach</div>
        </div>
    @endif

    <div class="cx-steps">
        <div class="cx-step cx-step--done">
            <div class="cx-step__circle">✓</div>
            <div class="cx-step__label">Datos y Motivo</div>
        </div>
        <div class="cx-connector cx-connector--done"></div>
        <div class="cx-step cx-step--active">
            <div class="cx-step__circle">2</div>
            <div class="cx-step__label">Documentación</div>
        </div>
        <div class="cx-connector"></div>
        <div class="cx-step">
            <div class="cx-step__circle">3</div>
            <div class="cx-step__label">Finalizar</div>
        </div>
    </div>

    <div class="cx-card">
        <div class="cx-card__head">
            <div class="cx-card__head-icon">📂</div>
            <div>
                <h4 class="cx-card__head-title">Adjuntar documentación</h4>
                <p class="cx-card__head-sub">Trámite #{{ $idTramite }} · {{ $nombreMotivo }}</p>
            </div>
        </div>

        <div class="cx-card__body">

            <div class="cx-notice">
                <span class="cx-notice__icon">💡</span>
                <span>Cargue sus <strong>documentos base</strong> y luego el <strong>respaldo</strong> correspondiente a su motivo.</span>
            </div>

            <div class="cx-section">Documento de identidad</div>

            <div class="cx-upload-block {{ ($dniFrente && $dniReverso) ? 'uploaded' : '' }}">
                <div class="cx-drop-hint">
                    <div class="cx-drop-hint__icon">🪪</div>
                    <div class="cx-drop-hint__title">
                        {{ ($dniFrente && $dniReverso) ? 'Identidad completa cargada' : 'Tarjeta de identidad' }}
                    </div>
                    <div class="cx-drop-hint__sub">Sube frente y reverso en este mismo apartado</div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:14px; margin-top:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px; color:#1f2937;">
                            Frente
                        </label>
                        <input type="file" id="dni_frente_file" accept=".pdf,.jpg,.jpeg,.png" class="cx-folio-input visible">
                        @if ($dniFrente)
                            <div style="margin-top:8px; font-size:12px; color:#15803d;">
                                ✓ {{ $dniFrente->nombre_documento ?? 'Frente cargado' }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px; color:#1f2937;">
                            Reverso
                        </label>
                        <input type="file" id="dni_reverso_file" accept=".pdf,.jpg,.jpeg,.png" class="cx-folio-input visible">
                        @if ($dniReverso)
                            <div style="margin-top:8px; font-size:12px; color:#15803d;">
                                ✓ {{ $dniReverso->nombre_documento ?? 'Reverso cargado' }}
                            </div>
                        @endif
                    </div>
                </div>

                <div style="margin-top:14px; text-align:right;">
                    <button type="button" id="btnSubirIdentidad" class="cx-btn cx-btn--primary">
                        Subir identidad
                    </button>
                </div>
            </div>

            <div class="cx-section">Documentos académicos</div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
                <div>
                    <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:var(--cx-muted);">Historial académico</div>
                    <div class="cx-upload-block {{ $historial ? 'uploaded' : '' }}">
                        <input type="file" id="file-historial" class="cx-file-input" accept=".pdf">
                        <div class="cx-drop-hint">
                            <div class="cx-drop-hint__icon">📋</div>
                            <div class="cx-drop-hint__title">
                                {{ $historial ? ($historial->nombre_documento ?? 'Cargado') : 'Historial académico' }}
                            </div>
                            <div class="cx-drop-hint__sub">Solo PDF · máx. 10 MB</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:var(--cx-muted);">Forma 003</div>
                    <div class="cx-upload-block {{ $forma003 ? 'uploaded' : '' }}">
                        <input type="file" id="file-forma003" class="cx-file-input" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="cx-drop-hint">
                            <div class="cx-drop-hint__icon">📄</div>
                            <div class="cx-drop-hint__title">
                                {{ $forma003 ? ($forma003->nombre_documento ?? 'Cargado') : 'Forma 003' }}
                            </div>
                            <div class="cx-drop-hint__sub">PDF, JPG o PNG · máx. 10 MB</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cx-section">Respaldo de la causa justificada</div>

            @if ($motivoActual === 'ENFERMEDAD_ACCIDENTE')
                <div class="cx-upload-block {{ $constanciaMedica ? 'uploaded' : '' }}">
                    <input type="file" id="file-constancia-medica" class="cx-file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="cx-drop-hint">
                        <div class="cx-drop-hint__icon">🏥</div>
                        <div class="cx-drop-hint__title">
                            {{ $constanciaMedica ? ($constanciaMedica->nombre_documento ?? 'Cargada') : 'Constancia médica' }}
                        </div>
                        <div class="cx-drop-hint__sub">PDF, JPG o PNG · máx. 10 MB</div>
                    </div>
                </div>
                <div style="margin-top:14px; text-align:right;">
                    <button type="button" id="btnSubirConstancia"
                            class="cx-btn cx-btn--primary"
                            data-tipo="CONSTANCIA_MEDICA"
                            data-endpoint="{{ $urlSubirRiesgo }}">
                        Subir constancia
                    </button>
                </div>
            @endif

            @if ($motivoActual === 'PROBLEMAS_LABORALES')
                <div class="cx-upload-block {{ $constanciaLaboral ? 'uploaded' : '' }}">
                    <input type="file" id="file-constancia-laboral" class="cx-file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="cx-drop-hint">
                        <div class="cx-drop-hint__icon">💼</div>
                        <div class="cx-drop-hint__title">
                            {{ $constanciaLaboral ? ($constanciaLaboral->nombre_documento ?? 'Cargada') : 'Constancia laboral' }}
                        </div>
                        <div class="cx-drop-hint__sub">PDF, JPG o PNG · máx. 10 MB</div>
                    </div>
                </div>
                <div style="margin-top:14px; text-align:right;">
                    <button type="button" id="btnSubirConstancia"
                            class="cx-btn cx-btn--primary"
                            data-tipo="CONSTANCIA_LABORAL"
                            data-endpoint="{{ $urlSubirRiesgo }}">
                        Subir constancia
                    </button>
                </div>
            @endif

            @if ($motivoActual === 'CALAMIDAD_DOMESTICA')
                <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:16px;">
                    @foreach ($tiposCalamidad as $tipo => $titulo)
                        @php $docActual = $docPorTipo($tipo); @endphp
                        <div>
                            <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:var(--cx-muted);">{{ $titulo }}</div>
                            <div class="cx-upload-block {{ $docActual ? 'uploaded' : '' }}">
                                <input type="file" class="cx-file-input calamidad-file" accept=".pdf,.jpg,.jpeg,.png"
                                       data-tipo="{{ $tipo }}">
                                <div class="cx-drop-hint">
                                    <div class="cx-drop-hint__icon">{{ $iconoPorTipo[$tipo] ?? '📁' }}</div>
                                    <div class="cx-drop-hint__title">
                                        {{ $docActual ? ($docActual->nombre_documento ?? 'Cargado') : $titulo }}
                                    </div>
                                    <div class="cx-drop-hint__sub">PDF, JPG o PNG · máx. 10 MB</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($docs->isNotEmpty())
                <div class="cx-section">Documentos cargados</div>
                <div class="cx-doc-list">
                    @foreach ($docs as $doc)
                        <div class="cx-doc-row">
                            <div class="cx-doc-row__icon">{{ $iconoPorTipo[$doc->tipo_documento] ?? '📎' }}</div>
                            <div class="cx-doc-row__info">
                                <div class="cx-doc-row__name">{{ $etiquetaPorTipo[$doc->tipo_documento] ?? $doc->tipo_documento }}</div>
                                <div class="cx-doc-row__path">{{ $doc->nombre_documento ?? $doc->ruta_archivo ?? 'Archivo cargado' }}</div>
                            </div>
                            <div class="cx-doc-row__actions">
                                @if (!empty($doc->ruta_archivo))
                                    <a href="{{ asset('storage/' . $doc->ruta_archivo) }}"
                                       target="_blank"
                                       class="cx-btn cx-btn--sm"
                                       style="text-decoration:none;">Ver</a>
                                @endif
                                <button type="button"
                                        class="cx-btn cx-btn--sm cx-btn--danger btn-delete-doc"
                                        data-delete-url="{{ route('cancelacion.paso2.eliminar', ['id_tramite' => $idTramite, 'id_documento' => $doc->id_documento]) }}">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="cx-actions">
                <a href="{{ route('cancelacion.index') }}" class="cx-btn" style="text-decoration:none;">← Volver</a>
                <button type="button" id="btnValidarPaso2" class="cx-btn cx-btn--primary" style="margin-left:auto;">
                    Guardar y continuar →
                </button>
            </div>

        </div>
    </div>
</div>

<script>
const csrfToken         = '{{ csrf_token() }}';
const urlValidar        = @json($urlValidar);
const urlSubirBase      = @json($urlSubirBase);
const urlSubirIdentidad = @json($urlSubirIdentidad);
const urlSubirRiesgo    = @json($urlSubirRiesgo);
const urlSubirFlex      = @json($urlSubirFlex);

function alert$(tipo, msg) {
    const box = document.getElementById('cx-alert');
    box.className = 'cx-alert cx-alert--' + (tipo === 'ok' ? 'ok' : 'err') + ' show';
    document.getElementById('cx-alert-icon').textContent = tipo === 'ok' ? '✅' : '⚠️';
    document.getElementById('cx-alert-msg').innerHTML = msg;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function doUpload(endpoint, tipo, archivo, folio = null) {
    const fd = new FormData();
    fd.append('tipo_documento', tipo);
    fd.append('archivo', archivo);
    if (folio) {
        fd.append('tiene_referencia', '1');
        fd.append('numero_folio', folio);
    }

    const r = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    });

    const d = await r.json();
    if (!r.ok || !d.ok) throw new Error(d.mensaje || 'No se pudo subir el archivo.');
    return d;
}

async function doUploadIdentidad() {
    const frente = document.getElementById('dni_frente_file')?.files?.[0] ?? null;
    const reverso = document.getElementById('dni_reverso_file')?.files?.[0] ?? null;

    if (!frente && !reverso) {
        throw new Error('Debe seleccionar al menos un archivo de identidad.');
    }

    const fd = new FormData();
    if (frente) fd.append('dni_frente', frente);
    if (reverso) fd.append('dni_reverso', reverso);

    const r = await fetch(urlSubirIdentidad, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    });

    const d = await r.json();
    if (!r.ok || !d.ok) throw new Error(d.mensaje || 'No se pudo subir la identidad.');
    return d;
}

document.getElementById('btnSubirIdentidad')?.addEventListener('click', async function () {
    const orig = this.innerHTML;
    this.innerHTML = 'Subiendo...';
    this.disabled = true;

    try {
        await doUploadIdentidad();
        alert$('ok', 'Identidad cargada correctamente.');
        setTimeout(() => location.reload(), 700);
    } catch (err) {
        alert$('err', err.message);
        this.innerHTML = orig;
        this.disabled = false;
    }
});

document.getElementById('btnSubirConstancia')?.addEventListener('click', async function () {
    const tipo     = this.dataset.tipo;
    const endpoint = this.dataset.endpoint;

    const inputFile = tipo === 'CONSTANCIA_MEDICA'
        ? document.getElementById('file-constancia-medica')
        : document.getElementById('file-constancia-laboral');

    const archivo = inputFile?.files?.[0];
    if (!archivo) {
        alert$('err', 'Seleccione el archivo de constancia antes de subir.');
        return;
    }

    const orig = this.innerHTML;
    this.innerHTML = 'Subiendo...';
    this.disabled = true;

    try {
        await doUpload(endpoint, tipo, archivo);
        alert$('ok', 'Constancia cargada correctamente.');
        setTimeout(() => location.reload(), 700);
    } catch (err) {
        alert$('err', err.message);
        this.innerHTML = orig;
        this.disabled = false;
    }
});

document.querySelectorAll('.btn-delete-doc').forEach(btn => {
    btn.addEventListener('click', async function () {
        if (!confirm('¿Desea eliminar este documento?')) return;

        try {
            const r = await fetch(this.dataset.deleteUrl, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            const d = await r.json();
            if (!r.ok || !d.ok) throw new Error(d.mensaje || 'No se pudo eliminar.');

            alert$('ok', d.mensaje || 'Documento eliminado.');
            setTimeout(() => location.reload(), 700);
        } catch (err) {
            alert$('err', err.message);
        }
    });
});

document.getElementById('btnValidarPaso2')?.addEventListener('click', async function () {
    const btn = this;
    const orig = btn.innerHTML;
    btn.innerHTML = 'Guardando...';
    btn.disabled = true;

    try {
        const frente = document.getElementById('dni_frente_file')?.files?.[0] ?? null;
        const reverso = document.getElementById('dni_reverso_file')?.files?.[0] ?? null;

        if (frente || reverso) {
            await doUploadIdentidad();
        }

        const historial = document.getElementById('file-historial')?.files?.[0];
        if (historial) {
            await doUpload(urlSubirBase, 'HISTORIAL_ACADEMICO', historial);
        }

        const forma003 = document.getElementById('file-forma003')?.files?.[0];
        if (forma003) {
            await doUpload(urlSubirBase, 'FORMA_003', forma003);
        }

        const btnConstancia = document.getElementById('btnSubirConstancia');
        if (btnConstancia) {
            const tipo = btnConstancia.dataset.tipo;
            const archivo = tipo === 'CONSTANCIA_MEDICA'
                ? document.getElementById('file-constancia-medica')?.files?.[0]
                : document.getElementById('file-constancia-laboral')?.files?.[0];

            if (archivo) {
                await doUpload(urlSubirRiesgo, tipo, archivo);
            }
        }

        const calamidadInputs = document.querySelectorAll('.calamidad-file');
        for (const input of calamidadInputs) {
            const archivo = input.files?.[0];
            if (archivo) {
                await doUpload(urlSubirFlex, input.dataset.tipo, archivo);
            }
        }

        const r = await fetch(urlValidar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });

        const d = await r.json();
        if (!r.ok || !d.ok) {
            const faltantes = d.faltantes?.length ? ' — ' + d.faltantes.join(', ') : '';
            throw new Error((d.mensaje || 'Faltan documentos.') + faltantes);
        }

        alert$('ok', d.mensaje || 'Documentación guardada correctamente.');
        setTimeout(() => {
            if (d.redirect) window.location.href = d.redirect;
        }, 800);

    } catch (err) {
        alert$('err', err.message);
        btn.innerHTML = orig;
        btn.disabled = false;
    }
});
</script>
@endsection