@extends('portal_login')

@section('content')
@vite(['resources/css/cancelacion.css'])

<div class="puma-page">

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

        {{-- Stepper — completado --}}
        <div class="puma-steps">
            <div class="puma-step puma-step--done">
                <div class="puma-step__circle">✓</div>
                <div class="puma-step__label">Datos y Motivo</div>
            </div>
            <div class="puma-step-connector puma-step-connector--done"></div>
            <div class="puma-step puma-step--done">
                <div class="puma-step__circle">✓</div>
                <div class="puma-step__label">Documentación</div>
            </div>
            <div class="puma-step-connector puma-step-connector--done"></div>
            <div class="puma-step puma-step--active">
                <div class="puma-step__circle">✓</div>
                <div class="puma-step__label">Finalizar</div>
            </div>
        </div>

        <div class="puma-card puma-card--success animate-in">
            <div class="puma-success-icon">🎉</div>
            <h3 class="puma-success-title">¡Solicitud Enviada!</h3>
            <p class="puma-success-msg">{{ $mensaje ?? 'Su solicitud fue procesada correctamente.' }}</p>

            <div class="puma-success-info">
                <p>📧 Recibirá una notificación cuando la facultad emita su dictamen.</p>
                <p>⏱️ Tiempo estimado de respuesta: <strong>3 días hábiles</strong></p>
                <p>📋 Puede consultar el estado en la sección <strong>Gestión de Trámites</strong></p>
            </div>

            <div class="puma-actions-center" style="margin-top:30px;">
                <a href="{{ route('cancelacion.index') }}" class="puma-btn">
                    Nueva Solicitud
                </a>
                <a href="{{ url('/dashboard') }}" class="puma-btn-outline" style="margin-left:12px;">
                    Ir al Dashboard
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
