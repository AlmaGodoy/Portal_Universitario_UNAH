@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $usuario = auth()->user();
    $idRolActual = (int) ($usuario->id_rol ?? 0);

    $layout = match ($idRolActual) {
        1 => 'layouts.app-secretaria-academica',
        4 => 'layouts.app-coordinador',
        5 => 'layouts.app-secretaria',
        default => 'layouts.app-estudiantes',
    };

    $dashboardUrl = match ($idRolActual) {
        1, 4, 5 => Route::has('empleado.dashboard')
            ? route('empleado.dashboard')
            : url('/empleado/dashboard'),

        default => Route::has('dashboard')
            ? route('dashboard')
            : url('/dashboard'),
    };

    $mensajesIndexUrl = Route::has('mensajes.index')
        ? route('mensajes.index')
        : url('/mensajes');

    $mensajesEnviadosUrl = Route::has('mensajes.enviados')
        ? route('mensajes.enviados')
        : url('/mensajes/enviados');

    $mensajesCreateUrl = Route::has('mensajes.create')
        ? route('mensajes.create')
        : url('/mensajes/crear');
@endphp

@extends($layout)

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

    {{-- Encabezado --}}
    <div class="mensajes-header">
        <div class="mensajes-header-copy">
            <span class="mensajes-header-label">Comunicación interna</span>
            <h1>Mensajes enviados</h1>
            <p>Consulta los mensajes que has enviado desde PumaGestión.</p>
        </div>

        <div class="mensajes-header-actions">
            <a href="{{ $dashboardUrl }}" class="mensajes-back-btn">
                <i class="fas fa-arrow-left"></i>
                Volver al dashboard
            </a>

            <a href="{{ $mensajesCreateUrl }}" class="mensajes-btn mensajes-btn-primary">
                <i class="fas fa-pen-to-square"></i>
                <span>Nuevo mensaje</span>
            </a>
        </div>
    </div>

    <div class="mensajes-layout">

        <aside class="mensajes-sidebar">
            <a href="{{ $mensajesCreateUrl }}" class="mensajes-compose-button">
                <i class="fas fa-plus"></i>
                <span>Redactar mensaje</span>
            </a>

            <nav class="mensajes-navigation">
                <a href="{{ $mensajesIndexUrl }}" class="mensajes-navigation-item">
                    <span>
                        <i class="fas fa-inbox"></i>
                        Bandeja de entrada
                    </span>
                </a>

                <a href="{{ $mensajesEnviadosUrl }}" class="mensajes-navigation-item active">
                    <span>
                        <i class="fas fa-paper-plane"></i>
                        Enviados
                    </span>
                </a>
            </nav>

            <div class="mensajes-sidebar-info">
                <div class="mensajes-sidebar-info-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <div>
                    <strong>Mensajería interna</strong>
                    <p>
                        Los mensajes enviados solo pueden ser revisados por el remitente y el destinatario.
                    </p>
                </div>
            </div>
        </aside>

        <main class="mensajes-panel">

            <div class="mensajes-toolbar">
                <div>
                    <h2>Mensajes enviados</h2>
                    <span>{{ $mensajes->total() }} mensaje(s)</span>
                </div>

                <form method="GET"
                      action="{{ $mensajesEnviadosUrl }}"
                      class="mensajes-search-form">

                    <div class="mensajes-search">
                        <i class="fas fa-search"></i>

                        <input type="text"
                               name="buscar"
                               value="{{ $buscar ?? '' }}"
                               placeholder="Buscar mensaje enviado"
                               autocomplete="off">

                        @if (($buscar ?? '') !== '')
                            <a href="{{ $mensajesEnviadosUrl }}"
                               class="mensajes-search-clear"
                               title="Limpiar búsqueda">
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

                        $partes = preg_split(
                            '/\s+/',
                            trim($nombreDestinatario),
                            -1,
                            PREG_SPLIT_NO_EMPTY
                        );

                        $iniciales = '';

                        foreach (array_slice($partes ?: [], 0, 2) as $parte) {
                            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
                        }

                        if ($iniciales === '') {
                            $iniciales = 'U';
                        }

                        $mensajeShowUrl = Route::has('mensajes.show')
                            ? route('mensajes.show', $mensaje->id_mensaje)
                            : url('/mensajes/' . $mensaje->id_mensaje);

                        $mensajeDestroyUrl = Route::has('mensajes.destroy')
                            ? route('mensajes.destroy', $mensaje->id_mensaje)
                            : url('/mensajes/' . $mensaje->id_mensaje);
                    @endphp

                    <article class="mensaje-row">

                        <a href="{{ $mensajeShowUrl }}" class="mensaje-row-main">

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
                                    {{ Str::limit(strip_tags($mensaje->contenido), 140) }}
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
                              action="{{ $mensajeDestroyUrl }}"
                              class="mensaje-delete-form"
                              data-delete-message>

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="mensaje-action-button"
                                    title="Eliminar de enviados">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="mensajes-empty">
                        <div class="mensajes-empty-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>

                        @if (($buscar ?? '') !== '')
                            <h3>No se encontraron mensajes enviados</h3>
                            <p>No existen resultados para “{{ $buscar }}”.</p>

                            <a href="{{ $mensajesEnviadosUrl }}"
                               class="mensajes-btn mensajes-btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @else
                            <h3>No tienes mensajes enviados</h3>
                            <p>Cuando envíes un mensaje aparecerá en esta sección.</p>

                            <a href="{{ $mensajesCreateUrl }}"
                               class="mensajes-btn mensajes-btn-primary">
                                Redactar mensaje
                            </a>
                        @endif
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