@extends('portal_login')

@section('content')
@vite(['resources/css/cancelacion.css', 'resources/js/cancelacion.js'])

<div class="puma-page">

    {{-- ─── Topbar ─── --}}
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

        {{-- ─── Mensaje de éxito del paso 1 ─── --}}
        @if (session('success'))
            <div class="puma-alert puma-alert--success">
                <span>✅</span>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- ─── Mensaje de error ─── --}}
        @if ($errors->any())
            <div class="puma-alert puma-alert--error">
                <span>⚠️</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ─── Stepper — Paso 2 activo ─── --}}
        <div class="puma-steps">
            <div class="puma-step puma-step--done">
                <div class="puma-step__circle">✓</div>
                <div class="puma-step__label">Datos y Motivo</div>
            </div>
            <div class="puma-step-connector puma-step-connector--done"></div>
            <div class="puma-step puma-step--active">
                <div class="puma-step__circle">2</div>
                <div class="puma-step__label">Documentación</div>
            </div>
            <div class="puma-step-connector"></div>
            <div class="puma-step">
                <div class="puma-step__circle">3</div>
                <div class="puma-step__label">Finalizar</div>
            </div>
        </div>

        <div class="puma-card animate-in">
            <div class="puma-card__header">
                <div class="puma-card__header-icon">📎</div>
                <div>
                    <h4 class="puma-card__header-title">Adjuntar Documentación</h4>
                    <p class="puma-card__header-sub">Trámite N° {{ $id_tramite }} — Suba su constancia en PDF</p>
                </div>
            </div>

            <div class="puma-card__body">

                {{-- Info de requisitos --}}
                <div class="puma-notice-box puma-notice-box--compact">
                    <p><strong>Documentos aceptados según su motivo:</strong></p>
                    <ul class="puma-doc-list">
                        <li>📋 Constancia médica con fecha y firma del médico tratante</li>
                        <li>📋 Constancia laboral o carta de cambio de horario</li>
                        <li>📋 Acta de defunción o documento de calamidad doméstica</li>
                        <li>📋 Resolución o documento oficial de separación de la UNAH</li>
                    </ul>
                    <p class="puma-helper">Formato: <strong>PDF únicamente</strong> · Tamaño máximo: <strong>10 MB</strong></p>
                </div>

                <form action="{{ route('cancelacion.guardar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- ID del trámite oculto --}}
                    <input type="hidden" name="id_tramite" value="{{ $id_tramite }}">

                    <div class="puma-section" style="margin-top:20px">
                        <span>Subir Documento</span>
                    </div>

                    {{-- Área de subida de archivo --}}
                    <div class="puma-upload-area" id="upload-area" onclick="document.getElementById('archivo_pdf').click()">
                        <div class="puma-upload-icon">📄</div>
                        <p class="puma-upload-text">Haga clic para seleccionar su archivo PDF</p>
                        <p class="puma-helper">o arrastre y suelte aquí</p>
                        <p id="file-name-display" class="puma-file-name" style="display:none;"></p>
                    </div>

                    <input type="file" id="archivo_pdf" name="archivo_pdf"
                           accept=".pdf" style="display:none;"
                           onchange="mostrarNombreArchivo(this)">

                    @error('archivo_pdf')
                        <span class="puma-field-error" style="display:block;margin-top:8px;">{{ $message }}</span>
                    @enderror

                    <div class="puma-field" style="margin-top:20px">
                        <label class="puma-label">Descripción del documento <span class="puma-helper">(opcional)</span></label>
                        <input type="text" name="descripcion_doc" class="puma-input"
                               placeholder="Ej: Constancia médica del Dr. García">
                    </div>

                    {{-- Botones --}}
                    <div class="puma-btn-group">
                        <a href="{{ route('cancelacion.index') }}" class="puma-btn-outline">
                            ← Regresar al Paso 1
                        </a>
                        <button type="submit" class="puma-btn-full puma-btn-full--auto">
                            Enviar Solicitud Completa ✓
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>{{-- /puma-container --}}
</div>{{-- /puma-page --}}

<script>
function mostrarNombreArchivo(input) {
    const display = document.getElementById('file-name-display');
    const area    = document.getElementById('upload-area');
    if (input.files && input.files[0]) {
        const nombre = input.files[0].name;
        const tamano = (input.files[0].size / 1024 / 1024).toFixed(2);
        display.textContent = '✅ ' + nombre + ' (' + tamano + ' MB)';
        display.style.display = 'block';
        area.classList.add('puma-upload-area--selected');
    }
}

// Drag and drop
const area = document.getElementById('upload-area');
area.addEventListener('dragover',  e => { e.preventDefault(); area.classList.add('puma-upload-area--drag'); });
area.addEventListener('dragleave', () => area.classList.remove('puma-upload-area--drag'));
area.addEventListener('drop', e => {
    e.preventDefault();
    area.classList.remove('puma-upload-area--drag');
    const input = document.getElementById('archivo_pdf');
    input.files = e.dataTransfer.files;
    mostrarNombreArchivo(input);
});
</script>

@endsection
