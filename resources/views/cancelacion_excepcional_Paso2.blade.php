@php
    $idTramite = $tramite->id_tramite ?? session('id_tramite');

    $urlSubirIdentidad = route('cancelacion.paso2.subir-identidad', ['id_tramite' => $idTramite]);
    $urlSubirBase      = route('cancelacion.paso2.subir-base',      ['id_tramite' => $idTramite]);
    $urlSubirRiesgo    = route('cancelacion.paso2.subir-riesgo',    ['id_tramite' => $idTramite]);
    $urlSubirFlex      = route('cancelacion.paso2.subir-flexible',  ['id_tramite' => $idTramite]);
    $urlValidar        = route('cancelacion.paso2.validar',         ['id_tramite' => $idTramite]);

    $docs = collect($documentos ?? []);

    $nombreMotivo = match($motivoActual) {
        'ENFERMEDAD_ACCIDENTE' => 'Enfermedad o Accidente',
        'CALAMIDAD_DOMESTICA'  => 'Calamidad Doméstica',
        'PROBLEMAS_LABORALES'  => 'Problemas Laborales / Cambio de Horario',
        default                => 'No identificado',
    };

    $docPorTipo = fn($tipo) => $docs->firstWhere('tipo_documento', $tipo);

    $dniFrente         = $docPorTipo('DNI_FRENTE');
    $dniReverso        = $docPorTipo('DNI_REVERSO');
    $historial         = $docPorTipo('HISTORIAL_ACADEMICO');
    $forma003          = $docPorTipo('FORMA_003');
    $constanciaMedica  = $docPorTipo('CONSTANCIA_MEDICA');
    $constanciaLaboral = $docPorTipo('CONSTANCIA_LABORAL');

    $tiposCalamidad = [
        'RESPALDO_CALAMIDAD' => ['titulo' => 'Respaldo de calamidad',    'icono' => '📁'],
        'ACTA_DEFUNCION'     => ['titulo' => 'Acta de defunción',         'icono' => '📜'],
        'TESTIMONIO_PADRES'  => ['titulo' => 'Testimonio de padres',      'icono' => '👨‍👩‍👧'],
        'OTRO_RESPALDO'      => ['titulo' => 'Otro documento de respaldo', 'icono' => '📎'],
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

    $dniCompleto = $dniFrente && $dniReverso;
@endphp

@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional — Paso 2')

@section('content')

<div class="cx-page">

    <style>
        .cx-page{
            max-width: 1180px;
            margin: 0 auto;
            padding: 32px 26px 54px;
            font-family: 'Nunito', sans-serif;
        }

        .cx-alert{
            display:none;
            align-items:flex-start;
            gap:12px;
            border-radius:16px;
            padding:15px 18px;
            margin-bottom:22px;
            font-size:14px;
            line-height:1.55;
            border:1px solid transparent;
            box-shadow:0 14px 28px rgba(15,23,42,.10);
        }
        .cx-alert.show{ display:flex; }
        .cx-alert--ok{
            background:#ecfdf5;
            border-color:#bbf7d0;
            color:#166534;
        }
        .cx-alert--err{
            background:#fef2f2;
            border-color:#fecaca;
            color:#991b1b;
        }
        .cx-alert__icon{
            font-size:18px;
            line-height:1;
            margin-top:2px;
            flex-shrink:0;
        }

        .cx-steps{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:0;
            margin-bottom:34px;
        }
        .cx-step{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:8px;
            min-width:118px;
        }
        .cx-step__circle{
            width:46px;
            height:46px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:900;
            font-size:14px;
            border:2px solid #d6deea;
            background:#fff;
            color:#7b8798;
            box-shadow:0 8px 20px rgba(15,23,42,.08);
        }
        .cx-step__label{
            font-size:12px;
            font-weight:800;
            color:#7b8798;
            text-align:center;
        }
        .cx-step--done .cx-step__circle{
            border-color:#22c55e;
            background:#ecfdf5;
            color:#15803d;
        }
        .cx-step--done .cx-step__label{
            color:#15803d;
        }
        .cx-step--active .cx-step__circle{
            border-color:#1f4fa3;
            background:linear-gradient(135deg,#2458a6 0%, #173b7a 100%);
            color:#fff;
        }
        .cx-step--active .cx-step__label{
            color:#1f4fa3;
        }
        .cx-connector{
            width:86px;
            height:4px;
            border-radius:999px;
            background:#dbe5f2;
            margin:0 10px 24px;
        }
        .cx-connector--done{
            background:#8fd3a8;
        }

        .cx-card{
            background:#ffffff;
            border:1px solid #dbe5f2;
            border-radius:24px;
            overflow:hidden;
            box-shadow:0 18px 44px rgba(15,23,42,.10);
        }

        .cx-card__header{
            display:flex;
            align-items:center;
            gap:16px;
            padding:22px 26px;
            border-bottom:1px solid #dbe5f2;
            background:linear-gradient(180deg,#fafdff 0%,#f2f7ff 100%);
        }
        .cx-card__header-icon{
            width:52px;
            height:52px;
            min-width:52px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#eef4ff 0%,#fff5d8 100%);
            color:#173b7a;
            font-size:24px;
            box-shadow:0 10px 22px rgba(23,74,150,.12);
        }
        .cx-card__title{
            margin:0 0 4px;
            color:#173b7a;
            font-size:22px;
            line-height:1.15;
            font-weight:900;
            letter-spacing:-.02em;
        }
        .cx-card__subtitle{
            margin:0;
            color:#667085;
            font-size:13px;
            font-weight:700;
        }
        .cx-card__body{
            padding:24px 24px 24px;
            background:#fff;
        }

        .cx-notice{
            display:flex;
            align-items:flex-start;
            gap:10px;
            background:#fff9e8;
            border:1px solid #f2c95a;
            border-radius:16px;
            padding:15px 16px;
            color:#8a6500;
            font-size:13px;
            font-weight:800;
            line-height:1.55;
            margin-bottom:22px;
        }
        .cx-notice__icon{
            font-size:16px;
            line-height:1;
            margin-top:2px;
            flex-shrink:0;
        }

        .cx-section{
            font-size:12px;
            font-weight:900;
            letter-spacing:.09em;
            text-transform:uppercase;
            color:#173b7a;
            margin:24px 0 14px;
            padding-bottom:10px;
            border-bottom:1px solid #dbe5f2;
            position:relative;
        }
        .cx-section::after{
            content:'';
            position:absolute;
            left:0;
            bottom:-1px;
            width:82px;
            height:4px;
            border-radius:999px;
            background:linear-gradient(135deg,#f4c542 0%, #d6a419 100%);
        }

        .cx-dni-block{
            position:relative;
            border:2px dashed #9fb8e6;
            border-radius:20px;
            background:linear-gradient(180deg,#fbfdff 0%,#f2f7ff 100%);
            padding:40px 24px 36px;
            text-align:center;
            cursor:pointer;
            transition:all .22s ease;
            overflow:hidden;
            min-height:180px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.95), 0 10px 24px rgba(15,23,42,.04);
            margin-bottom:4px;
        }
        .cx-dni-block:hover{
            border-color:#2458a6;
            background:linear-gradient(180deg,#f7fbff 0%,#eaf2ff 100%);
            transform:translateY(-2px);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.95), 0 16px 28px rgba(23,74,150,.10);
        }
        .cx-dni-block.uploaded{
            border-style:solid;
            border-color:#86d6a5;
            background:linear-gradient(180deg,#f4fff7 0%,#e9fbef 100%);
        }
        .cx-dni-block input[type="file"]{
            position:absolute;
            inset:0;
            opacity:0;
            cursor:pointer;
            width:100%;
            height:100%;
            z-index:2;
        }
        .cx-dni-block__icon{
            font-size:42px;
            margin-bottom:12px;
            pointer-events:none;
            filter:drop-shadow(0 6px 10px rgba(23,74,150,.12));
        }
        .cx-dni-block__title{
            font-size:18px;
            font-weight:900;
            color:#173b7a;
            margin-bottom:7px;
            line-height:1.2;
            pointer-events:none;
        }
        .cx-dni-block__sub{
            font-size:12px;
            font-weight:700;
            color:#667085;
            line-height:1.55;
            max-width:680px;
            pointer-events:none;
        }
        .cx-dni-block__tags{
            margin-top:14px;
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap:8px;
            pointer-events:none;
        }
        .cx-dni-block__tag{
            display:inline-flex;
            align-items:center;
            gap:5px;
            background:#ecfdf5;
            border:1px solid #9fd4b5;
            border-radius:999px;
            padding:6px 14px;
            font-size:12px;
            font-weight:800;
            color:#15803d;
        }

        .cx-file-slot{
            display:flex;
            align-items:center;
            gap:14px;
            background:linear-gradient(180deg,#fbfdff 0%,#f4f8ff 100%);
            border:2px dashed #c3d3eb;
            border-radius:18px;
            padding:17px 18px;
            cursor:pointer;
            transition:all .22s ease;
            position:relative;
            overflow:hidden;
            margin-bottom:12px;
            min-height:86px;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.95), 0 8px 20px rgba(15,23,42,.03);
        }
        .cx-file-slot:hover{
            border-color:#2458a6;
            background:linear-gradient(180deg,#f7fbff 0%,#ebf3ff 100%);
            transform:translateY(-2px);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.95), 0 14px 24px rgba(23,74,150,.08);
        }
        .cx-file-slot.uploaded{
            border-style:solid;
            border-color:#9fd4b5;
            background:linear-gradient(180deg,#f4fff7 0%,#e9fbef 100%);
        }
        .cx-file-slot input[type="file"]{
            position:absolute;
            inset:0;
            opacity:0;
            cursor:pointer;
            width:100%;
            height:100%;
            z-index:2;
        }
        .cx-file-slot__icon{
            font-size:28px;
            flex-shrink:0;
            pointer-events:none;
            width:42px;
            text-align:center;
            filter:drop-shadow(0 4px 8px rgba(15,23,42,.10));
        }
        .cx-file-slot__info{
            flex:1;
            min-width:0;
            pointer-events:none;
        }
        .cx-file-slot__title{
            font-size:15px;
            font-weight:900;
            color:#173b7a;
            margin-bottom:4px;
            line-height:1.2;
        }
        .cx-file-slot__sub{
            font-size:12px;
            font-weight:700;
            color:#667085;
            line-height:1.45;
        }
        .cx-file-slot__name{
            font-size:12px;
            font-weight:800;
            color:#15803d;
            word-break:break-word;
            display:none;
        }
        .cx-file-slot.uploaded .cx-file-slot__sub{
            display:none;
        }
        .cx-file-slot.uploaded .cx-file-slot__name{
            display:block;
        }
        .cx-file-slot__check{
            width:30px;
            height:30px;
            border-radius:50%;
            background:#15803d;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:13px;
            font-weight:900;
            flex-shrink:0;
            opacity:0;
            transition:opacity .22s ease;
            pointer-events:none;
            box-shadow:0 8px 14px rgba(21,128,61,.18);
        }
        .cx-file-slot.uploaded .cx-file-slot__check{
            opacity:1;
        }

        .cx-cal-grid{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:12px;
        }

        .cx-doc-list{
            display:flex;
            flex-direction:column;
            gap:10px;
            margin-bottom:4px;
        }
        .cx-doc-row{
            display:flex;
            align-items:center;
            gap:12px;
            background:#f8fbff;
            border:1px solid #dbe5f2;
            border-radius:16px;
            padding:14px 15px;
            box-shadow:0 6px 16px rgba(15,23,42,.03);
        }
        .cx-doc-row__icon{
            font-size:22px;
            flex-shrink:0;
        }
        .cx-doc-row__info{
            flex:1;
            min-width:0;
        }
        .cx-doc-row__name{
            font-size:14px;
            font-weight:900;
            color:#173b7a;
            margin-bottom:4px;
        }
        .cx-doc-row__path{
            font-size:12px;
            color:#667085;
            word-break:break-all;
            font-weight:700;
        }
        .cx-doc-row__actions{
            display:flex;
            gap:8px;
            flex-shrink:0;
            flex-wrap:wrap;
        }

        .cx-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:6px;
            border:1px solid #cbd5e1;
            border-radius:14px;
            padding:10px 16px;
            font-size:13px;
            font-weight:900;
            cursor:pointer;
            background:#fff;
            color:#173b7a;
            text-decoration:none;
            transition:all .22s ease;
            white-space:nowrap;
            box-shadow:0 6px 14px rgba(15,23,42,.04);
        }
        .cx-btn:hover{
            background:#f8fbff;
            border-color:#93c5fd;
            color:#123d7f;
            text-decoration:none;
            transform:translateY(-1px);
        }
        .cx-btn--sm{
            padding:8px 12px;
            font-size:12px;
        }
        .cx-btn--danger{
            color:#b91c1c;
            border-color:#fecaca;
            background:#fff5f5;
        }
        .cx-btn--danger:hover{
            background:#feecec;
        }

        .cx-btn--save{
            border:none;
            border-radius:14px;
            padding:14px 26px;
            font-size:14px;
            font-weight:900;
            cursor:pointer;
            color:#fff;
            background:linear-gradient(135deg,#2458a6 0%,#173b7a 100%);
            box-shadow:0 14px 26px rgba(23,74,150,.24);
            transition:all .24s ease;
        }
        .cx-btn--save:hover{
            color:#fff;
            transform:translateY(-2px);
            box-shadow:0 18px 30px rgba(23,74,150,.30);
        }
        .cx-btn--save:disabled{
            opacity:.7;
            cursor:not-allowed;
            transform:none;
        }

        .cx-actions{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            margin-top:30px;
            padding-top:18px;
            border-top:1px solid #dbe5f2;
            flex-wrap:wrap;
        }

        @media (max-width: 991.98px){
            .cx-page{
                padding:24px 16px 38px;
            }
        }

        @media (max-width: 767.98px){
            .cx-card__header{
                padding:18px 16px;
            }
            .cx-card__body{
                padding:18px 16px;
            }
            .cx-card__title{
                font-size:19px;
            }
            .cx-cal-grid{
                grid-template-columns:1fr;
            }
            .cx-actions{
                flex-direction:column;
                align-items:stretch;
            }
            .cx-btn,
            .cx-btn--save{
                width:100%;
            }
            .cx-connector{
                width:38px;
            }
            .cx-step{
                min-width:84px;
            }
            .cx-dni-block{
                min-height:155px;
                padding:30px 18px 26px;
            }
        }

        @media (max-width: 480px){
            .cx-connector{
                display:none;
            }
            .cx-step{
                min-width:74px;
            }
            .cx-step__circle{
                width:38px;
                height:38px;
                font-size:12px;
            }
            .cx-step__label{
                font-size:11px;
            }
        }
    </style>

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
            <div>@foreach ($errors->all() as $e)<p style="margin:2px 0;">{{ $e }}</p>@endforeach</div>
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

        <div class="cx-card__header">
            <span class="cx-card__header-icon">📂</span>
            <div>
                <h4 class="cx-card__title">Adjuntar documentación</h4>
                <p class="cx-card__subtitle">Trámite #{{ $idTramite }} &nbsp;·&nbsp; {{ $nombreMotivo }}</p>
            </div>
        </div>

        <div class="cx-card__body">

            <div class="cx-notice">
                <span class="cx-notice__icon">💡</span>
                <span>Seleccione todos los archivos y presione <strong>Guardar documentos</strong> al final para enviarlos de una sola vez.</span>
            </div>

            <div class="cx-section">Documento de identidad</div>

            <label class="cx-dni-block {{ $dniCompleto ? 'uploaded' : '' }}" id="slot-dni">
                <input type="file" id="dni_files" accept=".pdf,.jpg,.jpeg,.png" multiple>

                <div class="cx-dni-block__icon">🪪</div>

                @if ($dniCompleto)
                    <div class="cx-dni-block__title">Identidad cargada</div>
                    <div class="cx-dni-block__sub">Haga clic para reemplazar los archivos</div>
                    <div class="cx-dni-block__tags">
                        <span class="cx-dni-block__tag">✓ {{ $dniFrente->nombre_documento ?? 'Frente' }}</span>
                        <span class="cx-dni-block__tag">✓ {{ $dniReverso->nombre_documento ?? 'Reverso' }}</span>
                    </div>
                @else
                    <div class="cx-dni-block__title">Tarjeta de identidad</div>
                    <div class="cx-dni-block__sub">Seleccione las dos fotos (frente y reverso) · PDF, JPG o PNG · máx. 10 MB c/u</div>
                    <div class="cx-dni-block__tags" id="dni-tags" style="display:none;"></div>
                @endif
            </label>

            <div class="cx-section">Documentos académicos</div>

            <label class="cx-file-slot {{ $historial ? 'uploaded' : '' }}" id="slot-historial">
                <input type="file" id="file-historial" accept=".pdf">
                <span class="cx-file-slot__icon">📋</span>
                <div class="cx-file-slot__info">
                    <div class="cx-file-slot__title">Historial académico</div>
                    <div class="cx-file-slot__sub">Solo PDF &nbsp;·&nbsp; máx. 10 MB</div>
                    <div class="cx-file-slot__name">{{ $historial->nombre_documento ?? '' }}</div>
                </div>
                <div class="cx-file-slot__check">✓</div>
            </label>

            <label class="cx-file-slot {{ $forma003 ? 'uploaded' : '' }}" id="slot-forma003">
                <input type="file" id="file-forma003" accept=".pdf,.jpg,.jpeg,.png">
                <span class="cx-file-slot__icon">📄</span>
                <div class="cx-file-slot__info">
                    <div class="cx-file-slot__title">Forma 003</div>
                    <div class="cx-file-slot__sub">PDF, JPG o PNG &nbsp;·&nbsp; máx. 10 MB</div>
                    <div class="cx-file-slot__name">{{ $forma003->nombre_documento ?? '' }}</div>
                </div>
                <div class="cx-file-slot__check">✓</div>
            </label>

            <div class="cx-section">Respaldo de la causa justificada</div>

            @if ($motivoActual === 'ENFERMEDAD_ACCIDENTE')
                <label class="cx-file-slot {{ $constanciaMedica ? 'uploaded' : '' }}" id="slot-constancia-medica">
                    <input type="file" id="file-constancia-medica" accept=".pdf,.jpg,.jpeg,.png">
                    <span class="cx-file-slot__icon">🏥</span>
                    <div class="cx-file-slot__info">
                        <div class="cx-file-slot__title">Constancia médica</div>
                        <div class="cx-file-slot__sub">PDF, JPG o PNG &nbsp;·&nbsp; máx. 10 MB</div>
                        <div class="cx-file-slot__name">{{ $constanciaMedica->nombre_documento ?? '' }}</div>
                    </div>
                    <div class="cx-file-slot__check">✓</div>
                </label>
            @endif

            @if ($motivoActual === 'PROBLEMAS_LABORALES')
                <label class="cx-file-slot {{ $constanciaLaboral ? 'uploaded' : '' }}" id="slot-constancia-laboral">
                    <input type="file" id="file-constancia-laboral" accept=".pdf,.jpg,.jpeg,.png">
                    <span class="cx-file-slot__icon">💼</span>
                    <div class="cx-file-slot__info">
                        <div class="cx-file-slot__title">Constancia laboral</div>
                        <div class="cx-file-slot__sub">PDF, JPG o PNG &nbsp;·&nbsp; máx. 10 MB</div>
                        <div class="cx-file-slot__name">{{ $constanciaLaboral->nombre_documento ?? '' }}</div>
                    </div>
                    <div class="cx-file-slot__check">✓</div>
                </label>
            @endif

            @if ($motivoActual === 'CALAMIDAD_DOMESTICA')
                <div class="cx-cal-grid">
                    @foreach ($tiposCalamidad as $tipo => $info)
                        @php $docActual = $docPorTipo($tipo); @endphp
                        <label class="cx-file-slot {{ $docActual ? 'uploaded' : '' }}" style="margin-bottom:0;">
                            <input type="file" class="calamidad-file" accept=".pdf,.jpg,.jpeg,.png" data-tipo="{{ $tipo }}">
                            <span class="cx-file-slot__icon">{{ $info['icono'] }}</span>
                            <div class="cx-file-slot__info">
                                <div class="cx-file-slot__title">{{ $info['titulo'] }}</div>
                                <div class="cx-file-slot__sub">PDF, JPG o PNG</div>
                                <div class="cx-file-slot__name">{{ $docActual->nombre_documento ?? '' }}</div>
                            </div>
                            <div class="cx-file-slot__check">✓</div>
                        </label>
                    @endforeach
                </div>
            @endif

            @if ($docs->isNotEmpty())
                <div class="cx-section">Documentos cargados</div>
                <div class="cx-doc-list">
                    @foreach ($docs as $doc)
                        <div class="cx-doc-row">
                            <span class="cx-doc-row__icon">{{ $iconoPorTipo[$doc->tipo_documento] ?? '📎' }}</span>
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
                <a href="{{ route('cancelacion.index') }}" class="cx-btn" style="text-decoration:none;">
                    ← Volver
                </a>
                <button type="button" id="btnGuardar" class="cx-btn--save">
                    Guardar documentos →
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

function alerta(tipo, msg) {
    const box = document.getElementById('cx-alert');
    box.className = 'cx-alert cx-alert--' + (tipo === 'ok' ? 'ok' : 'err') + ' show';
    document.getElementById('cx-alert-icon').textContent = tipo === 'ok' ? '✅' : '⚠️';
    document.getElementById('cx-alert-msg').innerHTML = msg;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.getElementById('dni_files')?.addEventListener('change', function () {
    const files = Array.from(this.files);
    if (!files.length) return;

    const slot = document.getElementById('slot-dni');
    const tags = document.getElementById('dni-tags');

    slot.classList.add('uploaded');

    if (tags) {
        tags.style.display = 'flex';
        tags.innerHTML = files.map(f => `<span class="cx-dni-block__tag">✓ ${f.name}</span>`).join('');
    }
});

function bindSlot(inputId, slotId) {
    const input = document.getElementById(inputId);
    const slot  = document.getElementById(slotId);
    if (!input || !slot) return;

    input.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;
        slot.classList.add('uploaded');
        const nameEl = slot.querySelector('.cx-file-slot__name');
        if (nameEl) nameEl.textContent = file.name;
    });
}

bindSlot('file-historial',          'slot-historial');
bindSlot('file-forma003',           'slot-forma003');
bindSlot('file-constancia-medica',  'slot-constancia-medica');
bindSlot('file-constancia-laboral', 'slot-constancia-laboral');

document.querySelectorAll('.calamidad-file').forEach(input => {
    input.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;
        const slot   = this.closest('.cx-file-slot');
        const nameEl = slot?.querySelector('.cx-file-slot__name');
        if (slot) slot.classList.add('uploaded');
        if (nameEl) nameEl.textContent = file.name;
    });
});

async function doUpload(endpoint, tipo, archivo) {
    const fd = new FormData();
    fd.append('tipo_documento', tipo);
    fd.append('archivo', archivo);

    const r = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    });

    const d = await r.json();
    if (!r.ok || !d.ok) throw new Error(d.mensaje || 'Error al subir ' + tipo);
    return d;
}

async function doUploadIdentidad(files) {
    if (!files || !files.length) return;

    const fd = new FormData();
    if (files[0]) fd.append('dni_frente', files[0]);
    if (files[1]) fd.append('dni_reverso', files[1]);

    const r = await fetch(urlSubirIdentidad, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    });

    const d = await r.json();
    if (!r.ok || !d.ok) throw new Error(d.mensaje || 'No se pudo subir la identidad.');
    return d;
}

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

            alerta('ok', d.mensaje || 'Documento eliminado.');
            setTimeout(() => location.reload(), 700);
        } catch (err) {
            alerta('err', err.message);
        }
    });
});

document.getElementById('btnGuardar')?.addEventListener('click', async function () {
    const btn  = this;
    const orig = btn.textContent;
    btn.textContent = 'Guardando…';
    btn.disabled = true;

    try {
        const dniFiles = document.getElementById('dni_files')?.files;
        if (dniFiles && dniFiles.length) {
            await doUploadIdentidad(dniFiles);
        }

        const historial = document.getElementById('file-historial')?.files?.[0];
        if (historial) await doUpload(urlSubirBase, 'HISTORIAL_ACADEMICO', historial);

        const forma003 = document.getElementById('file-forma003')?.files?.[0];
        if (forma003) await doUpload(urlSubirBase, 'FORMA_003', forma003);

        const cMed = document.getElementById('file-constancia-medica')?.files?.[0];
        if (cMed) await doUpload(urlSubirRiesgo, 'CONSTANCIA_MEDICA', cMed);

        const cLab = document.getElementById('file-constancia-laboral')?.files?.[0];
        if (cLab) await doUpload(urlSubirRiesgo, 'CONSTANCIA_LABORAL', cLab);

        for (const input of document.querySelectorAll('.calamidad-file')) {
            const archivo = input.files?.[0];
            if (archivo) await doUpload(urlSubirFlex, input.dataset.tipo, archivo);
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

        alerta('ok', d.mensaje || 'Documentos guardados correctamente.');
        setTimeout(() => {
            if (d.redirect) window.location.href = d.redirect;
        }, 800);

    } catch (err) {
        alerta('err', err.message);
        btn.textContent = orig;
        btn.disabled = false;
    }
});
</script>
@endsection