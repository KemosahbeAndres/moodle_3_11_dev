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
use block_sic\app\domain\activity;
use block_sic\app\domain\course;
use block_sic\app\domain\lesson;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\domain\section;
use block_sic\app\infraestructure\persistence\repository_context;

class lesson_controller extends controller {
    /**
     * @var consult_course_controller
     */
    private $courseLoader;

    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->content->coursepage = true;
        $this->courseLoader = new consult_course_controller($context);
    }

    public function create(request $request): response {
        $courseid = intval($request->params->courseid);
        $curso = $this->courseLoader->execute($courseid);
        $this->content->activities = array();
        /** @var activity $activity */
        foreach ($curso->get_activities() as $activity){
            $is_lesson = false;
            /** @var lesson $lesson */
            foreach ($curso->get_lessons() as $lesson){
                if($activity->equal($lesson->get_activity())) {
                    $is_lesson = true;
                    break;
                }
            }
            if($is_lesson) continue;
            $this->content->activities[] = $activity->__toObject();
        }
        return $this->response('course/lessons/create');
    }

    public function edit(request $request): response {
        $lessonid = $request->get_int('lessonid');
        $courseid = $request->get_int('courseid');
        $course = $this->courseLoader->execute($courseid);
        $lesson = $course->get_lesson($lessonid);
        $this->content->lesson = is_null($lesson) ? null : $lesson->basicObject();
        $this->content->activities = array();
        /** @var activity $activity */
        foreach ($course->get_activities() as $activity){
            $is_lesson = false;
            /** @var lesson $lesson */
            foreach ($course->get_lessons() as $lesson){
                if($activity->equal($lesson->get_activity())) {
                    $is_lesson = true;
                    break;
                }
            }
            if($is_lesson) continue;
            $actividad = $activity->__toObject();
            $actividad->selected = $activity->equal($lesson->get_activity());
            $this->content->activities[] = $actividad;
        }
        return $this->response('course/lessons/edit');
    }

    public function save(request $request): response {
        $message = "No se realizo ningun cambio!";
        try{
            $model = new \stdClass();

            $courseid = $request->get_int('courseid');
            $activityid = $request->get_int('activityid');

            $datetime = strtotime($request->get_string('datetime'));
            $duration = $request->get_int('duration');

            if($request->exists('lessonid')){
                $lessonid = $request->get_int('lessonid');
                $model->id = $lessonid;
            }

            if($courseid <= 0 or $activityid <= 0 or $datetime < 0 or $duration < 0) {
                throw new \exception($message);
            }

            $sectionid = 0;
            /** @var section $section */
            foreach($this->courseLoader->execute($courseid)->get_mdl_sections() as $section){
                if($section->have($activityid)){
                    $sectionid = $section->get_id();
                }
            }

            if($sectionid <= 0) throw new \exception($message);

            $model->date = $datetime;
            $model->duration = $duration;

            echo "<br>Guardando ID Actividad: ";
            print_r($activityid);
            echo "<br>";

            $this->context->lessons->attach_to($model, $sectionid, $activityid);
            $message = ($this->context->lessons->have($activityid) and !isset($lessonid)) ? "Clase creada con exito!" : "Encontramos una Clase existente! Clase modificada con exito!";
        }catch(\exception $e) {}

        return $this->redirect('lessons', $request->params, $message);
    }

    public function delete(request $request): response {
        $lessonid = $request->get_int('lessonid');
        $courseid = $request->get_int('courseid');
        $course = $this->courseLoader->execute($courseid);
        $lesson = $course->get_lesson($lessonid);
        $this->content->lesson = is_null($lesson) ? null : $lesson->__toObject();
        return $this->response('course/lessons/delete');
    }

    public function confirm_delete(request $request): response {
        try{
            $lessonid = $request->get_int('lessonid');
            if($lessonid <= 0) throw new \exception();
            $this->context->lessons->dettach($lessonid);
            $message = "Clase eliminada con exito!";
        }catch(\exception $e) {
            return $this->redirect('lessons', $request->params);
        }
        return $this->redirect('lessons', $request->params, $message);
    }

}