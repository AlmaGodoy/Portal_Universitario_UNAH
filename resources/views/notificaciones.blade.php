@extends('layouts.app-estudiantes')

@section('titulo', 'Notificaciones')

@section('content')

<style>
    .notifications-page {
        padding: 6px 0 28px;
    }

    .notifications-hero {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 26px 30px;
        margin-bottom: 18px;
        background:
            radial-gradient(circle at 92% 0%, rgba(255, 210, 31, 0.20), transparent 32%),
            linear-gradient(135deg, #123674 0%, #1c4f9d 58%, #174487 100%);
        color: #ffffff;
        box-shadow: 0 12px 28px rgba(8, 35, 78, 0.18);
        border-bottom: 4px solid #ffd21f;
    }

    .notifications-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,0.11), transparent 42%),
            linear-gradient(135deg, transparent 0 58%, rgba(9, 43, 105, 0.20) 58% 68%, transparent 68%);
        pointer-events: none;
    }

    .notifications-hero-content {
        position: relative;
        z-index: 2;
    }

    .notifications-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.20);
        color: #ffd21f;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .notifications-hero h1 {
        margin: 0 0 6px;
        font-size: 28px;
        font-weight: 900;
        color: #ffffff;
    }

    .notifications-hero p {
        margin: 0;
        max-width: 760px;
        color: rgba(255,255,255,0.85);
        font-size: 14px;
        line-height: 1.5;
    }

    .notifications-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2eaf6;
        box-shadow: 0 12px 28px rgba(8, 35, 78, 0.10);
        overflow: hidden;
    }

    .notifications-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e3ebf7;
        background: #f7faff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .notifications-card-header h2 {
        margin: 0;
        color: #123674;
        font-size: 18px;
        font-weight: 900;
    }

    .notifications-count {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(28, 79, 157, 0.10);
        color: #1c4f9d;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .notifications-list {
        list-style: none;
        margin: 0;
        padding: 10px;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid transparent;
        transition: all .18s ease;
        margin-bottom: 8px;
        background: #ffffff;
    }

    .notification-item:hover {
        background: #f6f9ff;
        border-color: #e2eaf6;
        transform: translateY(-1px);
    }

    .notification-item.unread {
        background: #f4f7fc;
        border-color: #dce7f7;
    }

    .notification-icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .notification-icon.blue {
        background: rgba(28, 79, 157, .12);
        color: #1c4f9d;
    }

    .notification-icon.gold {
        background: rgba(255, 210, 31, .20);
        color: #8a6500;
    }

    .notification-icon.green {
        background: rgba(39, 174, 96, .13);
        color: #198754;
    }

    .notification-icon.red {
        background: rgba(198, 40, 40, .13);
        color: #c62828;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
    }

    .notification-content h3 {
        margin: 0;
        color: #123674;
        font-size: 15px;
        font-weight: 900;
    }

    .notification-content p {
        margin: 0 0 6px;
        color: #5d6b82;
        font-size: 13px;
        line-height: 1.45;
    }

    .notification-date {
        color: #8a96a8;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .notification-unread-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #e63946;
        flex: 0 0 9px;
        margin-top: 8px;
        box-shadow: 0 0 0 4px rgba(230, 57, 70, 0.10);
    }

    .notification-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border-radius: 10px;
        border: 1px solid rgba(28, 79, 157, 0.24);
        color: #1c4f9d;
        background: #ffffff;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        transition: all .18s ease;
    }

    .notification-action:hover {
        background: #1c4f9d;
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .notifications-empty {
        padding: 36px 20px;
        text-align: center;
        color: #6b7890;
    }

    .notifications-empty i {
        font-size: 34px;
        color: #9aa7bb;
        margin-bottom: 10px;
    }

    .notifications-empty h3 {
        color: #123674;
        font-weight: 900;
        font-size: 17px;
        margin-bottom: 5px;
    }

    .notifications-empty p {
        margin: 0;
        font-size: 13px;
    }

    .notifications-pagination {
        padding: 12px 18px 18px;
    }

    .notifications-pagination nav {
        margin: 0;
    }

    @media (max-width: 768px) {
        .notifications-hero {
            padding: 22px 20px;
        }

        .notifications-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .notification-title-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
        }

        .notification-date {
            white-space: normal;
        }
    }
</style>

<div class="notifications-page">

    <div class="notifications-hero">
        <div class="notifications-hero-content">
            <div class="notifications-hero-badge">
                <i class="fas fa-bell"></i>
                Centro de notificaciones
            </div>

            <h1>Mis notificaciones</h1>

            <p>
                Aquí puedes revisar avisos relacionados con tus trámites académicos,
                observaciones emitidas, cambios de estado y mensajes importantes del sistema.
            </p>
        </div>
    </div>

    <div class="notifications-card">
        <div class="notifications-card-header">
            <h2>Historial de notificaciones</h2>

            <div class="notifications-count">
                <i class="fas fa-list"></i>
                {{ $notificaciones->total() }} notificación{{ $notificaciones->total() == 1 ? '' : 'es' }}
            </div>
        </div>

        @if($notificaciones->count() > 0)
            <ul class="notifications-list">
                @foreach($notificaciones as $notificacion)
                    @php
                        $color = $notificacion->color ?? 'blue';
                        $icono = $notificacion->icono ?? 'fas fa-bell';

                        if (!in_array($color, ['blue', 'gold', 'green', 'red'])) {
                            $color = 'blue';
                        }
                    @endphp

                    <li class="notification-item {{ !$notificacion->leida ? 'unread' : '' }}">
                        <div class="notification-icon {{ $color }}">
                            <i class="{{ $icono }}"></i>
                        </div>

                        <div class="notification-content">
                            <div class="notification-title-row">
                                <h3>{{ $notificacion->titulo }}</h3>

                                <span class="notification-date">
                                    {{ optional($notificacion->fecha_creacion)->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <p>{{ $notificacion->mensaje }}</p>

                            @if(!empty($notificacion->url_destino))
                                <a href="{{ route('notificaciones.abrir', $notificacion->id_notificacion) }}"
                                   class="notification-action">
                                    <i class="fas fa-arrow-right"></i>
                                    Abrir detalle
                                </a>
                            @endif
                        </div>

                        @if(!$notificacion->leida)
                            <span class="notification-unread-dot" title="No leída"></span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="notifications-pagination">
                {{ $notificaciones->links() }}
            </div>
        @else
            <div class="notifications-empty">
                <i class="fas fa-bell-slash"></i>
                <h3>No tienes notificaciones</h3>
                <p>Cuando haya cambios en tus trámites, aparecerán en esta bandeja.</p>
            </div>
        @endif
    </div>

</div>

@endsection