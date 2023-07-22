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

use block_sic\app\application\contracts\ilessons_repository;
use block_sic\app\domain\activity;
use block_sic\app\domain\course;
use block_sic\app\domain\module;
use block_sic\app\domain\section;
use block_sic\app\domain\session;
use stdClass;

final class attach_lesson_controller {
    private $lessons;
    public function __construct(ilessons_repository $repo) {
        $this->lessons = $repo;
    }

    public function execute(session $params) {
        $post = $params->get_post();

        $course = $params->get_course();

        if ($post->action != "attach_lesson") {
            return;
        }

        $data = json_decode($post->data);

        $activityid = intval($data->activityid);

        if(!$this->activity_exists($activityid, $course->get_activities())) {
            return;
        }

        $section = $this->section_from_activity($activityid, $params->get_course());

        if(is_null($section)) return;

        $lesson = new stdClass();
        $lesson->id = intval($data->id);
        $lesson->date = intval($data->date) + 86400;
        $lesson->duration = intval($data->duration);

        var_dump($lesson);

        $this->lessons->attach_to($lesson, $section->get_id(), $activityid);

    }

    private function activity_exists(int $id, array $haystack): bool {
        if($id <= 0) return false;
        /** @var activity $activity */
        foreach ($haystack as $activity){
            if($activity->get_id() == $id){
                //echo "<br>FOUND<br>";
                return true;
            }
        }
        return false;
    }

    private function section_from_activity(int $id, course $course): ?section {
        /** @var module $module */
        foreach ($course->get_modules() as $module){
            /** @var section $section */
            foreach ($module->get_sections() as $section){
                if($this->activity_exists($id, $section->get_activities())) {
                    return $section;
                }
            }
        }
        return null;
    }

}
