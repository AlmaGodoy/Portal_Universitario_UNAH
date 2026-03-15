@extends('layouts.app')

@section('content')

<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold">Módulo de Seguridad</h2>
        <p class="text-muted">Administración de roles, usuarios, objetos y accesos del sistema.</p>
    </div>

    <div class="row g-4">

        @foreach($modulos as $modulo)

        <div class="col-md-6 col-lg-3">

            <div class="card shadow border-0 h-100 security-card">

                <div class="card-body text-center">

                    <div class="mb-3">
                        <i class="bi {{ $modulo['icono'] }} fs-1 text-primary"></i>
                    </div>

                    <h5 class="fw-bold">{{ $modulo['titulo'] }}</h5>

                    <p class="text-muted small">
                        {{ $modulo['descripcion'] }}
                    </p>

                    <a href="{{ $modulo['ruta'] }}" class="btn btn-primary w-100">
                        Ingresar
                    </a>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

<style>

.security-card{
    border-radius:12px;
    transition: all .2s ease;
}

.security-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

</style>

@endsection
