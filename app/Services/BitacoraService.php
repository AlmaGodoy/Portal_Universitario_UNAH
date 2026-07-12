<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class BitacoraService
{
    /**
     * Consulta la bitácora de trámites para una carrera determinada.
     *
     * SEL_BITACORA_TRAMITE_NUEVO recibe 13 parámetros.
     */
    public function consultarBitacoraTramites(
        Request $request,
        array $filtros,
        int $carreraContextoObligatoria
    ): Paginator {
        $porPagina = $this->obtenerLimite($request);

        $pagina = max(
            (int) $request->query('page', 1),
            1
        );

        $desplazamiento = ($pagina - 1) * $porPagina;

        $resultados = $this->ejecutarProcedimiento(
            'CALL SEL_BITACORA_TRAMITE_NUEVO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )',
            [
                $filtros['fecha_inicio'],        // 1
                $filtros['fecha_fin'],           // 2
                $filtros['id_tramite'],          // 3
                $filtros['id_usuario'],          // 4
                $filtros['id_rol'],              // 5
                $filtros['id_carrera_actor'],    // 6
                $carreraContextoObligatoria,     // 7
                $filtros['tipo_tramite'],        // 8
                $filtros['estado'],              // 9
                $filtros['accion'],              // 10
                $filtros['nivel'],               // 11
                $porPagina + 1,                  // 12
                $desplazamiento,                 // 13
            ]
        );

        return $this->crearPaginador(
            resultados: $resultados,
            request: $request,
            porPagina: $porPagina,
            pagina: $pagina
        );
    }

    /**
     * Consulta la bitácora global del sistema.
     *
     * SEL_BITACORA_SISTEMA_NUEVO recibe 13 parámetros.
     */
    public function consultarBitacoraSistema(
        Request $request,
        array $filtros
    ): Paginator {
        $porPagina = $this->obtenerLimite($request);

        $pagina = max(
            (int) $request->query('page', 1),
            1
        );

        $desplazamiento = ($pagina - 1) * $porPagina;

        $resultados = $this->ejecutarProcedimiento(
            'CALL SEL_BITACORA_SISTEMA_NUEVO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )',
            [
                $filtros['fecha_inicio'],          // 1
                $filtros['fecha_fin'],             // 2
                $filtros['id_usuario'],            // 3
                $filtros['id_rol'],                // 4
                $filtros['id_carrera_actor'],      // 5
                $filtros['id_carrera_contexto'],   // 6
                $filtros['id_objeto'],             // 7
                $filtros['id_tramite'],            // 8
                $filtros['modulo'],                // 9
                $filtros['accion'],                // 10
                $filtros['nivel'],                 // 11
                $porPagina + 1,                    // 12
                $desplazamiento,                   // 13
            ]
        );

        return $this->crearPaginador(
            resultados: $resultados,
            request: $request,
            porPagina: $porPagina,
            pagina: $pagina
        );
    }

    /**
     * Ejecuta un procedimiento almacenado utilizando PDO.
     *
     * También consume los conjuntos de resultados adicionales para
     * evitar el error "Commands out of sync".
     */
    private function ejecutarProcedimiento(
        string $consulta,
        array $parametros
    ): array {
        $pdo = DB::connection()->getPdo();
        $sentencia = null;

        try {
            $sentencia = $pdo->prepare($consulta);

            foreach ($parametros as $indice => $valor) {
                $tipo = match (true) {
                    $valor === null => PDO::PARAM_NULL,
                    is_int($valor) => PDO::PARAM_INT,
                    is_bool($valor) => PDO::PARAM_BOOL,
                    default => PDO::PARAM_STR,
                };

                $sentencia->bindValue(
                    $indice + 1,
                    $valor,
                    $tipo
                );
            }

            $sentencia->execute();

            $resultados = $sentencia->fetchAll(
                PDO::FETCH_OBJ
            );

            while ($sentencia->nextRowset()) {
                // Consumir conjuntos de resultados adicionales.
            }

            return $resultados;
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        } finally {
            if ($sentencia !== null) {
                $sentencia->closeCursor();
            }
        }
    }

    /**
     * Construye el paginador de los registros obtenidos.
     *
     * Se solicita un registro adicional para saber si existe
     * una página siguiente.
     */
    private function crearPaginador(
        array $resultados,
        Request $request,
        int $porPagina,
        int $pagina
    ): Paginator {
        $hayMasPaginas = count($resultados) > $porPagina;

        if ($hayMasPaginas) {
            array_pop($resultados);
        }

        $paginador = new Paginator(
            $resultados,
            $porPagina,
            $pagina,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
                'pageName' => 'page',
            ]
        );

        $paginador->hasMorePagesWhen(
            $hayMasPaginas
        );

        return $paginador;
    }

    /**
     * Obtiene la cantidad permitida de registros por página.
     */
    private function obtenerLimite(
        Request $request
    ): int {
        $porPagina = (int) $request->input(
            'per_page',
            20
        );

        return min(
            max($porPagina, 10),
            100
        );
    }
}