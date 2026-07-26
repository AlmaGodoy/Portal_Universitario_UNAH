@extends('layouts.app-estudiantes')

@section('titulo', 'Mensajes enviados')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mensajes.css') }}">
@endpush

@section('content')
<div class="mensajes-page">

    @if (session('success'))
        <div class="mensajes-alert mensajes-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="mensajes-alert-close" data-alert-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="mensajes-header">
        <div>
            <span class="mensajes-header-label">Comunicación interna</span>
            <h1>Mensajes enviados</h1>
            <p>Consulta los mensajes que has enviado desde PumaGestión.</p>
        </div>

        <a href="{{ route('mensajes.create') }}" class="mensajes-btn mensajes-btn-primary">
            <i class="fas fa-pen-to-square"></i>
            <span>Nuevo mensaje</span>
        </a>
    </div>

    <div class="mensajes-layout">

        <aside class="mensajes-sidebar">
            <a href="{{ route('mensajes.create') }}" class="mensajes-compose-button">
                <i class="fas fa-plus"></i>
                <span>Redactar mensaje</span>
            </a>

            <nav class="mensajes-navigation">
                <a href="{{ route('mensajes.index') }}" class="mensajes-navigation-item">
                    <span>
                        <i class="fas fa-inbox"></i>
                        Bandeja de entrada
                    </span>
                </a>

                <a href="{{ route('mensajes.enviados') }}" class="mensajes-navigation-item active">
                    <span>
                        <i class="fas fa-paper-plane"></i>
                        Enviados
                    </span>
                </a>
            </nav>
        </aside>

        <main class="mensajes-panel">

            <div class="mensajes-toolbar">
                <div>
                    <h2>Mensajes enviados</h2>
                    <span>{{ $mensajes->total() }} mensaje(s)</span>
                </div>

                <form method="GET" action="{{ route('mensajes.enviados') }}" class="mensajes-search-form">
                    <div class="mensajes-search">
                        <i class="fas fa-search"></i>

                        <input type="text"
                               name="buscar"
                               value="{{ $buscar }}"
                               placeholder="Buscar mensaje enviado"
                               autocomplete="off">

                        @if ($buscar !== '')
                            <a href="{{ route('mensajes.enviados') }}" class="mensajes-search-clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="mensajes-btn mensajes-btn-secondary">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="mensajes-list">
                @forelse ($mensajes as $mensaje)
                    @php
                        $nombreDestinatario =
                            optional(optional($mensaje->destinatario)->persona)->nombre_persona
                            ?? optional($mensaje->destinatario)->name
                            ?? optional($mensaje->destinatario)->email
                            ?? 'Usuario';

                        $partes = preg_split('/\s+/', trim($nombreDestinatario), -1, PREG_SPLIT_NO_EMPTY);
                        $iniciales = '';

                        foreach (array_slice($partes ?: [], 0, 2) as $parte) {
                            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
                        }

                        if ($iniciales === '') {
                            $iniciales = 'U';
                        }
                    @endphp

                    <article class="mensaje-row">

                        <a href="{{ route('mensajes.show', $mensaje->id_mensaje) }}" class="mensaje-row-main">
                            <div class="mensaje-avatar">
                                {{ $iniciales }}
                            </div>

                            <div class="mensaje-row-content">
                                <div class="mensaje-row-top">
                                    <div class="mensaje-sender">
                                        Para: {{ $nombreDestinatario }}
                                    </div>

                                    <time>
                                        {{ optional($mensaje->fecha_creacion)->format('d/m/Y h:i A') }}
                                    </time>
                                </div>

                                <div class="mensaje-subject">
                                    {{ $mensaje->asunto }}
                                </div>

                                <div class="mensaje-preview">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($mensaje->contenido), 140) }}
                                </div>

                                <div class="mensaje-delivery-status">
                                    @if ($mensaje->leido)
                                        <span class="status-read">
                                            <i class="fas fa-check-double"></i>
                                            Leído
                                        </span>
                                    @else
                                        <span class="status-pending">
                                            <i class="fas fa-check"></i>
                                            Enviado
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>

                        <form method="POST"
                              action="{{ route('mensajes.destroy', $mensaje->id_mensaje) }}"
                              class="mensaje-delete-form"
                              data-delete-message>
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="mensaje-action-button" title="Eliminar de enviados">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="mensajes-empty">
                        <div class="mensajes-empty-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>

                        <h3>No tienes mensajes enviados</h3>
                        <p>Cuando envíes un mensaje aparecerá en esta sección.</p>

                        <a href="{{ route('mensajes.create') }}" class="mensajes-btn mensajes-btn-primary">
                            Redactar mensaje
                        </a>
                    </div>
                @endforelse
            </div>

            @if ($mensajes->hasPages())
                <div class="mensajes-pagination">
                    {{ $mensajes->links('pagination::bootstrap-4') }}
                </div>
            @endif

        </main>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/mensajes.js') }}"></script>
@endpush