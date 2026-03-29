@extends('layouts.app')

@section('titulo', 'Módulo de Seguridad')

@section('content')
<div class="container py-4 security-page fondo-seguridad">

    <div class="mb-4">
        <h2 class="security-title">Módulo de Seguridad</h2>
        <p class="security-subtitle">Administración de roles, usuarios, objetos y accesos del sistema.</p>
    </div>

    <div class="row g-4">
        @foreach($modulos as $modulo)
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
        @endforeach
    </div>

</div>
@endsection
