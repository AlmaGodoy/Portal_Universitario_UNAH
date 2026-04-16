@extends('layouts.app-estudiantes')

@section('titulo', 'Soporte')

@section('content')
<link rel="stylesheet" href="{{ asset('css/soporte_estudiante.css') }}">

<div class="stu-support-page">
    <section class="stu-support-hero">
        <span class="stu-support-kicker">
            <i class="fas fa-headset"></i>
            Centro de soporte
        </span>

        <h1>Soporte para estudiantes</h1>
        <p>
            Desde aquí puedes reportar inconvenientes del portal, consultas sobre trámites,
            problemas con documentos, errores de acceso o dudas sobre el uso del sistema.
        </p>
    </section>

    <div class="stu-support-grid">
        <div class="stu-support-main">
            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>Registrar una solicitud de soporte</h2>
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
                                <input type="text" id="supportAsunto" placeholder="Ejemplo: No puedo subir mi historial académico">
                            </div>

                            <div class="stu-support-field">
                                <label for="supportTipo">Tipo de incidencia</label>
                                <select id="supportTipo">
                                    <option value="">Seleccione una opción</option>
                                    <option>Acceso al sistema</option>
                                    <option>Problema con trámite</option>
                                    <option>Problema con documentos</option>
                                    <option>Error visual en la plataforma</option>
                                    <option>Consulta general</option>
                                </select>
                            </div>

                            <div class="stu-support-field">
                                <label for="supportPrioridad">Prioridad</label>
                                <select id="supportPrioridad">
                                    <option value="">Seleccione una opción</option>
                                    <option>Alta</option>
                                    <option>Media</option>
                                    <option>Baja</option>
                                </select>
                            </div>

                            <div class="stu-support-field">
                                <label for="supportModulo">Módulo relacionado</label>
                                <select id="supportModulo">
                                    <option value="">Seleccione una opción</option>
                                    <option>Inicio</option>
                                    <option>Equivalencias</option>
                                    <option>Mis trámites</option>
                                    <option>Configuración</option>
                                    <option>Soporte</option>
                                    <option>Otro</option>
                                </select>
                            </div>

                            <div class="stu-support-field stu-support-field-full">
                                <label for="supportDescripcion">Descripción del problema</label>
                                <textarea id="supportDescripcion" placeholder="Describe claramente lo que sucedió, qué estabas haciendo y qué error observaste."></textarea>
                                <div class="stu-support-help">
                                    Indica el módulo donde ocurrió el problema, el paso exacto y si apareció algún mensaje de error.
                                </div>
                            </div>
                        </div>

                        <div class="stu-support-actions">
                            <button type="button" class="stu-btn stu-btn-primary" id="btnEnviarSoporte">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar solicitud
                            </button>

                            <button type="reset" class="stu-btn stu-btn-light">
                                <i class="fas fa-rotate-left mr-2"></i> Limpiar formulario
                            </button>
                        </div>

                        <div class="stu-support-message" id="supportMessage">
                            Tu solicitud de soporte fue preparada correctamente. Solo falta conectarla al backend para guardarla en la base de datos.
                        </div>
                    </form>
                </div>
            </div>

            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>Preguntas frecuentes</h2>
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

        <aside class="stu-support-side">
            <div class="stu-support-card">
                <div class="stu-support-card-header">
                    <h2>Recomendaciones</h2>
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
        </aside>
    </div>
</div>

<script src="{{ asset('js/soporte_estudiante.js') }}"></script>
@endsection





