@php
    $layoutSeguridad = session('rol_texto') === 'secretaria_general'
        ? 'layouts.app-secretaria-academica'
        : 'layouts.app-coordinador';
@endphp

@extends($layoutSeguridad)

@section('titulo', 'Módulo de Seguridad')

@section('content')
<div class="container py-4 security-page fondo-seguridad">

    @if(session('status'))
        <div class="alert alert-success shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-4">
        <h2 class="security-title">Módulo de Seguridad</h2>

        @if($esSecretariaGeneral ?? false)
            <p class="security-subtitle">
                Administración global de roles, usuarios, objetos y accesos del sistema.
            </p>
        @elseif($esCoordinador ?? false)
            <p class="security-subtitle">
                Consulta y administración de usuarios pertenecientes únicamente a tu carrera.
            </p>
        @else
            <p class="security-subtitle">
                Consulta de opciones de seguridad según tu ámbito autorizado.
            </p>
        @endif
    </div>

    <div class="row g-4">
        @forelse($modulos as $modulo)
            <div class="col-md-6 col-lg-3">
                <div class="card shadow border-0 h-100 security-card">
                    <div class="card-body text-center d-flex flex-column">

                        <div class="mb-3">
                            <i class="{{ $modulo['icono'] }} fa-3x text-primary"></i>
                        </div>

                        <h5 class="fw-bold">{{ $modulo['titulo'] }}</h5>

                        <p class="text-muted small">
                            {{ $modulo['descripcion'] }}
                        </p>

                        <a href="{{ $modulo['ruta'] }}" class="btn btn-primary w-100 mt-auto">
                            Ingresar
                        </a>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning shadow-sm mb-0">
                    No hay módulos de seguridad disponibles para tu perfil.
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
