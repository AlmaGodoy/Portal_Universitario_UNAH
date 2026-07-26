@extends('layouts.app-estudiantes')

@section('titulo', 'Nuevo mensaje')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mensajes.css') }}">
@endpush

@section('content')
<div class="mensajes-page">

    <div class="mensajes-header">
        <div>
            <span class="mensajes-header-label">Comunicación interna</span>
            <h1>Nuevo mensaje</h1>
            <p>Redacta un mensaje para un empleado de la institución.</p>
        </div>

        <a href="{{ route('mensajes.index') }}" class="mensajes-btn mensajes-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <div class="mensajes-compose-card">

        <div class="mensajes-compose-header">
            <div class="mensajes-compose-header-icon">
                <i class="fas fa-pen-to-square"></i>
            </div>

            <div>
                <h2>Redactar mensaje</h2>
                <p>Completa los datos solicitados para enviar el mensaje.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mensajes-alert mensajes-alert-danger">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    <strong>No se pudo enviar el mensaje.</strong>
                    <span>Revisa los campos marcados.</span>
                </div>
            </div>
        @endif

        <form method="POST"
              action="{{ route('mensajes.store') }}"
              class="mensajes-compose-form"
              data-message-form>

            @csrf

            <div class="mensajes-form-group">
                <label for="buscarDestinatario">Buscar destinatario</label>

                <div class="mensajes-input-icon">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           id="buscarDestinatario"
                           placeholder="Escribe el nombre o correo"
                           autocomplete="off"
                           data-recipient-search>
                </div>
            </div>

            <div class="mensajes-form-group">
                <label for="id_destinatario">
                    Destinatario <span class="required">*</span>
                </label>

                <select name="id_destinatario"
                        id="id_destinatario"
                        class="{{ $errors->has('id_destinatario') ? 'is-invalid' : '' }}"
                        data-recipient-select
                        required>

                    <option value="">Seleccione un destinatario</option>

                    @foreach ($destinatarios as $destinatario)
                        @php
                            $nombre =
                                optional($destinatario->persona)->nombre_persona
                                ?? $destinatario->name
                                ?? $destinatario->email
                                ?? 'Usuario';

                            $correo =
                                $destinatario->email
                                ?? $destinatario->correo_institucional
                                ?? optional($destinatario->persona)->correo_institucional
                                ?? '';

                            $rolTexto = match ((int) ($destinatario->id_rol ?? 0)) {
                                1 => 'Secretaría General',
                                4 => 'Coordinador',
                                5 => 'Secretaría',
                                default => 'Empleado',
                            };
                        @endphp

                        <option value="{{ $destinatario->id_usuario }}"
                                data-search="{{ mb_strtolower($nombre . ' ' . $correo . ' ' . $rolTexto) }}"
                                {{ old('id_destinatario') == $destinatario->id_usuario ? 'selected' : '' }}>
                            {{ $nombre }} — {{ $rolTexto }}
                            {{ $correo !== '' ? '(' . $correo . ')' : '' }}
                        </option>
                    @endforeach
                </select>

                @error('id_destinatario')
                    <span class="mensajes-error">{{ $message }}</span>
                @enderror

                <span class="mensajes-field-help">
                    Como estudiante, puedes enviar mensajes a empleados del sistema.
                </span>
            </div>

            <div class="mensajes-form-group">
                <label for="asunto">
                    Asunto <span class="required">*</span>
                </label>

                <input type="text"
                       name="asunto"
                       id="asunto"
                       value="{{ old('asunto') }}"
                       maxlength="150"
                       class="{{ $errors->has('asunto') ? 'is-invalid' : '' }}"
                       placeholder="Escribe el asunto del mensaje"
                       required>

                <div class="mensajes-field-footer">
                    @error('asunto')
                        <span class="mensajes-error">{{ $message }}</span>
                    @else
                        <span></span>
                    @enderror

                    <span class="mensajes-counter" data-counter-for="asunto">0/150</span>
                </div>
            </div>

            <div class="mensajes-form-group">
                <label for="contenido">
                    Mensaje <span class="required">*</span>
                </label>

                <textarea name="contenido"
                          id="contenido"
                          rows="8"
                          maxlength="5000"
                          class="mensaje-compose-textarea {{ $errors->has('contenido') ? 'is-invalid' : '' }}"
                          placeholder="Escribe aquí tu mensaje"
                          required>{{ old('contenido') }}</textarea>

                <div class="mensajes-field-footer">
                    @error('contenido')
                        <span class="mensajes-error">{{ $message }}</span>
                    @else
                        <span class="mensajes-field-help">
                            No compartas contraseñas ni códigos de verificación.
                        </span>
                    @enderror

                    <span class="mensajes-counter" data-counter-for="contenido">0/5000</span>
                </div>
            </div>

            <div class="mensajes-compose-actions">
                <a href="{{ route('mensajes.index') }}" class="mensajes-btn mensajes-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="mensajes-btn mensajes-btn-primary" data-submit-message>
                    <i class="fas fa-paper-plane"></i>
                    <span>Enviar mensaje</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/mensajes.js') }}"></script>
@endpush