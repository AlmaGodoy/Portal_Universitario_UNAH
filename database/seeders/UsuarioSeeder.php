<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $rolSecretariaGeneral = 1;
            $rolAdministrador = 3;
            $rolCoordinador = 4;
            $rolSecretario = 5;

            $idDepartamento = DB::table('tbl_departamento')
                ->orderBy('id_departamento')
                ->value('id_departamento');

            if (!$idDepartamento) {
                throw new \Exception('No existe ningún departamento en tbl_departamento.');
            }

            $usuarios = [
                [
                    'nombre_persona' => 'Administrador Sistema',
                    'correo_institucional' => 'test.admin@unah.edu.hn',
                    'tipo_persona' => 'administrador',
                    'password' => 'Puma2026!',
                    'id_rol' => $rolAdministrador,
                    'cod_empleado' => 'ADM-1001',
                    'tipo_empleado' => 'administrador',
                ],
                [
                    'nombre_persona' => 'Secretario de Carrera',
                    'correo_institucional' => 'test.secre_carrera@unah.edu.hn',
                    'tipo_persona' => 'secretario',
                    'password' => 'Puma2026!',
                    'id_rol' => $rolSecretario,
                    'cod_empleado' => 'SEC-1002',
                    'tipo_empleado' => 'secretario',
                ],
                [
                    'nombre_persona' => 'Coordinador de Carrera',
                    'correo_institucional' => 'test.coordinador@unah.edu.hn',
                    'tipo_persona' => 'coordinador',
                    'password' => 'Puma2026!',
                    'id_rol' => $rolCoordinador,
                    'cod_empleado' => 'COOR-1003',
                    'tipo_empleado' => 'coordinador',
                ],
                [
                    'nombre_persona' => 'Secretaria Académica',
                    'correo_institucional' => 'test.secre_academica@unah.edu.hn',
                    'tipo_persona' => 'secretaria_general',
                    'password' => 'Puma2026!',
                    'id_rol' => $rolSecretariaGeneral,
                    'cod_empleado' => 'SG-1004',
                    'tipo_empleado' => 'secretaria_general',
                ],
            ];

            foreach ($usuarios as $u) {
                $persona = DB::table('tbl_persona')
                    ->where('correo_institucional', $u['correo_institucional'])
                    ->first();

                if (!$persona) {
                    DB::table('tbl_persona')->insert([
                        'nombre_persona' => $u['nombre_persona'],
                        'correo_institucional' => $u['correo_institucional'],
                        'tipo_usuario' => $u['tipo_persona'],
                        'estado' => 1,
                    ]);

                    $idPersona = DB::getPdo()->lastInsertId();
                } else {
                    $idPersona = $persona->id_persona;

                    DB::table('tbl_persona')
                        ->where('id_persona', $idPersona)
                        ->update([
                            'nombre_persona' => $u['nombre_persona'],
                            'tipo_usuario' => $u['tipo_persona'],
                            'estado' => 1,
                        ]);
                }

                $usuario = DB::table('tbl_usuario')
                    ->where('id_persona', $idPersona)
                    ->first();

                if (!$usuario) {
                    DB::table('tbl_usuario')->insert([
                        'id_persona' => $idPersona,
                        'contraseña' => Hash::make($u['password']),
                        'estado_cuenta' => 1,
                        'id_rol' => $u['id_rol'],
                    ]);

                    $idUsuario = DB::getPdo()->lastInsertId();
                } else {
                    $idUsuario = $usuario->id_usuario;

                    DB::table('tbl_usuario')
                        ->where('id_usuario', $idUsuario)
                        ->update([
                            'id_rol' => $u['id_rol'],
                            'estado_cuenta' => 1,
                        ]);
                }

                $empleado = DB::table('tbl_empleados')
                    ->where('id_persona', $idPersona)
                    ->first();

                if (!$empleado) {
                    DB::table('tbl_empleados')->insert([
                        'id_persona' => $idPersona,
                        'id_departamento' => $idDepartamento,
                        'cod_empleado' => $u['cod_empleado'],
                        'tipo_usuario' => $u['tipo_empleado'],
                    ]);
                } else {
                    DB::table('tbl_empleados')
                        ->where('id_persona', $idPersona)
                        ->update([
                            'id_departamento' => $idDepartamento,
                            'cod_empleado' => $u['cod_empleado'],
                            'tipo_usuario' => $u['tipo_empleado'],
                        ]);
                }

                DB::table('tbl_bitacora')->insert([
                    'id_usuario' => $idUsuario,
                    'id_objeto' => null,
                    'accion' => 'registro_usuario_seeder',
                    'fecha_accion' => now(),
                    'descripcion' => 'Usuario creado/actualizado por seeder: ' . $u['correo_institucional'],
                ]);
            }

            DB::commit();
            $this->command->info('Los 4 usuarios especiales fueron creados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Error en UsuarioSeeder: ' . $e->getMessage());
            throw $e;
        }
    }
}
