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
use block_sic\app\application\load_course_data_controller;
use block_sic\app\application\student_finder;
use block_sic\app\domain\activity;
use block_sic\app\domain\lesson;
use block_sic\app\domain\request;
use block_sic\app\domain\response;
use block_sic\app\infraestructure\persistence\repository_context;

class student_controller extends controller {

    private $studentFinder;
    /**
     * @param repository_context $context
     */
    public function __construct(repository_context $context) {
        parent::__construct($context);
        $this->content->participantspage = true;
        $this->studentFinder = new student_finder($context);
    }

    public function details(request $request): response {
        $model = new \stdClass();
        try{
            $id = intval($request->params->id);
            $courseid = intval($request->params->courseid);
            $student = $this->studentFinder->execute($id, $courseid);
            $model = $student->__toObject();
            $model->activities = array();
            /** @var activity $activity */
            foreach ($student->get_course()->get_activities() as $activity){
                $completion = $student->get_completion($activity);
                $grade = $student->get_grade($activity);
                $data = $activity->__toObject();
                $data->completed = "NO";
                if(!is_null($completion)){
                    $data->completed = $completion->completed() ? "SI" : "NO";
                }
                if(!is_null($grade)){
                    $data->grade = $grade->get_grade();
                }
                $model->activities[] = $data;
            }
            $model->lessons = array();
            /** @var lesson $lesson */
            foreach ($student->get_course()->get_lessons() as $lesson){
                $attendance = $student->get_attendance($lesson);
                $data = $lesson->__toObject();
                $data->assist = "NO";
                if(!is_null($attendance)){
                    $data->assist = $attendance->is_present() ? "SI" : "NO";
                }
                $model->lessons[] = $data;
            }
        }catch (\exception $e){}
        $this->content->student = $model;
        return $this->response('participants/students/details');
    }

    public function change_state(request $request): response {
        $courseid = intval($request->params->courseid);
        $studentid = intval($request->params->studentid);
        $student = $this->studentFinder->execute($studentid, $courseid);
        $this->content->student = $student->__toObject();
        return $this->response('participants/students/state');
    }

    public function confirm_change_state(request $request): response {
        $message = "No se realizo ningun cambio!";
        try {
            $courseid = intval($request->params->courseid);
            $studentid = intval($request->params->studentid);
            $statecode = intval($request->params->statecode);
            if($studentid <= 0 or $courseid <= 0) throw new \exception($message);
            if($statecode > 0 and $statecode < 4){
                $state = $this->context->states->by_code($statecode);
                $this->context->states->attach_to($state, $studentid, $courseid);
                $message = "Se cambio el estado del alumnno id '{$studentid}' a '{$state->estado}' con exito!";
            }
        }catch (\exception $e) {}
        return $this->redirect('participants', $request->params, $message);
    }
    public function exclude_include(request $request): response {
        $message = "No se realizo ningun cambio!";
        try{
            $courseid = intval($request->params->courseid);
            $studentid = intval($request->params->studentid);
            $include = intval($request->params->include);
            if($include == 1){
                $this->context->students->set_valid($studentid, $courseid);
                $message = "Estudiante incluido en los registros!";
            }else {
                $this->context->students->set_invalid($studentid, $courseid);
                $message = "Estudiante excluido de los registros!";
            }
        }catch (\exception $e) {}
        return $this->redirect('participants', $request->params, $message);
    }

    public function attendance(request $request): response {
        $model = new \stdClass();
        try{
            $studentid = intval($request->params->studentid);
            $courseid = intval($request->params->courseid);
            $student = $this->studentFinder->execute($studentid, $courseid);
            $model = $student->__toObject();
            $model->lessons = array();
            /** @var lesson $lesson */
            foreach ($student->get_course()->get_lessons() as $lesson){
                $attendance = $student->get_attendance($lesson);
                $data = $lesson->__toObject();
                $data->assist = "NO";
                if(!is_null($attendance)){
                    $data->assist = $attendance->is_present() ? "SI" : "NO";
                    $data->assist_check = $attendance->is_present();
                }
                $model->lessons[] = $data;
            }
        }catch (\exception $e){}
        $this->content->student = $model;
        return $this->response('participants/students/attendance');
    }

    // Actualizar asistencia
    public function save_attendance(request $request): response {
        $message = "No se realizo ningun cambio!";
        try{
            $courseid = intval($request->params->courseid);
            $studentid = intval($request->params->studentid);
            $student = $this->studentFinder->execute($studentid, $courseid);
            $lessons = $student->get_course()->get_lessons();
            $changes = "<br>";
            /** @var lesson $lesson */
            foreach($lessons as $lesson){
                $att = $this->context->attendances->between($student->get_id(), $lesson->get_id());
                if(isset($request->params->{"lesson_".$lesson->get_id()})){
                    $att->assist = true;
                    $changes .= $lesson->get_code() . " => SI <br>";
                }else{
                    $att->assist = false;
                    $changes .= $lesson->get_code() . " => NO <br>";
                }
                $this->context->attendances->attach_to($att, $student->get_id(), $lesson->get_id());
                $message = "Se actualizaron las clases: " . $changes;
            }
        }catch(\exception $e) {}
        return $this->redirect('participants', $request->params, $message);
    }

}