<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Trámites</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 25px;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #0b2c74;
            padding-bottom: 15px;
        }

        .encabezado h1 {
            margin: 0;
            font-size: 22px;
            color: #0b2c74;
        }

        .encabezado p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #4b5563;
        }

        .resumen {
            width: 100%;
            margin-bottom: 25px;
        }

        .resumen td {
            width: 33.33%;
            padding: 12px;
            text-align: center;
            border: 1px solid #d1d5db;
        }

        .resumen .titulo {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .resumen .valor {
            font-size: 20px;
            font-weight: bold;
        }

        .aprobado {
            color: #15803d;
        }

        .rechazado {
            color: #b91c1c;
        }

        .pendiente {
            color: #b45309;
        }

        .seccion-titulo {
            margin: 20px 0 10px;
            font-size: 15px;
            font-weight: bold;
            color: #0b2c74;
        }

        table.tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.tabla th,
        table.tabla td {
            border: 1px solid #d1d5db;
            padding: 8px;
            font-size: 11px;
            text-align: left;
        }

        table.tabla th {
            background: #0b2c74;
            color: white;
        }

        .sin-datos {
            text-align: center;
            color: #6b7280;
            padding: 15px;
        }

        .pie {
            margin-top: 30px;
            font-size: 11px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <h1>Reporte de Trámites Académicos</h1>
        <p>Resumen mensual y listado de estudiantes con trámites pendientes</p>
        <p><strong>Mes:</strong> {{ $mesActual }}</p>
    </div>

    <table class="resumen" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="titulo">Trámites Aprobados</div>
                <div class="valor aprobado">{{ $aprobadosMes }}</div>
            </td>
            <td>
                <div class="titulo">Trámites Rechazados</div>
                <div class="valor rechazado">{{ $rechazadosMes }}</div>
            </td>
            <td>
                <div class="titulo">Trámites Pendientes</div>
                <div class="valor pendiente">{{ $pendientesTotal }}</div>
            </td>
        </tr>
    </table>

    <div class="seccion-titulo">Listado de estudiantes con trámites pendientes</div>

    <table class="tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>Estudiante</th>
                <th>Tipo de trámite</th>
                <th>Fecha de solicitud</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tramitesPendientes as $index => $tramite)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $tramite['estudiante'] }}</td>
                    <td>{{ $tramite['tipo'] }}</td>
                    <td>{{ $tramite['fecha_solicitud'] }}</td>
                    <td>{{ $tramite['estado'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="sin-datos">No hay trámites pendientes registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">
        Generado automáticamente por el sistema
    </div>

</body>
</html>
