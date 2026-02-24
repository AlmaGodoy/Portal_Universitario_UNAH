@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 950px;">

        <h3 class="mb-5 text-center text-white fw-bold">
            Selecciona tu portal
        </h3>

        @if(session('status'))
            <div class="alert alert-success text-center">
                {{ session('status') }}
            </div>
        @endif

        <div class="row g-4">

            {{-- ================= ESTUDIANTE ================= --}}
            <div class="col-md-6">
                <div class="portal-card text-center">

                    <img src="{{ asset('images/portal/estudiantes.png') }}"
                         alt="Estudiante"
                         class="portal-icon">

                    <h4 class="text-white mt-3">Estudiantes</h4>
                    <p class="text-light mb-4">
                        Acceso exclusivo para estudiantes.
                    </p>

                    <div class="d-grid gap-2">
                        <a class="btn btn-primary"
                           href="{{ route('login.tipo', ['tipo'=>'estudiante']) }}">
                            Iniciar sesión
                        </a>

                        <a class="btn btn-outline-light"
                           href="{{ route('register.tipo', ['tipo'=>'estudiante']) }}">
                            Registrarme
                        </a>
                    </div>

                </div>
            </div>

            {{-- ================= EMPLEADO ================= --}}
            <div class="col-md-6">
                <div class="portal-card text-center">

                    <img src="{{ asset('images/portal/empleados.png') }}"
                         alt="Empleado"
                         class="portal-icon">

                    <h4 class="text-white mt-3">Empleados</h4>
                    <p class="text-light mb-4">
                        Acceso para Coordinadores y Secretarías.
                    </p>

                    <div class="d-grid gap-2">
                        <a class="btn btn-primary"
                           href="{{ route('login.tipo', ['tipo'=>'empleado']) }}">
                            Iniciar sesión
                        </a>

                        <a class="btn btn-outline-light"
                           href="{{ route('register.tipo', ['tipo'=>'empleado']) }}">
                            Registrarme
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
