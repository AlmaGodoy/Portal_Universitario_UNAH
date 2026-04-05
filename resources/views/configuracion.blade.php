@extends('layouts.app-estudiantes')

@section('titulo', 'Configuración')

@section('content')
    @vite(['resources/css/configuracion.css', 'resources/js/configuracion.js'])

    <div class="container-fluid config-page">

        {{-- Encabezado --}}
        <div class="config-header card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="config-header__top">
                    <div>
                        <h1 class="config-title">Configuración de la cuenta</h1>
                        <p class="config-subtitle mb-0">
                            Administra tu información, seguridad y preferencias del portal estudiantil.
                        </p>
                    </div>

                    <div class="config-actions-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-config-back">
                            <i class="fas fa-arrow-left me-2"></i> Volver al dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-success alert-config shadow-sm">
                <i class="fas fa-circle-check me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-config shadow-sm">
                <i class="fas fa-triangle-exclamation me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-config shadow-sm">
                <div class="fw-bold mb-2">
                    <i class="fas fa-circle-xmark me-2"></i>Se encontraron errores:
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            {{-- Columna izquierda --}}
            <div class="col-12 col-lg-5">
                {{-- Perfil --}}
                <div class="card config-card border-0 shadow-sm mb-4">
                    <div class="card-header config-card__header">
                        <h3 class="config-card__title mb-0">
                            <i class="fas fa-user-circle me-2"></i>Información de la cuenta
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="config-profile">
                            <div class="config-avatar">
                                {{ strtoupper(substr(auth()->user()->persona->nombre_persona ?? 'U', 0, 1)) }}
                            </div>

                            <div class="config-profile__info">
                                <h4 class="config-name">
                                    {{ auth()->user()->persona->nombre_persona ?? 'Usuario' }}
                                </h4>
                                <p class="config-email mb-0">
                                    {{ auth()->user()->persona->correo_institucional ?? 'Sin correo registrado' }}
                                </p>
                            </div>
                        </div>

                        <div class="config-info-list mt-4">
                            <div class="config-info-item">
                                <span class="config-label">Tipo de usuario</span>
                                <span class="config-value">
                                    {{ auth()->user()->persona->tipo_usuario ?? 'No definido' }}
                                </span>
                            </div>

                            <div class="config-info-item">
                                <span class="config-label">Estado de cuenta</span>
                                <span class="config-value">
                                    {{ auth()->user()->estado_cuenta ?? 'No definido' }}
                                </span>
                            </div>

                            <div class="config-info-item">
                                <span class="config-label">Rol actual</span>
                                <span class="config-value">
                                    {{ session('rol_texto') ?? 'Estudiante' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Seguridad --}}
                <div class="card config-card border-0 shadow-sm">
                    <div class="card-header config-card__header">
                        <h3 class="config-card__title mb-0">
                            <i class="fas fa-shield-halved me-2"></i>Seguridad
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="security-item">
                            <div>
                                <h4 class="security-title">Autenticación en dos pasos</h4>
                                <p class="security-text mb-0">
                                    Protege tu cuenta con una capa adicional de seguridad.
                                </p>
                            </div>
                            <span class="badge config-badge bg-success">Activa</span>
                        </div>

                        <hr>

                        <div class="security-item">
                            <div>
                                <h4 class="security-title">Dispositivo de confianza</h4>
                                <p class="security-text mb-0">
                                    Este equipo puede permanecer verificado temporalmente.
                                </p>
                            </div>
                            <span class="badge config-badge bg-primary">Habilitado</span>
                        </div>

                        <hr>

                        <div class="security-item">
                            <div>
                                <h4 class="security-title">Último acceso</h4>
                                <p class="security-text mb-0">
                                    Información referencial del último ingreso al sistema.
                                </p>
                            </div>
                            <span class="config-last-access">Reciente</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna derecha --}}
            <div class="col-12 col-lg-7">
                {{-- Cambiar contraseña --}}
                <div class="card config-card border-0 shadow-sm mb-4">
                    <div class="card-header config-card__header">
                        <h3 class="config-card__title mb-0">
                            <i class="fas fa-key me-2"></i>Cambiar contraseña
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.cambiar-password') }}" method="POST" id="formCambiarPassword" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="password_actual" class="form-label config-form-label">
                                        Contraseña actual
                                    </label>
                                    <div class="config-input-group">
                                        <input
                                            type="password"
                                            class="form-control config-input"
                                            id="password_actual"
                                            name="password_actual"
                                            placeholder="Ingresa tu contraseña actual"
                                            required
                                        >
                                        <button type="button" class="btn btn-toggle-password" data-target="password_actual">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password_nueva" class="form-label config-form-label">
                                        Nueva contraseña
                                    </label>
                                    <div class="config-input-group">
                                        <input
                                            type="password"
                                            class="form-control config-input"
                                            id="password_nueva"
                                            name="password_nueva"
                                            placeholder="Ingresa la nueva contraseña"
                                            required
                                        >
                                        <button type="button" class="btn btn-toggle-password" data-target="password_nueva">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="password-rules-card mt-3">
                                        <div class="password-rules-title">
                                            La contraseña debe incluir:
                                        </div>

                                        <ul class="password-rules-list">
                                            <li id="ruleLength" class="rule-item">Mínimo 8 caracteres</li>
                                            <li id="ruleUpper" class="rule-item">Al menos una letra mayúscula</li>
                                            <li id="ruleNumber" class="rule-item">Al menos un número</li>
                                            <li id="ruleSymbol" class="rule-item">Al menos un carácter especial</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password_nueva_confirmation" class="form-label config-form-label">
                                        Confirmar nueva contraseña
                                    </label>
                                    <div class="config-input-group">
                                        <input
                                            type="password"
                                            class="form-control config-input"
                                            id="password_nueva_confirmation"
                                            name="password_nueva_confirmation"
                                            placeholder="Confirma la nueva contraseña"
                                            required
                                        >
                                        <button type="button" class="btn btn-toggle-password" data-target="password_nueva_confirmation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="password-confirm-box mt-3">
                                        <small id="passwordMatchMessage" class="rule-item d-block">
                                            Confirma la contraseña nueva
                                        </small>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="config-password-meter">
                                        <div class="config-password-meter__bar" id="passwordStrengthBar"></div>
                                    </div>
                                    <small id="passwordStrengthText" class="config-password-meter__text">
                                        Seguridad de contraseña: pendiente
                                    </small>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-config-save">
                                        <i class="fas fa-floppy-disk me-2"></i>Actualizar contraseña
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Preferencias --}}
                <div class="card config-card border-0 shadow-sm">
                    <div class="card-header config-card__header">
                        <h3 class="config-card__title mb-0">
                            <i class="fas fa-sliders me-2"></i>Preferencias
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="preference-box">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notificacionesCorreo" checked>
                                        <label class="form-check-label fw-semibold" for="notificacionesCorreo">
                                            Notificaciones por correo
                                        </label>
                                    </div>
                                    <p class="preference-text mb-0">
                                        Recibe avisos del estado de tus trámites y mensajes importantes.
                                    </p>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="preference-box">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="recordatoriosSistema" checked>
                                        <label class="form-check-label fw-semibold" for="recordatoriosSistema">
                                            Recordatorios del sistema
                                        </label>
                                    </div>
                                    <p class="preference-text mb-0">
                                        Muestra avisos para revisar observaciones y avances de trámites.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection