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

namespace block_sic\app\controller;

use block_sic\app\application\consult_course_controller;
use block_sic\app\application\participants_finder;
use block_sic\app\application\prepare_json_controller;
use block_sic\app\application\student_finder;
use block_sic\app\domain\activity;
use block_sic\app\domain\lesson;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\section;
use block_sic\app\domain\sic\sic_response;
use block_sic\app\domain\student;
use block_sic\app\infraestructure\api\connection_manager;
use block_sic\app\infraestructure\persistence\repository_context;

class course_controller extends controller {
    /**
     * @var consult_course_controller
     */
    private $courseLoader;
    /**
     * @var participants_finder
     */
    private $participantsFinder;
    private $jsonPrepare;
    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->courseLoader = new consult_course_controller($context);
        $this->participantsFinder = new participants_finder($context);
    }

    public function index(request $request): response {
        $course = $this->courseLoader->execute($request->params->courseid);
        $this->content->course = $course->__toObject();
        //var_dump($this->content);
        return $this->response('course/course');
    }

    public function participants(request $request): response {
        $course = $this->courseLoader->execute($request->params->courseid);
        $this->content->participants = $this->participantsFinder->execute($course);
        return $this->response('participants/participants');
    }

    /**
     * @throws \dml_exception
     */
    public function free_sections(request $request): response {
        $this->content->coursepage = true;
        $course = $this->courseLoader->execute($request->params->courseid);
        $sections = $course->get_excluded_sections();
        $this->content->sections = array();
        /** @var section $section */
        foreach($sections as $section){
            $this->content->sections[] = $section->__toObject();
        }
        return $this->response('course/freesections');
    }

    public function lessons(request $request): response {
        $this->content->coursepage = true;
        $courseid = intval($request->params->courseid);
        $curso = $this->courseLoader->execute($courseid);
        $lecciones = $curso->get_lessons();
        $this->content->lessons = array();
        /** @var lesson $leccion */
        foreach ($lecciones as $leccion) {
            $this->content->lessons[] = $leccion->__toObject();
        }
        return $this->response('course/lessons/lessons');
    }


}