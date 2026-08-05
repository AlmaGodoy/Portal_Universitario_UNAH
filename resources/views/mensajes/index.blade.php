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

@section('titulo', 'Mensajes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mensajes.css') }}">
@endpush

@section('content')
<div class="mensajes-page">

    @if (session('success'))
        <div class="mensajes-alert mensajes-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>

            <button type="button"
                    class="mensajes-alert-close"
                    data-alert-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="mensajes-header">
        <div class="mensajes-header-copy">
            <span class="mensajes-header-label">Comunicación interna</span>
            <h1>Bandeja de entrada</h1>
            <p>Consulta los mensajes recibidos dentro del sistema PumaGestión.</p>
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
                <a href="{{ $mensajesIndexUrl }}" class="mensajes-navigation-item active">
                    <span>
                        <i class="fas fa-inbox"></i>
                        Bandeja de entrada
                    </span>

                    @if (($noLeidos ?? 0) > 0)
                        <span class="mensajes-navigation-badge">
                            {{ $noLeidos > 99 ? '99+' : $noLeidos }}
                        </span>
                    @endif
                </a>

                <a href="{{ $mensajesEnviadosUrl }}" class="mensajes-navigation-item">
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
                        Los mensajes solo pueden ser revisados por el remitente y el destinatario.
                    </p>
                </div>
            </div>
        </aside>

        <main class="mensajes-panel">

            <div class="mensajes-toolbar">
                <div>
                    <h2>Mensajes recibidos</h2>
                    <span>{{ $mensajes->total() }} mensaje(s)</span>
                </div>

                <form method="GET"
                      action="{{ $mensajesIndexUrl }}"
                      class="mensajes-search-form">

                    <div class="mensajes-search">
                        <i class="fas fa-search"></i>

                        <input type="text"
                               name="buscar"
                               value="{{ $buscar ?? '' }}"
                               placeholder="Buscar mensaje"
                               autocomplete="off">

                        @if (($buscar ?? '') !== '')
                            <a href="{{ $mensajesIndexUrl }}"
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
                        $nombreRemitente =
                            optional(optional($mensaje->remitente)->persona)->nombre_persona
                            ?? optional($mensaje->remitente)->name
                            ?? optional($mensaje->remitente)->email
                            ?? 'Usuario';

                        $partes = preg_split(
                            '/\s+/',
                            trim($nombreRemitente),
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

                    <article class="mensaje-row {{ !$mensaje->leido ? 'is-unread' : '' }}">

                        <a href="{{ $mensajeShowUrl }}" class="mensaje-row-main">

                            <div class="mensaje-avatar">
                                {{ $iniciales }}
                            </div>

                            <div class="mensaje-row-content">
                                <div class="mensaje-row-top">
                                    <div class="mensaje-sender">
                                        {{ $nombreRemitente }}

                                        @if (!$mensaje->leido)
                                            <span class="mensaje-unread-indicator">
                                                Nuevo
                                            </span>
                                        @endif
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
                                    title="Eliminar de mi bandeja">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="mensajes-empty">
                        <div class="mensajes-empty-icon">
                            <i class="fas fa-inbox"></i>
                        </div>

                        @if (($buscar ?? '') !== '')
                            <h3>No se encontraron mensajes</h3>
                            <p>No existen resultados para “{{ $buscar }}”.</p>

                            <a href="{{ $mensajesIndexUrl }}"
                               class="mensajes-btn mensajes-btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @else
                            <h3>Tu bandeja está vacía</h3>
                            <p>Cuando recibas mensajes aparecerán en esta sección.</p>
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