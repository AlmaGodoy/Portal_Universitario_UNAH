@extends('estudiante')

@section('titulo', 'Conversación')

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
            <span class="mensajes-header-label">Conversación</span>
            <h1>{{ $mensajePrincipal->asunto }}</h1>
            <p>Consulta el mensaje y responde dentro de la misma conversación.</p>
        </div>

        <a href="{{ route('mensajes.index') }}" class="mensajes-btn mensajes-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a mensajes</span>
        </a>
    </div>

    <div class="mensajes-conversation-card">

        <div class="mensajes-conversation-header">
            <div>
                <span>Asunto</span>
                <h2>{{ $mensajePrincipal->asunto }}</h2>
            </div>

            <div class="mensajes-conversation-count">
                <i class="fas fa-comments"></i>
                {{ $conversacion->count() }}
                {{ $conversacion->count() === 1 ? 'mensaje' : 'mensajes' }}
            </div>
        </div>

        <div class="mensajes-conversation" id="mensajesConversation">

            @foreach ($conversacion as $item)
                @php
                    $idUsuarioActual = (int) (auth()->user()->id_usuario ?? 0);
                    $esPropio = (int) $item->id_remitente === $idUsuarioActual;

                    $nombreRemitente =
                        optional(optional($item->remitente)->persona)->nombre_persona
                        ?? optional($item->remitente)->name
                        ?? optional($item->remitente)->email
                        ?? 'Usuario';

                    $partesNombre = preg_split('/\s+/', trim($nombreRemitente), -1, PREG_SPLIT_NO_EMPTY);
                    $iniciales = '';

                    foreach (array_slice($partesNombre ?: [], 0, 2) as $parte) {
                        $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
                    }

                    if ($iniciales === '') {
                        $iniciales = 'U';
                    }
                @endphp

                <div class="conversation-message {{ $esPropio ? 'is-own' : 'is-other' }}">

                    <div class="conversation-avatar">
                        {{ $iniciales }}
                    </div>

                    <div class="conversation-message-content">
                        <div class="conversation-message-meta">
                            <strong>{{ $esPropio ? 'Tú' : $nombreRemitente }}</strong>

                            <time>
                                {{ optional($item->fecha_creacion)->format('d/m/Y h:i A') }}
                            </time>
                        </div>

                        <div class="conversation-bubble">
                            {!! nl2br(e($item->contenido)) !!}
                        </div>

                        @if ($esPropio)
                            <div class="conversation-status">
                                @if ($item->leido)
                                    <i class="fas fa-check-double"></i>
                                    Leído
                                @else
                                    <i class="fas fa-check"></i>
                                    Enviado
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>

        <div class="mensajes-reply">
            <div class="mensajes-reply-title">
                <i class="fas fa-reply"></i>
                <div>
                    <strong>Responder</strong>
                    <span>Escribe tu respuesta para continuar la conversación.</span>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('mensajes.responder', $mensajePrincipal->id_mensaje) }}"
                  data-message-form>

                @csrf

                <textarea name="contenido"
                          id="contenido"
                          rows="5"
                          maxlength="5000"
                          class="mensaje-compose-textarea {{ $errors->has('contenido') ? 'is-invalid' : '' }}"
                          placeholder="Escribe una respuesta"
                          required>{{ old('contenido') }}</textarea>

                <div class="mensajes-field-footer">
                    @error('contenido')
                        <span class="mensajes-error">{{ $message }}</span>
                    @else
                        <span class="mensajes-field-help">
                            También puedes enviar con Ctrl + Enter.
                        </span>
                    @enderror

                    <span class="mensajes-counter" data-counter-for="contenido">
                        0/5000
                    </span>
                </div>

                <div class="mensajes-reply-actions">
                    <button type="submit" class="mensajes-btn mensajes-btn-primary" data-submit-message>
                        <i class="fas fa-paper-plane"></i>
                        <span>Enviar respuesta</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/mensajes.js') }}"></script>
@endpush