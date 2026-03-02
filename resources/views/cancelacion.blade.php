@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Paso 1: Identificación del Estudiante</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Por favor, ingrese sus datos personales y académicos actuales.</p>

                    <form id="formCancelacion">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Institucional</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->email }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prioridad de la Solicitud</label>
                            <select name="prioridad" id="prioridad" class="form-select" required>
                                <option value="">Seleccione una prioridad...</option>
                                <option value="Alta">Alta (Problemas de salud/fuerza mayor)</option>
                                <option value="Media">Media (Conflictos de horario)</option>
                                <option value="Baja">Baja (Otros motivos)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observación Inicial (Motivo)</label>
                            <textarea name="observacion_inicial" id="observacion_inicial" class="form-control" rows="4" placeholder="Explique brevemente por qué solicita la cancelación..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Siguiente: Motivo de Cancelación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formCancelacion').addEventListener('submit', function(e) {
    e.preventDefault();

    // Aquí es donde conectamos con TU API que usa el SP
    const datos = {
        prioridad: document.getElementById('prioridad').value,
        observacion_inicial: document.getElementById('observacion_inicial').value,
        _token: '{{ csrf_token() }}'
    };

    fetch('/api/cancelaciones/crear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        alert('Solicitud creada exitosamente. ID de Trámite: ' + (data.id_tramite || 'Procesado'));
        // Aquí podrías redirigir al paso 2
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error al conectar con la API');
    });
});
</script>
@endsection
