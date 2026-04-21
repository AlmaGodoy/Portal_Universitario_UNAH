@extends('layouts.app-estudiantes')

@section('titulo', 'Cancelación Excepcional - Finalizar')

@section('content')
@vite(['resources/css/cancelacion.css'])

<div class="cx-wrap">

    @if ($errors->any())
        <div class="cx-alert cx-alert--err show">
            <span class="cx-alert__icon">⚠️</span>
            <div>
                @foreach ($errors->all() as $error)
                    <p style="margin:2px 0;">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="cx-steps">
        <div class="cx-step cx-step--done">
            <div class="cx-step__circle">✓</div>
            <div class="cx-step__label">Datos y Motivo</div>
        </div>

        <div class="cx-connector cx-connector--done"></div>

        <div class="cx-step cx-step--done">
            <div class="cx-step__circle">✓</div>
            <div class="cx-step__label">Documentación</div>
        </div>

        <div class="cx-connector cx-connector--done"></div>

        <div class="cx-step cx-step--active">
            <div class="cx-step__circle">✓</div>
            <div class="cx-step__label">Finalizar</div>
        </div>
    </div>

    <div class="cx-card">
        <div class="cx-card__head">
            <div class="cx-card__head-icon">🎉</div>
            <div>
                <h4 class="cx-card__head-title">¡Solicitud enviada correctamente!</h4>
                <p class="cx-card__head-sub">Su trámite fue registrado con éxito</p>
            </div>
        </div>

        <div class="cx-card__body">

            <div class="cx-alert cx-alert--ok show" style="margin-bottom:18px;">
                <span class="cx-alert__icon">✅</span>
                <div>
                    {{ $mensaje ?? 'Su solicitud fue procesada correctamente.' }}
                </div>
            </div>

            <div class="cx-doc-list">
                @if(isset($tramite) && !empty($tramite->id_tramite))
                    <div class="cx-doc-row">
                        <div class="cx-doc-row__icon">📋</div>
                        <div class="cx-doc-row__info">
                            <div class="cx-doc-row__name">Número de trámite</div>
                            <div class="cx-doc-row__path">#{{ $tramite->id_tramite }}</div>
                        </div>
                    </div>
                @endif

                <div class="cx-doc-row">
                    <div class="cx-doc-row__icon">📧</div>
                    <div class="cx-doc-row__info">
                        <div class="cx-doc-row__name">Notificación</div>
                        <div class="cx-doc-row__path">Recibirá una notificación cuando la facultad emita su dictamen.</div>
                    </div>
                </div>

                <div class="cx-doc-row">
                    <div class="cx-doc-row__icon">⏱️</div>
                    <div class="cx-doc-row__info">
                        <div class="cx-doc-row__name">Tiempo estimado</div>
                        <div class="cx-doc-row__path">3 días hábiles.</div>
                    </div>
                </div>

                <div class="cx-doc-row">
                    <div class="cx-doc-row__icon">📌</div>
                    <div class="cx-doc-row__info">
                        <div class="cx-doc-row__name">Seguimiento</div>
                        <div class="cx-doc-row__path">Puede consultar el estado en la sección Mis trámites.</div>
                    </div>
                </div>
            </div>

            <div class="cx-actions" style="justify-content:center; margin-top: 28px;">
                <a href="/cancelacion/nueva" class="cx-btn">
                    Nueva solicitud
                </a>

                <a href="/dashboard" class="cx-btn cx-btn--primary">
                    Ir al dashboard
                </a>

                <a href="/mis-tramites" class="cx-btn">
                    Ver mis trámites
                </a>
            </div>

        </div>
    </div>
</div>
@endsection