@extends('layouts.app-secretaria')

@section('titulo', 'Mi perfil')

@push('styles')
<style>
    .secretaria-profile-page {
        padding: 10px 0 28px;
    }

    .secretaria-profile-hero {
        position: relative;
        overflow: hidden;
        min-height: 190px;
        padding: 28px;
        border-radius: 22px;
        background:
            radial-gradient(circle at 88% 10%, rgba(255, 210, 31, .24), transparent 31%),
            linear-gradient(135deg, #10346f 0%, #1c4f9d 56%, #173f7e 100%);
        border-bottom: 4px solid #ffd21f;
        box-shadow: 0 16px 34px rgba(8, 35, 78, .18);
        color: #fff;
    }

    .secretaria-profile-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,.12), transparent 38%),
            repeating-linear-gradient(
                135deg,
                transparent 0,
                transparent 32px,
                rgba(255,255,255,.025) 32px,
                rgba(255,255,255,.025) 34px
            );
        pointer-events: none;
    }

    .secretaria-profile-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .secretaria-profile-avatar {
        width: 104px;
        height: 104px;
        flex: 0 0 104px;
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffe681 0%, #ffd21f 100%);
        color: #123674;
        border: 4px solid rgba(255,255,255,.32);
        box-shadow: 0 14px 28px rgba(0,0,0,.22);
        font-size: 34px;
        font-weight: 900;
        letter-spacing: 1px;
    }

    .secretaria-profile-copy {
        min-width: 0;
    }

    .secretaria-profile-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 9px;
        padding: 6px 11px;
        border-radius: 999px;
        background: rgba(255, 210, 31, .17);
        border: 1px solid rgba(255, 210, 31, .34);
        color: #ffe16b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .secretaria-profile-name {
        margin: 0;
        color: #fff;
        font-size: clamp(1.65rem, 3vw, 2.35rem);
        font-weight: 900;
        line-height: 1.12;
        word-break: break-word;
    }

    .secretaria-profile-role {
        margin: 8px 0 0;
        color: rgba(255,255,255,.82);
        font-size: 14px;
        font-weight: 700;
    }

    .secretaria-profile-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(290px, .65fr);
        gap: 20px;
        margin-top: 22px;
    }

    .secretaria-profile-card {
        overflow: hidden;
        border: 1px solid #dde7f5;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(23, 52, 108, .10);
    }

    .secretaria-profile-card-header {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 18px 20px;
        background: linear-gradient(180deg, #fbfdff 0%, #f5f8fd 100%);
        border-bottom: 1px solid #e3ebf7;
    }

    .secretaria-profile-card-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(28, 79, 157, .12);
        color: #1c4f9d;
        font-size: 16px;
    }

    .secretaria-profile-card-title {
        margin: 0;
        color: #123674;
        font-size: 16px;
        font-weight: 900;
    }

    .secretaria-profile-card-subtitle {
        margin: 3px 0 0;
        color: #7b8799;
        font-size: 12px;
        font-weight: 700;
    }

    .secretaria-profile-card-body {
        padding: 20px;
    }

    .secretaria-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .secretaria-data-item {
        min-width: 0;
        padding: 15px;
        border: 1px solid #e3ebf7;
        border-radius: 15px;
        background: #f9fbfe;
    }

    .secretaria-data-item.full {
        grid-column: 1 / -1;
    }

    .secretaria-data-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
        color: #718099;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .secretaria-data-label i {
        color: #1c4f9d;
    }

    .secretaria-data-value {
        color: #22304a;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .secretaria-status-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        border-radius: 15px;
        background: rgba(39, 174, 96, .09);
        border: 1px solid rgba(39, 174, 96, .19);
    }

    .secretaria-status-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #27ae60;
        color: #fff;
    }

    .secretaria-status-copy strong,
    .secretaria-status-copy span {
        display: block;
    }

    .secretaria-status-copy strong {
        color: #166534;
        font-size: 14px;
        font-weight: 900;
    }

    .secretaria-status-copy span {
        margin-top: 2px;
        color: #4c6b58;
        font-size: 12px;
        font-weight: 700;
    }

    .secretaria-profile-note {
        margin-top: 15px;
        padding: 14px;
        border-radius: 14px;
        background: rgba(255, 210, 31, .12);
        border: 1px solid rgba(255, 210, 31, .28);
        color: #6d5700;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .secretaria-back-button {
        width: 100%;
        min-height: 44px;
        margin-top: 16px;
        border: none;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        background: linear-gradient(135deg, #123674 0%, #1c4f9d 100%);
        color: #fff !important;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 9px 18px rgba(18, 54, 116, .18);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .secretaria-back-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 23px rgba(18, 54, 116, .24);
    }

    @media (max-width: 991.98px) {
        .secretaria-profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .secretaria-profile-hero {
            padding: 22px 18px;
        }

        .secretaria-profile-hero-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .secretaria-profile-avatar {
            width: 86px;
            height: 86px;
            flex-basis: 86px;
            border-radius: 24px;
            font-size: 28px;
        }

        .secretaria-data-grid {
            grid-template-columns: 1fr;
        }

        .secretaria-data-item.full {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
@php
    $persona = $usuario->persona ?? null;

    $nombreCompleto = trim(
        data_get($persona, 'nombre_persona')
        ?? data_get($usuario, 'nombre_persona')
        ?? data_get($usuario, 'name')
        ?? 'Secretaría de Carrera'
    );

    $correo = data_get($usuario, 'email')
        ?? data_get($usuario, 'correo_institucional')
        ?? data_get($persona, 'correo_institucional')
        ?? 'No registrado';

    $identidad = data_get($persona, 'numero_identidad')
        ?? data_get($persona, 'identidad')
        ?? data_get($persona, 'dni')
        ?? 'No registrado';

    $telefono = data_get($persona, 'telefono')
        ?? data_get($persona, 'telefono_persona')
        ?? data_get($usuario, 'telefono')
        ?? 'No registrado';

    $direccion = data_get($persona, 'direccion')
        ?? data_get($persona, 'direccion_persona')
        ?? 'No registrada';

    $idUsuario = data_get($usuario, 'id_usuario')
        ?? data_get($usuario, 'id')
        ?? 'No disponible';

    $estadoRaw = data_get($usuario, 'estado_activo')
        ?? data_get($usuario, 'estado')
        ?? 1;

    $estadoActivo = in_array($estadoRaw, [1, '1', true, 'ACTIVO', 'Activo', 'activo'], true);

    $partesNombre = preg_split('/\s+/', $nombreCompleto);
    $inicialesPerfil = '';

    foreach (array_slice($partesNombre, 0, 2) as $parte) {
        if ($parte !== '') {
            $inicialesPerfil .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
    }

    if ($inicialesPerfil === '') {
        $inicialesPerfil = 'SC';
    }

    $inicioUrl = Route::has('empleado.dashboard')
        ? route('empleado.dashboard')
        : url('/');
@endphp

<div class="secretaria-profile-page">
    <section class="secretaria-profile-hero">
        <div class="secretaria-profile-hero-content">
            <div class="secretaria-profile-avatar">
                {{ $inicialesPerfil }}
            </div>

            <div class="secretaria-profile-copy">
                <span class="secretaria-profile-label">
                    <i class="fas fa-shield-halved"></i>
                    Cuenta institucional
                </span>

                <h1 class="secretaria-profile-name">{{ $nombreCompleto }}</h1>
                <p class="secretaria-profile-role">Secretaría de Carrera · FCEAC · UNAH</p>
            </div>
        </div>
    </section>

    <div class="secretaria-profile-grid">
        <section class="secretaria-profile-card">
            <div class="secretaria-profile-card-header">
                <span class="secretaria-profile-card-icon">
                    <i class="fas fa-address-card"></i>
                </span>
                <div>
                    <h2 class="secretaria-profile-card-title">Información personal</h2>
                    <p class="secretaria-profile-card-subtitle">Datos asociados a la cuenta autenticada</p>
                </div>
            </div>

            <div class="secretaria-profile-card-body">
                <div class="secretaria-data-grid">
                    <div class="secretaria-data-item full">
                        <div class="secretaria-data-label">
                            <i class="fas fa-user"></i>
                            Nombre completo
                        </div>
                        <div class="secretaria-data-value">{{ $nombreCompleto }}</div>
                    </div>

                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-envelope"></i>
                            Correo institucional
                        </div>
                        <div class="secretaria-data-value">{{ $correo }}</div>
                    </div>

                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-id-card"></i>
                            Identidad
                        </div>
                        <div class="secretaria-data-value">{{ $identidad }}</div>
                    </div>

                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-phone"></i>
                            Teléfono
                        </div>
                        <div class="secretaria-data-value">{{ $telefono }}</div>
                    </div>

                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-user-tag"></i>
                            Rol
                        </div>
                        <div class="secretaria-data-value">Secretaría de Carrera</div>
                    </div>

                    <div class="secretaria-data-item full">
                        <div class="secretaria-data-label">
                            <i class="fas fa-location-dot"></i>
                            Dirección
                        </div>
                        <div class="secretaria-data-value">{{ $direccion }}</div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="secretaria-profile-card">
            <div class="secretaria-profile-card-header">
                <span class="secretaria-profile-card-icon">
                    <i class="fas fa-user-gear"></i>
                </span>
                <div>
                    <h2 class="secretaria-profile-card-title">Información de la cuenta</h2>
                    <p class="secretaria-profile-card-subtitle">Estado y acceso al sistema</p>
                </div>
            </div>

            <div class="secretaria-profile-card-body">
                <div class="secretaria-status-box">
                    <span class="secretaria-status-icon">
                        <i class="fas {{ $estadoActivo ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                    </span>
                    <div class="secretaria-status-copy">
                        <strong>{{ $estadoActivo ? 'Cuenta activa' : 'Cuenta inactiva' }}</strong>
                        <span>
                            {{ $estadoActivo
                                ? 'La cuenta tiene acceso autorizado al sistema.'
                                : 'La cuenta no posee acceso activo actualmente.' }}
                        </span>
                    </div>
                </div>

                <div class="secretaria-data-grid" style="grid-template-columns: 1fr; margin-top: 14px;">
                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-hashtag"></i>
                            Identificador de usuario
                        </div>
                        <div class="secretaria-data-value">{{ $idUsuario }}</div>
                    </div>

                    <div class="secretaria-data-item">
                        <div class="secretaria-data-label">
                            <i class="fas fa-building-columns"></i>
                            Institución
                        </div>
                        <div class="secretaria-data-value">Universidad Nacional Autónoma de Honduras</div>
                    </div>
                </div>

                <div class="secretaria-profile-note">
                    <i class="fas fa-circle-info"></i>
                    La información mostrada proviene de la cuenta institucional autenticada. Los cambios de datos personales deben realizarse mediante el módulo administrativo autorizado.
                </div>

                <a href="{{ $inicioUrl }}" class="secretaria-back-button">
                    <i class="fas fa-arrow-left"></i>
                    Volver al inicio
                </a>
            </div>
        </aside>
    </div>
</div>
@endsection
