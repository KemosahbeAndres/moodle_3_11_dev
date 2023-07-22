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

namespace block_sic\app\application;

use block_sic\app\application\contracts\istates_repository;
use block_sic\app\application\contracts\iusers_repository;
use block_sic\app\application\old\list_manager_controller;
use block_sic\app\application\old\list_moderators_controller;
use block_sic\app\application\old\list_students_controller;
use block_sic\app\application\old\list_teachers_controller;
use block_sic\app\domain\course;
use block_sic\app\domain\manager;

class users_finder_controller {
    private $managers;
    private $teachers;
    private $moderators;
    private $students;


    public function __construct(
        iusers_repository $users,
        istates_repository $states
    ) {
        $this->managers = new list_manager_controller($users);
        $this->teachers = new list_teachers_controller($users);
        $this->moderators = new list_moderators_controller($users);
        $this->students = new list_students_controller($users, $states);
    }

    /**
     * @return manager
     */
    public function get_manager(course $course): manager {
        return $this->managers->execute($course);
    }

    /**
     * @return array
     */
    public function get_teachers(course $course):array {
        return $this->teachers->execute($course);
    }

    /**
     * @return array
     */
    public function get_moderators(course $course): array {
        return $this->moderators->execute($course);
    }

    /**
     * @return array
     */
    public function get_students(course $course): array {
        return $this->students->execute($course);
    }



}