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

use block_sic\app\domain\course;
use block_sic\app\domain\module;

class modules_handler extends abstract_handler {
    /**
     * @var course
     */
    protected $course;

    /**
     * @param course $course
     */
    public function __construct(course $course) {
        $this->course = $course;
    }

    public function handle(object $request): ?object {

        foreach ($request->listaAlumnos as $key=>$alumno){
            $request->listaAlumnos[$key]->listaModulos = array();
            /** @var module $module */
            foreach ($this->course->get_modules() as $module){
                $modulo = new \stdClass();

                $modulo->codigoModulo = $module->get_code();

                $request->listaAlumnos[$key]->listaModulos[] = $modulo;
            }
        }

        return parent::handle($request);
    }


}