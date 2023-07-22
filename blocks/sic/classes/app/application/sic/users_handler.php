<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides meta-data about the plugin.
 *
 * @package     block_sic
 * @author      {2023} {Andres Cubillos Salazar}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sic\app\application\sic;

use block_sic\app\domain\activity;
use block_sic\app\domain\course;
use block_sic\app\domain\module;
use block_sic\app\domain\student;
use block_sic\app\utils\Dates;

class users_handler extends abstract_handler {
    /**
     * @var array
     */
    protected $students;
    /**
     * @var course
     */
    protected $course;

    /**
     * @param course $course
     * @param array $students
     */
    public function __construct(course $course, array $students) {
        $this->course = $course;
        $this->students = $students;
    }

    public function handle($request): ?object {

        try {
            $request->listaAlumnos = array();
            /** @var student $student */
            foreach ($this->students as $student) {
                if (!$student->is_active()) continue;
                $alumno = new \stdClass();

                // ALUMNO
                $alumno->rutAlumno = $student->get_rut();
                $alumno->dvAlumno = $student->get_dv();
                $alumno->tiempoConectividad = $student->get_connection_time();
                $alumno->evaluacionFinal = $student->get_average();
                $alumno->estado = $student->get_state()->get_code();
                $alumno->porcentajeAvance = $student->get_progress();
                $alumno->fechaInicio = Dates::format_date_time($this->course->get_startdate());
                $alumno->fechaFin = Dates::format_date_time($this->course->get_enddate());

                //MODULOS
                $alumno->listaModulos = array();
                /** @var module $module */
                foreach ($this->course->get_modules() as $module) {
                    $modulo = new \stdClass();

                    $modulo->codigoModulo = $module->get_code();
                    $modulo->tiempoConectividad = $student->get_module_connection_time($module);
                    $modulo->porcentajeAvance = $student->get_module_progress($module);
                    $state_code = $student->get_state()->get_code();
                    $code = 1;
                    $average = $student->get_module_average($module);
                    if($state_code > 1 && $state_code < 4 && $modulo->porcentajeAvance == 100){
                        if( $average < 40){
                            $code = 3;
                        }else {
                            $code = 2;
                        }
                    }
                    $modulo->estado = $state_code;
                    $modulo->fechaInicio = Dates::format_date_time($module->get_startdate());
                    $modulo->fechaFin = Dates::format_date_time($module->get_enddate());
                    $modulo->notaModulo = $average;
                    $modulo->cantActividadAsincronica = $module->get_async_amount();
                    $modulo->cantActividadSincronica = $module->get_sync_amount();

                    //ACTIVIDADES
                    $modulo->listaActividades = array();
                    /** @var activity $activity */
                    foreach ($module->get_activities() as $activity) {
                        if ($activity->is_mandatory()) {
                            $actividad = new \stdClass();

                            $actividad->codigoActividad = $activity->get_code();

                            $modulo->listaActividades[] = $actividad;
                        }
                    }

                    $alumno->listaModulos[] = $modulo;
                }

                $request->listaAlumnos[] = $alumno;
            }
        }catch(\exception $e) {
            return null;
        }
        return parent::handle($request);
    }

}