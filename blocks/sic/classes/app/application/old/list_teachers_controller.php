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

namespace block_sic\app\application\old;

use block_sic\app\application\contracts\iusers_repository;
use block_sic\app\domain\course;
use block_sic\app\domain\teacher;

class list_teachers_controller {
    private $users;

    public function __construct(iusers_repository $users) {
        $this->users = $users;
    }

    public function execute(course $course): array {
        $teachers = $this->users->teachers_of($course->get_id());
        $output = array();
        foreach ($teachers as $user) {
            $teacher = new teacher(
                $user->id,
                $user->name,
                $user->rut,
                $user->dv
            );
            $teacher->set_course($course);
            $output[] = $teacher;
        }
        return $output;
    }

}