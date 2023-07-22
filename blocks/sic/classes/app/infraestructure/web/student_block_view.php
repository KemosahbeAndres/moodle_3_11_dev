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

namespace block_sic\app\infraestructure\web;

use block_sic\app\domain\user;

class student_block_view {
    private $user;

    public function __construct(user $user) {
        $this->user = $user;
    }

    public function render(): string {
        $curso = $this->user->get_course();
        $avance = 0;
        $tiempo = 0;

        if (empty($curso->get_modules())) {
            $avance = $curso->get_mdl_progress();
            $tiempo = $curso->get_mdl_dedication();
        } else {
            $avance = $curso->get_course_progress();
            $tiempo = $curso->get_course_connection_time();
        }

        return "<h3>Complemento en desarrollo</h3><br><p>Avance: {$avance}%</p><br><p>Tiempo de conexion: {$tiempo}</p>";

    }

}
