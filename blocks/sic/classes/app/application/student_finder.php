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

use block_sic\app\domain\student;
use block_sic\app\infraestructure\persistence\repository_context;

class student_finder {

    /**
     * @var repository_context
     */
    private $context;
    /**
     * @var load_course_data_controller
     */
    private $dataLoader;
    /**
     * @var consult_course_controller
     */
    private $courseFinder;

    /**
     * @param repository_context $context
     */
    public function __construct(repository_context $context) {
        $this->context = $context;
        $this->dataLoader = new load_course_data_controller($context);
        $this->courseFinder = new consult_course_controller($context);
    }

    public function execute(int $userid, int $courseid): ?student {
        $course = $this->courseFinder->execute($courseid);
        $students = $this->context->students->execute($courseid);
        $student = null;
        /** @var student $st */
        foreach ($students as $st){
            if($st->get_id() == $userid){
                $student = $st;
                break;
            }
        }
        if(!is_null($student)){
            $student->set_course($course);
            $this->dataLoader->execute($student);
        }
        return $student;
    }

    public function all(int $courseid): array {
        $course = $this->courseFinder->execute($courseid);
        $students = $this->context->students->execute($courseid);
        /** @var student $student */
        foreach ($students as $key=>$student){
            $student->set_course($course);
            $this->dataLoader->execute($student);
        }
        return $students;
    }

}