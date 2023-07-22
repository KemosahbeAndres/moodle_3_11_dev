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

use block_sic\app\domain\course;
use block_sic\app\domain\state;
use block_sic\app\domain\student;
use block_sic\app\infraestructure\persistence\repository_context;
use block_sic\app\utils\Arrays;

class list_students_controller {
    private $context;

    public function __construct(repository_context $context) {
        $this->context = $context;
    }

    public function __invoke(course $course): array {
        $students = Arrays::void();;
        $users = $this->context->students->related_to($course->get_id());
        foreach ($users as $user) {
            $state = new state(
                $this->context->states->between($user->id, $course->get_id())
            );
            $student = new student(
                $user->id,
                $user->name,
                $user->rut,
                $user->dv
            );
            $student->set_state($state);
            $student->set_course($course);
            $students[] = $student;
        }
        return $students;
    }

}
