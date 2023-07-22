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

use block_sic\app\application\contracts\iattendance_repository;
use block_sic\app\application\contracts\icompletion_repository;
use block_sic\app\application\contracts\idedication_repository;
use block_sic\app\application\contracts\igrades_repository;
use block_sic\app\domain\activity;
use block_sic\app\domain\activity_completion;
use block_sic\app\domain\activity_grade;
use block_sic\app\domain\lesson_attendance;
use block_sic\app\domain\section;
use block_sic\app\domain\section_dedication;
use block_sic\app\domain\student;

class load_section_data_controller {
    private $dedications;
    private $progress;
    private $grades;
    private $attendances;

    /**
     * @param idedication_repository $dedications
     * @param icompletion_repository $progress
     * @param igrades_repository $grades
     * @param iattendance_repository $attendances
     */
    public function __construct(
        idedication_repository $dedications,
        icompletion_repository $progress,
        igrades_repository     $grades,
        iattendance_repository $attendances
    ) {
        $this->dedications = $dedications;
        $this->progress = $progress;
        $this->grades = $grades;
        $this->attendances = $attendances;
    }

    public function execute(student $user, array $sections): void {
        /** @var section $section */
        foreach ($sections as $section) {
            $dedication = new section_dedication(
                $section,
                $this->dedications->between($user->get_id(), $section->object())->time
            );
            $user->add_dedication($dedication);
            $activities = $section->get_activities();
            /** @var activity $activity */
            foreach ($activities as $activity) {
                if(!$activity->is_mandatory()) {
                    continue;
                }
                $courseid = $user->get_course()->get_id();
                //echo"<br>";
                //echo"<br>ACTIVIDAD (ID {$activity->get_id()}): ";
                //$grade_item = $this->grades->get_grade($user->get_id(), $activity->__toObject(), $courseid);
                //print_r($grade_item);
                //echo"<br>";
                //$grade = $this->grades->between($user->get_id(), $activity->__toObject());
                $grade = $this->grades->get_grade($user->get_id(), $activity->__toObject(),$courseid);
                if (!is_null($grade)) {
                    $user->add_grade(new activity_grade($activity, $grade->grade));
                }
                $progress = $this->progress->between($user->get_id(), $activity->get_id());
                $user->add_completion(new activity_completion($activity, $progress->completed));
            }
            // Lessons.
            foreach ($section->get_lessons() as $lesson) {
                $attendance = $this->attendances->between(
                    $user->get_id(),
                    $lesson->get_id()
                );
                $user->add_attendance(
                    new lesson_attendance($attendance->id, $lesson, $attendance->assist)
                );
            }

        }
    }


}