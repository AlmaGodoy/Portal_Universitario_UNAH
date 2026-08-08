@php
    use Illuminate\Support\Facades\Route;

    $dashboardUrl = Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');
@endphp

@extends('layouts.app-estudiantes')

@section('titulo', 'Soporte')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/soporte_estudiante.css') }}">
@endpush

@section('content')

<div id="soporteEstudiantePage" class="stu-support-page">

    {{-- Encabezado --}}
    <section class="stu-support-hero">
        <div class="stu-support-hero-content">
            <span class="stu-support-kicker">
                <i class="fas fa-headset"></i>
                Centro de soporte
            </span>

            <h1>Soporte para estudiantes</h1>
            <p>
                Desde aquí puedes reportar inconvenientes del portal, consultas sobre trámites,
                problemas con documentos, errores de acceso o dudas sobre el uso del sistema.
            </p>
        </div>

        <div class="stu-support-hero-actions">
            <a href="{{ $dashboardUrl }}" class="stu-btn stu-btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver al dashboard
            </a>
        </div>
    </section>

    <div class="stu-support-grid">

        <div class="stu-support-main">

            {{-- Formulario --}}
            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>
                        <i class="fas fa-ticket-alt"></i>
                        Registrar una solicitud de soporte
                    </h2>
                    <p>
                        Completa el formulario con la información principal del problema
                        para que pueda ser atendido por el área correspondiente.
                    </p>
                </div>

                <div class="stu-support-card-body">
                    <form id="studentSupportForm">
                        <div class="stu-support-form-grid">

                            <div class="stu-support-field">
                                <label for="supportAsunto">Asunto</label>
                                <input
                                    type="text"
                                    id="supportAsunto"
                                    name="asunto"
                                    placeholder="Ejemplo: No puedo subir mi historial académico"
                                    required
                                >
                            </div>

                            <div class="stu-support-field">
                                <label for="supportTipo">Tipo de incidencia</label>
                                <select id="supportTipo" name="tipo" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="Acceso al sistema">Acceso al sistema</option>
                                    <option value="Problema con trámite">Problema con trámite</option>
                                    <option value="Problema con documentos">Problema con documentos</option>
                                    <option value="Error visual en la plataforma">Error visual en la plataforma</option>
                                    <option value="Consulta general">Consulta general</option>
                                </select>
                            </div>

                            <div class="stu-support-field">
                                <label for="supportPrioridad">Prioridad</label>
                                <select id="supportPrioridad" name="prioridad" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Media">Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>

                            <div class="stu-support-field">
                                <label for="supportModulo">Módulo relacionado</label>
                                <select id="supportModulo" name="modulo" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="Inicio">Inicio</option>
                                    <option value="Equivalencias">Equivalencias</option>
                                    <option value="Mis trámites">Mis trámites</option>
                                    <option value="Configuración">Configuración</option>
                                    <option value="Soporte">Soporte</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="stu-support-field stu-support-field-full">
                                <label for="supportDescripcion">Descripción del problema</label>
                                <textarea
                                    id="supportDescripcion"
                                    name="descripcion"
                                    placeholder="Describe claramente lo que sucedió, qué estabas haciendo y qué error observaste."
                                    required
                                ></textarea>

                                <div class="stu-support-help">
                                    Indica el módulo donde ocurrió el problema, el paso exacto y si apareció algún mensaje de error.
                                </div>
                            </div>

                        </div>

                        <div class="stu-support-actions">
                            <button type="submit" class="stu-btn stu-btn-primary" id="btnEnviarSoporte">
                                <i class="fas fa-paper-plane"></i>
                                Enviar solicitud
                            </button>

                            <button type="reset" class="stu-btn stu-btn-light">
                                <i class="fas fa-rotate-left"></i>
                                Limpiar formulario
                            </button>
                        </div>

                        <div
                            class="stu-support-message"
                            id="supportMessage"
                            role="alert"
                            aria-live="polite"
                            style="display: none;"
                        ></div>
                    </form>
                </div>
            </div>

            {{-- Preguntas frecuentes --}}
            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>
                        <i class="fas fa-circle-question"></i>
                        Preguntas frecuentes
                    </h2>
                    <p>
                        Algunas dudas comunes antes de registrar una nueva incidencia.
                    </p>
                </div>

                <div class="stu-support-card-body">
                    <div class="stu-support-faq">

                        <details>
                            <summary>No puedo iniciar sesión en el portal</summary>
                            <p>
                                Verifica primero que estés usando el correo correcto y que la contraseña esté actualizada.
                                Si el problema continúa, registra una solicitud en esta vista indicando el mensaje que te aparece.
                            </p>
                        </details>

                        <details>
                            <summary>El sistema no me deja subir un documento</summary>
                            <p>
                                Revisa que el archivo tenga el formato correcto y que no exceda el tamaño permitido.
                                También conviene intentar nuevamente desde otro navegador o recargando la página.
                            </p>
                        </details>

                        <details>
                            <summary>Un trámite no muestra cambios de estado</summary>
                            <p>
                                Algunos procesos requieren validación interna. Si consideras que el tiempo ya es excesivo,
                                crea una solicitud de soporte indicando el nombre del trámite y la fecha aproximada en la que lo enviaste.
                            </p>
                        </details>

                        <details>
                            <summary>La página se mira desordenada o incompleta</summary>
                            <p>
                                Actualiza el navegador, limpia caché e intenta nuevamente. Si persiste, reporta el módulo donde ocurre
                                y, de ser posible, agrega una captura cuando el backend del soporte esté conectado.
                            </p>
                        </details>

                    </div>
                </div>
            </div>

        </div>

        {{-- Lateral --}}
        <aside class="stu-support-side">

            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>
                        <i class="fas fa-lightbulb"></i>
                        Recomendaciones
                    </h2>
                    <p>
                        Antes de enviar una solicitud.
                    </p>
                </div>

                <div class="stu-support-card-body">
                    <ul class="stu-support-tips">
                        <li>Indica el módulo exacto donde ocurrió el problema.</li>
                        <li>Describe qué acción realizaste antes del error.</li>
                        <li>Menciona si el problema ocurre siempre o solo algunas veces.</li>
                        <li>Si aparece un mensaje en pantalla, escríbelo tal como se muestra.</li>
                    </ul>
                </div>
            </div>

            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>
                        <i class="fas fa-clock"></i>
                        Estado de atención
                    </h2>
                    <p>
                        Información general del proceso.
                    </p>
                </div>

                <div class="stu-support-card-body">
                    <div class="stu-support-status-box">

                        <div class="stu-support-status-item">
                            <strong>Recepción</strong>
                            <span>La solicitud será revisada por el área correspondiente.</span>
                        </div>

                        <div class="stu-support-status-item">
                            <strong>Seguimiento</strong>
                            <span>Se recomienda revisar el correo institucional por cualquier respuesta.</span>
                        </div>

                        <div class="stu-support-status-item">
                            <strong>Resolución</strong>
                            <span>Cuando el caso sea atendido, se indicará la acción realizada.</span>
                        </div>

                    </div>
                </div>
            </div>

        </aside>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('studentSupportForm');
    const submitButton = document.getElementById('btnEnviarSoporte');
    const messageBox = document.getElementById('supportMessage');

    const createUrl = @json(route('api.soporte.crear'));
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    if (!form || !submitButton || !messageBox) {
        return;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showMessage(message, type = 'success') {
        const isSuccess = type === 'success';

        messageBox.style.display = 'flex';
        messageBox.style.alignItems = 'center';
        messageBox.style.gap = '8px';
        messageBox.style.background = isSuccess
            ? 'rgba(22, 163, 74, .10)'
            : 'rgba(220, 38, 38, .10)';
        messageBox.style.border = isSuccess
            ? '1px solid rgba(22, 163, 74, .24)'
            : '1px solid rgba(220, 38, 38, .24)';
        messageBox.style.color = isSuccess
            ? '#15803d'
            : '#b91c1c';

        messageBox.innerHTML = `
            <i class="fas ${isSuccess
                ? 'fa-circle-check'
                : 'fa-triangle-exclamation'}"></i>
            <span>${escapeHtml(message)}</span>
        `;
    }

    function hideMessage() {
        messageBox.style.display = 'none';
        messageBox.innerHTML = '';
    }

    function setSubmitting(isSubmitting) {
        submitButton.disabled = isSubmitting;

        submitButton.innerHTML = isSubmitting
            ? '<i class="fas fa-spinner fa-spin"></i> Enviando solicitud...'
            : '<i class="fas fa-paper-plane"></i> Enviar solicitud';
    }

    async function parseResponse(response) {
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error(
                `El servidor devolvió una respuesta inválida (${response.status}).`
            );
        }

        return response.json();
    }

    form.addEventListener('reset', function () {
        hideMessage();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        hideMessage();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!csrfToken) {
            showMessage(
                'No se encontró el token de seguridad de la sesión.',
                'error'
            );
            return;
        }

        const payload = {
            asunto: document.getElementById('supportAsunto')?.value.trim(),
            tipo: document.getElementById('supportTipo')?.value,
            prioridad: document.getElementById('supportPrioridad')?.value,
            modulo: document.getElementById('supportModulo')?.value,
            descripcion:
                document.getElementById('supportDescripcion')?.value.trim(),
            canal: 'Portal estudiantil'
        };

        setSubmitting(true);

        try {
            const response = await fetch(createUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await parseResponse(response);

            if (!response.ok || data.ok !== true) {
                throw new Error(
                    data.message ||
                    'No fue posible registrar la solicitud de soporte.'
                );
            }

            const code =
                data.data?.codigo
                || data.codigo
                || data.id_soporte
                || '';

            const confirmation = code
                ? `Solicitud registrada correctamente. Código: ${code}.`
                : (
                    data.message
                    || 'Solicitud registrada correctamente.'
                );

            form.reset();
            showMessage(confirmation, 'success');
        } catch (error) {
            showMessage(
                error.message ||
                'Ocurrió un error al enviar la solicitud.',
                'error'
            );
        } finally {
            setSubmitting(false);
        }
    });
});
</script>
@endpush