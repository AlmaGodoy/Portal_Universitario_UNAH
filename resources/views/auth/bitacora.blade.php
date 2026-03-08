@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Bitácora del Sistema</h3>

    <!-- FORMULARIO DE CONSULTA -->
    <form method="GET" action="{{ route('bitacora') }}" class="mb-3">

        <div>
            <label>Fecha de Inicio: </label>
            <input type="text" name="fecha_inicial" value="{{ request('fecha_inicial') }}">
        </div>

        <div>
            <label>Fecha final: </label>
            <input type="date" name="fecha_final" value="{{ request('fecha_final') }}">
        </div>

        <button type="submit">Buscar</button>
    </form>

    <!-- TABLA DE RESULTADOS -->
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Usuario Responsable</th>
                <th>Operación Realizada</th>
                <th>Detalle</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bitacoras as $item)
                <tr>
                    <td>{{ $item->usuario_responsable }}</td>
                    <td>{{ $item->operacion_realizada }}</td>
                    <td>{{ $item->detalle }}</td>
                    <td>{{ $item->fecha_y_hora }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay resultados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PAGINACIÓN -->
    <!-- « Anterior   1   2   3   Siguiente » -->
    {{ $bitacoras->links() }}

</div>
@endsection
