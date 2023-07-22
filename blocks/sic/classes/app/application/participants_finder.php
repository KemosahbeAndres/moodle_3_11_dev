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

use block_sic\app\domain\course;
use block_sic\app\domain\moderator;
use block_sic\app\domain\student;
use block_sic\app\domain\teacher;
use block_sic\app\infraestructure\persistence\repository_context;

class participants_finder {

    /**
     * @var repository_context $context
     */
    private $context;
    private $dataloader;
    public function __construct(repository_context $context) {
        $this->context = $context;
        $this->dataloader = new load_course_data_controller($context);
    }

    public function execute(course $course): object {
        $participants = new \stdClass();
        // Gestor Curso
        $manager = $this->context->managers->execute($course->get_id());
        if($manager){
            $manager->set_course($course);
            $participants->manager = $manager->__toObject();
        }
        // Moderadores
        $participants->moderators = array();
        $moderators = $this->context->moderators->execute($course->get_id());
        /** @var moderator $moderator */
        foreach($moderators as $moderator) {
            $moderator->set_course($course);
            $participants->moderators[] = $moderator->__toObject();
        }
        // Profesores
        $participants->teachers = array();
        $teachers = $this->context->teachers->execute($course->get_id());
        /** @var teacher $teacher */
        foreach($teachers as $teacher) {
            $teacher->set_course($course);
            $participants->teachers[] = $teacher->__toObject();
        }
        // Estudiantes
        $participants->students = array();
        /** @var student $student */
        foreach($this->context->students->execute($course->get_id()) as $student){
            $student->set_course($course);
            $this->dataloader->execute($student);
            $participants->students[] = $student->__toObject();
        }
        return $participants;
    }

}