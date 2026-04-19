@extends('layouts.app-estudiantes')

@section('titulo', 'Mis Trámites')

@section('content')

<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-1">Mis trámites</h2>
                    <p class="text-muted mb-0">
                        Consulta aquí el historial de tus solicitudes académicas registradas en el sistema.
                    </p>
                </div>

                <div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill fs-6" id="totalTramitesBadge">
                        Total: {{ $total ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div id="mensajeErrorWrap">
        @if(!empty($mensajeError))
            <div class="alert alert-danger rounded-4 shadow-sm">
                {{ $mensajeError }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-4 shadow-sm">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div id="misTramitesContenido">
        @if(isset($tramites) && $tramites->count() > 0)

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">Listado de trámites</h5>
                            <small class="text-muted">
                                Se muestran tus trámites más recientes con la información más importante.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">N° Trámite</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Detalle clave</th>
                                    <th class="px-4 py-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMisTramitesBody">
                                @foreach($tramites as $tramite)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="fw-bold text-primary">
                                                #{{ $tramite->id_tramite }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="fw-semibold text-dark">
                                                {{ $tramite->tipo_tramite_mostrar ?? 'Trámite académico' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="text-dark fw-semibold">
                                                {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('d/m/Y') }}
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('h:i A') }}
                                            </small>
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="text-dark">
                                                {{ $tramite->detalle_clave ?? 'Trámite académico registrado' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <span class="badge {{ $tramite->badge_class ?? 'bg-secondary' }} px-3 py-2 rounded-pill">
                                                {{ $tramite->estado_mostrar ?? 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @else

            <div class="card border-0 shadow-sm rounded-4" id="sinTramitesCard">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4 class="fw-bold mb-2">Aún no tienes trámites registrados</h4>
                    <p class="text-muted mb-0">
                        Cuando realices una solicitud académica, aparecerá listada aquí.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-none" id="tablaTramitesCard">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">Listado de trámites</h5>
                            <small class="text-muted">
                                Se muestran tus trámites más recientes con la información más importante.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">N° Trámite</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Detalle clave</th>
                                    <th class="px-4 py-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMisTramitesBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tablaMisTramitesBody');
    const totalBadge = document.getElementById('totalTramitesBadge');
    const errorWrap = document.getElementById('mensajeErrorWrap');
    const sinTramitesCard = document.getElementById('sinTramitesCard');
    const tablaTramitesCard = document.getElementById('tablaTramitesCard');

    function escaparHtml(texto) {
        if (texto === null || texto === undefined) return '';
        return String(texto)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatearFecha(fecha) {
        if (!fecha) {
            return { fecha: '', hora: '' };
        }

        const f = new Date(String(fecha).replace(' ', 'T'));

        if (isNaN(f.getTime())) {
            return { fecha: fecha, hora: '' };
        }

        const fechaTexto = f.toLocaleDateString('es-HN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        const horaTexto = f.toLocaleTimeString('es-HN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        return { fecha: fechaTexto, hora: horaTexto };
    }

    function renderTabla(tramites) {
        if (!tbody) return;

        if (!Array.isArray(tramites) || tramites.length === 0) {
            if (tablaTramitesCard) tablaTramitesCard.classList.add('d-none');
            if (sinTramitesCard) sinTramitesCard.classList.remove('d-none');
            tbody.innerHTML = '';
            return;
        }

        if (tablaTramitesCard) tablaTramitesCard.classList.remove('d-none');
        if (sinTramitesCard) sinTramitesCard.classList.add('d-none');

        tbody.innerHTML = tramites.map(tramite => {
            const fecha = formatearFecha(tramite.fecha_solicitud);

            return `
                <tr>
                    <td class="px-4 py-3">
                        <div class="fw-bold text-primary">#${escaparHtml(tramite.id_tramite)}</div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="fw-semibold text-dark">
                            ${escaparHtml(tramite.tipo_tramite_mostrar || 'Trámite académico')}
                        </div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="text-dark fw-semibold">${escaparHtml(fecha.fecha)}</div>
                        <small class="text-muted">${escaparHtml(fecha.hora)}</small>
                    </td>

                    <td class="px-4 py-3">
                        <div class="text-dark">${escaparHtml(tramite.detalle_clave || 'Trámite académico registrado')}</div>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="badge ${escaparHtml(tramite.badge_class || 'bg-secondary')} px-3 py-2 rounded-pill">
                            ${escaparHtml(tramite.estado_mostrar || 'Pendiente')}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function cargarMisTramites() {
        try {
            const response = await fetch('{{ route('mis.tramites.json') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (errorWrap) {
                    errorWrap.innerHTML = `
                        <div class="alert alert-danger rounded-4 shadow-sm">
                            ${escaparHtml(data.message || 'No se pudieron cargar los trámites.')}
                        </div>
                    `;
                }
                return;
            }

            if (errorWrap) {
                errorWrap.innerHTML = '';
            }

            if (totalBadge) {
                totalBadge.textContent = `Total: ${data.total ?? 0}`;
            }

            renderTabla(data.tramites || []);
        } catch (error) {
            console.error('Error al actualizar mis trámites:', error);
        }
    }

    setInterval(cargarMisTramites, 10000);
});
</script>

@endsection


